<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Promo;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Promo\PromoLine;

/**
 * The messages of the Feinwerk promotion kit and the tip line: who sees them, which message, where the link leads, how
 * they are switched off. PHP 7.4 syntax (the plugin's 5.x line).
 *
 * @group Plugins
 * @group FeinwerkDataExploration
 */
class PromoLineTest extends TestCase
{
    public function test_on_by_default_and_every_spelling_of_off_switches_it_off(): void
    {
        self::assertTrue(PromoLine::enabledBy([]));
        self::assertTrue(PromoLine::enabledBy(['promotions' => '1']));
        foreach (['0', '', 'off', 'no', 'false', ' Off ', false, null] as $off) {
            self::assertFalse(PromoLine::enabledBy(['promotions' => $off]), var_export($off, true));
        }
    }

    /** Switch 2: [FeinwerkMarketplaceConnector] promotions = 0 turns it off for all Feinwerk plugins, also without the connector. */
    public function test_the_connectors_config_switches_it_off_for_all_plugins(): void
    {
        self::assertFalse(PromoLine::enabledBy([], ['promotions' => '0']));
        self::assertFalse(PromoLine::enabledBy([], ['promotions' => false]));
        self::assertTrue(PromoLine::enabledBy([], ['other' => '0']));
        self::assertFalse(PromoLine::enabledBy(['promotions' => '0'], ['promotions' => '1']), 'the plugin\'s own switch comes first');
    }

    /** Switch 3: the connector's setting in Matomo's UI; a failing connector counts as on. */
    public function test_the_connectors_setting_in_the_ui(): void
    {
        $off = static function (): bool {
            return false;
        };
        $on = static function (): bool {
            return true;
        };
        $broken = static function (): bool {
            throw new \RuntimeException('connector broken');
        };
        self::assertFalse(PromoLine::enabledBy([], [], $off));
        self::assertTrue(PromoLine::enabledBy([], [], $on));
        self::assertTrue(PromoLine::enabledBy([], [], $broken));
        self::assertFalse(PromoLine::enabledBy([], ['promotions' => '0'], $on), 'the config switch wins');
        self::assertFalse(PromoLine::enabledBy(['promotions' => 'off'], [], $on));
    }

    /** Older connectors have no switch, and it is asked only while the connector is activated. */
    public function test_the_connector_switch_only_when_it_exists_and_the_connector_is_active(): void
    {
        $active = static function (): bool {
            return true;
        };
        $inactive = static function (): bool {
            return false;
        };
        $failing = static function (): bool {
            throw new \RuntimeException('no plugin manager');
        };
        self::assertNull(PromoLine::connectorSwitch('\\Piwik\\Plugins\\NoSuchPlugin\\Promotions', $active), 'no class');
        self::assertNull(PromoLine::connectorSwitch(PromotionsWithoutSwitch::class, $active), 'older connector');
        self::assertNull(PromoLine::connectorSwitch(PromotionsOff::class, $inactive), 'connector not activated');
        self::assertNull(PromoLine::connectorSwitch(PromotionsOff::class, $failing));
        $switch = PromoLine::connectorSwitch(PromotionsOff::class, $active);
        self::assertNotNull($switch);
        self::assertFalse(PromoLine::enabledBy([], [], $switch));
    }

    /** The demo switch reads like the connector's Promotions::demo(), so no value turns it on in one plugin only. */
    public function test_the_demo_switch_reads_like_the_connectors(): void
    {
        foreach (['1', 'on', 'yes', 'true', 'TRUE', ' On ', 'Yes', true, 1] as $on) {
            self::assertTrue(PromoLine::demoBy(['promotions_demo' => $on]), var_export($on, true));
        }
        foreach (['0', 'off', 'no', 'false', '', '2', 2, 0, false, null, ['1'], 'enabled'] as $off) {
            self::assertFalse(PromoLine::demoBy(['promotions_demo' => $off]), var_export($off, true));
        }
        self::assertFalse(PromoLine::demoBy([]), 'off by default');
    }

    /** Every off switch beats demo: demo is only asked once promotions are on (shownToCurrentUser()). */
    public function test_an_off_switch_beats_demo(): void
    {
        self::assertFalse(PromoLine::enabledBy(['promotions' => '0'], ['promotions_demo' => '1']));
        self::assertFalse(PromoLine::enabledBy([], ['promotions' => '0', 'promotions_demo' => '1']));
        self::assertFalse(PromoLine::enabledBy([], ['promotions_demo' => '1'], static function (): bool {
            return false;
        }), 'the connector setting');
    }

