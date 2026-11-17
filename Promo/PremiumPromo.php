<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Promo;

use Piwik\Piwik;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Version;

/**
 * This plugin's primary placement (Peter, 2026-11-17: "Das Plugin ist so super, dafür müssen wir noch einen besseren
 * Werbeplatz einbauen. Bitte ein randloses Banner unter dem Hauptfenster mit Premium-Plugin Rotation"; design approved
 * "2. yes", docs/superpowers/specs/2026-11-17-premium-banner-design.md): paid Feinwerk plugins this Matomo does not have
 * yet, as a borderless banner below the result card, in place of the tip line under the result (WidgetProps). The
 * kit's rotation and fallback list (Page Filter Report main 99c4100); the page renders it (vue/src/PremiumBanner.vue).
 *
 * Who and from where (Peter, 2026-11-16, docs/promotions.md: admins too, and without a store connection):
 * - the connector's live list first: Promotions::paidPluginRecommendations() (connector 5.5.0+; admins and super
 *   users; super users get its install link, admins the public store page), else Promotions::paidPluginsNotInstalled()
 *   (connector 5.4.0, super users only);
 * - otherwise this plugin's fallback list (PaidPluginCatalog): the plugins the store sells, minus the ones present here;
 * - nothing when promotions are switched off, here (`promotions = 0`) or in the connector (PromoLine::enabled()).
 * Not inside Matomo for WordPress (Peter, 2026-11-17: "Dort nicht werben"): paid plugins cannot be installed there
 * yet (store ZIPs are no WordPress plugins), so the tile and the card stay empty, in demo too.
 * Demo mode (PromoLine::demo(), test instances): the connector's list topped up from the whole fallback list,
 * installed plugins and build floors ignored, every entry in the rotation, × keeps one away for the session.
 * Store links open in a new tab with the house campaign parameters; admins read one line that their super user can
 * install it. The order turns by one plugin each day; in the browser the first one not hidden shows, its x hides it
 * for 30 days and the next follows (javascripts/promocard.js).
 */
final class PremiumPromo
{
    /** how many candidates a placement carries for the rotation */
    public const MAX = 3;
    public const CONNECTOR = 'FeinwerkMarketplaceConnector';
    public const HELPER = 'Piwik\\Plugins\\FeinwerkMarketplaceConnector\\Promotions';

    /** campaign parameters of every store link (docs/promotions.md); mtm_content is rec-<slug> */
    public const CAMPAIGN = 'mtm_campaign=plugin-promo&mtm_source=FeinwerkDataExploration&mtm_content=rec-';

    /** @var array<int, array<string, string>>|null the candidates of this request */
    private static $current = null;

    /**
     * The candidates for the current user in today's order, [] when there are none or the promotion is switched off.
     *
     * @return array<int, array<string, string>>
     */
    public static function plugins(): array
    {
        if (self::$current === null) {
            try {
                // shownToCurrentUser(): every off switch, the connector's setting included, and the audience
                $demo = PromoLine::shownToCurrentUser() && PromoLine::demo();
                self::$current = PromoLine::shownToCurrentUser()
                    ? self::choose(self::valid(self::fromConnector(self::MAX)), static function () use ($demo): array {
                        return self::fromCatalog($demo);
                    }, intdiv(time(), 86400), $demo, PromoLine::isWordPress())
                    : [];
            } catch (\Throwable $e) {
                self::$current = [];
            }
        }

        return self::$current;
    }

    /**
     * The connector's live list when it has one, else the fallback list; turned by one each day, at most MAX. In demo
     * mode the connector's list topped up with every fallback entry it lacks, all of them in the rotation.
     *
     * @param array<int, array<string, string>> $fromConnector valid entries of the connector
     * @param callable(): array<int, mixed>     $fallback      the fallback list (asked when the connector has none, or in demo)
     * @return array<int, array<string, string>>
     */
    public static function choose(array $fromConnector, callable $fallback, int $day, bool $demo = false, bool $wordpress = false): array
    {
        if ($wordpress) {
            return [];
        }
        if ($demo) {
            $slugs = array_column($fromConnector, 'slug');
            foreach (self::valid($fallback()) as $entry) {
                if (!in_array($entry['slug'], $slugs, true)) {
                    $fromConnector[] = $entry;
                }
            }

            return self::rotate($fromConnector, $day);
        }
        $list = $fromConnector !== [] ? $fromConnector : self::valid($fallback());

        return array_slice(self::rotate($list, $day), 0, self::MAX);
    }

