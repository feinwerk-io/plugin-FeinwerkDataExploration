<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Catalog;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;

/**
 * @group FeinwerkDataExploration
 */
class MetricCatalogTest extends TestCase
{
    public function testIdsAndScopesMatchSpec(): void
    {
        $expected = [
            'nb_visits' => 'visit',
            'nb_uniq_visitors' => 'visit',
            'nb_actions' => 'visit',
            'bounce_rate' => 'visit',
            'avg_time_on_site' => 'visit',
            'nb_actions_per_visit' => 'visit',
            'max_actions' => 'visit',
            'sum_visit_length' => 'visit',
            'nb_conversions' => 'goal',
            'conversion_rate' => 'goal',
            'revenue' => 'goal',
            'nb_visits_converted' => 'goal',
            'revenue_per_visit' => 'goal',
            'nb_pageviews' => 'action',
            'nb_uniq_pageviews' => 'action',
            'nb_events' => 'action',
            'sum_event_value' => 'action',
            'avg_event_value' => 'action',
            'nb_downloads' => 'action',
            'nb_outlinks' => 'action',
            'nb_searches' => 'action',
        ];

        $scopes = [];
        foreach ((new MetricCatalog())->all() as $id => $metric) {
            $this->assertSame($id, $metric->id);
            $scopes[$id] = $metric->scope;
        }

        $this->assertSame($expected, $scopes);
    }

    /** More metrics (2026-11-24): every metric carries its unit and how it aggregates */
    public function testFixedMetricsCarryUnitAndAggregation(): void
    {
        $expected = [
            'nb_visits' => ['count', 'sum'], 'nb_uniq_visitors' => ['count', 'distinct'], 'nb_actions' => ['count', 'sum'],
            'bounce_rate' => ['rate', 'ratio'], 'avg_time_on_site' => ['seconds', 'average'],
            'nb_actions_per_visit' => ['number', 'average'], 'max_actions' => ['count', 'max'],
            'sum_visit_length' => ['seconds', 'sum'], 'nb_conversions' => ['count', 'sum'],
            'conversion_rate' => ['rate', 'ratio'], 'revenue' => ['money', 'sum'], 'nb_visits_converted' => ['count', 'sum'],
            'revenue_per_visit' => ['money', 'average'], 'nb_pageviews' => ['count', 'sum'],
            'nb_uniq_pageviews' => ['count', 'distinct'], 'nb_events' => ['count', 'sum'], 'sum_event_value' => ['number', 'sum'],
            'avg_event_value' => ['number', 'average'], 'nb_downloads' => ['count', 'sum'], 'nb_outlinks' => ['count', 'sum'],
            'nb_searches' => ['count', 'sum'],
        ];
        $actual = [];
        foreach ((new MetricCatalog())->all() as $id => $metric) {
            $actual[$id] = [$metric->unit, $metric->aggregation];
        }
        $this->assertSame($expected, $actual);
    }

    public function testGoalsAddThreeMetricsEachInOrder(): void
    {
        $catalog = $this->catalog([3 => 'Newsletter', 1 => 'Contact'], false);
        $goalIds = array_values(array_filter(array_keys($catalog->all(1)), fn ($id) => str_starts_with($id, 'goal_')));

        $this->assertSame([
            'goal_3_nb_conversions', 'goal_3_conversion_rate', 'goal_3_revenue',
            'goal_1_nb_conversions', 'goal_1_conversion_rate', 'goal_1_revenue',
        ], $goalIds);
        $revenue = $catalog->get('goal_3_revenue', 1);
        $this->assertSame(['goal', 'goals', '3', 'Newsletter', 'money', 'sum'],
            [$revenue->scope, $revenue->group, $revenue->goalKey, $revenue->goalName, $revenue->unit, $revenue->aggregation]);
        $this->assertSame('goals', $catalog->get('nb_conversions', 1)->group);
        $this->assertNull($catalog->get('nb_conversions', 1)->goalKey);
    }