    /** Demo lifts the message conditions: the whole rotation, whatever is installed or tracked. */
    public function test_demo_shows_every_message(): void
    {
        $everything = static function (): bool {
            return true;
        };

        self::assertSame(array_keys(PromoLine::MESSAGES), PromoLine::candidates($everything, false, true));
        self::assertNotSame(array_keys(PromoLine::MESSAGES), PromoLine::candidates($everything, false));
    }

    /** In demo a hidden notice stays away for the session only: days 0 plus the demo flag for the page (vue/src/PromoLine.vue). */
    public function test_demo_variables_keep_a_hidden_notice_away_for_the_session_only(): void
    {
        $translate = static function (string $key): string {
            return $key;
        };
        $normal = PromoLine::variables('campaigns', 'en', $translate, 'line');
        $demo = PromoLine::variables('campaigns', 'en', $translate, 'line', true);

        self::assertSame([30, false], [$normal['dismissDays'], $normal['demo']]);
        self::assertSame([0, true], [$demo['dismissDays'], $demo['demo']]);
    }

    /** Peter, 2026-11-11: "vermehrt": the connector message about every second time, the others in turn between. */
    public function test_the_connector_message_shows_about_every_second_time(): void
    {
        $candidates = ['campaigns', 'hosting', 'connector', 'sentinel'];
        $days = [];
        for ($day = 0; $day < 12; $day++) {
            $days[] = PromoLine::choose($candidates, $day);
        }

        self::assertSame(['connector', 'campaigns', 'connector', 'hosting', 'connector', 'sentinel'], array_slice($days, 0, 6));
        self::assertSame(6, count(array_keys($days, 'connector', true)));
        self::assertSame(['campaigns', 'hosting', 'sentinel'], array_values(array_unique(array_diff($days, ['connector']))));
        self::assertSame('connector', PromoLine::choose(['connector'], 1), 'alone it shows every day');
        $installed = static function (string $plugin): bool {
            return $plugin === 'FeinwerkMarketplaceConnector';
        };
        self::assertNotContains('connector', PromoLine::candidates($installed), 'not once the connector is installed');
    }

    /**
     * Super user with a usable Marketplace: the connector in the instance's own Marketplace (no campaign parameters).
     * Admin: plugins.matomo.org and a note that the super user can install it. A super user without a usable Marketplace
     * gets no connector message at all (test_the_line_for_each_kind_of_user).
     */
    public function test_the_connector_message_leads_each_user_where_they_can_act(): void
    {
        $translate = static function (string $key): string {
            return '[' . $key . ']';
        };
        $super = PromoLine::variables('connector', 'de', $translate, 'line', false, ['superUser' => true, 'marketplace' => true]);
        $admin = PromoLine::variables('connector', 'en', $translate, 'line', false, ['superUser' => false, 'marketplace' => true]);

        self::assertSame(['index.php?module=Marketplace&action=overview#?popover=browsePluginDetail%243AFeinwerkMarketplaceConnector', false, ''],
            [$super['url'], $super['external'], $super['note']]);
        self::assertSame(['https://plugins.matomo.org/FeinwerkMarketplaceConnector?mtm_campaign=plugin-promo&mtm_source=FeinwerkDataExploration&mtm_content=connector', true, '[FeinwerkDataExploration_PremiumAskSuperUser]'],
            [$admin['url'], $admin['external'], $admin['note']]);
        self::assertSame('[FeinwerkDataExploration_PromoConnectorLink]', $admin['link']);

        // where this plugin's installer page is offered, the super user goes there (Peter, 2026-11-23: "Auf den Installer")
        $installer = PromoLine::variables('connector', 'de', $translate, 'line', false, ['superUser' => true, 'marketplace' => true, 'installer' => true]);
        self::assertSame(['index.php?module=FeinwerkDataExploration&action=connectorInstaller', false, ''],
            [$installer['url'], $installer['external'], $installer['note']]);
        $adminInstaller = PromoLine::variables('connector', 'en', $translate, 'line', false, ['superUser' => false, 'marketplace' => true, 'installer' => true]);
        self::assertTrue($adminInstaller['external'], 'admins cannot use the installer');
    }

