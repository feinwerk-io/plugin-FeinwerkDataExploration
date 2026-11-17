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

/**
 * The fallback list of the paid-plugin recommendations (Peter, 2026-11-16): what the store sells, minus the plugins
 * present here; texts in the lang files, URLs only in PHP.
 */
class PaidPluginCatalogTest extends TestCase
{
    private static function key(): callable
    {
        return static function (string $key): string {
            return $key;
        };
    }

    public function test_plugins_present_here_and_the_recommending_plugin_are_left_out(): void
    {
        $list = PaidPluginCatalog::notInstalled(
            ['FeinwerkChannelGrouping', 'FeinwerkAuthSaml', 'CoreHome'],
            '5.14.2',
            'en',
            self::key(),
            'FeinwerkSearchReport'
        );
        $slugs = array_column($list, 'slug');

        self::assertSame(['FeinwerkConversionsApi', 'FeinwerkTrackingSpamScore', 'FeinwerkTrackingRouter', 'FeinwerkSentinelPro',
            'FeinwerkVisitJourney', 'FeinwerkEmailReportsPro'], $slugs);
        self::assertSame([
            'slug' => 'FeinwerkConversionsApi',
            'name' => 'FeinwerkDataExploration_PaidPluginConversionsApiName',
            'description' => 'FeinwerkDataExploration_PaidPluginConversionsApiText',
            'price' => '',
            'image' => '',
            'storeUrl' => 'https://plugins.feinwerk.io/en/plugins/FeinwerkConversionsApi',
        ], $list[0]);
    }

    /**
     * Like the connector: only plugins with a stable build for this Matomo (store, 2026-11-16). On Matomo 6 only the
     * one-build plugins, betas and release candidates included; on an old Matomo 5 not the plugins with a higher floor.
     */
    public function test_only_plugins_this_matomo_can_install(): void
    {
        $slugs = static function (string $version): array {
            return array_column(PaidPluginCatalog::notInstalled([], $version, 'en', self::key(), 'FeinwerkDataExploration'), 'slug');
        };

        self::assertSame(['FeinwerkSentinelPro', 'FeinwerkEmailReportsPro'], $slugs('6.0.0-rc1'));
        self::assertSame(['FeinwerkSentinelPro', 'FeinwerkEmailReportsPro'], $slugs('6.0.0-b6'));
        self::assertSame(['FeinwerkSentinelPro', 'FeinwerkEmailReportsPro'], $slugs('6.1.0'));
        self::assertCount(9, $slugs('5.14.2'));
        self::assertNotContains('FeinwerkVisitJourney', $slugs('5.11.0'), 'needs 5.12.0');
        self::assertContains('FeinwerkVisitJourney', $slugs('5.12.0'));
        self::assertNotContains('FeinwerkChannelGrouping', $slugs('5.2.0'), 'needs 5.3.0');
        self::assertSame([], $slugs('7.0.0'), 'no line known');
        self::assertFalse(PaidPluginCatalog::installableOn('FeinwerkAdCostImport', '5.14.2'));
        // the connector needs PHP 8.1: below it the fallback list is all there is, and it must not offer what fails
        self::assertSame(['FeinwerkTrackingRouter'], array_column(
            PaidPluginCatalog::notInstalled([], '5.14.2', 'en', self::key(), 'FeinwerkDataExploration', '7.4.33'),
            'slug'
        ));
        self::assertTrue(PaidPluginCatalog::installableOn('FeinwerkAuthSaml', '5.14.2', '8.1.0'));
        self::assertFalse(PaidPluginCatalog::installableOn('FeinwerkAuthSaml', '5.14.2', '8.0.30'));
    }

    /** Demo mode: the whole list but the recommending plugin, also plugins installed here or without a build. */
    public function test_demo_shows_the_whole_list(): void
    {
        $all = array_column(PaidPluginCatalog::all('de', self::key(), 'FeinwerkSearchReport'), 'slug');

        self::assertSame(array_values(array_diff(array_keys(PaidPluginCatalog::PLUGINS), ['FeinwerkSearchReport'])), $all);
        self::assertContains('FeinwerkAuthSaml', $all, 'no build for Matomo 6, shown anyway');
    }

    public function test_the_store_page_follows_the_viewers_language(): void
    {
        self::assertSame('https://plugins.feinwerk.io/de/plugins/FeinwerkAuthSaml', PaidPluginCatalog::storeUrl('FeinwerkAuthSaml', 'de'));
        self::assertSame('https://plugins.feinwerk.io/de/plugins/FeinwerkAuthSaml', PaidPluginCatalog::storeUrl('FeinwerkAuthSaml', 'de-DE'));
        self::assertSame('https://plugins.feinwerk.io/en/plugins/FeinwerkAuthSaml', PaidPluginCatalog::storeUrl('FeinwerkAuthSaml', 'fr'));
    }

    /** Only what the store sells: never a parked plugin, never a free one. */
    public function test_the_list_holds_only_paid_plugins_on_sale(): void
    {
        self::assertCount(9, PaidPluginCatalog::PLUGINS);
        self::assertArrayNotHasKey('FeinwerkAdCostImport', PaidPluginCatalog::PLUGINS);
        self::assertArrayNotHasKey('FeinwerkDataExploration', PaidPluginCatalog::PLUGINS);
        self::assertArrayNotHasKey('FeinwerkSentinel', PaidPluginCatalog::PLUGINS);
    }

    /** Every name and one-liner exists in both languages, without dashes; no address in the lang files (Marketplace). */
    public function test_every_text_exists_in_german_and_english_and_no_url_in_the_lang_files(): void
    {
        foreach (['de', 'en'] as $language) {
            $raw = (string) file_get_contents(__DIR__ . '/../../../lang/' . $language . '.json');
            $texts = json_decode($raw, true)['FeinwerkDataExploration'];
            foreach (PaidPluginCatalog::PLUGINS as [$stem]) {
                foreach (['Name', 'Text'] as $part) {
                    $key = 'PaidPlugin' . $stem . $part;
                    self::assertNotEmpty($texts[$key] ?? '', $language . ' ' . $key);
                    self::assertSame(0, preg_match('/[\x{2013}\x{2014}]/u', $texts[$key]), $language . ' ' . $key);
                }
            }
            self::assertNotEmpty($texts['PremiumAskSuperUser'] ?? '');
            self::assertStringNotContainsString('http', $raw, $language . '.json');
        }
    }
}
