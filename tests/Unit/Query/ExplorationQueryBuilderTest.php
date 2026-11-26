<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Query;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\CustomDimensionSource;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\ColumnProbe;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\LabelFormatter;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\FeinwerkDataExploration\Query\ExplorationQueryBuilder;
use Piwik\Plugins\FeinwerkDataExploration\Query\QueryContext;

/**
 * @group FeinwerkDataExploration
 */
class ExplorationQueryBuilderTest extends TestCase
{
    private const START = '2026-03-01 00:00:00';
    private const END = '2026-03-31 23:59:59';

    /** @var array<int, array> arguments of every Stage A call */
    private array $stageACalls = [];

    public function testVisitScopeUsesLogVisitWithoutGroupBy(): void
    {
        $this->build('{"dimensions": ["browser"], "metrics": ["nb_visits"]}');

        [$select, $from, $where, $bind, $groupBy] = $this->stageACalls[0];
        $this->assertSame(['log_visit'], $from);
        $this->assertNull($groupBy);
        $this->assertStringContainsString('log_visit.idvisit AS fwde_idvisit', $select);
        $this->assertStringContainsString("NULLIF(NULLIF(log_visit.config_browser_name, ''), 'UNK') AS fwde_k1", $select);
        $this->assertSame('log_visit.visit_last_action_time >= ? AND log_visit.visit_last_action_time <= ? AND log_visit.idsite = ?', $where);
        $this->assertSame([self::START, self::END, 1], $bind);
    }

    public function testVisitScopeSelectsOnlyNeededVisitColumns(): void
    {
        $this->build('{"dimensions": ["browser"], "metrics": ["nb_visits"]}');
        $select = $this->stageACalls[0][0];
        $this->assertStringNotContainsString('fwde_time', $select);
        $this->assertStringNotContainsString('fwde_actions', $select);
        $this->assertStringNotContainsString('fwde_idvisitor', $select);

        $this->stageACalls = [];
        $this->build('{"dimensions": ["browser"], "metrics": ["avg_time_on_site", "bounce_rate", "nb_uniq_visitors"]}');
        $select = $this->stageACalls[0][0];
        $this->assertStringContainsString('log_visit.visit_total_time AS fwde_time', $select);
        $this->assertStringContainsString('log_visit.visit_total_actions AS fwde_actions', $select);
        $this->assertStringContainsString('log_visit.idvisitor AS fwde_idvisitor', $select);
    }

    public function testVisitScopeMetricExpressions(): void
    {
        $sql = $this->build('{"dimensions": ["browser"], "metrics": ["nb_visits", "nb_uniq_visitors", "nb_actions", "bounce_rate", "avg_time_on_site"]}')['sql'];

        $this->assertStringContainsString('COUNT(*) AS nb_visits', $sql);
        $this->assertStringContainsString('COUNT(DISTINCT fwde_a.fwde_idvisitor) AS nb_uniq_visitors', $sql);
        $this->assertStringContainsString('SUM(fwde_a.fwde_actions) AS nb_actions', $sql);
        $this->assertStringContainsString('ROUND(SUM(CASE WHEN fwde_a.fwde_actions <= 1 THEN 1 ELSE 0 END) / COUNT(*), 2) AS bounce_rate', $sql);
        $this->assertStringContainsString('ROUND(SUM(fwde_a.fwde_time) / COUNT(*), 0) AS avg_time_on_site', $sql);
        $this->assertStringNotContainsString('fwde_v', $sql);
        $this->assertStringContainsString('GROUP BY fwde_a.fwde_k1 ', $sql);
    }

    public function testActionScopeGroupsByKeysAndIdvisit(): void
    {
        $this->build('{"dimensions": ["pageUrl", "browser"], "metrics": ["nb_pageviews"]}');

        [$select, $from, $where, $bind, $groupBy] = $this->stageACalls[0];
        $this->assertSame('fwde_k1, fwde_k2, log_link_visit_action.idvisit', $groupBy);
        $this->assertSame('log_link_visit_action', $from[0]);
        $this->assertSame(['table' => 'log_action', 'tableAlias' => 'fwde_url', 'joinOn' => 'fwde_url.idaction = log_link_visit_action.idaction_url'], $from[1]);
        $this->assertContains('log_visit', $from, 'the browser key reads log_visit');
        $this->assertContains([
            'table' => 'log_action', 'tableAlias' => 'fwde_k1a',
            'joinOn' => 'fwde_k1a.idaction = log_link_visit_action.idaction_url AND fwde_k1a.type IN (1, 10)',
        ], $from);
        $this->assertStringStartsWith('log_link_visit_action.server_time >= ? AND log_link_visit_action.server_time <= ? AND log_link_visit_action.idsite = ?', $where);
        $this->assertStringContainsString('log_link_visit_action.idvisit AS fwde_idvisit', $select);
    }

    public function testActionScopeWithoutVisitKeyDoesNotJoinLogVisit(): void
    {
        $this->build('{"dimensions": ["pageUrl"], "metrics": ["nb_pageviews"]}');

        $this->assertNotContains('log_visit', $this->stageACalls[0][1]);
    }

    public function testActionScopeExcludesNullActionKeys(): void
    {
        $this->build('{"dimensions": ["pageUrl", "browser"], "metrics": ["nb_pageviews"]}');

        $where = $this->stageACalls[0][2];
        $this->assertStringEndsWith(' AND fwde_k1a.name IS NOT NULL', $where);
        $this->assertStringNotContainsString('config_browser_name IS NOT NULL', $where);
    }

    public function testActionScopeMeasures(): void
    {
        $this->build('{"dimensions": ["pageUrl"], "metrics": ["nb_pageviews", "nb_uniq_pageviews", "nb_events", "nb_downloads", "nb_outlinks"]}');

        $select = $this->stageACalls[0][0];
        $pageview = 'log_link_visit_action.idaction_event_category IS NULL AND log_link_visit_action.idaction_url IS NOT NULL'
            . ' AND (fwde_url.type = 1 OR fwde_url.type IS NULL)';
        $this->assertStringContainsString('COUNT(DISTINCT CASE WHEN ' . $pageview . ' THEN log_link_visit_action.idlink_va END) AS fwde_pageviews', $select);
        $this->assertStringContainsString('COUNT(DISTINCT CASE WHEN ' . $pageview . ' THEN log_link_visit_action.idaction_url END) AS fwde_uniq_pageviews', $select);
        $this->assertStringContainsString('COUNT(DISTINCT CASE WHEN log_link_visit_action.idaction_event_category IS NOT NULL THEN log_link_visit_action.idlink_va END) AS fwde_events', $select);
        $this->assertStringContainsString('COUNT(DISTINCT CASE WHEN fwde_url.type = 3 THEN log_link_visit_action.idlink_va END) AS fwde_downloads', $select);
        $this->assertStringContainsString('COUNT(DISTINCT CASE WHEN fwde_url.type = 2 THEN log_link_visit_action.idlink_va END) AS fwde_outlinks', $select);
    }

    public function testUniquePageviewsOfPageTitlesCountTitles(): void
    {
        // Matomo's page title report counts the visits per title, so two URLs with one title are one unique pageview
        $this->build('{"dimensions": ["pageTitle"], "metrics": ["nb_uniq_pageviews"]}');

        $this->assertStringContainsString('THEN log_link_visit_action.idaction_name END) AS fwde_uniq_pageviews', $this->stageACalls[0][0]);
    }

    public function testUniquePageviewsOtherwiseCountUrls(): void
    {
        $this->build('{"dimensions": ["browser"], "metrics": ["nb_uniq_pageviews"]}');

        $this->assertStringContainsString('THEN log_link_visit_action.idaction_url END) AS fwde_uniq_pageviews', $this->stageACalls[0][0]);
    }

    public function testActionScopeVisitMetricJoinsLogVisit(): void
    {
        $sql = $this->build('{"dimensions": ["pageUrl"], "metrics": ["nb_pageviews", "bounce_rate"]}')['sql'];

        $this->assertStringContainsString(' INNER JOIN matomo_log_visit AS fwde_v ON fwde_v.idvisit = fwde_a.fwde_idvisit', $sql);
        $this->assertStringContainsString('ROUND(SUM(CASE WHEN fwde_v.visit_total_actions <= 1 THEN 1 ELSE 0 END) / COUNT(*), 2) AS bounce_rate', $sql);
        $this->assertStringContainsString('SUM(fwde_a.fwde_pageviews) AS nb_pageviews', $sql);
    }