    public function testEcommerceOnlyWhenEnabled(): void
    {
        $ids = fn (bool $ecommerce) => array_values(array_filter(array_keys($this->catalog([], $ecommerce)->all(1)),
            fn ($id) => str_starts_with($id, 'goal_ecommerce')));

        $this->assertSame([], $ids(false));
        $this->assertSame([
            'goal_ecommerceOrder_nb_conversions', 'goal_ecommerceOrder_revenue', 'goal_ecommerceOrder_items',
            'goal_ecommerceOrder_avg_order_revenue', 'goal_ecommerceOrder_conversion_rate',
            'goal_ecommerceAbandonedCart_nb_conversions', 'goal_ecommerceAbandonedCart_revenue',
        ], $ids(true));
        $this->assertSame('ecommerce', $this->catalog([], true)->get('goal_ecommerceOrder_items', 1)->group);
        $this->assertNull($this->catalog([], false)->get('goal_ecommerceOrder_items', 1));
    }

    public function testUnknownOrDeletedGoalIsNull(): void
    {
        $catalog = $this->catalog([3 => 'Newsletter'], false);
        $this->assertNull($catalog->get('goal_9_revenue', 1));
        $this->assertNull($catalog->get('goal_3_items', 1));
        $this->assertNull($catalog->get('goal_abc_revenue', 1));
        // without a website a per-goal id is resolved by its pattern (scope and unit for the engine)
        $this->assertSame('money', $catalog->get('goal_9_revenue')->unit);
    }

    public function testDerivedRules(): void
    {
        $catalog = $this->catalog([3 => 'Newsletter'], true);
        $rules = fn (string $id) => [
            $catalog->get($id, 1)->hasShare(), $catalog->get($id, 1)->addsUp(),
            $catalog->get($id, 1)->zeroForMissing(), $catalog->get($id, 1)->isInteger(),
        ];
        $this->assertSame([true, false, true, true], $rules('nb_uniq_visitors'));
        $this->assertSame([false, false, false, false], $rules('revenue_per_visit'));
        $this->assertSame([false, false, false, true], $rules('max_actions'));
        $this->assertSame([false, false, false, true], $rules('avg_time_on_site'));
        $this->assertSame([false, false, false, false], $rules('goal_3_conversion_rate'));
        $this->assertSame([true, true, true, true], $rules('goal_3_nb_conversions'));
        $this->assertSame([true, true, true, false], $rules('goal_ecommerceOrder_revenue'));
        $this->assertSame([true, true, true, true], $rules('sum_visit_length'));
    }

    public function testMetaIsPureById(): void
    {
        $this->assertSame(['unit' => 'rate', 'aggregation' => 'ratio'], MetricCatalog::meta('goal_12_conversion_rate'));
        $this->assertSame(['unit' => 'money', 'aggregation' => 'average'], MetricCatalog::meta('goal_ecommerceOrder_avg_order_revenue'));
        $this->assertNull(MetricCatalog::meta('goal_ecommerceAbandonedCart_items'));
        $this->assertNull(MetricCatalog::meta('nb_hits'));
    }

    /** Review focus 2: Matomo stores goal names escaped; the metric's name shows the plain text */
    public function testGoalNamesAreDecodedAndPrefixTheName(): void
    {
        $catalog = $this->catalog([3 => 'Q&amp;A: &quot;Leads&quot;'], true);
        $translate = fn (string $key, array $params = []) => $key . ($params ? '(' . implode(',', $params) . ')' : '');

        $this->assertSame('Q&A: "Leads"', $catalog->get('goal_3_revenue', 1)->goalName);
        $this->assertSame('Q&A: "Leads": General_ColumnRevenue', $catalog->get('goal_3_revenue', 1)->label($translate));
        $this->assertSame('Ecommerce_Orders', $catalog->get('goal_ecommerceOrder_nb_conversions', 1)->label($translate));
        $this->assertSame('General_ColumnNbActions', $catalog->get('nb_actions', 1)->label($translate));
        $this->assertSame('FeinwerkDataExploration_ActionsOfTheseVisits', $catalog->get('nb_actions', 1)->label($translate, true));
    }

