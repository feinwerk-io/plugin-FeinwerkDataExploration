<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Definition;

use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;

/**
 * What to explore: 1 to 3 dimensions, 1 to 10 metrics, an optional pivot dimension, an optional goal, sorting and a row limit. Only built through
 * fromJson(), which validates everything against the positive lists.
 */
final class ExplorationDefinition
{
    /** row dimensions (rows and pivot, 2026-11-24: up to three) */
    public const MAX_DIMENSIONS = 3;
    /** the label column of each row dimension, in order */
    public const LABEL_COLUMNS = ['label', 'secondary_label', 'tertiary_label'];
    /** how many pivot values become columns (default 5) */
    public const PIVOT_LIMITS = [3, 5, 10];
    public const PIVOT_DEFAULT_LIMIT = 5;
    /** pivot values times metrics at most */
    public const MAX_PIVOT_CELLS = 60;
    /** time dimensions with too many values for columns; Weekday may be the pivot (all seven days) */
    public const NO_PIVOT = ['date', 'hour'];
    public const PIVOT_WEEKDAY = 'weekday';
    public const MAX_METRICS = 10;
    public const SORT_LABEL = 'label';
    public const SORT_SECONDARY_LABEL = 'secondary_label';
    public const SORT_TERTIARY_LABEL = 'tertiary_label';
    /** conditions in all groups together (filter groups, 2026-11-23) */
    public const MAX_FILTERS = 10;
    public const MAX_GROUPS = 5;
    public const MATCH_ALL = 'all';
    public const MATCH_ANY = 'any';
    // long enough for a stored URL ("filter to this value" in the row menu)
    public const MAX_FILTER_TEXT = 1000;
    /** the longest regular expression of the "matches" operator */
    public const MAX_PATTERN = 200;
    public const TEXT_OPERATORS = [
        'contains', 'equals', 'notContains', 'notEquals', 'startsWith', 'endsWith', 'notStartsWith', 'notEndsWith', 'matches',
    ];
    public const NUMBER_OPERATORS = ['gt', 'gte', 'lt', 'lte'];

    /**
     * @param string[] $dimensionIds
     * @param string[] $metricIds
     * @param list<array{target: string, operator: string, value: string|float, group: int}> $filters the conditions of
     *        every group in order, each with the index of its group
     * @param list<string> $groupMatches "all" or "any" for each group: how its conditions combine
     */
    private function __construct(
        public readonly array $dimensionIds,
        public readonly array $metricIds,
        public readonly ?int $idGoal,
        public readonly string $sortColumn,
        public readonly string $sortOrder,
        public readonly int $limit,
        public readonly array $filters = [],
        /** how the groups combine: "all" with AND, "any" with OR (filter groups, 2026-11-23) */
        public readonly string $match = self::MATCH_ALL,
        public readonly array $groupMatches = [],
        /** @var array{dimension: string, limit: int}|null the pivot dimension and how many of its values become columns */
        public readonly ?array $pivot = null
    ) {
    }

