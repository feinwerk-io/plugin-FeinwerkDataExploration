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
 * What to explore: 1 to 2 dimensions, 1 to 5 metrics, an optional goal, sorting and a row limit. Only built through
 * fromJson(), which validates everything against the positive lists.
 */
final class ExplorationDefinition
{
    public const MAX_DIMENSIONS = 2;
    public const MAX_METRICS = 5;
    public const SORT_LABEL = 'label';
    public const SORT_SECONDARY_LABEL = 'secondary_label';
    public const MAX_FILTERS = 5;
    // long enough for a stored URL ("filter to this value" in the row menu)
    public const MAX_FILTER_TEXT = 1000;
    public const TEXT_OPERATORS = ['contains', 'equals', 'notContains'];
    public const NUMBER_OPERATORS = ['gt', 'lt'];

    /**
     * @param string[] $dimensionIds
     * @param string[] $metricIds
     * @param array<int, array{target: string, operator: string, value: string|float}> $filters
     */
    private function __construct(
        public readonly array $dimensionIds,
        public readonly array $metricIds,
        public readonly ?int $idGoal,
        public readonly string $sortColumn,
        public readonly string $sortOrder,
        public readonly int $limit,
        public readonly array $filters = []
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
            if ($metrics->get($id) === null) {
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

        $sortColumns = $metricIds;
        $sortColumns[] = self::SORT_LABEL;
        if (count($dimensionIds) === 2) {
            $sortColumns[] = self::SORT_SECONDARY_LABEL;
        }
        $sortColumn = $data['sort'] ?? $metricIds[0];
        $sortOrder = $data['sortOrder'] ?? 'desc';
        if (!in_array($sortColumn, $sortColumns, true) || !in_array($sortOrder, ['asc', 'desc'], true)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorSort');
        }

        $limit = $data['limit'] ?? $limits->defaultRows;
        if (!is_int($limit) || $limit < 1 || $limit > $limits->maxRows) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorLimit', [$limits->maxRows]);
        }

        $filters = self::filters($data['filters'] ?? [], count($dimensionIds), $metricIds);

        return new self($dimensionIds, $metricIds, $idGoal, $sortColumn, $sortOrder, $limit, $filters);
    }

    /**
     * Row filters: text filters on the (secondary) label, number filters on selected metrics.
     */
    private static function filters($filters, int $dimensionCount, array $metricIds): array
    {
        if (!is_array($filters) || !array_is_list($filters) || count($filters) > self::MAX_FILTERS) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorFilterCount', [self::MAX_FILTERS]);
        }

        $labelTargets = $dimensionCount === 2 ? [self::SORT_LABEL, self::SORT_SECONDARY_LABEL] : [self::SORT_LABEL];
        $parsed = [];
        foreach ($filters as $i => $filter) {
            $target = is_array($filter) ? ($filter['target'] ?? null) : null;
            $operator = is_array($filter) ? ($filter['operator'] ?? null) : null;
            $value = is_array($filter) ? ($filter['value'] ?? null) : null;

            if (in_array($operator, self::TEXT_OPERATORS, true) && in_array($target, $labelTargets, true) && is_string($value)) {
                $value = trim($value);
                if ($value !== '' && mb_strlen($value) <= self::MAX_FILTER_TEXT) {
                    $parsed[] = ['target' => $target, 'operator' => $operator, 'value' => $value];
                    continue;
                }
            } elseif (in_array($operator, self::NUMBER_OPERATORS, true) && in_array($target, $metricIds, true) && is_numeric($value)) {
                $parsed[] = ['target' => $target, 'operator' => $operator, 'value' => (float) $value];
                continue;
            }
            throw new ExplorationException('FeinwerkDataExploration_ErrorFilter', [$i + 1]);
        }
        return $parsed;
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
            'filters' => $this->filters,
        ];
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
