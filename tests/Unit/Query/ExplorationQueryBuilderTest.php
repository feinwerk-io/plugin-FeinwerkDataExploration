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

    public function testGoalMetricWithIdGoalBindsGoal(): void
    {
        $query = $this->build('{"dimensions": ["browser"], "metrics": ["nb_conversions"], "idGoal": 7}');

        $this->assertStringContainsString('AND log_conversion.idgoal >= 0 AND log_conversion.idgoal = ? GROUP BY', $query['sql']);
        $this->assertSame(7, end($query['bind']));
    }

    public function testBindOrderStageAThenConversions(): void
    {
        $query = $this->build('{"dimensions": ["browser"], "metrics": ["nb_conversions"]}');

        $this->assertSame([self::START, self::END, 1, 1, self::START, self::END], $query['bind']);
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

        $this->assertSame([self::START, self::END, 1, '%shop%', 1, self::START, self::END, 3, 0.0], $query['bind']);
    }

    private function build(string $json, array $resolvedFilters = [], bool $sortOutsideGrouping = false): array
    {
        $dimensions = $this->dimensions();
        $definition = ExplorationDefinition::fromJson($json, 1, $dimensions, new MetricCatalog(), new Limits());
        $context = new QueryContext($definition->scope($dimensions, new MetricCatalog(), 1), 'UTC', self::START, self::END);
        $dimensionDefinitions = array_map(fn (string $id) => $dimensions->get(1, $id), $definition->dimensionIds);

        $builder = new ExplorationQueryBuilder(
            function (string $select, array $from, string $where, array $bind, ?string $groupBy): array {
                $this->stageACalls[] = [$select, $from, $where, $bind, $groupBy];
                return ['sql' => 'STAGE_A ' . $select, 'bind' => $bind];
            },
            fn (string $table): string => 'matomo_' . $table
        );

        return $builder->build($definition, $dimensionDefinitions, 1, $context, $resolvedFilters, $sortOutsideGrouping);
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
