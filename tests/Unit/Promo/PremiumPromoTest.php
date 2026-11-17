<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Promo;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Promo\PaidPluginCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Promo\PremiumPromo;

/**
 * The banner below the result (Peter, 2026-11-17: "ein randloses Banner unter dem Hauptfenster mit Premium-Plugin
 * Rotation"): paid Feinwerk plugins not installed, from the connector or the kit's fallback list, in today's order.
 * The rotation and promotion kit parts as in Page Filter Report (99c4100); the markup is checked in the Vue spec.
 */
class PremiumPromoTest extends TestCase
{
    private const ENTRY = [
        'slug' => 'FeinwerkSentinelPro',
        'name' => 'Sentinel Pro',
        'description' => 'Watches your tracking every day.',
        'price' => 'from 59,00 € / month',
        'image' => 'index.php?module=FeinwerkMarketplaceConnector&action=image&src=https%3A%2F%2Fplugins.feinwerk.io%2Fa.png',
        'url' => 'index.php?module=FeinwerkMarketplaceConnector&action=index&fwmc_open=FeinwerkSentinelPro',
    ];

    public function test_a_valid_entry_gets_its_dismissal_id(): void
    {
        self::assertSame([['id' => 'premium-FeinwerkSentinelPro'] + self::ENTRY + ['external' => '']], PremiumPromo::valid([self::ENTRY]));
    }

    /** Customers run older connectors: without the helper (or with a failing one) nothing shows and nothing breaks. */
    public function test_without_the_connectors_helper_there_is_nothing_to_show(): void
    {
        $active = static function (): bool {
            return true;
        };
        $inactive = static function (): bool {
            return false;
        };
        $broken = static function (): bool {
            throw new \RuntimeException('no container');
        };

        self::assertSame([], PremiumPromo::fromConnector(3, $active, 'Piwik\\Plugins\\FeinwerkMarketplaceConnector\\NoSuchPromotions'));
        self::assertSame([], PremiumPromo::fromConnector(3, $active, OldConnectorPromotions::class), 'connector before the helper');
        self::assertSame([], PremiumPromo::fromConnector(3, $active, FailingConnectorPromotions::class), 'the helper throws');
        self::assertSame([], PremiumPromo::fromConnector(3, $inactive, CurrentConnectorPromotions::class), 'connector not active');
        self::assertSame([], PremiumPromo::fromConnector(3, $broken, CurrentConnectorPromotions::class));
        self::assertSame([], PremiumPromo::valid(PremiumPromo::fromConnector(3, $active, OldConnectorPromotions::class)));
    }

    public function test_with_the_helper_its_list_is_used_up_to_the_maximum(): void
    {
        $active = static function (): bool {
            return true;
        };

        $list = PremiumPromo::fromConnector(1, $active, CurrentConnectorPromotions::class);

        self::assertSame([self::ENTRY], $list);
        self::assertSame([['id' => 'premium-FeinwerkSentinelPro'] + self::ENTRY + ['external' => '']], PremiumPromo::valid($list));
    }

    public function test_entries_this_plugin_cannot_show_are_left_out(): void
    {
        $broken = [
            'not an array',
            ['slug' => 'SomeoneElse'] + self::ENTRY,
            ['slug' => 'FeinwerkDataExploration'] + self::ENTRY,
            ['name' => '  '] + self::ENTRY,
            ['url' => 'https://example.org/'] + self::ENTRY,
            ['url' => 'javascript:alert(1)'] + self::ENTRY,
            ['image' => 'https://example.org/a.png'] + self::ENTRY,
            ['slug' => null] + self::ENTRY,
        ];

        self::assertSame([], PremiumPromo::valid($broken));
    }

    public function test_an_entry_without_picture_or_price_stays(): void
    {
        $valid = PremiumPromo::valid([['image' => '', 'price' => ''] + self::ENTRY]);

        self::assertCount(1, $valid);
        self::assertSame('', $valid[0]['image']);
        self::assertSame('', $valid[0]['price']);
    }

    /**
     * Peter, 2026-11-16: admins too. The connector's newer helper gives super users its plugin window (installUrl) and
     * admins the public store page; the store page gets the house campaign parameters and opens in a new tab.
     * (View-only users and anonymous never get here: PromoLine::visibleTo(), PromoLineTest.)
     */
    public function test_super_users_get_the_connector_link_and_admins_the_store_page(): void
    {
        $active = static function (): bool {
            return true;
        };

        RecommendingConnectorPromotions::$superUser = true;
        $super = PremiumPromo::valid(PremiumPromo::fromConnector(3, $active, RecommendingConnectorPromotions::class));
        RecommendingConnectorPromotions::$superUser = false;
        $admin = PremiumPromo::valid(PremiumPromo::fromConnector(3, $active, RecommendingConnectorPromotions::class));

        self::assertSame('index.php?module=FeinwerkMarketplaceConnector&action=index&fwmc_open=FeinwerkVisitJourney', $super[0]['url']);
        self::assertSame('', $super[0]['external']);
        self::assertSame(
            'https://plugins.feinwerk.io/de/plugins/FeinwerkVisitJourney?mtm_campaign=plugin-promo&mtm_source=FeinwerkDataExploration&mtm_content=rec-FeinwerkVisitJourney',
            $admin[0]['url']
        );
        self::assertSame('1', $admin[0]['external']);
        self::assertSame('premium-FeinwerkVisitJourney', $admin[0]['id'], 'the same dismissal id whatever the source');
        self::assertSame('', $admin[0]['image'], 'no picture for admins: the proxy needs a super user');
    }

