<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Query;

use Piwik\Common;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;

/**
 * Builds the SQL of an exploration in two stages.
 *
 * Stage A is built by Matomo core (segment SQL and joins come from Segment::getSelectQuery()) and returns one row per
 * unit: the dimension keys plus one visit. In visit scope that is one row per visit of log_visit; in action scope one
 * row per (keys, visit) over log_link_visit_action, with that visit's action counts.
 *
 * Stage B wraps Stage A: it joins log_visit for visit metrics in action scope and a per-visit conversions subquery for
 * goal metrics, then groups by the keys, sorts and limits. Visit metrics therefore always count visits, never actions
 * (spec 2.4), and goal metrics are the conversions of those visits within the period.
 */
final class ExplorationQueryBuilder
{
    private const VISIT = 'log_visit';
    private const ACTION = 'log_link_visit_action';

    private const IS_PAGEVIEW = 'log_link_visit_action.idaction_event_category IS NULL AND log_link_visit_action.idaction_url IS NOT NULL'
        . ' AND (fwde_url.type = 1 OR fwde_url.type IS NULL)';
    private const IS_EVENT = 'log_link_visit_action.idaction_event_category IS NOT NULL';

    /** Stage A columns per action metric (action scope). */
    private const ACTION_MEASURES = [
        'nb_pageviews' => ['fwde_pageviews', 'COUNT(DISTINCT CASE WHEN ' . self::IS_PAGEVIEW . ' THEN log_link_visit_action.idlink_va END)'],
        // the page column (idaction_url unless a dimension says otherwise) is filled in by stageAMeasures()
        'nb_uniq_pageviews' => ['fwde_uniq_pageviews', 'COUNT(DISTINCT CASE WHEN ' . self::IS_PAGEVIEW . ' THEN log_link_visit_action.%s END)'],
        'nb_events' => ['fwde_events', 'COUNT(DISTINCT CASE WHEN ' . self::IS_EVENT . ' THEN log_link_visit_action.idlink_va END)'],
        // A conversion segment joins log_conversion on idvisit and repeats every action row once per matching
        // conversion. The repetition is the same for all rows of one visit, so scaling by distinct rows / rows undoes it.
        'sum_event_value' => ['fwde_event_value', 'SUM(CASE WHEN ' . self::IS_EVENT . ' THEN log_link_visit_action.custom_float ELSE 0 END)'
            . ' * COUNT(DISTINCT log_link_visit_action.idlink_va) / COUNT(*)'],
        'nb_downloads' => ['fwde_downloads', 'COUNT(DISTINCT CASE WHEN fwde_url.type = 3 THEN log_link_visit_action.idlink_va END)'],
        'nb_outlinks' => ['fwde_outlinks', 'COUNT(DISTINCT CASE WHEN fwde_url.type = 2 THEN log_link_visit_action.idlink_va END)'],
        // site searches have no URL; their name is a log_action of type 8 (joined as fwde_search when counted)
        'nb_searches' => ['fwde_searches', 'COUNT(DISTINCT CASE WHEN fwde_search.idaction IS NOT NULL THEN log_link_visit_action.idlink_va END)'],
    ];

    /** The divisor of the average event value, as in Matomo's Events reports: events that have a value. */
    private const EVENTS_WITH_VALUE = ['fwde_events_with_value', 'COUNT(DISTINCT CASE WHEN ' . self::IS_EVENT
        . ' AND log_link_visit_action.custom_float IS NOT NULL THEN log_link_visit_action.idlink_va END)'];

    /** @var callable fn(string $select, array $from, string $where, array $bind, ?string $groupBy): array{sql: string, bind: array} */
    private $segmentedSelect;
    /** @var callable fn(string $table): string */
    private $prefixTable;
    private MetricCatalog $metrics;

    public function __construct(callable $segmentedSelect, callable $prefixTable, ?MetricCatalog $metrics = null)
    {
        $this->segmentedSelect = $segmentedSelect;
        $this->prefixTable = $prefixTable;
        $this->metrics = $metrics ?? new MetricCatalog();
    }