    public function testActionMetricWithoutVisitMetricDoesNotJoinLogVisit(): void
    {
        $sql = $this->build('{"dimensions": ["pageUrl"], "metrics": ["nb_pageviews"]}')['sql'];

        $this->assertStringNotContainsString('fwde_v', $sql);
    }

    public function testGoalMetricAddsConversionSubqueryWithAllGoals(): void
    {
        $query = $this->build('{"dimensions": ["browser"], "metrics": ["nb_conversions", "conversion_rate", "revenue"]}');

        $this->assertStringContainsString(
            ' LEFT JOIN (SELECT log_conversion.idvisit AS idvisit, COUNT(*) AS fwde_conversions,'
            . ' SUM(CASE WHEN ABS(log_conversion.revenue) > 1000000000000 THEN 0 ELSE log_conversion.revenue END) AS fwde_revenue'
            . ' FROM matomo_log_conversion AS log_conversion WHERE log_conversion.idsite = ? AND log_conversion.server_time >= ?'
            . ' AND log_conversion.server_time <= ? AND log_conversion.idgoal >= 0 GROUP BY log_conversion.idvisit) AS fwde_c'
            . ' ON fwde_c.idvisit = fwde_a.fwde_idvisit',
            $query['sql']
        );
        $this->assertStringContainsString('COALESCE(SUM(fwde_c.fwde_conversions), 0) AS nb_conversions', $query['sql']);
        $this->assertStringContainsString('ROUND(SUM(CASE WHEN fwde_c.fwde_conversions > 0 THEN 1 ELSE 0 END) / COUNT(*), 4) AS conversion_rate', $query['sql']);
        $this->assertStringContainsString('ROUND(COALESCE(SUM(fwde_c.fwde_revenue), 0), 2) AS revenue', $query['sql']);
    }

    /** since more metrics (2026-11-24) idGoal narrows the all-goals columns by a conditional sum, not the subquery */
    public function testGoalMetricWithIdGoalNarrowsTheAllGoalsColumns(): void
    {
        $query = $this->build('{"dimensions": ["browser"], "metrics": ["nb_conversions"], "idGoal": 7}');

        $this->assertStringContainsString('SUM(CASE WHEN log_conversion.idgoal = 7 THEN 1 ELSE 0 END) AS fwde_conversions', $query['sql']);
        $this->assertStringContainsString('AND log_conversion.idgoal >= 0 GROUP BY', $query['sql']);
        $this->assertSame([self::START, self::END, 1, 1, self::START, self::END], $query['bind']);
    }

    public function testPerGoalColumnsAreConditionalSums(): void
    {
        $query = $this->build('{"dimensions": ["browser"], "metrics": ["goal_3_nb_conversions", "goal_3_conversion_rate", "goal_5_revenue"]}');
        $revenue = 'CASE WHEN ABS(log_conversion.revenue) > 1000000000000 THEN 0 ELSE log_conversion.revenue END';

        $this->assertStringContainsString('SUM(CASE WHEN log_conversion.idgoal = 3 THEN 1 ELSE 0 END) AS fwde_conv_g3', $query['sql']);
        $this->assertStringContainsString('SUM(CASE WHEN log_conversion.idgoal = 5 THEN ' . $revenue . ' ELSE 0 END) AS fwde_rev_g5', $query['sql']);
        $this->assertStringContainsString('COALESCE(SUM(fwde_c.fwde_conv_g3), 0) AS goal_3_nb_conversions', $query['sql']);
        $this->assertStringContainsString('ROUND(SUM(CASE WHEN fwde_c.fwde_conv_g3 > 0 THEN 1 ELSE 0 END) / COUNT(*), 4) AS goal_3_conversion_rate', $query['sql']);
        $this->assertStringContainsString('ROUND(COALESCE(SUM(fwde_c.fwde_rev_g5), 0), 2) AS goal_5_revenue', $query['sql']);
        $this->assertStringContainsString('AND log_conversion.idgoal >= 0 GROUP BY', $query['sql']);
        $this->assertSame([self::START, self::END, 1, 1, self::START, self::END], $query['bind']);
    }

    /** review focus 4: a definition mixing idGoal with per-goal and visit metrics never gives silent zeros */
    public function testIdGoalNarrowsOnlyTheAllGoalsMetrics(): void
    {
        $sql = $this->build('{"dimensions": ["browser"], "metrics": ["nb_conversions", "goal_5_nb_conversions", "nb_visits_converted"], "idGoal": 3}')['sql'];

        $this->assertStringContainsString('SUM(CASE WHEN log_conversion.idgoal = 3 THEN 1 ELSE 0 END) AS fwde_conversions', $sql);
        $this->assertStringContainsString('SUM(CASE WHEN log_conversion.idgoal = 5 THEN 1 ELSE 0 END) AS fwde_conv_g5', $sql);
        $this->assertStringContainsString('COUNT(*) AS fwde_conv_any', $sql);
        $this->assertStringContainsString('AND log_conversion.idgoal >= 0 GROUP BY', $sql);
    }

    public function testAbandonedCartsWidenTheIdgoalConditionOnlyWhenAsked(): void
    {
        $sql = $this->build('{"dimensions": ["browser"], "metrics": ["nb_conversions", "goal_ecommerceAbandonedCart_nb_conversions",'
            . ' "goal_ecommerceAbandonedCart_revenue"]}')['sql'];

        $this->assertStringContainsString('AND log_conversion.idgoal >= -1 GROUP BY', $sql);
        $this->assertStringContainsString('SUM(CASE WHEN log_conversion.idgoal >= 0 THEN 1 ELSE 0 END) AS fwde_conversions', $sql);
        $this->assertStringContainsString('SUM(CASE WHEN log_conversion.idgoal = -1 THEN 1 ELSE 0 END) AS fwde_carts', $sql);
        $this->assertStringContainsString('COALESCE(SUM(fwde_c.fwde_carts), 0) AS goal_ecommerceAbandonedCart_nb_conversions', $sql);
        $this->assertStringContainsString('ROUND(COALESCE(SUM(fwde_c.fwde_cart_rev), 0), 2) AS goal_ecommerceAbandonedCart_revenue', $sql);

        $this->assertStringContainsString('AND log_conversion.idgoal >= 0 GROUP BY',
            $this->build('{"dimensions": ["browser"], "metrics": ["goal_ecommerceOrder_nb_conversions"]}')['sql']);
    }

    public function testEcommerceAndVisitMetricSql(): void
    {
        $sql = $this->build('{"dimensions": ["browser"], "metrics": ["goal_ecommerceOrder_nb_conversions", "goal_ecommerceOrder_revenue",'
            . ' "goal_ecommerceOrder_items", "goal_ecommerceOrder_avg_order_revenue", "goal_ecommerceOrder_conversion_rate",'
            . ' "nb_visits_converted", "revenue_per_visit", "sum_visit_length"]}')['sql'];

        $this->assertStringContainsString('SUM(CASE WHEN log_conversion.idgoal = 0 THEN log_conversion.items ELSE 0 END) AS fwde_items', $sql);
        $this->assertStringContainsString('COALESCE(SUM(fwde_c.fwde_orders), 0) AS goal_ecommerceOrder_nb_conversions', $sql);
        $this->assertStringContainsString('ROUND(COALESCE(SUM(fwde_c.fwde_order_rev), 0), 2) AS goal_ecommerceOrder_revenue', $sql);
        $this->assertStringContainsString('COALESCE(SUM(fwde_c.fwde_items), 0) AS goal_ecommerceOrder_items', $sql);
        $this->assertStringContainsString('ROUND(COALESCE(SUM(fwde_c.fwde_order_rev) / NULLIF(SUM(fwde_c.fwde_orders), 0), 0), 2)'
            . ' AS goal_ecommerceOrder_avg_order_revenue', $sql);
        $this->assertStringContainsString('ROUND(SUM(CASE WHEN fwde_c.fwde_orders > 0 THEN 1 ELSE 0 END) / COUNT(*), 4)'
            . ' AS goal_ecommerceOrder_conversion_rate', $sql);
        $this->assertStringContainsString('SUM(CASE WHEN fwde_c.fwde_conv_any > 0 THEN 1 ELSE 0 END) AS nb_visits_converted', $sql);
        $this->assertStringContainsString('ROUND(COALESCE(SUM(fwde_c.fwde_rev_any), 0) / COUNT(*), 2) AS revenue_per_visit', $sql);
        $this->assertStringContainsString('SUM(fwde_a.fwde_time) AS sum_visit_length', $sql);
        $this->assertStringContainsString('log_visit.visit_total_time AS fwde_time', end($this->stageACalls)[0]);

        // next to an action dimension: the time of the visits behind the row
        $this->assertStringContainsString('SUM(fwde_v.visit_total_time) AS sum_visit_length',
            $this->build('{"dimensions": ["pageUrl"], "metrics": ["sum_visit_length"]}')['sql']);
    }