    /** Only the store's own plugin pages, never another address. */
    public function test_a_store_link_must_be_the_plugins_own_store_page(): void
    {
        $entry = ['slug' => 'FeinwerkVisitJourney', 'name' => 'Visit Journey', 'description' => '', 'price' => '', 'image' => ''];

        self::assertSame([], PremiumPromo::valid([$entry + ['storeUrl' => 'https://example.org/de/plugins/FeinwerkVisitJourney']]));
        self::assertSame([], PremiumPromo::valid([$entry + ['storeUrl' => 'https://plugins.feinwerk.io/de/plugins/FeinwerkAuthSaml']]));
        self::assertSame([], PremiumPromo::valid([$entry + ['storeUrl' => 'https://plugins.feinwerk.io/de/plugins/FeinwerkVisitJourney?x=1']]));
        self::assertCount(1, PremiumPromo::valid([$entry + ['storeUrl' => 'https://plugins.feinwerk.io/en/plugins/FeinwerkVisitJourney']]));
    }

    /**
     * Without the connector's live list (connector missing, older than both helpers, not connected, or the user is no
     * super user on connector 5.4.0) the fallback list is used; with it, the fallback is not even asked.
     */
    public function test_the_fallback_list_steps_in_when_the_connector_has_nothing(): void
    {
        $asked = 0;
        $fallback = static function () use (&$asked): array {
            $asked++;

            return PaidPluginCatalog::notInstalled(['FeinwerkChannelGrouping'], '5.14.2', 'de', static function (string $key): string {
                return $key;
            }, 'FeinwerkDataExploration');
        };
        $active = static function (): bool {
            return true;
        };

        $list = PremiumPromo::choose(PremiumPromo::valid(PremiumPromo::fromConnector(3, $active, OldConnectorPromotions::class)), $fallback, 0);
        self::assertSame(1, $asked);
        self::assertCount(PremiumPromo::MAX, $list);
        self::assertSame('premium-FeinwerkSearchReport', $list[0]['id'], 'the first plugin not present here, in the store order');
        self::assertSame('1', $list[0]['external']);
        self::assertSame('FeinwerkDataExploration_PaidPluginSearchReportText', $list[0]['description']);

        $connected = PremiumPromo::choose(PremiumPromo::valid(PremiumPromo::fromConnector(3, $active, CurrentConnectorPromotions::class)), $fallback, 0);
        self::assertSame(1, $asked, 'the connector had a list');
        self::assertSame('premium-FeinwerkSentinelPro', $connected[0]['id']);

        $nothing = PremiumPromo::choose([], static function (): array {
            return [];
        }, 3);
        self::assertSame([], $nothing, 'every paid plugin present');
    }

    /**
     * Demo mode tops the connector's list up from the whole fallback list (no duplicates) and keeps every entry in the
     * rotation; without demo the connector's list alone, cut to MAX.
     */
    public function test_demo_tops_up_the_connectors_list_and_shows_the_whole_rotation(): void
    {
        $active = static function (): bool {
            return true;
        };
        $whole = static function (): array {
            return PaidPluginCatalog::all('de', static function (string $key): string {
                return $key;
            }, 'FeinwerkDataExploration');
        };
        $connector = PremiumPromo::valid(PremiumPromo::fromConnector(3, $active, CurrentConnectorPromotions::class));

        $demo = PremiumPromo::choose($connector, $whole, 0, true);
        $slugs = array_column($demo, 'slug');

        self::assertCount(count(PaidPluginCatalog::PLUGINS), $demo, 'the 9 catalogue plugins, Sentinel Pro and Email Reports Pro once');
        self::assertSame($slugs, array_values(array_unique($slugs)));
        self::assertSame('premium-FeinwerkSentinelPro', $demo[0]['id'], 'the connector entries first');
        self::assertSame('', $demo[0]['external'], 'with the connector link');
        self::assertCount(2, PremiumPromo::choose($connector, $whole, 0), 'without demo: the connector list only');
    }