    /**
     * @param DimensionDefinition[] $dimensions the definition's dimensions, in order
     * @param array<int, array{values: list<string>, includeNull: bool}> $resolvedFilters by filter index: text filters on
     *        coded dimensions, already resolved to stored values (see LabelFilterResolver)
     * @param bool $sortOutsideGrouping sort in an outer query (MySQL before 8.0, see GroupingQuirk)
     * @return array{sql: string, bind: array} result columns fwde_k1, fwde_k2 (two dimensions only) and the metric ids
     */
    public function build(ExplorationDefinition $definition, array $dimensions, int $idSite, QueryContext $context, array $resolvedFilters = [], bool $sortOutsideGrouping = false): array
    {
        $isAction = $context->scope === QueryContext::SCOPE_ACTION;
        $keys = $this->keys($dimensions, $context);
        $stageA = $this->stageA($definition, $dimensions, $keys, $idSite, $context, $resolvedFilters);

        $hasVisitMetric = $this->hasMetricOfScope($definition, MetricCatalog::SCOPE_VISIT);
        $hasGoalMetric = $this->hasMetricOfScope($definition, MetricCatalog::SCOPE_GOAL);

        $select = [];
        foreach (array_keys($keys) as $alias) {
            $select[] = 'fwde_a.' . $alias . ' AS ' . $alias;
        }
        foreach ($definition->metricIds as $metricId) {
            $select[] = $this->metricSql($metricId, $isAction) . ' AS ' . $metricId;
        }

        $sql = 'SELECT ' . implode(', ', $select) . ' FROM (' . $stageA['sql'] . ') AS fwde_a';
        $bind = $stageA['bind'];

        if ($isAction && $hasVisitMetric) {
            $sql .= ' INNER JOIN ' . ($this->prefixTable)(self::VISIT) . ' AS fwde_v ON fwde_v.idvisit = fwde_a.fwde_idvisit';
        }
        if ($hasGoalMetric) {
            $conversions = $this->conversions($definition, $idSite, $context);
            $sql .= ' LEFT JOIN (' . $conversions['sql'] . ') AS fwde_c ON fwde_c.idvisit = fwde_a.fwde_idvisit';
            $bind = array_merge($bind, $conversions['bind']);
        }

        $groupBy = array_map(fn (string $alias) => 'fwde_a.' . $alias, array_keys($keys));
        $sql .= ' GROUP BY ' . implode(', ', $groupBy);

        $having = [];
        foreach ($definition->filters as $filter) {
            if (in_array($filter['operator'], ExplorationDefinition::NUMBER_OPERATORS, true)) {
                $having[] = $filter['target'] . ($filter['operator'] === 'gt' ? ' > ?' : ' < ?');
                $bind[] = $filter['value'];
            }
        }
        if ($having !== []) {
            $sql .= ' HAVING ' . implode(' AND ', $having);
        }

        $orderBy = ' ORDER BY ' . $this->orderBy($definition, array_keys($keys)) . ' LIMIT ' . $definition->limit;
        // see GroupingQuirk: the outer query sorts the grouped rows only
        $sql = $sortOutsideGrouping ? 'SELECT * FROM (' . $sql . ') AS fwde_g' . $orderBy : $sql . $orderBy;

        return ['sql' => $sql, 'bind' => $bind];
    }

    /**
     * @return array<string, string> alias => key SQL
     */
    private function keys(array $dimensions, QueryContext $context): array
    {
        $keys = [];
        foreach (array_values($dimensions) as $i => $dimension) {
            $alias = 'fwde_k' . ($i + 1);
            $keys[$alias] = $dimension->keySql($context, $alias);
        }
        return $keys;
    }

    private function stageA(ExplorationDefinition $definition, array $dimensions, array $keys, int $idSite, QueryContext $context, array $resolvedFilters): array
    {
        $isAction = $context->scope === QueryContext::SCOPE_ACTION;
        $base = $isAction ? self::ACTION : self::VISIT;

        $from = [$base];
        if ($isAction) {
            $from[] = ['table' => 'log_action', 'tableAlias' => 'fwde_url', 'joinOn' => 'fwde_url.idaction = log_link_visit_action.idaction_url'];
            if (in_array('nb_searches', $definition->metricIds, true)) {
                $from[] = ['table' => 'log_action', 'tableAlias' => 'fwde_search',
                    'joinOn' => 'fwde_search.idaction = log_link_visit_action.idaction_name AND fwde_search.type = 8'];
            }
        }
        $joins = [];
        foreach (array_values($dimensions) as $i => $dimension) {
            $joins = array_merge($joins, $dimension->joins($context, 'fwde_k' . ($i + 1)));
        }
        if ($isAction && $this->readsLogVisit($keys, $joins)) {
            $from[] = self::VISIT;
        }
        $from = array_merge($from, $joins);

        $select = [$base . '.idvisit AS fwde_idvisit'];
        foreach ($keys as $alias => $keySql) {
            $select[] = $keySql . ' AS ' . $alias;
        }
        foreach ($this->stageAMeasures($definition, $dimensions, $isAction) as $alias => $sql) {
            $select[] = $sql . ' AS ' . $alias;
        }

        $dateColumn = $base . ($isAction ? '.server_time' : '.visit_last_action_time');
        $where = $dateColumn . ' >= ? AND ' . $dateColumn . ' <= ? AND ' . $base . '.idsite = ?';
        if ($isAction) {
            // rows without a value of an action dimension are not about that kind of action
            foreach (array_values($dimensions) as $i => $dimension) {
                if ($dimension->scope === DimensionCatalog::SCOPE_ACTION) {
                    $where .= ' AND ' . $keys['fwde_k' . ($i + 1)] . ' IS NOT NULL';
                }
            }
        }

        $bind = [$context->windowStartUtc, $context->windowEndUtc, $idSite];
        foreach ($definition->filters as $i => $filter) {
            if (!in_array($filter['operator'], ExplorationDefinition::TEXT_OPERATORS, true)) {
                continue;
            }
            $index = $filter['target'] === ExplorationDefinition::SORT_LABEL ? 0 : 1;
            $key = $keys['fwde_k' . ($index + 1)];
            // Matomo stores titles, event names, keywords and custom dimension values HTML-escaped
            $value = array_values($dimensions)[$index]->storesSanitizedText
                ? Common::sanitizeInputValue((string) $filter['value'])
                : (string) $filter['value'];
            $condition = isset($resolvedFilters[$i])
                ? self::resolvedFilterSql($key, $filter['operator'], $resolvedFilters[$i], $bind)
                : self::textFilterSql($key, $filter['operator'], $value, $bind);
            if ($condition !== null) {
                $where .= ' AND ' . $condition;
            }
        }

        $groupBy = $isAction ? implode(', ', array_keys($keys)) . ', log_link_visit_action.idvisit' : null;

        return ($this->segmentedSelect)(implode(', ', $select), $from, $where, $bind, $groupBy);
    }