    public function testBindOrderStageAThenConversions(): void
    {
        $query = $this->build('{"dimensions": ["browser"], "metrics": ["nb_conversions"]}');

        $this->assertSame([self::START, self::END, 1, 1, self::START, self::END], $query['bind']);
    }

    /**
     * Saved reports (2026-11-24): the first level of a two-dimension report, each first value's totals over what the text
     * filters let through (a filter on the second dimension too), grouped by the first key only, no number filters,
     * no sort, no limit.
     */
    /** rows and pivot (2026-11-24): a deeper level of a report groups by its keys, the third one's filter still applies */
    public function testLevelGroupsByItsKeys(): void
    {
        [$definition, $dimensionDefinitions, $context, $builder] = $this->parts('{"dimensions": ["pageUrl", "browser", "deviceType"],'
            . ' "metrics": ["nb_visits"], "filters": [{"target": "tertiary_label", "operator": "equals", "value": "desktop"}]}');
        $query = $builder->buildLevel($definition, $dimensionDefinitions, 1, $context, [], [['/a', 'Firefox'], [null, 'Chrome']]);

        [$select, , $where, , $groupBy] = end($this->stageACalls);
        $this->assertStringContainsString('AS fwde_k2', $select);
        $this->assertStringNotContainsString('AS fwde_k3', $select);
        $this->assertSame('fwde_k1, fwde_k2, log_link_visit_action.idvisit', $groupBy);
        $this->assertStringContainsString('config_device_type', $where, 'the third dimension\'s filter still applies');
        $this->assertStringContainsString('CASE WHEN (fwde_a.fwde_k1 <=> ? AND fwde_a.fwde_k2 <=> ?) THEN 0 WHEN (fwde_a.fwde_k1 <=> ? AND fwde_a.fwde_k2 <=> ?) THEN 1 END AS fwde_row', $query['sql']);
    }

    public function testFirstLevelGroupsByTheFirstKeyOnly(): void
    {
        [$definition, $dimensionDefinitions, $context, $builder] = $this->parts('{"dimensions": ["pageUrl", "browser"],'
            . ' "metrics": ["nb_visits", "nb_pageviews"], "limit": 10, "filters": [{"target": "secondary_label", "operator":'
            . ' "contains", "value": "fire"}, {"target": "nb_visits", "operator": "gt", "value": 5}]}');
        $query = $builder->buildLevel($definition, $dimensionDefinitions, 1, $context, [], [['/a'], [null]]);

        [$select, , $where, , $groupBy] = end($this->stageACalls);
        $this->assertStringContainsString('AS fwde_k1', $select);
        $this->assertStringNotContainsString('AS fwde_k2', $select);
        $this->assertSame('fwde_k1, log_link_visit_action.idvisit', $groupBy);
        $this->assertStringContainsString('LIKE', $where, 'the second dimension\'s text filter still applies');
        $this->assertStringContainsString('CASE WHEN (fwde_a.fwde_k1 <=> ?) THEN 0 WHEN (fwde_a.fwde_k1 <=> ?) THEN 1 END AS fwde_row', $query['sql']);
        $this->assertStringContainsString('WHERE (fwde_a.fwde_k1 IN (?) OR fwde_a.fwde_k1 IS NULL) GROUP BY fwde_row HAVING fwde_row IS NOT NULL', $query['sql']);
        $this->assertStringNotContainsString('nb_visits > ?', $query['sql']);
        $this->assertStringNotContainsString('LIMIT', $query['sql']);
    }

    /** rows and pivot (2026-11-24): a third key in stage A, its filter and its sort */
    public function testThreeKeysWithTheirFilterAndSort(): void
    {
        $query = $this->build('{"dimensions": ["browser", "deviceType", "pageTitle"], "metrics": ["nb_visits"], "sort": "tertiary_label",'
            . ' "sortOrder": "asc", "filters": [{"target": "tertiary_label", "operator": "contains", "value": "shop"}]}');
        [$select, , $where, , $groupBy] = end($this->stageACalls);

        $this->assertStringContainsString('AS fwde_k3', $select);
        $this->assertSame('fwde_k1, fwde_k2, fwde_k3, log_link_visit_action.idvisit', $groupBy);
        $this->assertStringContainsString('LIKE', $where);
        $this->assertStringContainsString(' GROUP BY fwde_a.fwde_k1, fwde_a.fwde_k2, fwde_a.fwde_k3', $query['sql']);
        $this->assertStringContainsString(' ORDER BY fwde_k3 ASC, fwde_k1 ASC, fwde_k2 ASC LIMIT 100', $query['sql']);
    }

    /** the pivot values: grouped by the pivot key alone, ordered by the first metric, cut at the limit */
    public function testPivotValues(): void
    {
        [$definition, $dimensions, $context, $builder] = $this->parts('{"dimensions": ["browser"], "metrics": ["nb_visits", "bounce_rate"],'
            . ' "pivot": {"dimension": "deviceType", "limit": 3}}');
        $pivot = $this->dimensions()->get(1, 'deviceType');
        $query = $builder->buildPivotValues($definition, $dimensions, $pivot, 1, $context, [], 3);
        [$select, , , , $groupBy] = end($this->stageACalls);

        $this->assertStringContainsString('AS fwde_k2', $select, 'the pivot key follows the row keys');
        $this->assertStringNotContainsString('AS fwde_k1', $select);
        $this->assertStringContainsString(' GROUP BY fwde_a.fwde_k2 ORDER BY nb_visits DESC, fwde_k2 ASC LIMIT 3', $query['sql']);
        $this->assertStringContainsString('AS bounce_rate', $query['sql']);
        // Weekday: every value in its own order
        $all = $builder->buildPivotValues($definition, $dimensions, $pivot, 1, $context, [], null);
        $this->assertStringEndsWith(' GROUP BY fwde_a.fwde_k2 ORDER BY fwde_k2 ASC', $all['sql']);
        // MySQL before 8.0 (GroupingQuirk): the outer query sorts the grouped values
        $quirk = $builder->buildPivotValues($definition, $dimensions, $pivot, 1, $context, [], 3, true);
        $this->assertStringStartsWith('SELECT * FROM (SELECT ', $quirk['sql']);
        $this->assertStringEndsWith(' GROUP BY fwde_a.fwde_k2) AS fwde_g ORDER BY nb_visits DESC, fwde_k2 ASC LIMIT 3', $quirk['sql']);
    }

    /**
     * The archive's totals per pivot value (archived reports, 2026-11-25): bounded like the archive's other queries,
     * sorted by a stored column (a rate's alias is not selected in archive mode, only its pair).
     */
    public function testArchivedPivotValuesAreBounded(): void
    {
        [$definition, $dimensions, $context, $builder] = $this->parts('{"dimensions": ["browser"], "metrics": ["bounce_rate", "nb_visits"],'
            . ' "pivot": {"dimension": "deviceType", "limit": 3}}');
        $pivot = $this->dimensions()->get(1, 'deviceType');
        $query = $builder->withArchiveColumns()->buildPivotValues($definition, $dimensions, $pivot, 1, $context, [], 50000, false, 'nb_visits');
        $this->assertStringEndsWith(' GROUP BY fwde_a.fwde_k2 ORDER BY nb_visits DESC, fwde_k2 ASC LIMIT 50000', $query['sql']);
        $this->assertStringContainsString('AS bounce_rate__n', $query['sql']);
        $this->assertStringNotContainsString('AS bounce_rate,', $query['sql']);
    }

