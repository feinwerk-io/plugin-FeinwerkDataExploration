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
 * Goal metrics against Goals.get, and Matomo segments applied by core's own segment SQL.
 *
 * @group FeinwerkDataExploration
 */
class GoalAndSegmentParityTest extends SystemTestCase
{
    use ParityHelper;

    /** @var ExplorationFixture */
    public static $fixture = null;

    private const GOAL_METRICS = ['nb_conversions', 'revenue', 'conversion_rate'];

    public function testAllGoalsPerDayMatchGoalsGet(): void
    {
        $ours = $this->ourRows(['dimensions' => ['date'], 'metrics' => self::GOAL_METRICS], 'range', ExplorationFixture::RANGE);

        $expected = [];
        foreach (ExplorationFixture::DATES as $date) {
            $expected[$date] = $this->goalMetrics('day', $date, []);
        }

        $this->assertEquals(self::pick($expected, self::GOAL_METRICS), self::pick($ours, self::GOAL_METRICS));
        $this->assertEquals(2, $expected['2026-03-29']['nb_conversions'], 'fixture: two goals in one visit');
    }

    public function testSingleGoalMatchesGoalsGet(): void
    {
        $definition = ['dimensions' => ['date'], 'metrics' => self::GOAL_METRICS, 'idGoal' => ExplorationFixture::GOAL_BROCHURE];
        $ours = $this->ourRows($definition, 'range', ExplorationFixture::RANGE);

        $expected = [];
        foreach (ExplorationFixture::DATES as $date) {
            $metrics = $this->goalMetrics('day', $date, ['idGoal' => ExplorationFixture::GOAL_BROCHURE]);
            $expected[$date] = $metrics + ['nb_visits' => 0];
        }
        $ours += array_fill_keys(array_diff(ExplorationFixture::DATES, array_keys($ours)), ['nb_conversions' => 0, 'revenue' => 0, 'conversion_rate' => 0]);

        $this->assertEquals(self::pick($expected, self::GOAL_METRICS), self::pick($ours, self::GOAL_METRICS));
    }

    public function testBrowserGoalMetricsMatch(): void
    {
        $ours = $this->ourRows(['dimensions' => ['browser'], 'metrics' => self::GOAL_METRICS], 'range', ExplorationFixture::RANGE);

        $expected = [];
        foreach ($this->coreRows('DevicesDetection.getBrowsers', 'range', ExplorationFixture::RANGE) as $browser => $row) {
            $expected[$browser] = $this->goalMetrics('range', ExplorationFixture::RANGE, ['segment' => $row['__metadata']['segment']]);
        }

        $this->assertEquals(self::pick($expected, self::GOAL_METRICS), self::pick($ours, self::GOAL_METRICS));
    }

    public function testVisitSegmentMatchesCore(): void
    {
        $this->assertVisitMetricsMatch('DevicesDetection.getBrowsers', [], 'browser', 'range', ExplorationFixture::RANGE, false, null, [], 'countryCode==de');
    }

    public function testActionSegmentOnVisitScopeMatchesCore(): void
    {
        // a visit query with an action segment: core wraps the query (inner GROUP BY log_visit.idvisit)
        $this->assertVisitMetricsMatch('DevicesDetection.getBrowsers', [], 'browser', 'range', ExplorationFixture::RANGE, false, null, [], 'pageUrl=@%2Fblog');
        foreach (['2026-03-27', '2026-03-28', '2026-03-29'] as $date) { // the days with Video events
            $this->assertVisitMetricsMatch('UserCountry.getCountry', [], 'country', 'day', $date, false, null, [], 'eventCategory==Video');
        }
    }

    public function testActionSegmentOnActionScopeMatchesCore(): void
    {
        $segment = 'browserCode==CH';
        $expected = [];
        foreach ($this->coreRows('Actions.getPageUrls', 'range', ExplorationFixture::RANGE, ['flat' => 1, 'segment' => $segment]) as $label => $row) {
            $expected[$label] = (int) $row['nb_hits'];
        }
        $actual = [];
        foreach ($this->ourRows(['dimensions' => ['pageUrl'], 'metrics' => ['nb_pageviews']], 'range', ExplorationFixture::RANGE, $segment) as $label => $row) {
            $actual[self::path($label)] = (int) $row['nb_pageviews'];
        }
        ksort($expected);
        ksort($actual);

        $this->assertNotEmpty($expected);
        $this->assertSame($expected, $actual);
    }

    public function testEventValueUnderConversionSegmentIsNotMultiplied(): void
    {
        // visitConvertedGoalId joins log_conversion on idvisit: a visit with two matching conversions repeats every
        // action row twice. Expected values come from the visit log of exactly those visits.
        $segment = 'visitConvertedGoalId>=1';
        $visits = \Piwik\API\Request::processRequest('Live.getLastVisitsDetails', [
            'idSite' => 1, 'period' => 'range', 'date' => ExplorationFixture::RANGE, 'segment' => $segment, 'filter_limit' => -1,
        ]);

        $expected = [];
        foreach ($visits->getRows() as $visit) {
            $browser = $visit->getColumn('browserName');
            $expected[$browser] = $expected[$browser] ?? ['nb_events' => 0, 'sum_event_value' => 0.0];
            foreach ($visit->getColumn('actionDetails') as $action) {
                if ($action['type'] === 'event') {
                    $expected[$browser]['nb_events']++;
                    $expected[$browser]['sum_event_value'] += (float) ($action['eventValue'] ?? 0);
                }
            }
        }
        $expected = array_filter($expected, fn ($row) => $row['nb_events'] > 0);

        $ours = $this->ourRows(['dimensions' => ['browser'], 'metrics' => ['nb_events', 'sum_event_value']], 'range', ExplorationFixture::RANGE, $segment);
        $ours = array_filter($ours, fn ($row) => $row['nb_events'] > 0); // converted visits of other browsers had no events

        $this->assertEquals(['Chrome' => ['nb_events' => 2, 'sum_event_value' => 3.75]], $expected, 'fixture: V1 and V8 convert, V8 twice');
        $this->assertEquals(self::pick($expected, ['nb_events', 'sum_event_value']), self::pick($ours, ['nb_events', 'sum_event_value']));
    }

    /**
     * Goal metrics of Goals.get, with the conversion rate as a fraction of the visits of VisitsSummary.get.
     */
    private function goalMetrics(string $period, string $date, array $params): array
    {
        $goals = $this->coreRows('Goals.get', $period, $date, $params);
        $goal = reset($goals) ?: [];
        $summaryParams = array_intersect_key($params, ['segment' => 1]);
        $summary = $this->coreRows('VisitsSummary.get', $period, $date, $summaryParams);
        $visits = (int) (reset($summary)['nb_visits'] ?? 0);

        return [
            'nb_conversions' => (int) ($goal['nb_conversions'] ?? 0),
            'revenue' => (float) ($goal['revenue'] ?? 0),
            'conversion_rate' => $visits === 0 ? 0.0 : round(($goal['nb_visits_converted'] ?? 0) / $visits, 4),
        ];
    }
}

GoalAndSegmentParityTest::$fixture = new ExplorationFixture();
