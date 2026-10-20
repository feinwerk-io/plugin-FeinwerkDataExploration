<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Plugin\Manager;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Query\ExplorationQueryBuilder;
use Piwik\Plugins\FeinwerkDataExploration\Query\GroupingQuirk;
use Piwik\Plugins\FeinwerkDataExploration\Query\QueryContext;
use Piwik\Plugins\FeinwerkDataExploration\Query\QueryRunner;
use Piwik\Segment;

/**
 * Runs one exploration for one site and period and returns it as a flat DataTable: label, secondary_label (two
 * dimensions only) and the requested metrics in the requested order.
 */
final class Explorer
{
    /** Metrics that are counts or whole seconds; all others are rates or amounts. */
    private const INTEGER_METRICS = [
        'nb_visits', 'nb_uniq_visitors', 'nb_actions', 'avg_time_on_site', 'nb_conversions',
        'nb_pageviews', 'nb_uniq_pageviews', 'nb_events', 'nb_downloads', 'nb_outlinks',
    ];

    public function __construct(
        private readonly DimensionCatalog $dimensions,
        private readonly MetricCatalog $metrics,
        private readonly Limits $limits
    ) {
    }

    /**
     * @param bool $readableColumns name the columns by the translated dimension and metric names (exports)
     */
    public function explore(int $idSite, string $period, string $date, ExplorationDefinition $definition, string $segment, bool $readableColumns = false): DataTable
    {
        $window = ExplorationWindow::for($idSite, $period, $date, $this->limits);
        $context = new QueryContext($definition->scope($this->dimensions, $this->metrics, $idSite), $window->timezone, $window->startUtc, $window->endUtc);
        $dimensions = array_map(fn (string $id) => $this->dimensions->get($idSite, $id), $definition->dimensionIds);
        $segmentObject = new Segment($segment, [$idSite], $window->start, $window->end);

        $cache = new ResultCache();
        $cacheKey = $cache->key($idSite, $context->windowStartUtc, $context->windowEndUtc, $segment, $definition, self::pluginVersion());
        $rows = $cache->fetch($cacheKey);

        if ($rows === null) {
            $runner = new QueryRunner($this->limits);
            $slot = (new ConcurrencyGate())->acquire($this->limits);
            try {
                // the visit count of a window is reused while its results are (the user changes one thing at a time)
                (new CostGuard($runner))->check($idSite, $context->windowStartUtc, $context->windowEndUtc, $this->limits, $window->cacheTtl());

                $builder = new ExplorationQueryBuilder(
                    static fn (string $select, array $from, string $where, array $bind, ?string $groupBy): array =>
                        $segmentObject->getSelectQuery($select, $from, $where, $bind, false, $groupBy ?? false),
                    static fn (string $table): string => Common::prefixTable($table),
                    $this->metrics
                );
                $resolvedFilters = $this->resolveLabelFilters($definition, $dimensions, $idSite, $context, $runner, $window->cacheTtl());
                $rows = $runner->fetchAll($builder->build(
                    $definition,
                    $dimensions,
                    $idSite,
                    $context,
                    $resolvedFilters,
                    GroupingQuirk::applies($runner->serverVersion())
                ));
            } finally {
                $slot->unlock();
            }

            $cache->save($cacheKey, $rows, $window->cacheTtl());
        }

        $table = $this->toDataTable($rows, $definition, $dimensions, $idSite, !$readableColumns);
        if ($readableColumns) {
            $this->useReadableColumnNames($table, $definition, $dimensions, $context->scope);
        }
        return $table;
    }

    /**
     * Renames the columns to what the user sees: dimension names and metric names (visit metrics next to an action
     * dimension by their "with this action" name).
     */
    private function useReadableColumnNames(DataTable $table, ExplorationDefinition $definition, array $dimensions, string $scope): void
    {
        $names = ['label' => $dimensions[0]->name];
        if (isset($dimensions[1])) {
            $names['secondary_label'] = $dimensions[1]->name;
        }
        foreach ($definition->metricIds as $metricId) {
            $metric = $this->metrics->get($metricId);
            $key = $scope === QueryContext::SCOPE_ACTION && $metric->nameInActionScopeKey !== null ? $metric->nameInActionScopeKey : $metric->nameKey;
            $names[$metricId] = Piwik::translate($key);
        }

        foreach ($table->getRows() as $row) {
            $columns = [];
            foreach ($row->getColumns() as $column => $value) {
                $columns[$names[$column] ?? $column] = $value;
            }
            $row->setColumns($columns);
        }
    }