    /**
     * A text filter on the stored value (labels equal stored values for these dimensions).
     */
    private static function textFilterSql(string $key, string $operator, string $value, array &$bind): string
    {
        if ($operator === 'equals') {
            $bind[] = $value;
            return '(' . $key . ') = ?';
        }

        // LIKE treats % and _ as wildcards and \ as escape character; the filter text is matched literally
        $bind[] = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value) . '%';
        return $operator === 'contains'
            ? '(' . $key . ') LIKE ?'
            : '((' . $key . ') IS NULL OR (' . $key . ') NOT LIKE ?)';
    }

    /**
     * A text filter on a coded dimension, resolved to the stored values whose label matches.
     *
     * @param array{values: list<string>, includeNull: bool} $resolved
     * @return string|null null when the filter excludes nothing
     */
    private static function resolvedFilterSql(string $key, string $operator, array $resolved, array &$bind): ?string
    {
        $values = $resolved['values'];
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        array_push($bind, ...$values);

        if ($operator !== 'notContains') {
            if ($values === []) {
                return $resolved['includeNull'] ? '((' . $key . ') IS NULL)' : '1 = 0';
            }
            return '((' . $key . ') IN (' . $placeholders . ')' . ($resolved['includeNull'] ? ' OR (' . $key . ') IS NULL' : '') . ')';
        }

        if ($values === []) {
            return $resolved['includeNull'] ? '((' . $key . ') IS NOT NULL)' : null;
        }
        // NOT IN is NULL for NULL keys, so they drop out unless they must stay
        return $resolved['includeNull']
            ? '((' . $key . ') NOT IN (' . $placeholders . '))'
            : '((' . $key . ') IS NULL OR (' . $key . ') NOT IN (' . $placeholders . '))';
    }

    /**
     * @return array<string, string> alias => SQL of the per-unit values the requested metrics need
     */
    private function stageAMeasures(ExplorationDefinition $definition, array $dimensions, bool $isAction): array
    {
        $pageColumn = 'idaction_url';
        foreach ($dimensions as $dimension) {
            $pageColumn = $dimension->pageIdentityColumn ?? $pageColumn;
        }

        $measures = [];
        foreach ($definition->metricIds as $metricId) {
            if ($isAction) {
                if ($metricId === 'avg_event_value') {
                    [$alias, $sql] = self::ACTION_MEASURES['sum_event_value'];
                    $measures[$alias] = $sql;
                    [$alias, $sql] = self::EVENTS_WITH_VALUE;
                    $measures[$alias] = $sql;
                } elseif (isset(self::ACTION_MEASURES[$metricId])) {
                    [$alias, $sql] = self::ACTION_MEASURES[$metricId];
                    $measures[$alias] = $metricId === 'nb_uniq_pageviews' ? sprintf($sql, $pageColumn) : $sql;
                }
                continue;
            }
            if ($metricId === 'nb_uniq_visitors') {
                $measures['fwde_idvisitor'] = 'log_visit.idvisitor';
            } elseif (in_array($metricId, ['nb_actions', 'bounce_rate', 'nb_actions_per_visit', 'max_actions'], true)) {
                $measures['fwde_actions'] = 'log_visit.visit_total_actions';
            } elseif ($metricId === 'avg_time_on_site') {
                $measures['fwde_time'] = 'log_visit.visit_total_time';
            }
        }
        return $measures;
    }

    private function metricSql(string $metricId, bool $isAction): string
    {
        $idVisitor = $isAction ? 'fwde_v.idvisitor' : 'fwde_a.fwde_idvisitor';
        $actions = $isAction ? 'fwde_v.visit_total_actions' : 'fwde_a.fwde_actions';
        $time = $isAction ? 'fwde_v.visit_total_time' : 'fwde_a.fwde_time';

        switch ($metricId) {
            case 'nb_visits':
                return 'COUNT(*)';
            case 'nb_uniq_visitors':
                return 'COUNT(DISTINCT ' . $idVisitor . ')';
            case 'nb_actions':
                return 'SUM(' . $actions . ')';
            case 'bounce_rate':
                return 'ROUND(SUM(CASE WHEN ' . $actions . ' <= 1 THEN 1 ELSE 0 END) / COUNT(*), 2)';
            case 'avg_time_on_site':
                return 'ROUND(SUM(' . $time . ') / COUNT(*), 0)';
            case 'nb_actions_per_visit':
                return 'ROUND(SUM(' . $actions . ') / COUNT(*), 1)';
            case 'max_actions':
                return 'MAX(' . $actions . ')';
            case 'nb_conversions':
                return 'COALESCE(SUM(fwde_c.fwde_conversions), 0)';
            case 'conversion_rate':
                return 'ROUND(SUM(CASE WHEN fwde_c.fwde_conversions > 0 THEN 1 ELSE 0 END) / COUNT(*), 4)';
            case 'revenue':
                return 'ROUND(COALESCE(SUM(fwde_c.fwde_revenue), 0), 2)';
            case 'sum_event_value':
                return 'ROUND(COALESCE(SUM(fwde_a.fwde_event_value), 0), 2)';
            case 'avg_event_value':
                return 'ROUND(COALESCE(SUM(fwde_a.fwde_event_value) / NULLIF(SUM(fwde_a.fwde_events_with_value), 0), 0), 2)';
            default:
                return 'SUM(fwde_a.' . self::ACTION_MEASURES[$metricId][0] . ')';
        }
    }

    private function conversions(ExplorationDefinition $definition, int $idSite, QueryContext $context): array
    {
        // fwde_ names: HAVING looks up names in the joined tables before the select aliases (e.g. "revenue")
        $sql = 'SELECT log_conversion.idvisit AS idvisit, COUNT(*) AS fwde_conversions,'
            . ' SUM(CASE WHEN ABS(log_conversion.revenue) > 1000000000000 THEN 0 ELSE log_conversion.revenue END) AS fwde_revenue'
            . ' FROM ' . ($this->prefixTable)('log_conversion') . ' AS log_conversion'
            . ' WHERE log_conversion.idsite = ? AND log_conversion.server_time >= ? AND log_conversion.server_time <= ?'
            // ecommerce orders (0) count as conversions, abandoned carts (-1) do not, as in Matomo's goal reports
            . ' AND log_conversion.idgoal >= 0';
        $bind = [$idSite, $context->windowStartUtc, $context->windowEndUtc];
        if ($definition->idGoal !== null) {
            $sql .= ' AND log_conversion.idgoal = ?';
            $bind[] = $definition->idGoal;
        }
        $sql .= ' GROUP BY log_conversion.idvisit';

        return ['sql' => $sql, 'bind' => $bind];
    }

    private function orderBy(ExplorationDefinition $definition, array $keyAliases): string
    {
        $direction = strtoupper($definition->sortOrder);
        if ($definition->sortColumn === ExplorationDefinition::SORT_LABEL) {
            $first = 'fwde_k1';
        } elseif ($definition->sortColumn === ExplorationDefinition::SORT_SECONDARY_LABEL) {
            $first = 'fwde_k2';
        } else {
            $first = $definition->sortColumn;
        }

        $orderBy = [$first . ' ' . $direction];
        foreach ($keyAliases as $alias) {
            if ($alias !== $first) {
                $orderBy[] = $alias . ' ASC';
            }
        }
        return implode(', ', $orderBy);
    }

    private function hasMetricOfScope(ExplorationDefinition $definition, string $scope): bool
    {
        foreach ($definition->metricIds as $metricId) {
            if ($this->metrics->get($metricId)->scope === $scope) {
                return true;
            }
        }
        return false;
    }

    private function readsLogVisit(array $keys, array $joins): bool
    {
        foreach ($keys as $sql) {
            if (str_contains($sql, self::VISIT . '.')) {
                return true;
            }
        }
        foreach ($joins as $join) {
            if (str_contains($join['joinOn'], self::VISIT . '.')) {
                return true;
            }
        }
        return false;
    }
}