    /** the cells of the shown rows by pivot value, and the Others group of the rest */
    public function testPivotCellsAndOthers(): void
    {
        [$definition, $dimensions, $context, $builder] = $this->parts('{"dimensions": ["browser"], "metrics": ["nb_visits"],'
            . ' "pivot": {"dimension": "deviceType"}}');
        $pivot = $this->dimensions()->get(1, 'deviceType');
        $cells = $builder->buildPivotCells($definition, $dimensions, $pivot, 1, $context, [], [['Firefox'], ['Chrome']], ['smartphone', 'desktop'], false);

        $this->assertStringContainsString('CASE WHEN (fwde_a.fwde_k1 <=> ?) THEN 0 WHEN (fwde_a.fwde_k1 <=> ?) THEN 1 END AS fwde_row', $cells['sql']);
        $this->assertStringContainsString(', CASE WHEN fwde_a.fwde_k2 <=> ? THEN 0 WHEN fwde_a.fwde_k2 <=> ? THEN 1 END AS fwde_p', $cells['sql']);
        $this->assertStringContainsString('(fwde_a.fwde_k2 IN (?, ?))', $cells['sql']);
        $this->assertStringContainsString('GROUP BY fwde_row, fwde_p HAVING fwde_row IS NOT NULL', $cells['sql']);
        $this->assertSame(['Firefox', 'Chrome', 'smartphone', 'desktop'], array_slice($cells['bind'], 0, 4), 'the row, then the pivot index');
        $this->assertSame(['smartphone', 'desktop'], array_slice($cells['bind'], -2));

        $others = $builder->buildPivotCells($definition, $dimensions, $pivot, 1, $context, [], [['Firefox']], ['smartphone'], true);
        $this->assertStringContainsString('NULL AS fwde_p', $others['sql']);
        $this->assertStringContainsString('(fwde_a.fwde_k2 IN (?)) IS NOT TRUE', $others['sql']);
        $this->assertStringContainsString('GROUP BY fwde_row HAVING fwde_row IS NOT NULL', $others['sql']);

        $total = $builder->buildPivotTotals($definition, $dimensions, $pivot, 1, $context, [], ['smartphone']);
        $this->assertStringContainsString('(fwde_a.fwde_k2 IN (?)) IS NOT TRUE', $total['sql']);
        $this->assertStringNotContainsString('GROUP BY', $total['sql']);
    }

    public function testSortAndLimit(): void
    {
        $sql = $this->build('{"dimensions": ["pageUrl", "browser"], "metrics": ["nb_visits", "nb_pageviews"], "sort": "nb_pageviews"}')['sql'];

        $this->assertStringEndsWith(' ORDER BY nb_pageviews DESC, fwde_k1 ASC, fwde_k2 ASC LIMIT 100', $sql);
    }

    public function testSortsOutsideTheGroupingOnlyWhenAsked(): void
    {
        // MySQL before 8.0 splits groups of a computed key when ORDER BY sits next to GROUP BY and COUNT(DISTINCT)
        $json = '{"dimensions": ["browser"], "metrics": ["nb_visits", "nb_uniq_visitors"]}';
        $wrapped = $this->build($json, [], true)['sql'];
        $this->assertStringStartsWith('SELECT * FROM (SELECT ', $wrapped);
        $this->assertStringEndsWith(' GROUP BY fwde_a.fwde_k1) AS fwde_g ORDER BY nb_visits DESC, fwde_k1 ASC LIMIT 100', $wrapped);

        $plain = $this->build($json)['sql'];
        $this->assertStringStartsWith('SELECT fwde_a.fwde_k1 AS fwde_k1, ', $plain);
        $this->assertStringEndsWith(' GROUP BY fwde_a.fwde_k1 ORDER BY nb_visits DESC, fwde_k1 ASC LIMIT 100', $plain);
    }

    public function testSortByLabelUsesKey(): void
    {
        $sql = $this->build('{"dimensions": ["pageUrl", "browser"], "metrics": ["nb_pageviews"], "sort": "label", "sortOrder": "desc", "limit": 5}')['sql'];
        $this->assertStringEndsWith(' ORDER BY fwde_k1 DESC, fwde_k2 ASC LIMIT 5', $sql);

        $sql = $this->build('{"dimensions": ["pageUrl", "browser"], "metrics": ["nb_pageviews"], "sort": "secondary_label", "sortOrder": "asc"}')['sql'];
        $this->assertStringEndsWith(' ORDER BY fwde_k2 ASC, fwde_k1 ASC LIMIT 100', $sql);
    }

    public function testStageBSelectsKeysAndWrapsStageA(): void
    {
        $sql = $this->build('{"dimensions": ["browser", "country"], "metrics": ["nb_visits"]}')['sql'];

        $this->assertStringStartsWith('SELECT fwde_a.fwde_k1 AS fwde_k1, fwde_a.fwde_k2 AS fwde_k2, COUNT(*) AS nb_visits FROM (STAGE_A', $sql);
        $this->assertStringContainsString(') AS fwde_a GROUP BY fwde_a.fwde_k1, fwde_a.fwde_k2 ORDER BY', $sql);
    }

    public function testEventValueUsesMultiplicityCorrection(): void
    {
        $query = $this->build('{"dimensions": ["eventCategory"], "metrics": ["sum_event_value"]}');

        $this->assertStringContainsString(
            'SUM(CASE WHEN log_link_visit_action.idaction_event_category IS NOT NULL THEN log_link_visit_action.custom_float ELSE 0 END)'
            . ' * COUNT(DISTINCT log_link_visit_action.idlink_va) / COUNT(*) AS fwde_event_value',
            $this->stageACalls[0][0]
        );
        $this->assertStringContainsString('ROUND(COALESCE(SUM(fwde_a.fwde_event_value), 0), 2) AS sum_event_value', $query['sql']);
    }

    public function testAverageEventValueDividesByTheEventsWithAValue(): void
    {
        // like Matomo's Events reports: sum of the values / events that have a value
        $query = $this->build('{"dimensions": ["eventCategory"], "metrics": ["avg_event_value"]}');

        $this->assertStringContainsString(' AS fwde_event_value', $this->stageACalls[0][0]);
        $this->assertStringContainsString(
            'COUNT(DISTINCT CASE WHEN log_link_visit_action.idaction_event_category IS NOT NULL'
            . ' AND log_link_visit_action.custom_float IS NOT NULL THEN log_link_visit_action.idlink_va END) AS fwde_events_with_value',
            $this->stageACalls[0][0]
        );
        $this->assertStringContainsString(
            'ROUND(COALESCE(SUM(fwde_a.fwde_event_value) / NULLIF(SUM(fwde_a.fwde_events_with_value), 0), 0), 2) AS avg_event_value',
            $query['sql']
        );
    }

    public function testSearchesCountSiteSearchActions(): void
    {
        $query = $this->build('{"dimensions": ["browser"], "metrics": ["nb_searches"]}');

        $this->assertContains(
            ['table' => 'log_action', 'tableAlias' => 'fwde_search',
                'joinOn' => 'fwde_search.idaction = log_link_visit_action.idaction_name AND fwde_search.type = 8'],
            $this->stageACalls[0][1]
        );
        $this->assertStringContainsString(
            'COUNT(DISTINCT CASE WHEN fwde_search.idaction IS NOT NULL THEN log_link_visit_action.idlink_va END) AS fwde_searches',
            $this->stageACalls[0][0]
        );
        $this->assertStringContainsString('SUM(fwde_a.fwde_searches) AS nb_searches', $query['sql']);

        // the join only when searches are counted
        $this->build('{"dimensions": ["browser"], "metrics": ["nb_pageviews"]}');
        $this->assertNotContains('fwde_search', array_column(array_filter($this->stageACalls[1][1], 'is_array'), 'tableAlias'));
    }

    public function testMaxActionsInVisitAndActionScope(): void
    {
        $visit = $this->build('{"dimensions": ["browser"], "metrics": ["max_actions"]}');
        $this->assertStringContainsString('log_visit.visit_total_actions AS fwde_actions', $this->stageACalls[0][0]);
        $this->assertStringContainsString('MAX(fwde_a.fwde_actions) AS max_actions', $visit['sql']);

        $action = $this->build('{"dimensions": ["pageUrl"], "metrics": ["max_actions"]}');
        $this->assertStringContainsString('MAX(fwde_v.visit_total_actions) AS max_actions', $action['sql']);
    }

