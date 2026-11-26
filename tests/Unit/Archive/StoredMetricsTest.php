<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Archive;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Archive\StoredMetrics;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Query\ExplorationQueryBuilder;

/**
 * What an archived exploration stores per metric (design 2026-11-25): sums and maxima as they are, rates and averages as
 * their numerator and denominator so any period is exact, distinct counts for a day only.
 */
class StoredMetricsTest extends TestCase
{
    private const RATIOS = ['bounce_rate', 'avg_time_on_site', 'nb_actions_per_visit', 'conversion_rate', 'revenue_per_visit',
        'avg_event_value', 'goal_1_conversion_rate', 'goal_ecommerceOrder_conversion_rate', 'goal_ecommerceOrder_avg_order_revenue'];

    public function testColumnsPerAggregation(): void
    {
        $this->assertSame(['nb_visits'], StoredMetrics::columns('nb_visits'));
        $this->assertSame(['max_actions'], StoredMetrics::columns('max_actions'));
        $this->assertSame(['nb_uniq_visitors'], StoredMetrics::columns('nb_uniq_visitors'));
        $this->assertSame(['bounce_rate__n', 'bounce_rate__d'], StoredMetrics::columns('bounce_rate'));
        $this->assertSame(['goal_2_conversion_rate__n', 'goal_2_conversion_rate__d'], StoredMetrics::columns('goal_2_conversion_rate'));
    }

    public function testOps(): void
    {
        $this->assertSame([
            'nb_visits' => 'sum', 'bounce_rate__n' => 'sum', 'bounce_rate__d' => 'sum', 'max_actions' => 'max',
            'nb_uniq_visitors' => 'skip', 'goal_1_revenue' => 'sum',
        ], StoredMetrics::aggregationOps(['nb_visits', 'bounce_rate', 'max_actions', 'nb_uniq_visitors', 'goal_1_revenue']));
    }

    public function testValueFromPair(): void
    {
        $this->assertSame(0.33, StoredMetrics::value('bounce_rate', ['bounce_rate__n' => 1, 'bounce_rate__d' => 3], false));
        $this->assertSame(0.0, StoredMetrics::value('bounce_rate', ['bounce_rate__n' => 0, 'bounce_rate__d' => 0], false));
        $this->assertSame(123.0, StoredMetrics::value('avg_time_on_site', ['avg_time_on_site__n' => 370, 'avg_time_on_site__d' => 3], false));
        $this->assertSame(0.0, StoredMetrics::value('goal_ecommerceOrder_avg_order_revenue',
            ['goal_ecommerceOrder_avg_order_revenue__n' => 0, 'goal_ecommerceOrder_avg_order_revenue__d' => 0], false));
        $this->assertSame(0.6667, StoredMetrics::value('goal_1_conversion_rate',
            ['goal_1_conversion_rate__n' => 2, 'goal_1_conversion_rate__d' => 3], false));
        $this->assertSame(42, StoredMetrics::value('nb_visits', ['nb_visits' => 42], false));
        // a missing column of a sum is 0, as a missing row is
        $this->assertSame(0, StoredMetrics::value('nb_visits', [], false));
    }

    public function testDistinctOnlyOnADay(): void
    {
        $this->assertSame(7, StoredMetrics::value('nb_uniq_visitors', ['nb_uniq_visitors' => 7], true));
        $this->assertNull(StoredMetrics::value('nb_uniq_visitors', ['nb_uniq_visitors' => 7], false));
    }

    public function testEveryCatalogRatioHasAPair(): void
    {
        $builder = new ExplorationQueryBuilder(static fn () => '', static fn (string $table): string => $table);
        foreach (self::RATIOS as $id) {
            $this->assertNotNull(MetricCatalog::meta($id), $id);
            $this->assertContains(MetricCatalog::meta($id)['aggregation'], ['ratio', 'average'], $id);
            foreach ([false, true] as $isAction) {
                $pair = $builder->componentSql($id, $isAction);
                $this->assertIsArray($pair, $id);
                $this->assertCount(2, $pair, $id);
            }
            $this->assertArrayHasKey(StoredMetrics::precisionKey($id), StoredMetrics::PRECISION, $id);
        }
        $this->assertNull($builder->componentSql('nb_visits', false));
    }
}