    /** Matomo for WordPress (Peter, 2026-11-17: "Dort nicht werben"): no connector message there, in demo too. */
    public function test_no_connector_message_inside_wordpress(): void
    {
        $nothing = static function (): bool {
            return false;
        };

        self::assertSame(['campaigns', 'hosting', 'sentinel'], PromoLine::candidates($nothing, false, false, true));
        self::assertNotContains('connector', PromoLine::candidates($nothing, true, true, true), 'demo too');
        self::assertContains('connector', PromoLine::candidates($nothing, false, false, false));
        self::assertTrue(PromoLine::isWordPressBy(true, true));
        self::assertFalse(PromoLine::isWordPressBy(true, false), 'ABSPATH alone is not Matomo for WordPress');
        self::assertFalse(PromoLine::isWordPressBy(false, true));
        self::assertFalse(PromoLine::isWordPress(), 'the test runs outside WordPress');
    }

    /**
     * current()'s decisions, through lineFor(): WordPress and a super user without a usable Marketplace get no connector
     * message (Peter, 2026-11-17), a super user with one the Marketplace link, an admin plugins.matomo.org plus the note
     * who can install it.
     */
    public function test_the_line_for_each_kind_of_user(): void
    {
        $nothing = static function (): bool {
            return false;
        };
        $translate = static function (string $key, array $args = []): string {
            return $key . ($args === [] ? '' : '(' . implode(',', $args) . ')');
        };
        $env = ['day' => 0, 'ecommerce' => false, 'demo' => false, 'wordpress' => false, 'superUser' => true, 'marketplace' => true];

        $super = PromoLine::lineFor($env, $nothing, $translate, 'de');
        self::assertSame('connector', $super['id'], 'day 0 is a connector day');
        self::assertSame([false, ''], [$super['external'], $super['note']]);

        $admin = PromoLine::lineFor(['superUser' => false, 'marketplace' => false] + $env, $nothing, $translate, 'de');
        self::assertSame('connector', $admin['id']);
        self::assertStringStartsWith('https://plugins.matomo.org/FeinwerkMarketplaceConnector?', $admin['url']);
        self::assertSame('FeinwerkDataExploration_PremiumAskSuperUser', $admin['note']);

        foreach ([
            'super user without a usable Marketplace' => ['marketplace' => false] + $env,
            'Matomo for WordPress' => ['wordpress' => true] + $env,
            'Matomo for WordPress, demo' => ['wordpress' => true, 'demo' => true] + $env,
        ] as $case => $without) {
            for ($day = 0; $day < 8; $day++) {
                $line = PromoLine::lineFor(['day' => $day] + $without, $nothing, $translate, 'de');
                self::assertNotSame('connector', $line['id'] ?? null, $case . ', day ' . $day);
            }
        }
    }

    public function test_only_admins_and_super_users(): void
    {
        self::assertTrue(PromoLine::visibleTo(false, true));
        self::assertFalse(PromoLine::visibleTo(false, false), 'view access only');
        self::assertFalse(PromoLine::visibleTo(true, false), 'anonymous');
        self::assertFalse(PromoLine::visibleTo(true, true), 'anonymous, whatever the access says');
    }

    public function test_not_in_embedded_widgets(): void
    {
        self::assertTrue(PromoLine::isEmbedded('Widgetize', 0));
        self::assertTrue(PromoLine::isEmbedded('CoreHome', 1), 'disableLink=1');
        self::assertFalse(PromoLine::isEmbedded('CoreHome', 0));
        self::assertFalse(PromoLine::isEmbedded('Dashboard', 0));
    }

    public function test_a_product_that_is_installed_is_not_advertised(): void
    {
        $everything = static function (string $plugin): bool {
            return true;
        };
        self::assertSame(['campaigns', 'hosting'], PromoLine::candidates($everything, true), 'websites have no plugin to check');
        foreach (PromoLine::MESSAGES as $id => $message) {
            if ($message['product'] !== null) {
                $product = $message['product'];
                $installed = static function (string $plugin) use ($product): bool {
                    return $plugin === $product;
                };
                self::assertNotContains($id, PromoLine::candidates($installed, true));
            }
        }
    }

    public function test_conversions_api_only_for_websites_with_ecommerce(): void
    {
        $nothing = static function (string $plugin): bool {
            return false;
        };
        self::assertSame(['campaigns', 'hosting', 'connector', 'sentinel'], PromoLine::candidates($nothing));
        self::assertSame(['campaigns', 'hosting', 'capi', 'connector', 'sentinel'], PromoLine::candidates($nothing, true));
    }