    public function testAllAliasesAreLowercase(): void
    {
        $sql = $this->build('{"dimensions": ["pageUrl", "entryPageTitle"], "metrics": ["nb_visits", "nb_uniq_visitors", "nb_pageviews", "revenue", "sum_event_value"]}')['sql'];

        preg_match_all('/fwde_[A-Za-z0-9_]+/', $sql . ' ' . $this->stageACalls[0][0], $aliases);
        foreach ($aliases[0] as $alias) {
            $this->assertSame(strtolower($alias), $alias);
        }
    }

    public function testContainsFilterOnRawDimension(): void
    {
        $query = $this->build('{"dimensions": ["pageUrl"], "metrics": ["nb_pageviews"], '
            . '"filters": [{"target": "label", "operator": "contains", "value": "blog"}]}');

        $this->assertStringEndsWith(' AND fwde_k1a.name IS NOT NULL AND (fwde_k1a.name) LIKE ?', $this->stageACalls[0][2]);
        $this->assertSame([self::START, self::END, 1, '%blog%'], $this->stageACalls[0][3]);
        $this->assertSame([self::START, self::END, 1, '%blog%'], $query['bind']);
    }

    public function testEqualsAndNotContainsFilters(): void
    {
        $this->build('{"dimensions": ["browser", "referrerName"], "metrics": ["nb_visits"], "filters": ['
            . '{"target": "secondary_label", "operator": "equals", "value": "Google"},'
            . '{"target": "secondary_label", "operator": "notContains", "value": "goo"}]}');

        $this->assertStringEndsWith(' AND (log_visit.referer_name) = ? AND ((log_visit.referer_name) IS NULL OR (log_visit.referer_name) NOT LIKE ?)',
            $this->stageACalls[0][2]);
        $this->assertSame([self::START, self::END, 1, 'Google', '%goo%'], $this->stageACalls[0][3]);
    }

    public function testIsNotStartsWithAndEndsWithFilters(): void
    {
        $this->build('{"dimensions": ["browser", "referrerName"], "metrics": ["nb_visits"], "filters": ['
            . '{"target": "secondary_label", "operator": "notEquals", "value": "Google"},'
            . '{"target": "secondary_label", "operator": "startsWith", "value": "Bi_"},'
            . '{"target": "secondary_label", "operator": "endsWith", "value": ".de"}]}');

        $key = 'log_visit.referer_name';
        $this->assertStringEndsWith(" AND (($key) IS NULL OR ($key) <> ?) AND ($key) LIKE ? AND ($key) LIKE ?", $this->stageACalls[0][2]);
        $this->assertSame([self::START, self::END, 1, 'Google', 'Bi\\_%', '%.de'], $this->stageACalls[0][3]);
    }

    public function testResolvedIsNotStartsWithAndEndsWith(): void
    {
        $this->build('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": ['
            . '{"target": "label", "operator": "notEquals", "value": "chrome"},'
            . '{"target": "label", "operator": "startsWith", "value": "chr"},'
            . '{"target": "label", "operator": "endsWith", "value": "fox"}]}',
            [0 => ['values' => ['CH'], 'includeNull' => false], 1 => ['values' => ['CH', 'CM'], 'includeNull' => false],
                2 => ['values' => ['FF'], 'includeNull' => false]]);

        $key = "NULLIF(NULLIF(log_visit.config_browser_name, ''), 'UNK')";
        // "is not" negates like "does not contain"; "starts with" and "ends with" keep the matching values
        $this->assertStringEndsWith(" AND (($key) IS NULL OR ($key) NOT IN (?)) AND (($key) IN (?, ?)) AND (($key) IN (?))",
            $this->stageACalls[0][2]);
    }

    /** "Match any" (2026-11-20): text filters only, so they stay in stage A, joined with OR */
    public function testMatchAnyJoinsTextFiltersWithOr(): void
    {
        $this->build('{"dimensions": ["browser", "referrerName"], "metrics": ["nb_visits"], "match": "any", "filters": ['
            . '{"target": "secondary_label", "operator": "equals", "value": "Google"},'
            . '{"target": "secondary_label", "operator": "contains", "value": "bing"}]}');

        $this->assertStringEndsWith(' AND ((log_visit.referer_name) = ? OR (log_visit.referer_name) LIKE ?)', $this->stageACalls[0][2]);
        $this->assertSame([self::START, self::END, 1, 'Google', '%bing%'], $this->stageACalls[0][3]);
    }

    /** "Match any" with a number filter: a row qualifies by its value or by its number, so all conditions go to HAVING */
    public function testMatchAnyWithNumberFilterMovesTextFiltersToHaving(): void
    {
        $query = $this->build('{"dimensions": ["referrerName"], "metrics": ["nb_visits"], "match": "any", "filters": ['
            . '{"target": "label", "operator": "contains", "value": "goo"},'
            . '{"target": "nb_visits", "operator": "gt", "value": 5}]}');

        $this->assertStringEndsWith(' AND log_visit.idsite = ?', $this->stageACalls[0][2]);
        // the key inside an aggregate: MySQL (not MariaDB) may check a bare column of the merged derived table against
        // a row of another group; within a group every key is equal, so MAX() is the group's key
        $this->assertStringContainsString(' HAVING ((MAX(fwde_a.fwde_k1)) LIKE ? OR nb_visits > ?)', $query['sql']);
        $this->assertSame(['%goo%', 5.0], array_slice($query['bind'], -2));
    }

    /** A filter that excludes nothing lets every row through, and the binds of the dropped OR group go with it */
    public function testMatchAnyWithAFilterExcludingNothingLeavesNoBinds(): void
    {
        $this->build('{"dimensions": ["browser", "referrerName"], "metrics": ["nb_visits"], "match": "any", "filters": ['
            . '{"target": "secondary_label", "operator": "contains", "value": "goo"},'
            . '{"target": "label", "operator": "notEquals", "value": "Opera"}]}',
            [1 => ['values' => [], 'includeNull' => false]]);

        $this->assertStringEndsWith(' AND log_visit.idsite = ?', $this->stageACalls[0][2]);
        $this->assertSame([self::START, self::END, 1], $this->stageACalls[0][3]);
    }

    public function testMatchAnyByNumberWithAFilterExcludingNothingLeavesNoBinds(): void
    {
        $query = $this->build('{"dimensions": ["browser"], "metrics": ["nb_visits"], "match": "any", "filters": ['
            . '{"target": "nb_visits", "operator": "gt", "value": 5},'
            . '{"target": "label", "operator": "notEquals", "value": "Opera"}]}',
            [1 => ['values' => [], 'includeNull' => false]]);

        $this->assertStringNotContainsString(' HAVING ', $query['sql']);
        // stage A's three binds only: the number filter's 5 went with the dropped HAVING group
        $this->assertSame([self::START, self::END, 1], $query['bind']);
    }

    public function testTotalsInMatchAnyWithNumberFilterKeepEveryRow(): void
    {
        $this->totals('{"dimensions": ["referrerName"], "metrics": ["nb_visits"], "match": "any", "filters": ['
            . '{"target": "label", "operator": "contains", "value": "goo"},'
            . '{"target": "nb_visits", "operator": "gt", "value": 5}]}');

        $this->assertStringEndsWith(' AND log_visit.idsite = ?', $this->stageACalls[0][2]);
    }

    /** Filter groups (2026-11-23): outer "any" with a number condition puts every condition on the grouped rows */
    public function testOuterAnyWithNumberMovesEverythingToHaving(): void
    {
        $query = $this->build('{"dimensions": ["browser", "referrerName"], "metrics": ["nb_visits"], "filterGroups": {"match": "any", "groups": ['
            . '{"filters": [{"target": "secondary_label", "operator": "contains", "value": "goo"}, {"target": "secondary_label", "operator": "endsWith", "value": ".de"}]},'
            . '{"filters": [{"target": "nb_visits", "operator": "gte", "value": 5}]}]}}');

        $this->assertStringEndsWith(' AND log_visit.idsite = ?', $this->stageACalls[0][2]);
        $this->assertStringContainsString(' HAVING (((MAX(fwde_a.fwde_k2)) LIKE ? AND (MAX(fwde_a.fwde_k2)) LIKE ?) OR nb_visits >= ?)', $query['sql']);
        $this->assertSame(['%goo%', '%.de', 5.0], array_slice($query['bind'], -3));
    }

