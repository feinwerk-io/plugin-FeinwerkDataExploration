<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\System;

use Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures\ExplorationFixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * Explorations with one visit dimension must give the numbers of Matomo's archived reports.
 *
 * @group FeinwerkDataExploration
 */
class VisitDimensionParityTest extends SystemTestCase
{
    use ParityHelper;

    /** @var ExplorationFixture */
    public static $fixture = null;

    public function testBrowserMatchesDevicesDetection(): void
    {
        foreach (ExplorationFixture::DATES as $date) {
            $this->assertVisitMetricsMatch('DevicesDetection.getBrowsers', [], 'browser', 'day', $date, true);
        }
        $this->assertVisitMetricsMatch('DevicesDetection.getBrowsers', [], 'browser', 'range', ExplorationFixture::RANGE, false);
    }

    public function testDeviceTypeMatches(): void
    {
        foreach (ExplorationFixture::DATES as $date) {
            $this->assertVisitMetricsMatch('DevicesDetection.getType', [], 'deviceType', 'day', $date, true);
        }
    }

    public function testOperatingSystemMatches(): void
    {
        foreach (ExplorationFixture::DATES as $date) {
            $this->assertVisitMetricsMatch('DevicesDetection.getOsFamilies', [], 'operatingSystem', 'day', $date, false);
        }
    }

    public function testResolutionMatches(): void
    {
        foreach (ExplorationFixture::DATES as $date) {
            $this->assertVisitMetricsMatch('Resolution.getResolution', [], 'resolution', 'day', $date, true);
        }
    }

    public function testCountryRegionCityMatch(): void
    {
        foreach (ExplorationFixture::DATES as $date) {
            $this->assertVisitMetricsMatch('UserCountry.getCountry', [], 'country', 'day', $date, false);
            $this->assertVisitMetricsMatch('UserCountry.getRegion', [], 'region', 'day', $date, false);
            $this->assertVisitMetricsMatch('UserCountry.getCity', [], 'city', 'day', $date, false);
        }
        $this->assertVisitMetricsMatch('UserCountry.getCountry', [], 'country', 'range', ExplorationFixture::RANGE, false);
    }

    public function testLanguageMatches(): void
    {
        foreach (ExplorationFixture::DATES as $date) {
            $this->assertVisitMetricsMatch('UserLanguage.getLanguage', [], 'language', 'day', $date, false);
        }
    }

    public function testChannelTypeMatches(): void
    {
        foreach (ExplorationFixture::DATES as $date) {
            $this->assertVisitMetricsMatch('Referrers.getReferrerType', [], 'channelType', 'day', $date, false);
        }
        $this->assertVisitMetricsMatch('Referrers.getReferrerType', [], 'channelType', 'range', ExplorationFixture::RANGE, false);
    }

    public function testReferrerNameMatches(): void
    {
        $expected = [];
        foreach (['Referrers.getWebsites', 'Referrers.getSearchEngines'] as $method) {
            foreach ($this->coreRows($method, 'range', ExplorationFixture::RANGE) as $label => $row) {
                $expected[$label] = ['nb_visits' => (float) $row['nb_visits'], 'nb_actions' => (float) $row['nb_actions']];
            }
        }
        $ours = self::pick($this->ourRows(['dimensions' => ['referrerName'], 'metrics' => ['nb_visits', 'nb_actions']], 'range', ExplorationFixture::RANGE), ['nb_visits', 'nb_actions']);

        ksort($expected);
        $this->assertSame(['Google', 'partner-blog.example.org'], array_keys($expected));
        $this->assertEquals($expected, array_intersect_key($ours, $expected));
    }

    public function testCampaignNameMatches(): void
    {
        $this->assertVisitMetricsMatch('Referrers.getCampaigns', [], 'campaignName', 'range', ExplorationFixture::RANGE, false, null, ['Campaign name not defined']);
    }

    public function testVisitorTypeMatchesVisitFrequency(): void
    {
        foreach (ExplorationFixture::DATES as $date) {
            $frequency = $this->coreRows('VisitFrequency.get', 'day', $date);
            $core = reset($frequency);
            $ours = $this->ourRows(['dimensions' => ['visitorType'], 'metrics' => ['nb_visits']], 'day', $date);

            $this->assertEquals((int) $core['nb_visits_new'], (int) ($ours['New visitor']['nb_visits'] ?? 0), $date);
            $this->assertEquals((int) $core['nb_visits_returning'], (int) ($ours['Returning visitor']['nb_visits'] ?? 0), $date);
        }
    }

    public function testVisitConvertedMatchesVisitsSummary(): void
    {
        foreach (ExplorationFixture::DATES as $date) {
            $summary = $this->coreRows('VisitsSummary.get', 'day', $date);
            $core = reset($summary);
            $ours = $this->ourRows(['dimensions' => ['visitConverted'], 'metrics' => ['nb_visits']], 'day', $date);

            $this->assertEquals((int) $core['nb_visits_converted'], (int) ($ours['Yes']['nb_visits'] ?? 0), $date);
            $this->assertEquals((int) $core['nb_visits'] - (int) $core['nb_visits_converted'], (int) ($ours['No']['nb_visits'] ?? 0), $date);
        }
    }

    public function testEntryAndExitPagesMatch(): void
    {
        $cases = [
            ['Actions.getEntryPageUrls', 'entryPageUrl', 'entry_nb_visits', true],
            ['Actions.getEntryPageTitles', 'entryPageTitle', 'entry_nb_visits', false],
            ['Actions.getExitPageUrls', 'exitPageUrl', 'exit_nb_visits', true],
            ['Actions.getExitPageTitles', 'exitPageTitle', 'exit_nb_visits', false],
        ];
        foreach ($cases as [$method, $dimension, $column, $isUrl]) {
            foreach (array_merge(ExplorationFixture::DATES, [ExplorationFixture::RANGE]) as $date) {
                $period = strpos($date, ',') ? 'range' : 'day';
                $expected = [];
                foreach ($this->coreRows($method, $period, $date, ['flat' => 1]) as $label => $row) {
                    if (!empty($row[$column])) {
                        $expected[$label] = (int) $row[$column];
                    }
                }
                $actual = [];
                foreach ($this->ourRows(['dimensions' => [$dimension], 'metrics' => ['nb_visits']], $period, $date) as $label => $row) {
                    if (str_ends_with($label, ' not defined')) {
                        continue; // e.g. visits that ended with an outlink: Matomo's exit page report leaves them out
                    }
                    $actual[$isUrl ? self::path($label) : $label] = (int) $row['nb_visits'];
                }
                ksort($expected);
                ksort($actual);
                $this->assertSame($expected, $actual, "$dimension vs $method, $date");
            }
        }
    }

    public function testVisitCustomDimensionMatches(): void
    {
        $this->assertVisitMetricsMatch(
            'CustomDimensions.getCustomDimension',
            ['idDimension' => self::$fixture->idVisitDimension],
            'customDimension' . self::$fixture->idVisitDimension,
            'range',
            ExplorationFixture::RANGE,
            false
        );
    }
}

VisitDimensionParityTest::$fixture = new ExplorationFixture();