    public function test_the_free_plugin_shows_one_message_per_day_in_turn(): void
    {
        self::assertNull(PromoLine::PREFERRED, 'free plugins prefer no message');
        self::assertSame('campaigns', PromoLine::choose(['campaigns', 'hosting', 'sentinel'], 9));
        self::assertSame('hosting', PromoLine::choose(['campaigns', 'hosting', 'sentinel'], 10));
        self::assertSame('sentinel', PromoLine::choose(['campaigns', 'hosting', 'sentinel'], 11));
        self::assertSame('hosting', PromoLine::choose([2 => 'hosting'], 7), 'keys do not matter');
        self::assertNull(PromoLine::choose([], 7));
    }

    public function test_link_in_the_users_language_with_only_the_campaign_parameters(): void
    {
        self::assertSame(
            'https://campaigns.feinwerk.io/de/matomo?mtm_campaign=plugin-promo&mtm_source=FeinwerkDataExploration&mtm_content=campaigns',
            PromoLine::url('campaigns', 'de')
        );
        self::assertStringStartsWith('https://campaigns.feinwerk.io/en/matomo?', PromoLine::url('campaigns', 'fr'), 'English for other languages');
        self::assertSame(
            'https://feinwerk.io/en/contact?service=hosting&mtm_campaign=plugin-promo&mtm_source=FeinwerkDataExploration&mtm_content=hosting',
            PromoLine::url('hosting', 'en'),
            'a landing page with its own query string'
        );
    }

    public function test_every_message_has_texts_a_page_in_both_languages_and_a_placement(): void
    {
        foreach (PromoLine::MESSAGES as $id => $message) {
            self::assertSame(1, preg_match('/^[a-z_]+$/', $id), $id); // PHPUnit 8.5 (Matomo 5 runner) lacks assertMatchesRegularExpression
            foreach (['title', 'text', 'link'] as $field) {
                self::assertStringStartsWith('FeinwerkDataExploration_Promo', $message[$field]);
            }
            foreach (['de', 'en'] as $language) {
                if ($id === PromoLine::CONNECTOR_MESSAGE) {
                    continue; // its link depends on the user: connectorUrl()
                }
                self::assertStringStartsWith('https://', $message['urls'][$language]);
            }
            self::assertArrayHasKey('placement', $message);
            self::assertIsBool($message['ecommerce']);
        }
    }

    /** Only the connector message has a short link for narrow lines (Peter, 2026-11-25: "Kurzer Linktext"). */
    public function test_the_connector_message_has_a_short_link(): void
    {
        $translate = static function (string $key): string {
            return '[' . $key . ']';
        };
        $connector = PromoLine::variables('connector', 'de', $translate, 'line', false, ['superUser' => true, 'marketplace' => true]);
        self::assertSame('[FeinwerkDataExploration_PromoConnectorLinkShort]', $connector['linkShort']);
        foreach (['campaigns', 'hosting', 'capi', 'sentinel'] as $id) {
            self::assertSame('', PromoLine::variables($id, 'de', $translate)['linkShort'], $id);
        }
    }

    public function test_variables_of_the_templates(): void
    {
        $translate = static function (string $key): string {
            return '[' . $key . ']';
        };
        self::assertSame([
            'id' => 'campaigns',
            'placement' => 'line',
            'title' => '[FeinwerkDataExploration_PromoCampaignsTitle]',
            'text' => '[FeinwerkDataExploration_PromoCampaignsText]',
            'link' => '[FeinwerkDataExploration_PromoCampaignsLink]',
            'linkShort' => '',
            'url' => PromoLine::url('campaigns', 'en'),
            'external' => true,
            'note' => '',
            'dismissDays' => 30,
            'demo' => false,
        ], PromoLine::variables('campaigns', 'en', $translate));
        self::assertSame('adminHome', PromoLine::variables('hosting', 'de', $translate, 'adminHome')['placement']);
    }

    public function test_never_advertises_itself(): void
    {
        foreach (PromoLine::MESSAGES as $id => $message) {
            $this->assertNotSame('FeinwerkDataExploration', $message['product'], $id);
        }
    }

