<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Saved;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\ColumnProbe;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\CustomDimensionSource;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\LabelFormatter;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedReportUnavailable;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedStateDefinition;

/**
 * Saved reports (2026-11-24): a saved state becomes the definition the page would send (vue/src/state.ts parseState,
 * withGoalMetrics, filterGroups.ts), so a report shows the table its page shows.
 *
 * @group FeinwerkDataExploration
 */
class SavedStateDefinitionTest extends TestCase
{
    public function testPlainState(): void
    {
        $this->assertSame([
            'dimensions' => ['browser', 'pageUrl'],
            'metrics' => ['nb_visits', 'nb_pageviews'],
            'sort' => 'nb_pageviews',
            'sortOrder' => 'asc',
            'limit' => 250,
        ], $this->definition([
            'fwdeDims' => 'browser,pageUrl', 'fwdeMetrics' => 'nb_visits,nb_pageviews', 'fwdeSort' => 'nb_pageviews',
            'fwdeOrder' => 'asc', 'fwdeLimit' => '250', 'fwdeView' => 'chart', 'fwdeMenu' => 'Goals_Goals',
        ]));
    }

    public function testFallbacksForSortAndLimit(): void
    {
        $definition = $this->definition(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits', 'fwdeSort' => 'nb_hits',
            'fwdeOrder' => 'up', 'fwdeLimit' => '5000']);
        $this->assertSame(['nb_visits', 'desc', 100], [$definition['sort'], $definition['sortOrder'], $definition['limit']]);
        $this->assertSame('secondary_label', $this->definition(['fwdeDims' => 'browser,country', 'fwdeMetrics' => 'nb_visits',
            'fwdeSort' => 'secondary_label'])['sort']);
    }

    public function testGroupsWithTheirSwitches(): void
    {
        $definition = $this->definition(['fwdeDims' => 'browser,pageUrl', 'fwdeMetrics' => 'nb_visits',
            'fwdeFilters' => 'label~contains~fire;secondary_label~notContains~shop|any:nb_visits~gt~5;label~equals~Chrome',
            'fwdeMatch' => 'any']);
        $this->assertSame(['match' => 'any', 'groups' => [
            ['match' => 'all', 'filters' => [
                ['target' => 'label', 'operator' => 'contains', 'value' => 'fire'],
                ['target' => 'secondary_label', 'operator' => 'notContains', 'value' => 'shop'],
            ]],
            ['match' => 'any', 'filters' => [
                ['target' => 'nb_visits', 'operator' => 'gt', 'value' => 5.0],
                ['target' => 'label', 'operator' => 'equals', 'value' => 'Chrome'],
            ]],
        ]], $definition['filterGroups']);
    }

    /** review focus 4: a state from before filter groups, its rate filter entered in percent */
    public function testLegacyFiltersAndRatesInPercent(): void
    {
        $definition = $this->definition(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits,bounce_rate',
            'fwdeFilters' => 'bounce_rate~gt~50;label~contains~a%3Bb~c', 'fwdeMatch' => 'any']);
        $this->assertSame(['match' => 'all', 'groups' => [['match' => 'any', 'filters' => [
            ['target' => 'bounce_rate', 'operator' => 'gt', 'value' => 0.5],
            ['target' => 'label', 'operator' => 'contains', 'value' => 'a;b~c'],
        ]]]], $definition['filterGroups']);
    }

    public function testBrokenConditionsAreDropped(): void
    {
        $definition = $this->definition(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits',
            'fwdeFilters' => 'label~bogus~x;nb_visits~gt~abc;secondary_label~contains~x;label~contains~%E0%A4%A;'
                . 'label~contains~%zz;nb_actions~lt~3;label~equals~Chrome']);
        $this->assertSame([['target' => 'label', 'operator' => 'equals', 'value' => 'Chrome']],
            $definition['filterGroups']['groups'][0]['filters']);
        $this->assertArrayNotHasKey('filterGroups', $this->definition(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits',
            'fwdeFilters' => 'label~bogus~x']));
    }

    /**
     * Release review 2026-11-25: a pattern the server cannot compile and a number that is not finite are dropped like
     * any broken condition, so the saved state always gives a definition the API takes.
     */
    public function testUnusablePatternsAndNumbersAreDropped(): void
    {
        $definition = $this->definition(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits',
            'fwdeFilters' => 'label~matches~(;nb_visits~gt~1e999;nb_visits~gt~-1e999;label~matches~^Chr']);
        $this->assertSame([['target' => 'label', 'operator' => 'matches', 'value' => '^Chr']],
            $definition['filterGroups']['groups'][0]['filters']);
        $this->assertNotFalse(json_encode($definition));
    }

    public function testAChosenGoalBecomesTheGoalsOwnMetrics(): void
    {
        $definition = $this->definition(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits,nb_conversions,conversion_rate',
            'fwdeGoal' => '3', 'fwdeSort' => 'nb_conversions', 'fwdeFilters' => 'conversion_rate~gt~10']);
        $this->assertSame(['nb_visits', 'goal_3_nb_conversions', 'goal_3_conversion_rate'], $definition['metrics']);
        $this->assertSame('goal_3_nb_conversions', $definition['sort']);
        $this->assertSame(['target' => 'goal_3_conversion_rate', 'operator' => 'gt', 'value' => 0.1],
            $definition['filterGroups']['groups'][0]['filters'][0]);
        // goal 9 is gone: the all-goals metrics stay
        $this->assertSame(['nb_conversions'], $this->definition(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_conversions',
            'fwdeGoal' => '9'])['metrics']);
    }

    public function testADeletedGoalsMetricIsLeftOut(): void
    {
        $definition = $this->definition(['fwdeDims' => 'browser', 'fwdeMetrics' => 'goal_9_revenue,goal_3_revenue',
            'fwdeSort' => 'goal_9_revenue', 'fwdeFilters' => 'goal_9_revenue~gt~1']);
        $this->assertSame(['goal_3_revenue'], $definition['metrics']);
        $this->assertSame('goal_3_revenue', $definition['sort']);
        $this->assertArrayNotHasKey('filterGroups', $definition);
    }

    public function testNoMetricLeftIsRefused(): void
    {
        $this->assertUnavailable('FeinwerkDataExploration_ErrorSavedNoMetric', [],
            ['fwdeDims' => 'browser', 'fwdeMetrics' => 'goal_9_revenue']);
    }

    /** rows and pivot (2026-11-24): three row dimensions and a pivot with its limit */
    public function testThreeDimensionsAndAPivot(): void
    {
        $this->assertSame([
            'dimensions' => ['browser', 'country', 'pageTitle'],
            'metrics' => ['nb_visits'],
            'sort' => 'tertiary_label',
            'sortOrder' => 'asc',
            'limit' => 100,
            'filterGroups' => ['match' => 'all', 'groups' => [['match' => 'all', 'filters' => [
                ['target' => 'tertiary_label', 'operator' => 'contains', 'value' => 'shop'],
            ]]]],
            'pivot' => ['dimension' => 'deviceType', 'limit' => 10],
        ], $this->definition(['fwdeDims' => 'browser,country,pageTitle', 'fwdeMetrics' => 'nb_visits', 'fwdeSort' => 'tertiary_label',
            'fwdeOrder' => 'asc', 'fwdeFilters' => 'tertiary_label~contains~shop', 'fwdePivot' => 'deviceType', 'fwdePivotLimit' => '10']));
    }

    /** review focus 4: an older state reads as before */
    public function testOlderStateHasNoPivot(): void
    {
        $this->assertArrayNotHasKey('pivot', $this->definition(['fwdeDims' => 'browser,country', 'fwdeMetrics' => 'nb_visits']));
        $this->assertSame('nb_visits', $this->definition(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits', 'fwdeSort' => 'tertiary_label'])['sort'],
            'a third label without a third dimension falls back');
    }

    /** a pivot the definition cannot take is dropped, as the page drops it from a link; its limit falls back or fits */
    public function testPivotFallbacks(): void
    {
        $pivot = fn (array $state) => $this->definition($state + ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits'])['pivot'] ?? null;

        $this->assertNull($pivot(['fwdePivot' => 'customDimension7']), 'a gone dimension');
        $this->assertNull($pivot(['fwdePivot' => 'browser']), 'a row dimension');
        $this->assertNull($pivot(['fwdePivot' => 'date']), 'Date');
        $this->assertSame(['dimension' => 'deviceType', 'limit' => 5], $pivot(['fwdePivot' => 'deviceType', 'fwdePivotLimit' => '7']));
        $this->assertSame(['dimension' => 'deviceType', 'limit' => 5], $pivot(['fwdePivot' => 'deviceType']));
        $tenMetrics = 'nb_visits,nb_actions,nb_pageviews,bounce_rate,avg_time_on_site,nb_uniq_visitors,sum_visit_length,nb_events,nb_downloads,nb_outlinks';
        $this->assertSame(['dimension' => 'deviceType', 'limit' => 5], $this->definition(['fwdeDims' => 'browser', 'fwdeMetrics' => $tenMetrics,
            'fwdePivot' => 'deviceType', 'fwdePivotLimit' => '10'])['pivot'], '10 values by 10 metrics pass 60 cells');
        $this->assertArrayNotHasKey('pivot', $this->definition(['fwdeDims' => 'browser', 'fwdeMetrics' => $tenMetrics, 'fwdePivot' => 'weekday']),
            'seven days by 10 metrics pass 60 cells');
    }

    public function testAGoneDimensionIsRefusedNamingIt(): void
    {
        $this->assertUnavailable('FeinwerkDataExploration_ErrorSavedDimensionGone', ['customDimension7'],
            ['fwdeDims' => 'browser,customDimension7', 'fwdeMetrics' => 'nb_visits']);
        $this->assertUnavailable('FeinwerkDataExploration_ErrorSavedDimensionGone', [''],
            ['fwdeMetrics' => 'nb_visits']);
    }

    private function assertUnavailable(string $key, array $params, array $state): void
    {
        try {
            $this->definition($state);
            $this->fail('expected ' . $key);
        } catch (SavedReportUnavailable $e) {
            $this->assertSame([$key, $params, 400], [$e->getTranslationKey(), $e->getParams(), $e->getCode()]);
        }
    }

    private function definition(array $state): array
    {
        $noCustomDimensions = new class implements CustomDimensionSource {
            public function activeFor(int $idSite): array
            {
                return [];
            }
        };
        $dimensions = new DimensionCatalog(fn (string $coreId) => $coreId, $noCustomDimensions,
            new LabelFormatter(fn (string $key, array $params = []) => $key), new ColumnProbe(fn () => []));
        $metrics = new MetricCatalog(fn (int $idSite) => [3 => 'Newsletter'], fn (int $idSite) => false);
        return SavedStateDefinition::toDefinition($state, 1, $dimensions, $metrics, new Limits());
    }
}
