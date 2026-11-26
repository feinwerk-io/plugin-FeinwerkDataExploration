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
    /** text operators that keep rows without a value and test the negation of their positive match */
    private const NEGATED_TEXT_OPERATORS = ['notContains', 'notEquals', 'notStartsWith', 'notEndsWith'];
    private const NUMBER_SQL = ['gt' => '>', 'gte' => '>=', 'lt' => '<', 'lte' => '<='];
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

    /** archive mode (design 2026-11-25): rates and averages selected as their numerator and denominator */
    private bool $archiveColumns = false;

    public function __construct(callable $segmentedSelect, callable $prefixTable, ?MetricCatalog $metrics = null)
    {
        $this->segmentedSelect = $segmentedSelect;
        $this->prefixTable = $prefixTable;
        $this->metrics = $metrics ?? new MetricCatalog();
    }

    /**
     * The same builder in archive mode: each rate or average is selected as `<id>__n` and `<id>__d` (componentSql), so
     * Matomo can add up days into any period (Archive/StoredMetrics.php).
     */
    public function withArchiveColumns(): self
    {
        $copy = clone $this;
        $copy->archiveColumns = true;
        return $copy;
    }

    /**
     * @param DimensionDefinition[] $dimensions the definition's dimensions, in order
     * @param array<int, array{values: list<string>, includeNull: bool}> $resolvedFilters by filter index: text filters on
     *        coded dimensions, already resolved to stored values (see LabelFilterResolver)
     * @param bool $sortOutsideGrouping sort in an outer query (MySQL before 8.0, see GroupingQuirk)
     * @param list<array{0: ?string, 1: ?string}>|null $onlyKeys only these rows (raw keys of fwde_k1, fwde_k2), without
     *        number filters, sorting and limit: a comparison column for the rows of the own view. The result then has
     *        fwde_row (the index in $onlyKeys) instead of the keys: the database matches the groups with its own
     *        collation (it groups "Shoes" and "shoes" as one, and either spelling may come back)
     * @return array{sql: string, bind: array} result columns fwde_k1, fwde_k2 (two dimensions only) and the metric ids
     */
    public function build(ExplorationDefinition $definition, array $dimensions, int $idSite, QueryContext $context, array $resolvedFilters = [], bool $sortOutsideGrouping = false, ?array $onlyKeys = null): array
    {
        $keys = $this->keys($dimensions, $context);
        $stageA = $this->stageA($definition, $dimensions, $keys, $idSite, $context, $resolvedFilters);
        if ($onlyKeys !== null) {
            return $this->restricted($definition, array_keys($keys), $stageA, $idSite, $context, $onlyKeys);
        }
        [$sql, $bind] = $this->stageB($definition, array_keys($keys), $stageA, $idSite, $context);

        $groupBy = array_map(fn (string $alias) => 'fwde_a.' . $alias, array_keys($keys));
        $sql .= ' GROUP BY ' . implode(', ', $groupBy);

        // clauses with a number condition test the grouped rows; their text conditions test the grouped key. The key goes
        // inside an aggregate: MySQL (not MariaDB) may check a bare column of the merged derived table against a row of
        // another group; within a group every key is equal, so MAX() is the group's key.
        $having = [];
        $havingBind = [];
        foreach (FilterClauses::of($definition) as $clause) {
            if (!$clause['number']) {
                continue;
            }
            [$condition, $conditionBind] = self::clauseSql($clause, function (int $i, array &$conditionBind) use ($definition, $dimensions, $resolvedFilters): ?string {
                $filter = $definition->filters[$i];
                if (in_array($filter['operator'], ExplorationDefinition::NUMBER_OPERATORS, true)) {
                    $conditionBind[] = $filter['value'];
                    return $filter['target'] . ' ' . self::NUMBER_SQL[$filter['operator']] . ' ?';
                }
                $alias = 'MAX(fwde_a.fwde_k' . self::labelKey($filter['target']) . ')';
                return self::textCondition($filter, $i, $dimensions, $alias, $resolvedFilters, $conditionBind);
            });
            if ($condition !== null) {
                $having[] = $condition;
                array_push($havingBind, ...$conditionBind);
            }
        }
        if ($having !== []) {
            $sql .= ' HAVING ' . implode(' AND ', $having);
            array_push($bind, ...$havingBind);
        }

        $orderBy = ' ORDER BY ' . $this->orderBy($definition, array_keys($keys)) . ' LIMIT ' . $definition->limit;
        // see GroupingQuirk: the outer query sorts the grouped rows only
        $sql = $sortOutsideGrouping ? 'SELECT * FROM (' . $sql . ') AS fwde_g' . $orderBy : $sql . $orderBy;

        return ['sql' => $sql, 'bind' => $bind];
    }

    /**
     * The exact rest of a cut table (design 2026-11-20): the definition's metrics over everything the table covers (as
     * build(): its filters in stage A, the dimensions' kind of action) except the shown rows, one row. Stage A keeps the
     * keys, so the shown key tuples are compared on the grouped units, null-safe (a "not defined" row has a NULL key).
     * Exact only when the table adds up (Engine\Additivity); the caller checks that.
     *
     * @param list<array{0: ?string, 1: ?string}> $shownKeys raw keys of the shown rows (Explorer::rowKeys)
     * @return array{sql: string, bind: array} one row with the metric ids as columns
     */
    public function buildOthers(ExplorationDefinition $definition, array $dimensions, int $idSite, QueryContext $context, array $resolvedFilters, array $shownKeys): array
    {
        $keys = $this->keys($dimensions, $context);
        $aliases = array_keys($keys);
        $stageA = $this->stageA($definition, $dimensions, $keys, $idSite, $context, $resolvedFilters);
        [$sql, $bind] = $this->stageB($definition, [], $stageA, $idSite, $context);
        if ($shownKeys === []) {
            return ['sql' => $sql, 'bind' => $bind];
        }

        // a shown row needs each key among the shown ones: IN lists (looked up quickly, with the collation the database
        // groups by) first; with two dimensions the null-safe pairs decide only for the rows that pass them
        $parts = [];
        foreach ($aliases as $i => $alias) {
            $values = [];
            $hasNull = false;
            foreach ($shownKeys as $tuple) {
                if (($tuple[$i] ?? null) === null) {
                    $hasNull = true;
                } else {
                    $values[(string) $tuple[$i]] = true;
                }
            }
            $tests = [];
            if ($values !== []) {
                $tests[] = 'fwde_a.' . $alias . ' IN (' . implode(', ', array_fill(0, count($values), '?')) . ')';
                array_push($bind, ...array_map('strval', array_keys($values)));
            }
            if ($hasNull) {
                $tests[] = 'fwde_a.' . $alias . ' IS NULL';
            }
            $parts[] = count($tests) > 1 && count($aliases) > 1 ? '(' . implode(' OR ', $tests) . ')' : implode(' OR ', $tests);
        }
        if (count($aliases) > 1) {
            $pairs = [];
            foreach ($shownKeys as $tuple) {
                $pair = [];
                foreach ($aliases as $i => $alias) {
                    $pair[] = 'fwde_a.' . $alias . ' <=> ?';
                    $bind[] = $tuple[$i] ?? null;
                }
                $pairs[] = '(' . implode(' AND ', $pair) . ')';
            }
            $parts[] = '(' . implode(' OR ', $pairs) . ')';
        }
        // IS NOT TRUE, not NOT: a NULL key against an IN list without NULL is unknown, and that row belongs to the rest
        return ['sql' => $sql . ' WHERE (' . implode(' AND ', $parts) . ') IS NOT TRUE', 'bind' => $bind];
    }

    /**
     * The totals of a definition (comparison design 3): its metrics over everything the table covers (text filters and,
     * in action scope, the dimensions' kind of action), before number filters and the row limit; one row. In action
     * scope stage A has one row per visit, so a visit with several matching actions counts once.
     *
     * @return array{sql: string, bind: array} one row with the metric ids as columns
     */
    public function buildTotals(ExplorationDefinition $definition, array $dimensions, int $idSite, QueryContext $context, array $resolvedFilters = []): array
    {
        $keys = $this->keys($dimensions, $context);
        $stageA = $this->stageA($definition, $dimensions, $keys, $idSite, $context, $resolvedFilters, true);
        [$sql, $bind] = $this->stageB($definition, [], $stageA, $idSite, $context);
        return ['sql' => $sql, 'bind' => $bind];
    }

    /**
     * An upper level of a report (saved reports, 2026-11-24; any depth since rows and pivot): each prefix of the first
     * k dimensions in $prefixKeys (k = the length of its tuples) with its totals over what the text filters let through
     * (filters on the deeper dimensions included), as buildTotals() does for the whole table; stage A grouped by visit
     * and the k keys, so a visit counts once per prefix. No number filters, no sorting, no limit.
     *
     * @param list<list<?string>> $prefixKeys the raw keys of the prefixes, all of one length (1 to the dimensions - 1)
     * @return array{sql: string, bind: array} result columns fwde_row (the index into $prefixKeys) and the metric ids
     */
    /**
     * One level of an archived day (archived reports, 2026-11-25): grouped by the first $level keys while every
     * dimension's conditions still apply (as the saved reports' upper levels), the definition's sort and limit
     * (ExplorationDefinition::forArchive). The archive's definition holds no number condition (Archive/FilterSplit.php).
     *
     * @param DimensionDefinition[] $dimensions the definition's dimensions, in order
     */
    public function buildArchiveLevel(ExplorationDefinition $definition, array $dimensions, int $idSite, QueryContext $context, array $resolvedFilters, int $level, bool $sortOutsideGrouping): array
    {
        $keys = $this->keys($dimensions, $context);
        $aliases = array_slice(array_keys($keys), 0, max(1, min($level, count($keys))));
        $stageA = $this->stageA($definition, $dimensions, $keys, $idSite, $context, $resolvedFilters, false, null, $aliases);
        [$sql, $bind] = $this->stageB($definition, $aliases, $stageA, $idSite, $context);
        $sql .= ' GROUP BY ' . implode(', ', array_map(static fn (string $alias): string => 'fwde_a.' . $alias, $aliases));
        $orderBy = ' ORDER BY ' . $this->orderBy($definition, $aliases) . ' LIMIT ' . $definition->limit;
        // see GroupingQuirk: the outer query sorts the grouped rows only
        $sql = $sortOutsideGrouping ? 'SELECT * FROM (' . $sql . ') AS fwde_g' . $orderBy : $sql . $orderBy;
        return ['sql' => $sql, 'bind' => $bind];
    }

    public function buildLevel(ExplorationDefinition $definition, array $dimensions, int $idSite, QueryContext $context, array $resolvedFilters, array $prefixKeys): array
    {
        $keys = $this->keys($dimensions, $context);
        $aliases = array_slice(array_keys($keys), 0, max(1, count($prefixKeys[0] ?? [])));
        $stageA = $this->stageA($definition, $dimensions, $keys, $idSite, $context, $resolvedFilters, false, null, $aliases);
        return $this->restricted($definition, $aliases, $stageA, $idSite, $context, array_values($prefixKeys));
    }

    /**
     * The pivot's values (rows and pivot, 2026-11-24) with every metric of the definition: grouped by the pivot key
     * alone over what the text filters let through (the row dimensions' filters and kind of action included), the
     * first metric's largest first, cut at $limit; with a null limit every value in the key's own order (Weekday). The
     * pivot key follows the row keys (fwde_k{n+1}); in action scope stage A has one row per visit and pivot value.
     * $sortOutsideGrouping: see GroupingQuirk.
     *
     * @return array{sql: string, bind: array} result columns fwde_k{n+1} and the metric ids
     */
    public function buildPivotValues(ExplorationDefinition $definition, array $dimensions, DimensionDefinition $pivot, int $idSite, QueryContext $context, array $resolvedFilters, ?int $limit, bool $sortOutsideGrouping = false, ?string $orderColumn = null): array
    {
        [$all, $keys, $alias] = $this->withPivot($dimensions, $pivot, $context);
        $stageA = $this->stageA($definition, $all, $keys, $idSite, $context, $resolvedFilters, false, null, [$alias]);
        [$sql, $bind] = $this->stageB($definition, [$alias], $stageA, $idSite, $context);
        $sql .= ' GROUP BY fwde_a.' . $alias;
        // $orderColumn: the archive's stored column (archive mode selects a rate's pair, not the rate)
        $orderBy = ' ORDER BY ' . ($limit === null ? $alias . ' ASC' : ($orderColumn ?? $definition->metricIds[0]) . ' DESC, ' . $alias . ' ASC LIMIT ' . $limit);
        // see GroupingQuirk: the outer query sorts the grouped values only
        $sql = $sortOutsideGrouping ? 'SELECT * FROM (' . $sql . ') AS fwde_g' . $orderBy : $sql . $orderBy;
        return ['sql' => $sql, 'bind' => $bind];
    }

    /**
     * The pivot cells of the shown rows: each row (fwde_row, the index in $rowKeys, matched as the database compares)
     * by pivot value (fwde_p, the index in $pivotKeys, matched the same way); with $others the rest of the pivot values
     * as one group (fwde_p NULL).
     * No number filters, sorting or limit.
     *
     * @param list<list<?string>> $rowKeys raw row keys of the shown rows (Explorer::rowKeys)
     * @param list<?string> $pivotKeys raw keys of the pivot values shown as columns
     * @return array{sql: string, bind: array} result columns fwde_row, fwde_p and the metric ids
     */
    public function buildPivotCells(ExplorationDefinition $definition, array $dimensions, DimensionDefinition $pivot, int $idSite, QueryContext $context, array $resolvedFilters, array $rowKeys, array $pivotKeys, bool $others): array
    {
        [$all, $keys, $alias] = $this->withPivot($dimensions, $pivot, $context);
        $stageA = $this->stageA($definition, $all, $keys, $idSite, $context, $resolvedFilters);
        $rowAliases = array_slice(array_keys($keys), 0, count($dimensions));
        return $this->restricted($definition, $rowAliases, $stageA, $idSite, $context, $rowKeys, false,
            ['alias' => $alias, 'keys' => $pivotKeys, 'others' => $others]);
    }

    /**
     * The Others column's total: the definition's metrics over what the text filters let through, outside the pivot
     * values shown as columns (the column totals of the shown values come with buildPivotValues()). One row.
     *
     * @param list<?string> $pivotKeys raw keys of the pivot values shown as columns
     * @return array{sql: string, bind: array} one row with the metric ids as columns
     */
    public function buildPivotTotals(ExplorationDefinition $definition, array $dimensions, DimensionDefinition $pivot, int $idSite, QueryContext $context, array $resolvedFilters, array $pivotKeys): array
    {
        [$all, $keys, $alias] = $this->withPivot($dimensions, $pivot, $context);
        $stageA = $this->stageA($definition, $all, $keys, $idSite, $context, $resolvedFilters, false, null, [$alias]);
        [$sql, $bind] = $this->stageB($definition, [], $stageA, $idSite, $context);
        [$condition, $conditionBind] = self::pivotCondition($alias, $pivotKeys, true);
        return ['sql' => $sql . ' WHERE ' . $condition, 'bind' => array_merge($bind, $conditionBind)];
    }

    /** @return array{0: list<DimensionDefinition>, 1: array<string, string>, 2: string} the dimensions with the pivot last, their keys, the pivot's alias */
    private function withPivot(array $dimensions, DimensionDefinition $pivot, QueryContext $context): array
    {
        $all = array_merge(array_values($dimensions), [$pivot]);
        $keys = $this->keys($all, $context);
        return [$all, $keys, 'fwde_k' . count($all)];
    }

    /**
     * The pivot key among the given values (an IN list, NULL for a value without key), or with $others outside them:
     * IS NOT TRUE, so a NULL key outside the list belongs to the rest.
     *
     * @return array{0: string, 1: array}
     */
    private static function pivotCondition(string $alias, array $pivotKeys, bool $others): array
    {
        $values = array_values(array_unique(array_map('strval', array_filter($pivotKeys, static fn ($key) => $key !== null))));
        $tests = [];
        if ($values !== []) {
            $tests[] = 'fwde_a.' . $alias . ' IN (' . implode(', ', array_fill(0, count($values), '?')) . ')';
        }
        if (in_array(null, $pivotKeys, true)) {
            $tests[] = 'fwde_a.' . $alias . ' IS NULL';
        }
        $condition = '(' . ($tests === [] ? '1 = 0' : implode(' OR ', $tests)) . ')';
        return [$others ? $condition . ' IS NOT TRUE' : $condition, $values];
    }

    /**
     * The trend (trend design 5): with $onlyKeys the rows of the own view by their index (fwde_row, as in build()) and
     * the time key, otherwise the totals (as buildTotals()) by the time key. The time key fwde_t is the expression of
     * the "Date" or "Hour" dimension, so the trend counts days and hours as those dimensions do. No number filters,
     * no sorting, no limit.
     *
     * @param list<array{0: ?string, 1: ?string}>|null $onlyKeys
     * @return array{sql: string, bind: array} result columns fwde_t, the metric ids and, with $onlyKeys, fwde_row
     */
    public function buildTrend(ExplorationDefinition $definition, array $dimensions, int $idSite, QueryContext $context, array $resolvedFilters, DimensionDefinition $time, ?array $onlyKeys): array
    {
        $keys = $this->keys($dimensions, $context);
        $stageA = $this->stageA($definition, $dimensions, $keys, $idSite, $context, $resolvedFilters, $onlyKeys === null, $time);
        if ($onlyKeys !== null) {
            return $this->restricted($definition, array_keys($keys), $stageA, $idSite, $context, $onlyKeys, true);
        }
        [$sql, $bind] = $this->stageB($definition, ['fwde_t'], $stageA, $idSite, $context);
        return ['sql' => $sql . ' GROUP BY fwde_a.fwde_t', 'bind' => $bind];
    }

    /**
     * Stage B without grouping: the given key aliases and the metrics over stage A, with the visit and conversion joins.
     *
     * @param string[] $keyAliases
     * @param array{sql: string, bind: array}|null $leading a first select column (its binds come first)
     * @return array{0: string, 1: array}
     */
    private function stageB(ExplorationDefinition $definition, array $keyAliases, array $stageA, int $idSite, QueryContext $context, ?array $leading = null): array
    {
        $isAction = $context->scope === QueryContext::SCOPE_ACTION;
        $select = $leading === null ? [] : [$leading['sql']];
        foreach ($keyAliases as $alias) {
            $select[] = 'fwde_a.' . $alias . ' AS ' . $alias;
        }
        foreach ($definition->metricIds as $metricId) {
            $pair = $this->archiveColumns ? $this->componentSql($metricId, $isAction) : null;
            if ($pair !== null) {
                $select[] = $pair[0] . ' AS ' . $metricId . '__n';
                $select[] = $pair[1] . ' AS ' . $metricId . '__d';
                continue;
            }
            $select[] = $this->metricSql($metricId, $isAction) . ' AS ' . $metricId;
        }

        $sql = 'SELECT ' . implode(', ', $select) . ' FROM (' . $stageA['sql'] . ') AS fwde_a';
        $bind = array_merge($leading['bind'] ?? [], $stageA['bind']);

        if ($isAction && $this->hasMetricOfScope($definition, MetricCatalog::SCOPE_VISIT)) {
            $sql .= ' INNER JOIN ' . ($this->prefixTable)(self::VISIT) . ' AS fwde_v ON fwde_v.idvisit = fwde_a.fwde_idvisit';
        }
        if ($this->hasMetricOfScope($definition, MetricCatalog::SCOPE_GOAL)) {
            $conversions = $this->conversions($definition, $idSite, $context);
            $sql .= ' LEFT JOIN (' . $conversions['sql'] . ') AS fwde_c ON fwde_c.idvisit = fwde_a.fwde_idvisit';
            $bind = array_merge($bind, $conversions['bind']);
        }
        return [$sql, $bind];
    }

    /**
     * Stage B for the rows of the own view: each group gets the index of the own row it belongs to (fwde_row), as the
     * database compares (collation, NULL-safe); IN lists per key filter first, so the CASE runs only on candidates.
     *
     * @param string[] $aliases fwde_k1, fwde_k2
     * @param list<array{0: ?string, 1: ?string}> $onlyKeys
     * @param bool $byTime also by the time key fwde_t of stage A (trend)
     */
    private function restricted(ExplorationDefinition $definition, array $aliases, array $stageA, int $idSite, QueryContext $context, array $onlyKeys, bool $byTime = false, ?array $pivot = null): array
    {
        $whens = [];
        $caseBind = [];
        foreach ($onlyKeys as $index => $keys) {
            $parts = [];
            foreach ($aliases as $i => $alias) {
                $parts[] = 'fwde_a.' . $alias . ' <=> ?';
                $caseBind[] = $keys[$i] ?? null;
            }
            $whens[] = 'WHEN (' . implode(' AND ', $parts) . ') THEN ' . (int) $index;
        }
        $leading = $onlyKeys === []
            ? ['sql' => 'NULL AS fwde_row', 'bind' => []]
            : ['sql' => 'CASE ' . implode(' ', $whens) . ' END AS fwde_row', 'bind' => $caseBind];
        if ($pivot !== null) {
            // the pivot value of each group (rows and pivot): its index in the pivot keys, matched as the database
            // compares (as the rows), or nothing for the Others group
            if ($pivot['others'] || $pivot['keys'] === []) {
                $leading['sql'] .= ', NULL AS fwde_p';
            } else {
                $pivotWhens = [];
                foreach (array_values($pivot['keys']) as $index => $key) {
                    $pivotWhens[] = 'WHEN fwde_a.' . $pivot['alias'] . ' <=> ? THEN ' . (int) $index;
                    $leading['bind'][] = $key;
                }
                $leading['sql'] .= ', CASE ' . implode(' ', $pivotWhens) . ' END AS fwde_p';
            }
        }
        [$sql, $bind] = $this->stageB($definition, $byTime ? ['fwde_t'] : [], $stageA, $idSite, $context, $leading);

        if ($onlyKeys === []) {
            return ['sql' => $sql . ' WHERE 1 = 0', 'bind' => $bind];
        }
        $filters = [];
        foreach ($aliases as $i => $alias) {
            $values = [];
            $hasNull = false;
            foreach ($onlyKeys as $keys) {
                if (($keys[$i] ?? null) === null) {
                    $hasNull = true;
                } else {
                    $values[(string) $keys[$i]] = true;
                }
            }
            $parts = [];
            if ($values !== []) {
                $parts[] = 'fwde_a.' . $alias . ' IN (' . implode(', ', array_fill(0, count($values), '?')) . ')';
                array_push($bind, ...array_map('strval', array_keys($values)));
            }
            if ($hasNull) {
                $parts[] = 'fwde_a.' . $alias . ' IS NULL';
            }
            $filters[] = '(' . implode(' OR ', $parts) . ')';
        }
        $groupBy = 'fwde_row' . ($byTime ? ', fwde_a.fwde_t' : '');
        if ($pivot !== null) {
            [$pivotSql, $pivotBind] = self::pivotCondition($pivot['alias'], $pivot['keys'], $pivot['others']);
            $filters[] = $pivotSql;
            array_push($bind, ...$pivotBind);
            $groupBy .= $pivot['others'] ? '' : ', fwde_p';
        }
        $sql .= ' WHERE ' . implode(' AND ', $filters) . ' GROUP BY ' . $groupBy . ' HAVING fwde_row IS NOT NULL';
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

    /**
     * @param bool $perVisit totals: no key columns, and in action scope one row per visit
     * @param DimensionDefinition|null $time a time key fwde_t, selected and grouped in both modes (trend)
     */
    private function stageA(ExplorationDefinition $definition, array $dimensions, array $keys, int $idSite, QueryContext $context, array $resolvedFilters, bool $perVisit = false, ?DimensionDefinition $time = null, ?array $groupKeys = null): array
    {
        // the keys stage A selects and groups by: all, none per visit, or only some (the first level, saved reports);
        // the filters still read every key
        $grouping = $perVisit ? [] : ($groupKeys === null ? $keys : array_intersect_key($keys, array_flip($groupKeys)));
        $timeKeys = $time === null ? [] : ['fwde_t' => $time->keySql($context, 'fwde_t')];
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
        if ($time !== null) {
            $joins = array_merge($joins, $time->joins($context, 'fwde_t'));
        }
        if ($isAction && $this->readsLogVisit($keys + $timeKeys, $joins)) {
            $from[] = self::VISIT;
        }
        $from = array_merge($from, $joins);

        $select = [$base . '.idvisit AS fwde_idvisit'];
        foreach ($grouping + $timeKeys as $alias => $keySql) {
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
        // text-only clauses test every unit here; clauses with a number condition test the grouped rows (build())
        foreach (FilterClauses::of($definition) as $clause) {
            if ($clause['number']) {
                continue;
            }
            [$condition, $conditionBind] = self::clauseSql($clause, function (int $i, array &$conditionBind) use ($definition, $dimensions, $keys, $resolvedFilters): ?string {
                $filter = $definition->filters[$i];
                $key = $keys['fwde_k' . self::labelKey($filter['target'])];
                return self::textCondition($filter, $i, $dimensions, $key, $resolvedFilters, $conditionBind);
            });
            if ($condition !== null) {
                $where .= ' AND ' . $condition;
                array_push($bind, ...$conditionBind);
            }
        }

        $grouped = array_keys($grouping + $timeKeys);
        $groupBy = $isAction ? implode(', ', array_merge($grouped, ['log_link_visit_action.idvisit'])) : null;

        return ($this->segmentedSelect)(implode(', ', $select), $from, $where, $bind, $groupBy);
    }

    /**
     * The SQL of one clause: its groups joined by OR, each group's conditions by its join. A condition that excludes
     * nothing (null) drops out of an AND group and makes an OR group true; a true group makes the clause true (null,
     * no binds): binds stay with their conditions, so a dropped part takes its binds along.
     *
     * @param array{groups: list<array{join: string, indexes: list<int>}>, number: bool} $clause
     * @param callable $condition fn(int $index, array &$bind): ?string the SQL of one condition
     * @return array{0: ?string, 1: array}
     */
    private static function clauseSql(array $clause, callable $condition): array
    {
        $groups = [];
        $bind = [];
        foreach ($clause['groups'] as $group) {
            $parts = [];
            $partBind = [];
            foreach ($group['indexes'] as $i) {
                $conditionBind = [];
                $sql = $condition($i, $conditionBind);
                if ($sql === null) {
                    if ($group['join'] === 'OR') {
                        return [null, []];
                    }
                    continue;
                }
                $parts[] = $sql;
                array_push($partBind, ...$conditionBind);
            }
            if ($parts === []) {
                return [null, []];
            }
            $groups[] = count($parts) === 1 ? $parts[0] : '(' . implode(' ' . $group['join'] . ' ', $parts) . ')';
            array_push($bind, ...$partBind);
        }
        return [count($groups) === 1 ? $groups[0] : '(' . implode(' OR ', $groups) . ')', $bind];
    }

    /**
     * The SQL of one text filter on $key (a key expression in stage A or a key alias of the grouped rows), null when it
     * excludes nothing. Matomo stores titles, event names, keywords and custom dimension values HTML-escaped.
     *
     * @param DimensionDefinition[] $dimensions
     */
    private static function textCondition(array $filter, int $i, array $dimensions, string $key, array $resolvedFilters, array &$bind): ?string
    {
        $index = self::labelKey($filter['target']) - 1;
        // Matomo stores titles and the like HTML-escaped: a text is escaped the same way; a pattern matches the five
        // escaped characters in both forms (StoredTextPattern)
        $value = (string) $filter['value'];
        if (array_values($dimensions)[$index]->storesSanitizedText) {
            $value = $filter['operator'] === 'matches' ? StoredTextPattern::of($value) : Common::sanitizeInputValue($value);
        }
        return isset($resolvedFilters[$i])
            ? self::resolvedFilterSql($key, $filter['operator'], $resolvedFilters[$i], $bind)
            : self::textFilterSql($key, $filter['operator'], $value, $bind);
    }

    /**
     * A text filter on the stored value (labels equal stored values for these dimensions).
     */
    private static function textFilterSql(string $key, string $operator, string $value, array &$bind): string
    {
        // "is empty" and "is not empty" (2026-11-25): no value or an empty one; as text, so a DATE or a number key is
        // never compared with '' (MySQL 8 refuses an invalid DATE literal, a number would read '' as 0)
        if ($operator === 'empty') {
            return "((" . $key . ") IS NULL OR CAST((" . $key . ") AS CHAR) = '')";
        }
        if ($operator === 'notEmpty') {
            return "((" . $key . ") IS NOT NULL AND CAST((" . $key . ") AS CHAR) <> '')";
        }
        if ($operator === 'equals' || $operator === 'notEquals') {
            $bind[] = $value;
            return $operator === 'equals' ? '(' . $key . ') = ?' : '((' . $key . ') IS NULL OR (' . $key . ') <> ?)';
        }
        if ($operator === 'matches') {
            // the database's regular expressions, case-insensitive with the tables' collations
            $bind[] = $value;
            return '(' . $key . ') REGEXP ?';
        }

        // LIKE treats % and _ as wildcards and \ as escape character; the filter text is matched literally
        $literal = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
        $anchoredStart = $operator === 'startsWith' || $operator === 'notStartsWith';
        $anchoredEnd = $operator === 'endsWith' || $operator === 'notEndsWith';
        $bind[] = ($anchoredStart ? '' : '%') . $literal . ($anchoredEnd ? '' : '%');
        return in_array($operator, self::NEGATED_TEXT_OPERATORS, true)
            ? '((' . $key . ') IS NULL OR (' . $key . ') NOT LIKE ?)'
            : '(' . $key . ') LIKE ?';
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

        // notEmpty negates the empty values the resolver returns, and drops a missing value with them
        if (!in_array($operator, self::NEGATED_TEXT_OPERATORS, true) && $operator !== 'notEmpty') {
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
            } elseif (in_array($metricId, ['avg_time_on_site', 'sum_visit_length'], true)) {
                $measures['fwde_time'] = 'log_visit.visit_total_time';
            }
        }
        return $measures;
    }

    /**
     * The numerator and denominator of a rate or average (design 2026-11-25), the same parts as its live expression in
     * metricSql() without the division and rounding; null for every other metric.
     *
     * @return array{0: string, 1: string}|null
     */
    public function componentSql(string $metricId, bool $isAction): ?array
    {
        $actions = $isAction ? 'fwde_v.visit_total_actions' : 'fwde_a.fwde_actions';
        $time = $isAction ? 'fwde_v.visit_total_time' : 'fwde_a.fwde_time';
        switch ($metricId) {
            case 'bounce_rate':
                return ['SUM(CASE WHEN ' . $actions . ' <= 1 THEN 1 ELSE 0 END)', 'COUNT(*)'];
            case 'avg_time_on_site':
                return ['SUM(' . $time . ')', 'COUNT(*)'];
            case 'nb_actions_per_visit':
                return ['SUM(' . $actions . ')', 'COUNT(*)'];
            case 'conversion_rate':
                return ['SUM(CASE WHEN fwde_c.fwde_conversions > 0 THEN 1 ELSE 0 END)', 'COUNT(*)'];
            case 'revenue_per_visit':
                return ['COALESCE(SUM(fwde_c.fwde_rev_any), 0)', 'COUNT(*)'];
            case 'avg_event_value':
                return ['COALESCE(SUM(fwde_a.fwde_event_value), 0)', 'COALESCE(SUM(fwde_a.fwde_events_with_value), 0)'];
        }
        if (preg_match('/^goal_(\d+|ecommerceOrder|ecommerceAbandonedCart)_(conversion_rate|avg_order_revenue)$/', $metricId, $m) === 1) {
            if ($m[2] === 'avg_order_revenue') {
                return ['COALESCE(SUM(fwde_c.fwde_order_rev), 0)', 'COALESCE(SUM(fwde_c.fwde_orders), 0)'];
            }
            $count = ['ecommerceOrder' => 'fwde_orders', 'ecommerceAbandonedCart' => 'fwde_carts'][$m[1]] ?? 'fwde_conv_g' . $m[1];
            return ['SUM(CASE WHEN fwde_c.' . $count . ' > 0 THEN 1 ELSE 0 END)', 'COUNT(*)'];
        }
        return null;
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
            case 'sum_visit_length':
                return 'SUM(' . $time . ')';
            case 'nb_visits_converted':
                return 'SUM(CASE WHEN fwde_c.fwde_conv_any > 0 THEN 1 ELSE 0 END)';
            case 'revenue_per_visit':
                return 'ROUND(COALESCE(SUM(fwde_c.fwde_rev_any), 0) / COUNT(*), 2)';
            case 'sum_event_value':
                return 'ROUND(COALESCE(SUM(fwde_a.fwde_event_value), 0), 2)';
            case 'avg_event_value':
                return 'ROUND(COALESCE(SUM(fwde_a.fwde_event_value) / NULLIF(SUM(fwde_a.fwde_events_with_value), 0), 0), 2)';
        }
        if (preg_match('/^goal_(\d+|ecommerceOrder|ecommerceAbandonedCart)_([a-z_]+)$/', $metricId, $m) === 1) {
            return self::goalMetricSql($m[1], $m[2]);
        }
        return 'SUM(fwde_a.' . self::ACTION_MEASURES[$metricId][0] . ')';
    }

    /**
     * A goal's or e-commerce metric from the per-visit columns of the conversions subquery (conversionColumns()): counts
     * and revenue summed, rates over the visits of the row, the average order value over the orders.
     */
    private static function goalMetricSql(string $goalKey, string $metric): string
    {
        [$count, $revenue] = [
            'ecommerceOrder' => ['fwde_orders', 'fwde_order_rev'],
            'ecommerceAbandonedCart' => ['fwde_carts', 'fwde_cart_rev'],
        ][$goalKey] ?? ['fwde_conv_g' . $goalKey, 'fwde_rev_g' . $goalKey];
        switch ($metric) {
            case 'nb_conversions':
                return 'COALESCE(SUM(fwde_c.' . $count . '), 0)';
            case 'conversion_rate':
                return 'ROUND(SUM(CASE WHEN fwde_c.' . $count . ' > 0 THEN 1 ELSE 0 END) / COUNT(*), 4)';
            case 'revenue':
                return 'ROUND(COALESCE(SUM(fwde_c.' . $revenue . '), 0), 2)';
            case 'items':
                return 'COALESCE(SUM(fwde_c.fwde_items), 0)';
            case 'avg_order_revenue':
                // 0 without orders, as every average without data (avg_event_value)
                return 'ROUND(COALESCE(SUM(fwde_c.fwde_order_rev) / NULLIF(SUM(fwde_c.fwde_orders), 0), 0), 2)';
        }
        throw new \InvalidArgumentException('Unknown goal metric ' . $metric);
    }

    /** a conversion's revenue, without the absurd values a broken tracker can send */
    private const CONVERSION_REVENUE = 'CASE WHEN ABS(log_conversion.revenue) > 1000000000000 THEN 0 ELSE log_conversion.revenue END';

    /**
     * Per visit the conversions and revenue the requested metrics need, each as a conditional sum over one pass of the
     * visits' conversions (more metrics, 2026-11-24): all goals (idGoal narrows only these), any goal or order for the
     * visit metrics, each requested goal, e-commerce orders with their items, abandoned carts. Ecommerce orders (0) count
     * as conversions, abandoned carts (-1) do not, as in Matomo's goal reports; the idgoal condition widens to the
     * carts only when a cart metric asks for them. Goal ids are integers from the catalogue, written into the SQL.
     */
    private function conversions(ExplorationDefinition $definition, int $idSite, QueryContext $context): array
    {
        $needs = [];
        foreach ($definition->metricIds as $metricId) {
            if (in_array($metricId, ['nb_conversions', 'conversion_rate', 'revenue'], true)) {
                $needs['all'] = true;
            } elseif (in_array($metricId, ['nb_visits_converted', 'revenue_per_visit'], true)) {
                $needs['any'] = true;
            } elseif (preg_match('/^goal_(\d+|ecommerceOrder|ecommerceAbandonedCart)_/', $metricId, $m) === 1) {
                $needs[ctype_digit($m[1]) ? 'g' . (int) $m[1] : $m[1]] = true;
            }
        }
        $carts = isset($needs['ecommerceAbandonedCart']);
        $noCarts = $carts ? 'log_conversion.idgoal >= 0' : null;

        // fwde_ names: HAVING looks up names in the joined tables before the select aliases (e.g. "revenue")
        $columns = [];
        $add = static function (string $alias, ?string $condition, string $value) use (&$columns): void {
            if ($condition === null) {
                $columns[] = ($value === '1' ? 'COUNT(*)' : 'SUM(' . $value . ')') . ' AS ' . $alias;
            } else {
                $columns[] = 'SUM(CASE WHEN ' . $condition . ' THEN ' . $value . ' ELSE 0 END) AS ' . $alias;
            }
        };
        foreach (array_keys($needs) as $need) {
            if ($need === 'all') {
                $condition = $definition->idGoal !== null ? 'log_conversion.idgoal = ' . (int) $definition->idGoal : $noCarts;
                $add('fwde_conversions', $condition, '1');
                $add('fwde_revenue', $condition, self::CONVERSION_REVENUE);
            } elseif ($need === 'any') {
                $add('fwde_conv_any', $noCarts, '1');
                $add('fwde_rev_any', $noCarts, self::CONVERSION_REVENUE);
            } elseif ($need === 'ecommerceOrder') {
                $add('fwde_orders', 'log_conversion.idgoal = 0', '1');
                $add('fwde_order_rev', 'log_conversion.idgoal = 0', self::CONVERSION_REVENUE);
                $add('fwde_items', 'log_conversion.idgoal = 0', 'log_conversion.items');
            } elseif ($need === 'ecommerceAbandonedCart') {
                $add('fwde_carts', 'log_conversion.idgoal = -1', '1');
                $add('fwde_cart_rev', 'log_conversion.idgoal = -1', self::CONVERSION_REVENUE);
            } else {
                $idGoal = (int) substr($need, 1);
                $add('fwde_conv_g' . $idGoal, 'log_conversion.idgoal = ' . $idGoal, '1');
                $add('fwde_rev_g' . $idGoal, 'log_conversion.idgoal = ' . $idGoal, self::CONVERSION_REVENUE);
            }
        }

        $sql = 'SELECT log_conversion.idvisit AS idvisit, ' . implode(', ', $columns)
            . ' FROM ' . ($this->prefixTable)('log_conversion') . ' AS log_conversion'
            . ' WHERE log_conversion.idsite = ? AND log_conversion.server_time >= ? AND log_conversion.server_time <= ?'
            . ' AND log_conversion.idgoal >= ' . ($carts ? '-1' : '0')
            . ' GROUP BY log_conversion.idvisit';

        return ['sql' => $sql, 'bind' => [$idSite, $context->windowStartUtc, $context->windowEndUtc]];
    }

    private function orderBy(ExplorationDefinition $definition, array $keyAliases): string
    {
        $direction = strtoupper($definition->sortOrder);
        $first = in_array($definition->sortColumn, ExplorationDefinition::LABEL_COLUMNS, true)
            ? 'fwde_k' . self::labelKey($definition->sortColumn)
            : $definition->sortColumn;

        $orderBy = [$first . ' ' . $direction];
        foreach ($keyAliases as $alias) {
            if ($alias !== $first) {
                $orderBy[] = $alias . ' ASC';
            }
        }
        return implode(', ', $orderBy);
    }

    /** The key number of a label column: label 1, secondary_label 2, tertiary_label 3. */
    private static function labelKey(string $target): int
    {
        return (int) array_search($target, ExplorationDefinition::LABEL_COLUMNS, true) + 1;
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