    public function test_no_url_in_translations(): void
    {
        // plugins on the Matomo Marketplace: URLs only in PHP (docs/promotions.md)
        foreach (['en', 'de'] as $language) {
            $json = (string) file_get_contents(__DIR__ . '/../../../lang/' . $language . '.json');
            $this->assertStringNotContainsString('http', $json, $language);
        }
    }

    public function test_unknown_message_is_refused(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PromoLine::variables('pricing', 'en', static function (string $key): string {
            return $key;
        });
    }

    /** Every text of every message exists in both languages, without en or em dashes (house rule). */
    public function test_texts_in_german_and_english(): void
    {
        // no label key (PromoTip): this plugin has only the tip line, which shows no label (docs/footer.md)
        $keys = ['FeinwerkDataExploration_PromoDismiss'];
        foreach (PromoLine::MESSAGES as $message) {
            $keys[] = $message['title'];
            $keys[] = $message['text'];
            $keys[] = $message['link'];
            if (isset($message['linkShort'])) {
                $keys[] = $message['linkShort'];
            }
        }
        // the campaigns steps build their keys at run time (tests/Unit/TranslationsTest.php leaves that prefix to us)
        foreach ([['open', 'index.php?a=1', ''], ['install', '', 'index.php?b=1'], ['install', '', '']] as [$stage, $open, $install]) {
            $step = PromoLine::campaignsStep(['stage' => $stage, 'openUrl' => $open, 'installUrl' => $install], 'en');
            array_push($keys, $step['title'], $step['text'], $step['link']);
        }
        self::assertContains('FeinwerkDataExploration_PromoConnectorLinkShort', $keys);
        self::assertContains('FeinwerkDataExploration_PromoCampaignsOpenText', $keys);
        foreach (['en', 'de'] as $language) {
            $texts = json_decode((string) file_get_contents(__DIR__ . '/../../../lang/' . $language . '.json'), true);
            foreach ($keys as $key) {
                $text = $texts['FeinwerkDataExploration'][substr($key, strlen('FeinwerkDataExploration_'))] ?? '';
                self::assertNotSame('', $text, $language . ' ' . $key);
                self::assertSame(0, preg_match('/[\x{2013}\x{2014}]/u', $text), $language . ' ' . $key);
            }
        }
    }

    /**
     * Regression (kit release review 2026-11-20, PFR 0bbee14): without automatic updates (enable_auto_update = 0 or
     * multi_server_environment) Matomo's Marketplace offers a ZIP download instead of Install, the case Peter ruled out
     * ("Dann keine Nachricht"). The connector link into the Marketplace needs all four of Matomo's conditions.
     */
    public function test_the_marketplace_counts_as_usable_only_where_matomo_offers_its_install_button(): void
    {
        $all = ['marketplace' => true, 'internet' => true, 'pluginsAdmin' => true, 'autoUpdate' => true];
        self::assertTrue(PromoLine::marketplaceUsableBy($all));
        foreach (array_keys($all) as $switch) {
            self::assertFalse(PromoLine::marketplaceUsableBy([$switch => false] + $all), $switch);
        }
    }

    /**
     * Peter's decision 8 of 2026-11-23 (Campaign Manager): the campaigns tip in three steps. Active: its overview, none
     * once connected; installed but switched off: none; not installed: install once listed, else today's tip. Demo
     * always shows a step.
     */
    public function test_the_campaigns_step_for_each_state(): void
    {
        $stage = [PromoLine::class, 'campaignsStage'];
        // installed, active, connected, listed
        self::assertSame(PromoLine::CAMPAIGNS_PLATFORM, $stage(false, false, false, false));
        self::assertSame(PromoLine::CAMPAIGNS_INSTALL, $stage(false, false, false, true));
        self::assertSame(PromoLine::CAMPAIGNS_OPEN, $stage(true, true, false, false));
        self::assertSame(PromoLine::CAMPAIGNS_OPEN, $stage(true, true, false, true));
        self::assertSame(PromoLine::CAMPAIGNS_NONE, $stage(true, true, true, true));
        self::assertSame(PromoLine::CAMPAIGNS_NONE, $stage(true, false, false, true), 'switched off by the admin');
        self::assertSame(PromoLine::CAMPAIGNS_OPEN, $stage(true, true, true, true, true), 'demo: connected still shows');
        self::assertSame(PromoLine::CAMPAIGNS_PLATFORM, $stage(true, false, false, true, true), 'demo: switched off');
        self::assertTrue(PromoLine::CAMPAIGN_MANAGER_LISTED, 'listed in the store since 2026-11-25: the install step is on');
    }

