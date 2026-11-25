<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Engine;

use Piwik\Plugins\FeinwerkDataExploration\Engine\Explorer;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Definition\Comparison;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ComparisonTable;

/**
 * The arithmetic of comparison columns (comparison design 3): kinds, shares, changes, day offsets.
 *
 * @group FeinwerkDataExploration
 */
class ComparisonTableTest extends TestCase
{
    public function testKindFollowsWhatDiffersFromThePage(): void
    {
        $this->assertSame('segment', ComparisonTable::kind(new Comparison('browserCode==FF', 'day', '2026-03-28'), '', 'day', '2026-03-28'));
        $this->assertSame('period', ComparisonTable::kind(new Comparison('', 'day', '2026-03-27'), '', 'day', '2026-03-28'));
        $this->assertSame('period', ComparisonTable::kind(new Comparison('', 'month', '2026-03-28'), '', 'day', '2026-03-28'));
        $this->assertSame('both', ComparisonTable::kind(new Comparison('browserCode==FF', 'day', '2026-03-27'), '', 'day', '2026-03-28'));
        // the page's own column again counts as a segment column (nothing to change against)
        $this->assertSame('segment', ComparisonTable::kind(new Comparison('', 'day', '2026-03-28'), '', 'day', '2026-03-28'));
    }

    public function testKindByTheResolvedPeriods(): void
    {
        $this->assertSame('segment', ComparisonTable::kindOf(new Comparison('browserCode==FF', 'range', 'last7'), '', true));
        $this->assertSame('period', ComparisonTable::kindOf(new Comparison('', 'day', '2026-03-27'), '', false));
        $this->assertSame('both', ComparisonTable::kindOf(new Comparison('browserCode==FF', 'day', '2026-03-27'), '', false));
    }

    public function testDaysBetweenLocalDates(): void
    {
        $this->assertSame(-2, ComparisonTable::daysBetween('2026-03-29', '2026-03-27'));
        $this->assertSame(31, ComparisonTable::daysBetween('2026-03-01', '2026-04-01'));
        $this->assertSame(1, ComparisonTable::daysBetween('2026-03-28', '2026-03-29'), 'across the DST change in Europe');
    }

    public function testShareOfTheColumnTotal(): void
    {
        $this->assertSame(88.9, ComparisonTable::share(8, 9));
        $this->assertSame(100.0, ComparisonTable::share(9, 9));
        $this->assertNull(ComparisonTable::share(3, 0));
        $this->assertNull(ComparisonTable::share(3, null));
        $this->assertNull(ComparisonTable::share(null, 9));
    }

    public function testChangeOfThePagesPeriodAgainstTheColumn(): void
    {
        $this->assertSame(10.0, ComparisonTable::change(110, 100));
        $this->assertSame(-25.0, ComparisonTable::change(0.3, 0.4));
        $this->assertSame(0.0, ComparisonTable::change(5, 5));
        $this->assertNull(ComparisonTable::change(5, 0));
        $this->assertNull(ComparisonTable::change(5, null));
        $this->assertNull(ComparisonTable::change(null, 5));
    }

    public function testShiftDateAcrossMonthAndYear(): void
    {
        $this->assertSame('2026-03-27', ComparisonTable::shiftDate('2026-03-29', -2));
        $this->assertSame('2026-02-28', ComparisonTable::shiftDate('2026-03-01', -1));
        $this->assertSame('2027-01-02', ComparisonTable::shiftDate('2026-12-31', 2));
    }

    /** counts and sums, also a goal's (more metrics, 2026-11-24): their rows add up to the column, so a share makes sense */
    public function testShareMetricsAreCountsAndSums(): void
    {
        foreach (['nb_visits', 'nb_pageviews', 'revenue', 'sum_event_value', 'nb_conversions', 'nb_uniq_visitors', 'goal_3_revenue',
            'goal_3_nb_conversions', 'goal_ecommerceOrder_items', 'nb_visits_converted', 'sum_visit_length'] as $metric) {
            $this->assertTrue(MetricCatalog::byId($metric)->hasShare(), $metric);
        }
        foreach (['bounce_rate', 'conversion_rate', 'avg_time_on_site', 'nb_actions_per_visit', 'max_actions', 'avg_event_value',
            'revenue_per_visit', 'goal_3_conversion_rate', 'goal_ecommerceOrder_avg_order_revenue'] as $metric) {
            $this->assertFalse(MetricCatalog::byId($metric)->hasShare(), $metric);
        }
    }

    /** a row missing in a column: 0 for counts and sums, nothing for rates and averages; whole numbers stay whole */
    public function testMetricValueOfNewMetrics(): void
    {
        $this->assertSame(0, Explorer::metricValue('goal_3_nb_conversions', null));
        $this->assertSame(0.0, Explorer::metricValue('goal_3_revenue', null));
        $this->assertNull(Explorer::metricValue('revenue_per_visit', null));
        $this->assertNull(Explorer::metricValue('goal_3_conversion_rate', null));
        $this->assertSame(4, Explorer::metricValue('goal_3_nb_conversions', '4'));
        $this->assertSame(30, Explorer::metricValue('sum_visit_length', '30'));
        $this->assertSame(12.5, Explorer::metricValue('goal_ecommerceOrder_avg_order_revenue', '12.5'));
        $this->assertSame(7, Explorer::metricValue('max_actions', '7'));
    }
}