    /**
     * The fallback list for this Matomo: the store's paid plugins (PaidPluginCatalog) with a build for this Matomo whose
     * folder is not here, in the viewer's language. One scan of the plugins directory. In demo mode the whole list.
     *
     * @return array<int, array<string, string>>
     */
    public static function fromCatalog(bool $demo = false): array
    {
        try {
            if ($demo) {
                return PaidPluginCatalog::all(PromoLine::language(), [Piwik::class, 'translate'], PromoLine::PLUGIN);
            }

            return PaidPluginCatalog::notInstalled(
                (array) PluginManager::getInstance()->readPluginsDirectory(),
                Version::VERSION,
                PromoLine::language(),
                [Piwik::class, 'translate'],
                PromoLine::PLUGIN
            );
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** for tests: forget the candidates of this request */
    public static function reset(): void
    {
        self::$current = null;
    }

    /**
     * The connector's shared helper, guarded: paidPluginRecommendations() when the connector has it (admins and super
     * users), else paidPluginsNotInstalled() (super users). [] when the connector is not active, too old for both
     * helpers (before its 2026-11-13 release), not connected, or a helper fails. Never the connector's internals.
     *
     * @param callable|null $isActive test seam: whether the connector is activated (default: Matomo's plugin manager)
     * @param string        $helper   test seam: the helper class
     * @return array<int, mixed>
     */
    public static function fromConnector(int $max, ?callable $isActive = null, string $helper = self::HELPER): array
    {
        try {
            $isActive = $isActive ?? static function (): bool {
                return PluginManager::getInstance()->isPluginActivated(self::CONNECTOR);
            };
            if (!$isActive() || !class_exists($helper)) {
                return [];
            }
            foreach (['paidPluginRecommendations', 'paidPluginsNotInstalled'] as $method) {
                if (method_exists($helper, $method)) {
                    return (array) call_user_func([$helper, $method], $max);
                }
            }

            return [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Only entries this plugin can show: a Feinwerk slug, a name and a link, either inside Matomo (the connector's
     * plugin window: `installUrl`, or `url` of the older helper) or the plugin's public store page (`storeUrl`, exactly
     * PaidPluginCatalog::STORE{de|en}/plugins/<slug>), which gets the campaign parameters and opens in a new tab
     * (external = '1'). A picture only through the connector's image proxy inside Matomo. Ids for the 30-day dismissal:
     * premium-<slug> for the banner, premium-<slug>-card for the card, whatever the source, so the two hide independently.
     *
     * @param array<int, mixed> $list
     * @return array<int, array<string, string>>
     */
    public static function valid(array $list): array
    {
        $out = [];
        foreach ($list as $plugin) {
            if (!is_array($plugin)) {
                continue;
            }
            $slug = is_string($plugin['slug'] ?? null) ? $plugin['slug'] : '';
            $name = is_string($plugin['name'] ?? null) ? trim($plugin['name']) : '';
            $image = is_string($plugin['image'] ?? null) ? $plugin['image'] : '';
            if (!preg_match('/^Feinwerk[A-Za-z0-9]+$/', $slug) || $slug === PromoLine::PLUGIN || $name === ''
                || ($image !== '' && strpos($image, 'index.php?') !== 0)
            ) {
                continue;
            }
            [$url, $external] = self::link($plugin, $slug);
            if ($url === '') {
                continue;
            }
            $out[] = [
                'id' => 'premium-' . $slug,
                'slug' => $slug,
                'name' => $name,
                'description' => is_string($plugin['description'] ?? null) ? trim($plugin['description']) : '',
                'price' => is_string($plugin['price'] ?? null) ? trim($plugin['price']) : '',
                'image' => $image,
                'url' => $url,
                'external' => $external,
            ];
        }

        return $out;
    }

    /**
     * The link of an entry: the connector's plugin window inside Matomo when the entry has one, else the public store
     * page with the campaign parameters. ['', ''] when neither is usable.
     *
     * @param array<string, mixed> $plugin
     * @return array{0: string, 1: string} url, external ('1' for the store page)
     */
    private static function link(array $plugin, string $slug): array
    {
        foreach (['installUrl', 'url'] as $key) {
            $url = is_string($plugin[$key] ?? null) ? $plugin[$key] : '';
            if (strpos($url, 'index.php?') === 0) {
                return [$url, ''];
            }
        }
        $store = is_string($plugin['storeUrl'] ?? null) ? $plugin['storeUrl'] : '';
        foreach (['de', 'en'] as $language) {
            if ($store === PaidPluginCatalog::storeUrl($slug, $language)) {
                return [$store . '?' . self::CAMPAIGN . rawurlencode($slug), '1'];
            }
        }

        return ['', ''];
    }

    /**
     * Today's order: the list turned by one entry each day, so another plugin comes first.
     *
     * @param array<int, array<string, string>> $list
     * @return array<int, array<string, string>>
     */
    public static function rotate(array $list, int $day): array
    {
        $list = array_values($list);
        if ($list === []) {
            return [];
        }
        $start = $day % count($list);

        return array_merge(array_slice($list, $start), array_slice($list, 0, $start));
    }

    /**
     * The banner's widget prop for the current user (WidgetProps): null without plugins or on any error, so a failing
     * promotion never breaks the page.
     *
     * @return array{plugins: array<int, array<string, string>>, days: int, demo: bool, note: string}|null
     */
    public static function bannerProps(): ?array
    {
        try {
            $plugins = self::plugins();

            return $plugins === [] ? null : self::forBanner(
                $plugins,
                PromoLine::demo(),
                Piwik::hasUserSuperUserAccess(),
                [Piwik::class, 'translate']
            );
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * What the page gets: the plugins in today's order, the days × hides one (demo: this browser session, 0), and for
     * anyone who cannot install plugins the line who can. The German sentence takes Matomo's own word for the super
     * user (Installation_SuperUser); the English one, also the fallback of other languages, has its own.
     *
     * @param array<int, array<string, string>> $plugins valid() entries
     * @param callable $translate like Piwik::translate(string $key, array $args = [])
     * @return array{plugins: array<int, array<string, string>>, days: int, demo: bool, note: string}|null
     */
    public static function forBanner(array $plugins, bool $demo, bool $superUser, callable $translate): ?array
    {
        if ($plugins === []) {
            return null;
        }

        return [
            'plugins' => array_values($plugins),
            'days' => PromoLine::dismissDays($demo),
            'demo' => $demo,
            'note' => $superUser ? '' : (string) $translate(
                'FeinwerkDataExploration_PremiumAskSuperUser',
                [(string) $translate('Installation_SuperUser')]
            ),
        ];
    }
}