    /**
     * Text filters on coded dimensions (browser, country, ...) match the label users see, so they are turned into the
     * stored values first. Coded dimensions are all visit dimensions; their values are read from log_visit.
     *
     * @return array<int, array{values: list<string>, includeNull: bool}>
     */
    private function resolveLabelFilters(ExplorationDefinition $definition, array $dimensions, int $idSite, QueryContext $context, QueryRunner $runner, int $ttl): array
    {
        $visitContext = new QueryContext(QueryContext::SCOPE_VISIT, $context->timezone, $context->windowStartUtc, $context->windowEndUtc);
        $remember = new Remember();
        $resolver = new LabelFilterResolver(function ($dimension) use ($visitContext, $idSite, $runner, $remember, $ttl): array {
            // the stored values of a dimension in a window are reused, e.g. while the filter text is typed
            $key = 'fwde_values_' . sha1(json_encode([self::pluginVersion(), $idSite, $visitContext->windowStartUtc, $visitContext->windowEndUtc, $dimension->id]));
            return $remember->get($key, $ttl, function () use ($dimension, $visitContext, $idSite, $runner): array {
                $sql = 'SELECT DISTINCT ' . $dimension->keySql($visitContext, 'fwde_k1') . ' AS k FROM ' . Common::prefixTable('log_visit')
                    . ' AS log_visit WHERE log_visit.idsite = ? AND log_visit.visit_last_action_time >= ?'
                    . ' AND log_visit.visit_last_action_time <= ?';
                $rows = $runner->fetchAll(['sql' => $sql, 'bind' => [$idSite, $visitContext->windowStartUtc, $visitContext->windowEndUtc]]);
                return array_map(static fn (array $row) => $row['k'] === null ? null : (string) $row['k'], $rows);
            });
        });

        $resolved = [];
        foreach ($definition->filters as $i => $filter) {
            if (!in_array($filter['operator'], ExplorationDefinition::TEXT_OPERATORS, true)) {
                continue;
            }
            $dimension = $dimensions[$filter['target'] === ExplorationDefinition::SORT_LABEL ? 0 : 1];
            if ($dimension->filtersByLabel()) {
                $resolved[$i] = $resolver->resolve($dimension, $filter['operator'], (string) $filter['value'], $idSite);
            }
        }
        return $resolved;
    }

    /**
     * @param bool $withSegments add each row's Matomo segment as row metadata (not in exports)
     */
    private function toDataTable(array $rows, ExplorationDefinition $definition, array $dimensions, int $idSite, bool $withSegments): DataTable
    {
        $table = new DataTable();
        foreach ($rows as $row) {
            $columns = ['label' => $dimensions[0]->formatLabel(self::raw($row['fwde_k1']), $idSite)];
            if (isset($dimensions[1])) {
                $columns['secondary_label'] = $dimensions[1]->formatLabel(self::raw($row['fwde_k2']), $idSite);
            }
            foreach ($definition->metricIds as $metricId) {
                $columns[$metricId] = in_array($metricId, self::INTEGER_METRICS, true) ? (int) $row[$metricId] : (float) $row[$metricId];
            }
            $tableRow = new DataTable\Row([DataTable\Row::COLUMNS => $columns]);
            if ($withSegments) {
                $segment = self::rowSegment($row, $dimensions);
                if ($segment !== null) {
                    $tableRow->setMetadata('segment', $segment);
                }
                if (self::hasNoValue($row, count($dimensions))) {
                    // "… not defined": a row filter cannot select it, the page disables "filter to this value"
                    $tableRow->setMetadata('fwde_no_value', true);
                }
                // "filter to this value" matches the stored value; the label Matomo shows is URL- and HTML-decoded
                foreach ($dimensions as $i => $dimension) {
                    $raw = self::raw($row['fwde_k' . ($i + 1)]);
                    if ($raw !== null && !$dimension->filtersByLabel()) {
                        $tableRow->setMetadata($i === 0 ? 'fwde_value' : 'fwde_value2', $raw);
                    }
                }
            }
            $table->addRow($tableRow);
        }

        // the exploration sorts and limits itself; Matomo's default filter_limit (100 over HTTP) must not cut it
        $table->setMetadata(DataTable::GENERIC_FILTERS_TO_DISABLE_METADATA_NAME, ['Sort', 'Limit', 'Truncate']);

        return $table;
    }

    /**
     * The visits behind a row: the segments of its dimensions joined by AND, or null when one of them has none.
     */
    private static function hasNoValue(array $row, int $dimensionCount): bool
    {
        for ($i = 1; $i <= $dimensionCount; $i++) {
            $raw = self::raw($row['fwde_k' . $i]);
            if ($raw === null || $raw === '') {
                return true;
            }
        }
        return false;
    }

    private static function rowSegment(array $row, array $dimensions): ?string
    {
        $parts = [];
        foreach ($dimensions as $i => $dimension) {
            $segment = $dimension->segmentFor(self::raw($row['fwde_k' . ($i + 1)]));
            if ($segment === null) {
                return null;
            }
            $parts[] = $segment;
        }
        return implode(';', $parts);
    }

    private static function pluginVersion(): string
    {
        $plugin = Manager::getInstance()->getLoadedPlugin('FeinwerkDataExploration');
        return $plugin === null ? '' : (string) $plugin->getVersion();
    }

    private static function raw($value): ?string
    {
        return $value === null ? null : (string) $value;
    }
}
