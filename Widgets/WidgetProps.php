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
use Piwik\Plugins\FeinwerkDataExploration\Promo\PromoLine;
use Piwik\Translation\Translator;

/**
 * The props both exploration pages (free-form table, funnel) get from PHP.
 */
final class WidgetProps
{
    /** @return array{idSite: int, maxDays: int, rawDataFrom: ?string, canShare: bool, canCopy: bool, feedback: array, promo: ?array} */
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
            'promo' => self::promo($idSite),
        ];
    }

    /**
     * The tip line under the result (Feinwerk promotion kit, Promo\PromoLine): null for users without admin access,
     * when promotions are off and in embeds; a failing promotion never breaks the page.
     *
     * @return array<string, mixed>|null
     */
    private static function promo(int $idSite): ?array
    {
        try {
            $module = trim(Common::getRequestVar('module', '', 'string')); // Matomo 6 does not trim
            $disableLink = Common::getRequestVar('disableLink', 0, 'int');
            return PromoLine::isEmbedded($module, $disableLink) ? null : PromoLine::current($idSite);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