    /** Outer "all": an "any" group of text and the text of an "all" group run before the grouping, its number after */
    public function testOuterAllSplitsGroups(): void
    {
        $query = $this->build('{"dimensions": ["browser", "referrerName"], "metrics": ["nb_visits"], "filterGroups": {"groups": ['
            . '{"match": "any", "filters": [{"target": "secondary_label", "operator": "equals", "value": "Google"}, {"target": "secondary_label", "operator": "equals", "value": "Bing"}]},'
            . '{"filters": [{"target": "secondary_label", "operator": "contains", "value": "o"}, {"target": "nb_visits", "operator": "gt", "value": 10}]}]}}');

        $key = 'log_visit.referer_name';
        $this->assertStringEndsWith(" AND (($key) = ? OR ($key) = ?) AND ($key) LIKE ?", $this->stageACalls[0][2]);
        $this->assertSame([self::START, self::END, 1, 'Google', 'Bing', '%o%'], $this->stageACalls[0][3]);
        $this->assertStringContainsString(' HAVING nb_visits > ? ', $query['sql'] . ' ');
        $this->assertSame(10.0, $query['bind'][count($query['bind']) - 1]);
    }

    /** A condition that excludes nothing makes its "any" group true: the clause and its binds go */
    public function testUnrestrictedConditionInOrGroupDropsTheClauseAndItsBinds(): void
    {
        $query = $this->build('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filterGroups": {"match": "any", "groups": ['
            . '{"filters": [{"target": "label", "operator": "contains", "value": "chr"}]},'
            . '{"filters": [{"target": "label", "operator": "notEquals", "value": "Opera"}, {"target": "nb_visits", "operator": "lt", "value": 3}]}]}}',
            [0 => ['values' => ['CH'], 'includeNull' => false], 1 => ['values' => [], 'includeNull' => false]]);

        // group 2: "is not Opera" excludes nothing, but "visits < 3" still restricts it, so the clause stays
        $this->assertStringContainsString(' HAVING (((MAX(fwde_a.fwde_k1)) IN (?)) OR nb_visits < ?)', $query['sql']);

        $all = $this->build('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filterGroups": {"match": "any", "groups": ['
            . '{"filters": [{"target": "label", "operator": "contains", "value": "chr"}]},'
            . '{"filters": [{"target": "label", "operator": "notEquals", "value": "Opera"}]}]}}',
            [0 => ['values' => ['CH'], 'includeNull' => false], 1 => ['values' => [], 'includeNull' => false]]);
        $this->assertStringEndsWith(' AND log_visit.idsite = ?', $this->stageACalls[1][2]);
        $this->assertSame([self::START, self::END, 1], $this->stageACalls[1][3]);
        $this->assertStringNotContainsString(' HAVING ', $all['sql']);
    }

    public function testNewOperatorSql(): void
    {
        $query = $this->build('{"dimensions": ["browser", "referrerName"], "metrics": ["nb_visits", "bounce_rate"], "filters": ['
            . '{"target": "secondary_label", "operator": "notStartsWith", "value": "Bi_"},'
            . '{"target": "secondary_label", "operator": "notEndsWith", "value": ".de"},'
            . '{"target": "secondary_label", "operator": "matches", "value": "^(goo|bi)[a-z]+$"},'
            . '{"target": "nb_visits", "operator": "gte", "value": 2},'
            . '{"target": "bounce_rate", "operator": "lte", "value": 0.5}]}');

        $key = 'log_visit.referer_name';
        $this->assertStringEndsWith(" AND (($key) IS NULL OR ($key) NOT LIKE ?) AND (($key) IS NULL OR ($key) NOT LIKE ?) AND ($key) REGEXP ?",
            $this->stageACalls[0][2]);
        $this->assertSame([self::START, self::END, 1, 'Bi\\_%', '%.de', '^(goo|bi)[a-z]+$'], $this->stageACalls[0][3]);
        $this->assertStringContainsString(' HAVING nb_visits >= ? AND bounce_rate <= ?', $query['sql']);
    }

    /** Titles are stored HTML-escaped: the pattern matches an apostrophe in both forms (final review, 2026-11-24) */
    public function testMatchesOnAnEscapedDimensionMatchesBothForms(): void
    {
        $this->build('{"dimensions": ["pageTitle"], "metrics": ["nb_pageviews"], "filters": ['
            . '{"target": "label", "operator": "matches", "value": "^Men\'s"}]}');

        $this->assertSame("^Men('|&#039;)s", $this->stageACalls[0][3][3]);
    }

    public function testResolvedNegatedNewOperators(): void
    {
        $this->build('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": ['
            . '{"target": "label", "operator": "notStartsWith", "value": "chr"},'
            . '{"target": "label", "operator": "matches", "value": "fox$"}]}',
            [0 => ['values' => ['CH', 'CM'], 'includeNull' => false], 1 => ['values' => ['FF'], 'includeNull' => false]]);

        $key = "NULLIF(NULLIF(log_visit.config_browser_name, ''), 'UNK')";
        $this->assertStringEndsWith(" AND (($key) IS NULL OR ($key) NOT IN (?, ?)) AND (($key) IN (?))", $this->stageACalls[0][2]);
    }

    public function testTotalsUseOnlyStageAClauses(): void
    {
        $this->totals('{"dimensions": ["referrerName"], "metrics": ["nb_visits"], "filterGroups": {"groups": ['
            . '{"filters": [{"target": "label", "operator": "contains", "value": "goo"}, {"target": "nb_visits", "operator": "gt", "value": 5}]}]}}');

        $this->assertStringEndsWith(' AND (log_visit.referer_name) LIKE ?', $this->stageACalls[0][2]);
        $this->assertSame([self::START, self::END, 1, '%goo%'], $this->stageACalls[0][3]);
    }

    /** "Others" (2026-11-20): one row over everything but the shown key tuples, NULL keys compared null-safe */
    public function testOthersExcludeTheShownKeysNullSafe(): void
    {
        [$definition, $dimensionDefinitions, $context, $builder] = $this->parts('{"dimensions": ["browser", "referrerName"], "metrics": ["nb_visits"]}');
        $query = $builder->buildOthers($definition, $dimensionDefinitions, 1, $context, [], [['CH', 'Google'], ['FF', null]]);

        // IN lists first (looked up quickly), the null-safe pairs only for the rows that pass them; IS NOT TRUE keeps a
        // row whose test is unknown (a NULL key against an IN list without NULL)
        $this->assertStringEndsWith(' WHERE (fwde_a.fwde_k1 IN (?, ?) AND (fwde_a.fwde_k2 IN (?) OR fwde_a.fwde_k2 IS NULL)'
            . ' AND ((fwde_a.fwde_k1 <=> ? AND fwde_a.fwde_k2 <=> ?) OR (fwde_a.fwde_k1 <=> ? AND fwde_a.fwde_k2 <=> ?))) IS NOT TRUE', $query['sql']);
        $this->assertSame(['CH', 'FF', 'Google', 'CH', 'Google', 'FF', null], array_slice($query['bind'], -7));
        $this->assertStringNotContainsString('GROUP BY fwde_a', $query['sql']);
        $this->assertStringContainsString(' AS fwde_k1', $this->stageACalls[0][0], 'stage A keeps the keys');
        $this->assertStringContainsString(' AS fwde_k2', $this->stageACalls[0][0]);

        $none = $builder->buildOthers($definition, $dimensionDefinitions, 1, $context, [], []);
        $this->assertStringNotContainsString(' WHERE NOT', $none['sql']);
    }

    /** One dimension: the IN list alone is exact (the database compares with the collation it groups by) */
    public function testOthersOfOneDimensionUseAnInListOnly(): void
    {
        [$definition, $dimensionDefinitions, $context, $builder] = $this->parts('{"dimensions": ["browser"], "metrics": ["nb_visits"]}');
        $query = $builder->buildOthers($definition, $dimensionDefinitions, 1, $context, [], [['CH', null], [null, null], ['CH', null]]);

        $this->assertStringEndsWith(' WHERE (fwde_a.fwde_k1 IN (?) OR fwde_a.fwde_k1 IS NULL) IS NOT TRUE', $query['sql']);
        $this->assertSame('CH', end($query['bind']));
        $this->assertStringNotContainsString('<=>', $query['sql']);
    }