    /** Matomo for WordPress: paid plugins cannot be installed there yet, so no tile and no card, in demo too. */
    public function test_no_recommendation_inside_wordpress(): void
    {
        $whole = static function (): array {
            return PaidPluginCatalog::all('de', static function (string $key): string {
                return $key;
            }, 'FeinwerkDataExploration');
        };

        self::assertSame([], PremiumPromo::choose([], $whole, 0, false, true));
        self::assertSame([], PremiumPromo::choose([], $whole, 0, true, true), 'demo too');
        self::assertNotSame([], PremiumPromo::choose([], $whole, 0, true, false));
    }

    /** "alternierend": another plugin comes first each day, the others follow in the store's order. */
    public function test_the_order_turns_by_one_each_day(): void
    {
        $list = [['id' => 'a'], ['id' => 'b'], ['id' => 'c']];
        $ids = static function (array $rotated): string {
            return implode('', array_column($rotated, 'id'));
        };

        self::assertSame('abc', $ids(PremiumPromo::rotate($list, 0)));
        self::assertSame('bca', $ids(PremiumPromo::rotate($list, 1)));
        self::assertSame('cab', $ids(PremiumPromo::rotate($list, 2)));
        self::assertSame('abc', $ids(PremiumPromo::rotate($list, 3)));
        self::assertSame([], PremiumPromo::rotate([], 5));
    }

    /** What the page gets: nothing without plugins; the 30 days (demo: this session); the note only for admins. */
    public function test_the_banner_props(): void
    {
        $translate = static function (string $key, array $args = []): string {
            return $key . ($args === [] ? '' : '(' . implode(',', $args) . ')');
        };
        $plugins = PremiumPromo::valid([self::ENTRY, ['slug' => 'FeinwerkSearchReport', 'name' => 'Search Report',
            'storeUrl' => 'https://plugins.feinwerk.io/en/plugins/FeinwerkSearchReport']]);
        self::assertCount(2, $plugins);

        self::assertNull(PremiumPromo::forBanner([], false, true, $translate));

        $super = PremiumPromo::forBanner($plugins, false, true, $translate);
        self::assertSame(['plugins' => $plugins, 'days' => 30, 'demo' => false, 'note' => ''], $super);

        $admin = PremiumPromo::forBanner($plugins, false, false, $translate);
        self::assertSame('FeinwerkDataExploration_PremiumAskSuperUser(Installation_SuperUser)', $admin['note']);

        $demo = PremiumPromo::forBanner($plugins, true, true, $translate);
        self::assertSame([0, true], [$demo['days'], $demo['demo']]);
    }
}

/** A connector before its 2026-11-13 release: Promotions without the helper. */
final class OldConnectorPromotions
{
    public static function areEnabled(): bool
    {
        return true;
    }
}

/** A helper that fails (it never should): the plugin still shows nothing and does not break. */
final class FailingConnectorPromotions
{
    public static function paidPluginsNotInstalled(int $max = 3): array
    {
        throw new \RuntimeException('store list unreadable');
    }
}

/** The connector's newer helper (after 5.4.0): admins and super users; installUrl and image for super users only. */
final class RecommendingConnectorPromotions
{
    /** @var bool */
    public static $superUser = false;

    public static function paidPluginRecommendations(int $max = 3): array
    {
        return [[
            'slug' => 'FeinwerkVisitJourney',
            'name' => 'Visit Journey',
            'description' => 'A funnel with no presets.',
            'price' => 'from 49,00 € / month',
            'image' => self::$superUser ? 'index.php?module=FeinwerkMarketplaceConnector&action=image&src=x' : '',
            'storeUrl' => 'https://plugins.feinwerk.io/de/plugins/FeinwerkVisitJourney',
            'installUrl' => self::$superUser ? 'index.php?module=FeinwerkMarketplaceConnector&action=index&fwmc_open=FeinwerkVisitJourney' : '',
        ]];
    }

    /** the older helper is ignored when the newer one exists */
    public static function paidPluginsNotInstalled(int $max = 3): array
    {
        return [];
    }
}

/** The connector's helper as of its contract: up to $max entries. */
final class CurrentConnectorPromotions
{
    public static function paidPluginsNotInstalled(int $max = 3): array
    {
        return array_slice([
            [
                'slug' => 'FeinwerkSentinelPro',
                'name' => 'Sentinel Pro',
                'description' => 'Watches your tracking every day.',
                'price' => 'from 59,00 € / month',
                'image' => 'index.php?module=FeinwerkMarketplaceConnector&action=image&src=https%3A%2F%2Fplugins.feinwerk.io%2Fa.png',
                'url' => 'index.php?module=FeinwerkMarketplaceConnector&action=index&fwmc_open=FeinwerkSentinelPro',
            ],
            [
                'slug' => 'FeinwerkEmailReportsPro',
                'name' => 'Email Reports Pro',
                'description' => '',
                'price' => '',
                'image' => '',
                'url' => 'index.php?module=FeinwerkMarketplaceConnector&action=index&fwmc_open=FeinwerkEmailReportsPro',
            ],
        ], 0, $max);
    }
}
