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
 * Action dimensions and action metrics against Matomo's Actions, Events and CustomDimensions reports.
 *
 * @group FeinwerkDataExploration
 */
class ActionParityTest extends SystemTestCase
{
    use ParityHelper;

    /** @var ExplorationFixture */
    public static $fixture = null;

    public function testPageUrlMatchesActionsPageUrls(): void
    {
        foreach ($this->periods() as [$period, $date]) {
            $this->assertActionReportMatches('Actions.getPageUrls', ['flat' => 1], 'pageUrl',
                ['nb_hits' => 'nb_pageviews', 'nb_visits' => 'nb_uniq_pageviews'], $period, $date, [self::class, 'path']);
        }
    }

    public function testPageTitleMatches(): void
    {
        foreach ($this->periods() as [$period, $date]) {
            $this->assertActionReportMatches('Actions.getPageTitles', ['flat' => 1], 'pageTitle',
                ['nb_hits' => 'nb_pageviews', 'nb_visits' => 'nb_uniq_pageviews'], $period, $date);
        }
    }

    public function testEventDimensionsMatch(): void
    {
        $cases = ['Events.getCategory' => 'eventCategory', 'Events.getAction' => 'eventAction', 'Events.getName' => 'eventName'];
        foreach ($cases as $method => $dimension) {
            foreach ($this->periods() as [$period, $date]) {
                $this->assertActionReportMatches($method, [], $dimension,
                    ['nb_events' => 'nb_events', 'sum_event_value' => 'sum_event_value'], $period, $date);
            }
        }
    }

    public function testDownloadsAndOutlinksMatch(): void
    {
        foreach ($this->periods() as [$period, $date]) {
            $this->assertActionReportMatches('Actions.getDownloads', ['flat' => 1], 'downloadUrl',
                ['nb_hits' => 'nb_downloads', 'nb_visits' => 'nb_visits'], $period, $date, [self::class, 'hostAndPath']);
            $this->assertActionReportMatches('Actions.getOutlinks', ['flat' => 1], 'outlinkUrl',
                ['nb_hits' => 'nb_outlinks', 'nb_visits' => 'nb_visits'], $period, $date, [self::class, 'hostAndPath']);
        }
    }

    public function testSiteSearchKeywordMatches(): void
    {
        foreach ($this->periods() as [$period, $date]) {
            $this->assertActionReportMatches('Actions.getSiteSearchKeywords', [], 'siteSearchKeyword',
                ['nb_visits' => 'nb_visits'], $period, $date);
        }
    }

    public function testActionCustomDimensionMatches(): void
    {
        foreach ($this->periods() as [$period, $date]) {
            $this->assertActionReportMatches('CustomDimensions.getCustomDimension', ['idDimension' => self::$fixture->idActionDimension],
                'customDimension' . self::$fixture->idActionDimension, ['nb_hits' => 'nb_pageviews', 'nb_visits' => 'nb_uniq_pageviews'], $period, $date);
        }
    }

    public function testTotalsMatchActionsGet(): void
    {
        foreach (ExplorationFixture::DATES as $date) {
            $actions = $this->coreRows('Actions.get', 'day', $date);
            $core = reset($actions);

            $sum = function (string $dimension, string $metric) use ($date): int {
                return (int) array_sum(array_column($this->ourRows(['dimensions' => [$dimension], 'metrics' => [$metric]], 'day', $date), $metric));
            };

            $this->assertSame((int) $core['nb_pageviews'], $sum('pageUrl', 'nb_pageviews'), $date);
            $this->assertSame((int) $core['nb_uniq_pageviews'], $sum('pageUrl', 'nb_uniq_pageviews'), $date);
            $this->assertSame((int) $core['nb_downloads'], $sum('downloadUrl', 'nb_downloads'), $date);
            $this->assertSame((int) $core['nb_outlinks'], $sum('outlinkUrl', 'nb_outlinks'), $date);
        }
    }

    public function testActionMetricsByVisitDimensionMatchSegmentedActionsGet(): void
    {
        $metrics = ['nb_pageviews', 'nb_uniq_pageviews', 'nb_downloads', 'nb_outlinks'];
        $ours = $this->ourRows(['dimensions' => ['browser'], 'metrics' => $metrics], 'range', ExplorationFixture::RANGE);

        foreach ($this->coreRows('DevicesDetection.getBrowsers', 'range', ExplorationFixture::RANGE) as $browser => $row) {
            $actions = $this->coreRows('Actions.get', 'range', ExplorationFixture::RANGE, ['segment' => $row['__metadata']['segment']]);
            $core = reset($actions);
            foreach ($metrics as $metric) {
                $this->assertEquals((int) $core[$metric], (int) $ours[$browser][$metric], "$browser $metric");
            }
        }
    }