    /** A NULL key of the rest is unknown against an IN list without NULL: it must count, not drop out */
    public function testOthersKeepANullKeyThatIsNotShown(): void
    {
        [$definition, $dimensionDefinitions, $context, $builder] = $this->parts('{"dimensions": ["browser"], "metrics": ["nb_visits"]}');
        $query = $builder->buildOthers($definition, $dimensionDefinitions, 1, $context, [], [['CH', null], ['FF', null]]);

        $this->assertStringEndsWith(' WHERE (fwde_a.fwde_k1 IN (?, ?)) IS NOT TRUE', $query['sql']);
    }

    /** No shown rows (a number filter removed them all): the rest is everything, no empty condition */
    public function testOthersWithoutShownRowsHaveNoCondition(): void
    {
        [$definition, $dimensionDefinitions, $context, $builder] = $this->parts('{"dimensions": ["browser", "referrerName"], "metrics": ["nb_visits"]}');
        $query = $builder->buildOthers($definition, $dimensionDefinitions, 1, $context, [], []);

        $this->assertStringNotContainsString('WHERE', substr($query['sql'], strrpos($query['sql'], ') AS fwde_a')));
        $this->assertSame([self::START, self::END, 1], $query['bind']);
    }

    public function testLikeWildcardsAreEscaped(): void
    {
        $this->build('{"dimensions": ["pageUrl"], "metrics": ["nb_pageviews"], '
            . '"filters": [{"target": "label", "operator": "contains", "value": "50%_off\\\\"}]}');

        $this->assertSame('%50\\%\\_off\\\\%', end($this->stageACalls[0][3]));
    }

    public function testResolvedLabelFilterUsesInList(): void
    {
        $this->build('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": ['
            . '{"target": "label", "operator": "contains", "value": "chrome"},'
            . '{"target": "label", "operator": "notContains", "value": "mobile"}]}',
            [0 => ['values' => ['CH', 'CM'], 'includeNull' => false], 1 => ['values' => ['CM'], 'includeNull' => true]]);

        $key = "NULLIF(NULLIF(log_visit.config_browser_name, ''), 'UNK')";
        $this->assertStringEndsWith(" AND (($key) IN (?, ?)) AND (($key) NOT IN (?))", $this->stageACalls[0][2]);
        $this->assertSame([self::START, self::END, 1, 'CH', 'CM', 'CM'], $this->stageACalls[0][3]);
    }

    public function testResolvedLabelFilterWithNull(): void
    {
        $this->build('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": ['
            . '{"target": "label", "operator": "equals", "value": "unknown"},'
            . '{"target": "label", "operator": "notContains", "value": "x"}]}',
            [0 => ['values' => [], 'includeNull' => true], 1 => ['values' => ['XY'], 'includeNull' => false]]);

        $key = "NULLIF(NULLIF(log_visit.config_browser_name, ''), 'UNK')";
        $this->assertStringEndsWith(" AND (($key) IS NULL) AND (($key) IS NULL OR ($key) NOT IN (?))", $this->stageACalls[0][2]);
    }

    public function testEmptyResolvedMatchExcludesAll(): void
    {
        $this->build('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": ['
            . '{"target": "label", "operator": "contains", "value": "nothing"},'
            . '{"target": "label", "operator": "notContains", "value": "nothing"}]}',
            [0 => ['values' => [], 'includeNull' => false], 1 => ['values' => [], 'includeNull' => false]]);

        $this->assertStringEndsWith(' AND log_visit.idsite = ? AND 1 = 0', $this->stageACalls[0][2]);
    }

    public function testMetricFilterUsesHaving(): void
    {
        $query = $this->build('{"dimensions": ["browser"], "metrics": ["nb_visits", "bounce_rate"], "filters": ['
            . '{"target": "nb_visits", "operator": "gt", "value": 10}, {"target": "bounce_rate", "operator": "lt", "value": 0.5}]}');

        $this->assertStringContainsString(' GROUP BY fwde_a.fwde_k1 HAVING nb_visits > ? AND bounce_rate < ? ORDER BY ', $query['sql']);
        $this->assertSame([10.0, 0.5], array_slice($query['bind'], -2));
    }

    public function testConversionColumnsDoNotShadowMetricAliases(): void
    {
        // HAVING resolves names in the joined tables before the select aliases: a subquery column "revenue" would
        // turn "HAVING revenue > ?" into a comparison with one visit's revenue
        $sql = $this->build('{"dimensions": ["browser"], "metrics": ["nb_conversions", "revenue"], "filters": ['
            . '{"target": "revenue", "operator": "gt", "value": 0}]}')['sql'];

        $this->assertStringContainsString('COUNT(*) AS fwde_conversions,', $sql);
        $this->assertStringContainsString(' ELSE log_conversion.revenue END) AS fwde_revenue FROM ', $sql);
        $this->assertStringContainsString('COALESCE(SUM(fwde_c.fwde_conversions), 0) AS nb_conversions', $sql);
        $this->assertStringContainsString('ROUND(COALESCE(SUM(fwde_c.fwde_revenue), 0), 2) AS revenue', $sql);
    }

    public function testFilterOnSanitizedTextMatchesTheStoredForm(): void
    {
        // Matomo stores page titles, event names, search keywords and custom dimension values HTML-escaped
        $this->build('{"dimensions": ["pageTitle", "pageUrl"], "metrics": ["nb_pageviews"], "filters": ['
            . '{"target": "label", "operator": "contains", "value": "Tom & Jerry\'s"},'
            . '{"target": "secondary_label", "operator": "contains", "value": "a=1&b=2"}]}');

        $bind = $this->stageACalls[0][3];
        $this->assertSame('%Tom &amp; Jerry&#039;s%', $bind[3]);
        $this->assertSame('%a=1&b=2%', $bind[4], 'page URLs are stored as they are');
    }

    public function testBindOrderWithFiltersAndGoals(): void
    {
        $query = $this->build('{"dimensions": ["pageUrl"], "metrics": ["nb_conversions"], "idGoal": 3, "filters": ['
            . '{"target": "nb_conversions", "operator": "gt", "value": 0}, {"target": "label", "operator": "contains", "value": "shop"}]}');

        $this->assertSame([self::START, self::END, 1, '%shop%', 1, self::START, self::END, 0.0], $query['bind']);
    }

    /**
     * Comparison columns (comparison design 5): the rows of the own view. The database decides which group belongs to
     * which own row (its collation groups "Shoes" and "shoes" as one), so the query returns the own row's index;
     * IN lists filter first. No number filters, no sorting, no limit.
     */
    public function test_only_keys_return_the_own_row_index_without_number_filters_and_limit(): void
    {
        $query = $this->build('{"dimensions": ["browser"], "metrics": ["nb_visits"], "limit": 10, '
            . '"filters": [{"target": "nb_visits", "operator": "gt", "value": 5}]}', [], true, [['Firefox', null], [null, null], ['Chrome', null]]);

        $this->assertStringContainsString('CASE WHEN (fwde_a.fwde_k1 <=> ?) THEN 0 WHEN (fwde_a.fwde_k1 <=> ?) THEN 1 WHEN (fwde_a.fwde_k1 <=> ?) THEN 2 END AS fwde_row', $query['sql']);
        $this->assertStringContainsString('WHERE (fwde_a.fwde_k1 IN (?, ?) OR fwde_a.fwde_k1 IS NULL)', $query['sql']);
        $this->assertStringContainsString('GROUP BY fwde_row HAVING fwde_row IS NOT NULL', $query['sql']);
        $this->assertStringNotContainsString('fwde_k1 AS fwde_k1', $query['sql']);
        $this->assertStringNotContainsString('LIMIT', $query['sql']);
        $this->assertStringNotContainsString('ORDER BY', $query['sql']);
        $this->assertStringNotContainsString('nb_visits > ?', $query['sql']);
        // CASE binds lead (the select list comes first), the IN lists close
        $this->assertSame(['Firefox', null, 'Chrome'], array_slice($query['bind'], 0, 3));
        $this->assertSame(['Firefox', 'Chrome'], array_slice($query['bind'], -2));
        $this->assertSame(substr_count($query['sql'], '?'), count($query['bind']) - count($this->stageACalls[0][3]));
    }

