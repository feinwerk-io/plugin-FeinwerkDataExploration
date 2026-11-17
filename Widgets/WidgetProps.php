<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Widgets;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\FeinwerkDataExploration\Engine\AccessPolicy;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\FeinwerkDataExploration\Engine\RawDataRetention;
use Piwik\Plugins\FeinwerkDataExploration\Feedback\PluginFeedback;
use Piwik\Plugins\FeinwerkDataExploration\Promo\PremiumPromo;
use Piwik\Plugins\FeinwerkDataExploration\Promo\PromoLine;
use Piwik\Translation\Translator;

/**
 * The props both exploration pages (free-form table, funnel) get from PHP.
 */
final class WidgetProps
{
    /**
     * Both pages show the premium banner below their result (Peter, 2026-11-17).
     *
     * @return array{idSite: int, maxDays: int, rawDataFrom: ?string, canShare: bool, canCopy: bool, feedback: array, premium: ?array, promo: ?array}
     */
    public static function common(int $idSite, Limits $limits): array
    {
        return [
            'idSite' => $idSite,
            'maxDays' => $limits->maxDays,
            'rawDataFrom' => RawDataRetention::earliestCompleteDay($idSite),
            'canShare' => AccessPolicy::canShare($idSite),
            'canCopy' => AccessPolicy::canCopy(),
            'feedback' => PluginFeedback::clientProps(
                StaticContainer::get(PluginFeedback::class)->isEnabled(),
                (string) StaticContainer::get(Translator::class)->getCurrentLanguage(),
                (string) PluginManager::getInstance()->getLoadedPlugin(PluginFeedback::PLUGIN)->getVersion()
            ),
        ] + self::promotions(self::isEmbedded(), [PremiumPromo::class, 'bannerProps'], static function () use ($idSite): ?array {
            return PromoLine::current($idSite);
        });
    }

    /**
     * The Feinwerk notices of the page (promotion kit, docs/promotions.md), one at a time: the premium banner below the
     * result (Promo\PremiumPromo, Peter 2026-11-17) and, while it has no plugin, the tip line under the result
     * (Promo\PromoLine). Each is null for users without admin access and when promotions are off; both are null in
     * embeds. A failing promotion empties only its own prop and never breaks the page.
     *
     * @param callable(): ?array $banner
     * @param callable(): ?array $line
     * @return array{premium: ?array, promo: ?array}
     */
    public static function promotions(bool $embedded, callable $banner, callable $line): array
    {
        if ($embedded) {
            return ['premium' => null, 'promo' => null];
        }
        $premium = self::guarded($banner);

        return ['premium' => $premium, 'promo' => $premium === null ? self::guarded($line) : null];
    }

    /** @param callable(): ?array $promotion */
    private static function guarded(callable $promotion): ?array
    {
        try {
            return $promotion();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Widgetize and other embeds show no notices; a failing check counts as embedded. */
    private static function isEmbedded(): bool
    {
        try {
            $module = trim(Common::getRequestVar('module', '', 'string')); // Matomo 6 does not trim
            $disableLink = Common::getRequestVar('disableLink', 0, 'int');

            return PromoLine::isEmbedded($module, $disableLink);
        } catch (\Throwable $e) {
            return true;
        }
    }
}