    /**
     * @param string $json the definition as sent; HTML-escaped quotes (sanitized request parameters) are accepted
     * @throws ExplorationException
     */
    public static function fromJson(string $json, int $idSite, DimensionCatalog $dimensions, MetricCatalog $metrics, Limits $limits): self
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            $data = json_decode(html_entity_decode($json, ENT_QUOTES), true);
        }
        if (!is_array($data)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorInvalidDefinition');
        }

        $dimensionIds = self::stringList($data['dimensions'] ?? null);
        if ($dimensionIds === null || count($dimensionIds) < 1 || count($dimensionIds) > self::MAX_DIMENSIONS) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorDimensionCount');
        }
        foreach ($dimensionIds as $i => $id) {
            if ($dimensions->get($idSite, $id) === null) {
                throw new ExplorationException('FeinwerkDataExploration_ErrorUnknownDimension', [$id]);
            }
            if (array_search($id, $dimensionIds, true) !== $i) {
                throw new ExplorationException('FeinwerkDataExploration_ErrorDuplicateDimension', [$id]);
            }
        }

        $metricIds = self::stringList($data['metrics'] ?? null);
        if ($metricIds === null || count($metricIds) < 1 || count($metricIds) > self::MAX_METRICS) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorMetricCount', [self::MAX_METRICS]);
        }
        foreach ($metricIds as $i => $id) {
            if ($metrics->get($id, $idSite) === null) {
                throw new ExplorationException('FeinwerkDataExploration_ErrorUnknownMetric', [$id]);
            }
            if (array_search($id, $metricIds, true) !== $i) {
                throw new ExplorationException('FeinwerkDataExploration_ErrorDuplicateMetric', [$id]);
            }
        }

        $idGoal = $data['idGoal'] ?? null;
        if ($idGoal !== null && (!is_int($idGoal) || $idGoal < 1)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorGoal');
        }

        $sortColumns = array_merge($metricIds, self::labelColumns(count($dimensionIds)));
        $sortColumn = $data['sort'] ?? $metricIds[0];
        $sortOrder = $data['sortOrder'] ?? 'desc';
        if (!in_array($sortColumn, $sortColumns, true) || !in_array($sortOrder, ['asc', 'desc'], true)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorSort');
        }

        $limit = $data['limit'] ?? $limits->defaultRows;
        if (!is_int($limit) || $limit < 1 || $limit > $limits->maxRows) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorLimit', [$limits->maxRows]);
        }

        $labelTargets = self::labelColumns(count($dimensionIds));
        if (array_key_exists('filterGroups', $data)) {
            if (array_key_exists('filters', $data) || array_key_exists('match', $data)) {
                throw new ExplorationException('FeinwerkDataExploration_ErrorFilterGroupsBoth');
            }
            [$filters, $match, $groupMatches] = self::filterGroups($data['filterGroups'], $labelTargets, $metricIds);
        } else {
            // the form before filter groups: one list and its "match", i.e. one group with that switch
            $legacy = $data['filters'] ?? [];
            if (!is_array($legacy) || !array_is_list($legacy) || count($legacy) > self::MAX_FILTERS) {
                throw new ExplorationException('FeinwerkDataExploration_ErrorFilterCount', [self::MAX_FILTERS]);
            }
            $legacyMatch = self::match($data['match'] ?? self::MATCH_ALL, 'match');
            $filters = [];
            foreach ($legacy as $j => $filter) {
                $filters[] = self::condition($filter, 'filters[' . $j . ']', 0, $labelTargets, $metricIds);
            }
            $match = self::MATCH_ALL;
            $groupMatches = $filters === [] ? [] : [$legacyMatch];
        }

        $pivot = array_key_exists('pivot', $data) ? self::pivot($data['pivot'], $idSite, $dimensions, $dimensionIds, count($metricIds)) : null;

        return new self($dimensionIds, $metricIds, $idGoal, $sortColumn, $sortOrder, $limit, $filters, $match, $groupMatches, $pivot);
    }

    /** The label columns of $count row dimensions: label, secondary_label, tertiary_label. */
    public static function labelColumns(int $count): array
    {
        return array_slice(self::LABEL_COLUMNS, 0, max(1, min($count, self::MAX_DIMENSIONS)));
    }

    /**
     * The pivot (rows and pivot, 2026-11-24): {"dimension": id, "limit": 3|5|10}; another dimension than the rows, not
     * Date or Hour; values times metrics at most MAX_PIVOT_CELLS (Weekday: its seven days).
     *
     * @return array{dimension: string, limit: int}|null
     */
    private static function pivot($value, int $idSite, DimensionCatalog $dimensions, array $dimensionIds, int $metricCount): ?array
    {
        if ($value === null) {
            return null;
        }
        $id = is_array($value) && is_string($value['dimension'] ?? null) ? $value['dimension'] : '';
        if ($id === '' || in_array($id, $dimensionIds, true) || in_array($id, self::NO_PIVOT, true) || $dimensions->get($idSite, $id) === null) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorPivotDimension', [$id]);
        }
        $limit = $value['limit'] ?? self::PIVOT_DEFAULT_LIMIT;
        if (!in_array($limit, self::PIVOT_LIMITS, true)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorPivotLimit', [implode(', ', self::PIVOT_LIMITS)]);
        }
        $columns = $id === self::PIVOT_WEEKDAY ? 7 : $limit;
        if ($columns * $metricCount > self::MAX_PIVOT_CELLS) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorPivotCells', [self::MAX_PIVOT_CELLS]);
        }
        return ['dimension' => $id, 'limit' => $limit];
    }

    /**
     * Row filters in groups: {"match": "all|any", "groups": [{"match": "all|any", "filters": [conditions]}]}.
     *
     * @return array{0: list<array{target: string, operator: string, value: string|float, group: int}>, 1: string, 2: list<string>}
     */
    private static function filterGroups($value, array $labelTargets, array $metricIds): array
    {
        $groups = is_array($value) ? ($value['groups'] ?? null) : null;
        if (!is_array($groups) || !array_is_list($groups) || count($groups) > self::MAX_GROUPS) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorFilterGroups', [self::MAX_GROUPS, self::MAX_FILTERS]);
        }
        $match = self::match($value['match'] ?? self::MATCH_ALL, 'filterGroups.match');

        $filters = [];
        $groupMatches = [];
        foreach ($groups as $i => $group) {
            $conditions = is_array($group) ? ($group['filters'] ?? null) : null;
            if (!is_array($conditions) || !array_is_list($conditions) || $conditions === []
                || count($filters) + count($conditions) > self::MAX_FILTERS) {
                throw new ExplorationException('FeinwerkDataExploration_ErrorFilterGroups', [self::MAX_GROUPS, self::MAX_FILTERS]);
            }
            $groupMatches[] = self::match($group['match'] ?? self::MATCH_ALL, 'filterGroups.groups[' . $i . '].match');
            foreach ($conditions as $j => $filter) {
                $filters[] = self::condition($filter, 'filterGroups.groups[' . $i . '].filters[' . $j . ']', $i, $labelTargets, $metricIds);
            }
        }
        return [$filters, $match, $groupMatches];
    }

    private static function match($value, string $path): string
    {
        if (!in_array($value, [self::MATCH_ALL, self::MATCH_ANY], true)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorFilterMatchValue', [$path]);
        }
        return $value;
    }

    /**
     * One condition: a text operator on the (secondary) label, or a number operator on a selected metric.
     *
     * @param string $path where the condition is in the definition, for the error message
     * @return array{target: string, operator: string, value: string|float, group: int}
     */
    private static function condition($filter, string $path, int $group, array $labelTargets, array $metricIds): array
    {
        $target = is_array($filter) ? ($filter['target'] ?? null) : null;
        $operator = is_array($filter) ? ($filter['operator'] ?? null) : null;
        $value = is_array($filter) ? ($filter['value'] ?? null) : null;

        if (in_array($operator, self::TEXT_OPERATORS, true) && in_array($target, $labelTargets, true) && is_string($value)) {
            $value = trim($value);
            if ($operator === 'matches' && $value !== '' && (mb_strlen($value) > self::MAX_PATTERN || !self::isPattern($value))) {
                throw new ExplorationException('FeinwerkDataExploration_ErrorFilterPattern', [$path]);
            }
            if ($value !== '' && mb_strlen($value) <= self::MAX_FILTER_TEXT) {
                return ['target' => $target, 'operator' => $operator, 'value' => $value, 'group' => $group];
            }
        } elseif (in_array($operator, self::NUMBER_OPERATORS, true) && in_array($target, $metricIds, true) && is_numeric($value)) {
            return ['target' => $target, 'operator' => $operator, 'value' => (float) $value, 'group' => $group];
        }
        throw new ExplorationException('FeinwerkDataExploration_ErrorFilterCondition', [
            $path, implode(', ', self::TEXT_OPERATORS), implode(', ', self::NUMBER_OPERATORS),
        ]);
    }

    /**
     * The PCRE form of a "matches" value: case-insensitive, UTF-8, with a delimiter no pattern can contain (labels of
     * coded dimensions are matched with it in PHP, Engine\LabelFilterResolver).
     */
    public static function pcre(string $pattern): string
    {
        return "\x01" . $pattern . "\x01iu";
    }

    /** Whether the server can use a "matches" pattern (also for saved states, Saved/SavedStateDefinition). */
    public static function isPattern(string $pattern): bool
    {
        return strpos($pattern, "\x01") === false && @preg_match(self::pcre($pattern), '') !== false;
    }

    /**
     * "action" when any dimension or metric is about actions (base table log_link_visit_action), else "visit".
     */
    public function scope(DimensionCatalog $dimensions, MetricCatalog $metrics, int $idSite): string
    {
        foreach ($this->dimensionIds as $id) {
            if ($dimensions->get($idSite, $id)->scope === DimensionCatalog::SCOPE_ACTION) {
                return DimensionCatalog::SCOPE_ACTION;
            }
        }
        foreach ($this->metricIds as $id) {
            if ($metrics->get($id)->scope === MetricCatalog::SCOPE_ACTION) {
                return DimensionCatalog::SCOPE_ACTION;
            }
        }
        return DimensionCatalog::SCOPE_VISIT;
    }

    /**
     * The same exploration without its pivot: its rows and their totals (rows and pivot, 2026-11-24), cached as the
     * plain exploration's.
     */
    public function withoutPivot(): self
    {
        return new self($this->dimensionIds, $this->metricIds, $this->idGoal, $this->sortColumn, $this->sortOrder, $this->limit,
            $this->filters, $this->match, $this->groupMatches);
    }

    /**
     * The definition with a fixed key order, e.g. for cache keys and URLs.
     */
    public function normalized(): array
    {
        return [
            'dimensions' => $this->dimensionIds,
            'metrics' => $this->metricIds,
            'idGoal' => $this->idGoal,
            'sort' => $this->sortColumn,
            'sortOrder' => $this->sortOrder,
            'limit' => $this->limit,
            'filterGroups' => $this->filterGroupsArray(),
        ] + ($this->pivot === null ? [] : ['pivot' => $this->pivot]);
    }

    /**
     * The filters in the API's shape (filterGroups), null without filters.
     */
    public function filterGroupsArray(): ?array
    {
        if ($this->filters === []) {
            return null;
        }
        $groups = [];
        foreach ($this->groupMatches as $i => $groupMatch) {
            $groups[$i] = ['match' => $groupMatch, 'filters' => []];
        }
        foreach ($this->filters as $filter) {
            $groups[$filter['group']]['filters'][] = [
                'target' => $filter['target'], 'operator' => $filter['operator'], 'value' => $filter['value'],
            ];
        }
        return ['match' => $this->match, 'groups' => array_values($groups)];
    }

    /**
     * @return string[]|null null when the value is not a list of strings
     */
    private static function stringList($value): ?array
    {
        if (!is_array($value) || !array_is_list($value)) {
            return null;
        }
        foreach ($value as $item) {
            if (!is_string($item)) {
                return null;
            }
        }
        return $value;
    }
}