    /** final review: Matomo allows two goals with one name; their metrics must stay apart (export columns, list headings) */
    public function testGoalsWithTheSameNameAreToldApartByTheirId(): void
    {
        $catalog = $this->catalog([3 => 'Purchase', 4 => 'Purchase', 5 => 'Lead'], false);
        $translate = fn (string $key) => $key;

        $this->assertSame('Purchase (3)', $catalog->get('goal_3_revenue', 1)->goalName);
        $this->assertSame('Purchase (4)', $catalog->get('goal_4_revenue', 1)->goalName);
        $this->assertSame('Lead', $catalog->get('goal_5_revenue', 1)->goalName);
        $this->assertSame('Purchase (4): General_ColumnRevenue', $catalog->get('goal_4_revenue', 1)->label($translate));
    }

    public function testEveryMetricOfAWebsiteHasADescription(): void
    {
        $catalog = $this->catalog([3 => 'Newsletter'], true);
        $translate = fn (string $key, array $params = []) => $key . ($params ? '(' . implode(',', $params) . ')' : '');

        foreach ($catalog->all(1) as $id => $metric) {
            $this->assertNotSame('', MetricCatalog::description($id, $translate, $metric->goalName), $id);
        }
        $this->assertSame('FeinwerkDataExploration_MetricDescriptionGoalRevenue(Newsletter)',
            MetricCatalog::description('goal_3_revenue', $translate, 'Newsletter'));
    }

    private function catalog(array $goals, bool $ecommerce): MetricCatalog
    {
        return new MetricCatalog(fn (int $idSite) => $goals, fn (int $idSite) => $ecommerce);
    }

    public function testUnknownIdReturnsNull(): void
    {
        $this->assertNull((new MetricCatalog())->get('nb_hits'));
    }

    public function testKnownIdReturnsDefinition(): void
    {
        $this->assertSame('action', (new MetricCatalog())->get('nb_events')->scope);
    }

    public function testMetricKinds(): void
    {
        $kinds = array_map(fn ($metric) => $metric->kind, (new MetricCatalog())->all());

        $this->assertSame('pageview', $kinds['nb_pageviews']);
        $this->assertSame('pageview', $kinds['nb_uniq_pageviews']);
        $this->assertSame('event', $kinds['nb_events']);
        $this->assertSame('event', $kinds['sum_event_value']);
        $this->assertSame('download', $kinds['nb_downloads']);
        $this->assertSame('outlink', $kinds['nb_outlinks']);
        $this->assertSame('event', $kinds['avg_event_value']);
        $this->assertSame('search', $kinds['nb_searches']);
        $this->assertNull($kinds['max_actions']);
        $this->assertNull($kinds['nb_visits']);
        $this->assertNull($kinds['revenue']);
    }

    public function testEveryVisitMetricHasActionScopeName(): void
    {
        foreach ((new MetricCatalog())->all() as $id => $metric) {
            if ($metric->scope === 'visit') {
                $this->assertNotNull($metric->nameInActionScopeKey, $id);
                $this->assertNotSame($metric->nameKey, $metric->nameInActionScopeKey, $id);
            } else {
                $this->assertNull($metric->nameInActionScopeKey, $id);
            }
        }
    }

    /** KPI card colours (2026-11-24): less is better only for the bounce rate and abandoned carts */
    public function testLowerIsBetter(): void
    {
        foreach (['bounce_rate', 'goal_ecommerceAbandonedCart_nb_conversions', 'goal_ecommerceAbandonedCart_revenue'] as $id) {
            $this->assertTrue(MetricCatalog::lowerIsBetter($id), $id);
        }
        foreach (['nb_visits', 'avg_time_on_site', 'goal_ecommerceOrder_revenue', 'goal_3_conversion_rate'] as $id) {
            $this->assertFalse(MetricCatalog::lowerIsBetter($id), $id);
        }
    }
}