    /** Texts and links of each step: the overview in the same tab; install through the connector or the store page. */
    public function test_the_campaigns_step_texts_and_links(): void
    {
        $key = 'FeinwerkDataExploration_PromoCampaigns';
        $open = PromoLine::campaignsStep(['stage' => 'open', 'openUrl' => 'index.php?module=CoreHome&x=1', 'installUrl' => ''], 'de');
        self::assertSame([$key . 'OpenTitle', $key . 'OpenText', $key . 'OpenLink', 'index.php?module=CoreHome&x=1', false],
            array_values($open));
        $connector = PromoLine::campaignsStep(['stage' => 'install', 'openUrl' => '', 'installUrl' => 'index.php?module=FeinwerkMarketplaceConnector&action=index&fwmc_open=FeinwerkCampaignManager'], 'en');
        self::assertSame([$key . 'Title', $key . 'InstallText', $key . 'InstallLink', false],
            [$connector['title'], $connector['text'], $connector['link'], $connector['external']]);
        $store = PromoLine::campaignsStep(['stage' => 'install', 'openUrl' => '', 'installUrl' => ''], 'de');
        self::assertSame([$key . 'StoreLink', true], [$store['link'], $store['external']]);
        self::assertSame('https://plugins.feinwerk.io/de/plugins/FeinwerkCampaignManager?mtm_campaign=plugin-promo'
            . '&mtm_source=FeinwerkDataExploration&mtm_content=campaigns', $store['url']);
        self::assertStringStartsWith('https://plugins.feinwerk.io/en/plugins/', PromoLine::campaignsStep(['stage' => 'install', 'openUrl' => '', 'installUrl' => ''], 'fr')['url']);
        self::assertNull(PromoLine::campaignsStep(['stage' => 'platform', 'openUrl' => '', 'installUrl' => ''], 'de'), 'today\'s tip');
        self::assertNull(PromoLine::campaignsStep(['stage' => 'open', 'openUrl' => '', 'installUrl' => ''], 'de'), 'no overview, no step');

        $translate = static function (string $key): string {
            return $key;
        };
        $platform = PromoLine::variables('campaigns', 'de', $translate);
        self::assertSame([$key . 'Link', true], [$platform['link'], $platform['external']], 'without a step: today\'s tip');
        $line = PromoLine::variables('campaigns', 'de', $translate, 'campaigns', false, null, ['stage' => 'open', 'openUrl' => 'index.php?a=1', 'installUrl' => '']);
        self::assertSame([$key . 'OpenLink', 'index.php?a=1', false, 'campaigns'], [$line['link'], $line['url'], $line['external'], $line['placement']]);
    }

    /** The tip line follows the step: none leaves campaigns out on every day, open leads to the overview. */
    public function test_the_line_follows_the_campaigns_step(): void
    {
        $nothing = static function (): bool {
            return false;
        };
        $translate = static function (string $key): string {
            return $key;
        };
        $env = ['ecommerce' => false, 'demo' => false, 'wordpress' => false, 'superUser' => false, 'marketplace' => false];
        $seen = static function (array $campaigns) use ($env, $nothing, $translate): array {
            $lines = [];
            for ($day = 0; $day < 24; $day++) {
                $line = PromoLine::lineFor(['day' => $day, 'campaigns' => $campaigns] + $env, $nothing, $translate, 'de');
                $lines[$line['id']] = $line;
            }

            return $lines;
        };
        self::assertArrayNotHasKey('campaigns', $seen(['stage' => 'none', 'openUrl' => '', 'installUrl' => '']));
        $open = $seen(['stage' => 'open', 'openUrl' => 'index.php?module=CoreHome', 'installUrl' => '']);
        self::assertSame(['index.php?module=CoreHome', false], [$open['campaigns']['url'], $open['campaigns']['external']]);
        $old = $seen(['stage' => 'platform', 'openUrl' => '', 'installUrl' => '']);
        self::assertStringStartsWith('https://campaigns.feinwerk.io/de/matomo?', $old['campaigns']['url']);
    }
}

/** A connector release with the UI setting switched off. */
class PromotionsOff
{
    public static function areEnabled(): bool
    {
        return false;
    }
}

/** A connector release before the UI setting. */
class PromotionsWithoutSwitch
{

}