    public function testEventsPerPageUrl(): void
    {
        $actual = [];
        foreach ($this->ourRows(['dimensions' => ['pageUrl', 'eventCategory'], 'metrics' => ['nb_events']], 'range', ExplorationFixture::RANGE) as $label => $row) {
            [$url, $category] = explode(' | ', $label);
            $actual[strtok(self::path($url), '?') . ' | ' . $category] = $row['nb_events'];
        }
        ksort($actual);

        $this->assertSame(['/blog/a | Video' => 2, '/blog/b | Video' => 1, '/shop | Video' => 2, '/tips | Video' => 1], $actual);
    }

    public function testPageviewsAndEventsOfAUrlWithAmpersandShareOneRow(): void
    {
        // the API escapes the label column like core's reports (SafeDecodeLabel); the renderers decode it again
        $rows = $this->ourRows(['dimensions' => ['pageUrl'], 'metrics' => ['nb_pageviews', 'nb_events']], 'day', '2026-03-30');
        $tips = array_filter($rows, fn ($label) => strpos($label, '/tips') !== false, ARRAY_FILTER_USE_KEY);

        $this->assertSame(['example.com/tips?a=1&amp;b=2'], array_keys($tips));
        $this->assertSame(1, (int) $tips['example.com/tips?a=1&amp;b=2']['nb_pageviews']);
        $this->assertSame(1, (int) $tips['example.com/tips?a=1&amp;b=2']['nb_events']);
    }

    public function testTwoDimensionsMatchCoreWithSegment(): void
    {
        $ours = $this->ourRows(['dimensions' => ['browser', 'pageUrl'], 'metrics' => ['nb_pageviews']], 'range', ExplorationFixture::RANGE);

        $browsers = $this->coreRows('DevicesDetection.getBrowsers', 'range', ExplorationFixture::RANGE);
        $this->assertGreaterThanOrEqual(3, count($browsers));
        foreach ($browsers as $browser => $row) {
            $expected = [];
            foreach ($this->coreRows('Actions.getPageUrls', 'range', ExplorationFixture::RANGE, ['flat' => 1, 'segment' => $row['__metadata']['segment']]) as $label => $page) {
                // core escapes its labels, our page URL is the secondary label here and stays as stored
                $expected[html_entity_decode($label, ENT_QUOTES)] = (int) $page['nb_hits'];
            }
            $actual = [];
            foreach ($ours as $label => $page) {
                [$ourBrowser, $url] = explode(' | ', $label);
                if ($ourBrowser === $browser) {
                    $actual[self::path($url)] = (int) $page['nb_pageviews'];
                }
            }
            ksort($expected);
            ksort($actual);
            $this->assertSame($expected, $actual, $browser);
        }
    }

    public function testImpossibleCombinationReturnsEmptyTable(): void
    {
        $this->assertSame([], $this->ourRows(['dimensions' => ['pageUrl', 'downloadUrl'], 'metrics' => ['nb_visits']], 'range', ExplorationFixture::RANGE));
        $this->assertSame([], $this->ourRows(['dimensions' => ['pageTitle', 'eventCategory'], 'metrics' => ['nb_events']], 'range', ExplorationFixture::RANGE));
    }

    private function periods(): array
    {
        $periods = array_map(fn (string $date) => ['day', $date], ExplorationFixture::DATES);
        $periods[] = ['range', ExplorationFixture::RANGE];
        return $periods;
    }

    /**
     * Every core row with a value equals our row with the same (mapped) label.
     *
     * @param array<string, string> $metrics core column => our metric
     */
    private function assertActionReportMatches(string $method, array $params, string $dimension, array $metrics, string $period, string $date, ?callable $ourLabel = null): void
    {
        $expected = [];
        foreach ($this->coreRows($method, $period, $date, $params) as $label => $row) {
            foreach ($metrics as $coreColumn => $ourMetric) {
                if (!empty($row[array_key_first($metrics)])) {
                    $expected[$label][$ourMetric] = (float) ($row[$coreColumn] ?? 0);
                }
            }
        }

        $actual = [];
        foreach ($this->ourRows(['dimensions' => [$dimension], 'metrics' => array_values($metrics)], $period, $date) as $label => $row) {
            foreach ($metrics as $ourMetric) {
                $actual[$ourLabel ? $ourLabel($label) : $label][$ourMetric] = (float) $row[$ourMetric];
            }
        }

        ksort($expected);
        ksort($actual);
        if ($period === 'range') {
            $this->assertNotEmpty($expected, "$method has no rows for the fixture range");
        }
        $this->assertEquals($expected, $actual, "$dimension vs $method, $period $date");
    }

    /**
     * "https://www.example.com/files/a.pdf" and "example.com/files/a.pdf" → "example.com/files/a.pdf"
     */
    private static function hostAndPath(string $url): string
    {
        return preg_replace('~^([a-z]+://)?(www\.)?~i', '', $url);
    }
}

ActionParityTest::$fixture = new ExplorationFixture();