    public function test_two_dimensions_match_both_keys(): void
    {
        $query = $this->build('{"dimensions": ["browser", "country"], "metrics": ["nb_visits"]}', [], false, [['Firefox', 'de']]);

        $this->assertStringContainsString('WHEN (fwde_a.fwde_k1 <=> ? AND fwde_a.fwde_k2 <=> ?) THEN 0', $query['sql']);
        $this->assertStringContainsString('(fwde_a.fwde_k1 IN (?)) AND (fwde_a.fwde_k2 IN (?))', $query['sql']);
    }

    public function test_no_keys_match_nothing(): void
    {
        $query = $this->build('{"dimensions": ["browser"], "metrics": ["nb_visits"]}', [], false, []);

        $this->assertStringContainsString('WHERE 1 = 0', $query['sql']);
    }

    /** The total row (comparison design 3): what the table covers, before number filters and the row limit. */
    public function test_totals_have_no_grouping_but_keep_text_filters(): void
    {
        $json = '{"dimensions": ["pageUrl"], "metrics": ["nb_visits", "nb_pageviews"], '
            . '"filters": [{"target": "label", "operator": "contains", "value": "blog"}, {"target": "nb_visits", "operator": "gt", "value": 2}]}';
        $query = $this->totals($json);

        $this->assertStringContainsString('LIKE ?', $this->stageACalls[0][2]);
        // one row per visit (never per page and visit), so a visit with two pages counts once
        $this->assertSame('log_link_visit_action.idvisit', $this->stageACalls[0][4]);
        $this->assertStringNotContainsString('AS fwde_k1', $this->stageACalls[0][0]);
        $this->assertContains('%blog%', $query['bind']);
        $this->assertStringNotContainsString('GROUP BY fwde_a.', $query['sql']);
        $this->assertStringNotContainsString('HAVING', $query['sql']);
        $this->assertStringNotContainsString('LIMIT', $query['sql']);
        $this->assertStringNotContainsString('fwde_k1 AS fwde_k1', $query['sql']);
        $this->assertStringContainsString(' AS nb_visits', $query['sql']);
        $this->assertStringContainsString(' AS nb_pageviews', $query['sql']);
        // the fake stage A keeps its binds but not its WHERE text: count stage B's placeholders only
        $this->assertSame(substr_count($query['sql'], '?'), count($query['bind']) - count($this->stageACalls[0][3]));
    }

    /** Trend (trend design 5): the own rows by their index and the time key, without number filters and limit. */
    public function test_trend_rows_group_by_row_index_and_time_without_number_filters_and_limit(): void
    {
        $query = $this->trend('{"dimensions": ["browser"], "metrics": ["nb_visits"], "limit": 10, '
            . '"filters": [{"target": "nb_visits", "operator": "gt", "value": 5}]}', [['Firefox', null], ['Chrome', null]]);

        $this->assertStringContainsString('DATE(fwde_local_time) AS fwde_t', $this->stageACalls[0][0]);
        $this->assertStringContainsString('fwde_a.fwde_t AS fwde_t', $query['sql']);
        $this->assertStringContainsString('CASE WHEN (fwde_a.fwde_k1 <=> ?) THEN 0 WHEN (fwde_a.fwde_k1 <=> ?) THEN 1 END AS fwde_row', $query['sql']);
        $this->assertStringContainsString('GROUP BY fwde_row, fwde_a.fwde_t HAVING fwde_row IS NOT NULL', $query['sql']);
        $this->assertStringNotContainsString('LIMIT', $query['sql']);
        $this->assertStringNotContainsString('ORDER BY', $query['sql']);
        $this->assertStringNotContainsString('nb_visits > ?', $query['sql']);
        $this->assertSame(substr_count($query['sql'], '?'), count($query['bind']) - count($this->stageACalls[0][3]));
    }

    public function test_trend_totals_group_by_time_only(): void
    {
        $query = $this->trend('{"dimensions": ["browser"], "metrics": ["nb_visits"], '
            . '"filters": [{"target": "label", "operator": "equals", "value": "FF"}]}', null);

        $this->assertStringContainsString('DATE(fwde_local_time) AS fwde_t', $this->stageACalls[0][0]);
        $this->assertStringNotContainsString('AS fwde_k1', $this->stageACalls[0][0], 'no dimension key in the totals');
        $this->assertStringEndsWith('GROUP BY fwde_a.fwde_t', $query['sql']);
        $this->assertStringNotContainsString('fwde_row', $query['sql']);
        $this->assertContains('FF', $this->stageACalls[0][3], 'text filters still apply');
    }

    public function test_trend_in_action_scope_groups_stage_a_by_time_and_visit(): void
    {
        $this->trend('{"dimensions": ["pageTitle"], "metrics": ["nb_pageviews"]}', null);
        $this->assertSame('fwde_t, log_link_visit_action.idvisit', $this->stageACalls[0][4]);

        $this->stageACalls = [];
        $this->trend('{"dimensions": ["pageTitle"], "metrics": ["nb_pageviews"]}', [['Home', null]]);
        $this->assertSame('fwde_k1, fwde_t, log_link_visit_action.idvisit', $this->stageACalls[0][4]);
    }

    public function test_trend_with_no_keys_matches_nothing(): void
    {
        $this->assertStringContainsString('WHERE 1 = 0', $this->trend('{"dimensions": ["browser"], "metrics": ["nb_visits"]}', [])['sql']);
    }

    private function trend(string $json, ?array $onlyKeys): array
    {
        [$definition, $dimensionDefinitions, $context, $builder] = $this->parts($json);
        $time = new DimensionDefinition('date', 'time', DimensionCatalog::SCOPE_VISIT, 'Date', 'raw',
            fn () => 'DATE(fwde_local_time)', null, new LabelFormatter(fn (string $key) => $key));
        return $builder->buildTrend($definition, $dimensionDefinitions, 1, $context, [], $time, $onlyKeys);
    }

    private function totals(string $json): array
    {
        [$definition, $dimensionDefinitions, $context, $builder] = $this->parts($json);
        return $builder->buildTotals($definition, $dimensionDefinitions, 1, $context);
    }

    private function build(string $json, array $resolvedFilters = [], bool $sortOutsideGrouping = false, ?array $onlyKeys = null): array
    {
        [$definition, $dimensionDefinitions, $context, $builder] = $this->parts($json);
        return $builder->build($definition, $dimensionDefinitions, 1, $context, $resolvedFilters, $sortOutsideGrouping, $onlyKeys);
    }

    private function parts(string $json): array
    {
        $dimensions = $this->dimensions();
        // goals 3 and 5 on an e-commerce website (more metrics, 2026-11-24)
        $definition = ExplorationDefinition::fromJson($json, 1, $dimensions,
            new MetricCatalog(fn (int $idSite) => [3 => 'Newsletter', 5 => 'Lead'], fn (int $idSite) => true), new Limits());
        $context = new QueryContext($definition->scope($dimensions, new MetricCatalog(), 1), 'UTC', self::START, self::END);
        $dimensionDefinitions = array_map(fn (string $id) => $dimensions->get(1, $id), $definition->dimensionIds);

        $builder = new ExplorationQueryBuilder(
            function (string $select, array $from, string $where, array $bind, ?string $groupBy): array {
                $this->stageACalls[] = [$select, $from, $where, $bind, $groupBy];
                return ['sql' => 'STAGE_A ' . $select, 'bind' => $bind];
            },
            fn (string $table): string => 'matomo_' . $table
        );

        return [$definition, $dimensionDefinitions, $context, $builder];
    }

    private function dimensions(): DimensionCatalog
    {
        $noCustomDimensions = new class implements CustomDimensionSource {
            public function activeFor(int $idSite): array
            {
                return [];
            }
        };
        return new DimensionCatalog(fn (string $coreId) => $coreId, $noCustomDimensions, new LabelFormatter(fn (string $key, array $params = []) => $key), new ColumnProbe(fn () => []));
    }
}
