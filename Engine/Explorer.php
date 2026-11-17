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
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\Comparison;
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
        'nb_pageviews', 'nb_uniq_pageviews', 'nb_events', 'nb_downloads', 'nb_outlinks', 'nb_searches', 'max_actions',
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
    public function explore(int $idSite, string $period, string $date, ExplorationDefinition $definition, string $segment, bool $readableColumns = false, array $comparisons = []): DataTable
    {
        $window = ExplorationWindow::for($idSite, $period, $date, $this->limits);
        $dimensions = array_map(fn (string $id) => $this->dimensions->get($idSite, $id), $definition->dimensionIds);
        $slot = new LazySlot($this->limits);
        try {
            $rows = $this->fetchRows($idSite, $window, $definition, $dimensions, $segment, $slot);
            if ($comparisons !== []) {
                return $this->compare($idSite, $period, $date, $definition, $dimensions, $segment, $window, $rows, $comparisons, $readableColumns, $slot);
            }
        } finally {
            $slot->release();
        }

        $table = $this->toDataTable($rows, $definition, $dimensions, $idSite, !$readableColumns);
        if ($readableColumns) {
            $this->useReadableColumnNames($table, $definition, $dimensions, $definition->scope($this->dimensions, $this->metrics, $idSite));
        }
        return $table;
    }

    /**
     * The raw rows of a definition in a window and segment, cached: the own rows, the rows of a comparison column
     * (restricted to $onlyKeys) or the totals (one row). With $time (the "Date" or "Hour" dimension) the same per time
     * unit (trend): $onlyKeys by fwde_row and fwde_t, or ($onlyKeys null) the totals by fwde_t. The slot is taken only
     * when a query has to run.
     *
     * @param list<array{0: ?string, 1: ?string}>|null $onlyKeys
     */
    public function fetchRows(int $idSite, ExplorationWindow $window, ExplorationDefinition $definition, array $dimensions, string $segment, LazySlot $slot, ?array $onlyKeys = null, bool $totals = false, ?DimensionDefinition $time = null): array
    {
        $context = new QueryContext($definition->scope($this->dimensions, $this->metrics, $idSite), $window->timezone, $window->startUtc, $window->endUtc);
        $segmentObject = new Segment($segment, [$idSite], $window->start, $window->end);

        $cache = new ResultCache();
        $version = self::pluginVersion();
        if ($time !== null) {
            $cacheKey = $cache->trendKey($idSite, $context->windowStartUtc, $context->windowEndUtc, $segment, $definition, $onlyKeys, $time->id, $version);
        } elseif ($totals) {
            $cacheKey = $cache->totalsKey($idSite, $context->windowStartUtc, $context->windowEndUtc, $segment, $definition, $version);
        } elseif ($onlyKeys !== null) {
            $cacheKey = $cache->restrictedKey($idSite, $context->windowStartUtc, $context->windowEndUtc, $segment, $definition, $onlyKeys, $version);
        } else {
            $cacheKey = $cache->key($idSite, $context->windowStartUtc, $context->windowEndUtc, $segment, $definition, $version);
        }
        $rows = $cache->fetch($cacheKey);
        if ($rows !== null) {
            return $rows;
        }

        $slot->acquire();
        $runner = new QueryRunner($this->limits);
        // the visit count of a window is reused while its results are (the user changes one thing at a time)
        (new CostGuard($runner))->check($idSite, $context->windowStartUtc, $context->windowEndUtc, $this->limits, $window->cacheTtl());
        $builder = new ExplorationQueryBuilder(
            static fn (string $select, array $from, string $where, array $bind, ?string $groupBy): array =>
                $segmentObject->getSelectQuery($select, $from, $where, $bind, false, $groupBy ?? false),
            static fn (string $table): string => Common::prefixTable($table),
            $this->metrics
        );
        $resolvedFilters = $this->resolveLabelFilters($definition, $dimensions, $idSite, $context, $runner, $window->cacheTtl());
        if ($time !== null) {
            $query = $builder->buildTrend($definition, $dimensions, $idSite, $context, $resolvedFilters, $time, $onlyKeys);
        } else {
            $query = $totals
                ? $builder->buildTotals($definition, $dimensions, $idSite, $context, $resolvedFilters)
                : $builder->build($definition, $dimensions, $idSite, $context, $resolvedFilters, $onlyKeys === null && GroupingQuirk::applies($runner->serverVersion()), $onlyKeys);
        }
        $rows = $runner->fetchAll($query);
        $cache->save($cacheKey, $rows, $window->cacheTtl());
        return $rows;
    }

    /**
     * The own rows with comparison columns (comparison design 3 and 5): a total row first, then the own rows with value
     * and share per metric and, per comparison n, the value ({m}__c{n}) and its share (segment columns) or the change
     * of the page's period against it (period columns). A column that cannot be computed refuses the whole request,
     * naming the column.
     *
     * @param list<Comparison> $comparisons
     */
    private function compare(int $idSite, string $period, string $date, ExplorationDefinition $definition, array $dimensions, string $segment, ExplorationWindow $window, array $rows, array $comparisons, bool $readableColumns, LazySlot $slot): DataTable
    {
        $twoDimensions = count($dimensions) === 2;
        $dateIndex = array_search('date', $definition->dimensionIds, true);
        $ownKeys = array_map(static fn (array $row): array => self::rowKeys($row, $twoDimensions), $rows);
        $ownTotals = $this->fetchRows($idSite, $window, $definition, $dimensions, $segment, $slot, null, true)[0] ?? [];

        $columns = [];
        foreach ($comparisons as $comparison) {
            // the kind as far as the request says it, for the column's name when it cannot be computed
            $kind = ComparisonTable::kind($comparison, $segment, $period, $date);
            try {
                $columnWindow = ExplorationWindow::for($idSite, $comparison->period, $comparison->date, $this->limits);
                // the resolved periods decide: "last7" and its two dates are the same period
                $kind = ComparisonTable::kindOf($comparison, $segment, $columnWindow->startUtc === $window->startUtc && $columnWindow->endUtc === $window->endUtc);
                $outside = [];
                if ($kind === ComparisonTable::KIND_SEGMENT && $comparison->segment === trim($segment)) {
                    // the own view as a column (the page asks it first): its rows, no query
                    $values = $rows;
                    $totals = $ownTotals;
                } else {
                    $keys = $ownKeys;
                    if ($kind !== ComparisonTable::KIND_SEGMENT && $dateIndex !== false) {
                        // "Date" in a period comparison: day against day by position (Peter, 2026-11-14)
                        $shift = ComparisonTable::daysBetween($window->firstDay, $columnWindow->firstDay);
                        foreach ($keys as $index => $rowKeys) {
                            $keys[$index] = self::shiftKeys($rowKeys, (int) $dateIndex, $shift);
                            $day = $keys[$index][(int) $dateIndex];
                            if ($day !== null && $day > $columnWindow->lastDay) {
                                // the compared period has no such day: empty, not 0
                                $outside[$index] = true;
                            }
                        }
                    }
                    $values = self::byRow($this->fetchRows($idSite, $columnWindow, $definition, $dimensions, $comparison->segment, $slot, $keys));
                    $totals = $this->fetchRows($idSite, $columnWindow, $definition, $dimensions, $comparison->segment, $slot, null, true)[0] ?? [];
                }
                // a column with another segment and period changes against that segment in the page's period
                $reference = $kind === ComparisonTable::KIND_BOTH
                    ? self::byRow($this->fetchRows($idSite, $window, $definition, $dimensions, $comparison->segment, $slot, $ownKeys))
                    : null;
                $referenceTotals = $kind === ComparisonTable::KIND_BOTH
                    ? ($this->fetchRows($idSite, $window, $definition, $dimensions, $comparison->segment, $slot, null, true)[0] ?? [])
                    : $ownTotals;
            } catch (\Throwable $e) {
                $title = ComparisonTitles::column($idSite, $kind, $comparison->segment, $comparison->period, $comparison->date);
                $code = $e instanceof ExplorationException && $e->getCode() > 0 ? $e->getCode() : 400;
                throw new ExplorationException('FeinwerkDataExploration_ErrorComparisonColumn', [$title, $e->getMessage()], $code);
            }
            $columns[] = [
                'kind' => $kind, 'values' => $values, 'totals' => $totals, 'reference' => $reference,
                'referenceTotals' => $referenceTotals, 'outside' => $outside, 'comparison' => $comparison,
            ];
        }

        $own = $this->toDataTable($rows, $definition, $dimensions, $idSite, !$readableColumns);
        $table = new DataTable();
        $total = ['label' => Piwik::translate('General_Total')];
        if ($twoDimensions) {
            $total['secondary_label'] = '';
        }
        // the total row has every column of the rows (exports take their column order from the first row)
        foreach ($definition->metricIds as $metricId) {
            $total[$metricId] = self::metricValue($metricId, $ownTotals[$metricId] ?? null);
            if (in_array($metricId, ComparisonTable::SHARE_METRICS, true)) {
                $total[$metricId . '__share'] = null;
            }
        }
        foreach ($columns as $i => $column) {
            $suffix = '__c' . ($i + 1);
            foreach ($definition->metricIds as $metricId) {
                $value = self::metricValue($metricId, $column['totals'][$metricId] ?? null);
                $total[$metricId . $suffix] = $value;
                if ($column['kind'] === ComparisonTable::KIND_SEGMENT) {
                    if (in_array($metricId, ComparisonTable::SHARE_METRICS, true)) {
                        $total[$metricId . $suffix . '__share'] = null;
                    }
                } else {
                    $current = self::metricValue($metricId, $column['referenceTotals'][$metricId] ?? null);
                    $total[$metricId . $suffix . '__change'] = ComparisonTable::change($current, $value);
                }
            }
        }
        // the page knows the total row by its marker; exports by its label (no metadata column there)
        $table->addRow(new DataTable\Row([
            DataTable\Row::COLUMNS => $total,
            DataTable\Row::METADATA => $readableColumns ? [] : ['fwde_total' => true],
        ]));

        foreach ($own->getRows() as $index => $row) {
            $values = $row->getColumns();
            $out = ['label' => $values['label']];
            if ($twoDimensions) {
                $out['secondary_label'] = $values['secondary_label'];
            }
            foreach ($definition->metricIds as $metricId) {
                $out[$metricId] = $values[$metricId];
                if (in_array($metricId, ComparisonTable::SHARE_METRICS, true)) {
                    $out[$metricId . '__share'] = ComparisonTable::share($values[$metricId], $total[$metricId] ?? null);
                }
            }
            foreach ($columns as $i => $column) {
                $suffix = '__c' . ($i + 1);
                $outside = isset($column['outside'][$index]);
                foreach ($definition->metricIds as $metricId) {
                    $value = $outside ? null : self::metricValue($metricId, $column['values'][$index][$metricId] ?? null);
                    $out[$metricId . $suffix] = $value;
                    if ($column['kind'] === ComparisonTable::KIND_SEGMENT) {
                        if (in_array($metricId, ComparisonTable::SHARE_METRICS, true)) {
                            $out[$metricId . $suffix . '__share'] = ComparisonTable::share($value, $total[$metricId . $suffix]);
                        }
                    } else {
                        $current = $column['reference'] === null
                            ? $values[$metricId]
                            : self::metricValue($metricId, $column['reference'][$index][$metricId] ?? null);
                        $out[$metricId . $suffix . '__change'] = ComparisonTable::change($current, $value);
                    }
                }
            }
            $row->setColumns($out);
            if (!$readableColumns) {
                // the page merges its column requests by this key (labels are not unique)
                $row->setMetadata('fwde_key', json_encode($ownKeys[$index]));
            }
            $table->addRow($row);
        }
        $table->setMetadata(DataTable::GENERIC_FILTERS_TO_DISABLE_METADATA_NAME, ['Sort', 'Limit', 'Truncate']);

        if ($readableColumns) {
            $this->useReadableComparisonNames($table, $definition, $dimensions, $idSite, $segment, $period, $date, $columns);
        }
        return $table;
    }

    /** Column names in the user's language for exports: "Visits (All visits)", "Visits, share (Mobile)", … */
    private function useReadableComparisonNames(DataTable $table, ExplorationDefinition $definition, array $dimensions, int $idSite, string $segment, string $period, string $date, array $columns): void
    {
        $scope = $definition->scope($this->dimensions, $this->metrics, $idSite);
        $comparesPeriods = false;
        foreach ($columns as $column) {
            $comparesPeriods = $comparesPeriods || $column['kind'] !== ComparisonTable::KIND_SEGMENT;
        }
        $ownTitle = ComparisonTitles::segment($idSite, $segment)
            . ($comparesPeriods ? ', ' . ComparisonTitles::period($idSite, $period, $date) : '');

        $names = ['label' => $dimensions[0]->name];
        if (isset($dimensions[1])) {
            $names['secondary_label'] = $dimensions[1]->name;
        }
        foreach ($definition->metricIds as $metricId) {
            $metric = $this->metricName($metricId, $scope);
            $names[$metricId] = Piwik::translate('FeinwerkDataExploration_ColumnFor', [$metric, $ownTitle]);
            $names[$metricId . '__share'] = Piwik::translate('FeinwerkDataExploration_ColumnShare', [$metric, $ownTitle]);
            foreach ($columns as $i => $column) {
                $c = $column['comparison'];
                $title = ComparisonTitles::column($idSite, $column['kind'], $c->segment, $c->period, $c->date);
                $suffix = $metricId . '__c' . ($i + 1);
                $names[$suffix] = Piwik::translate('FeinwerkDataExploration_ColumnFor', [$metric, $title]);
                $names[$suffix . '__share'] = Piwik::translate('FeinwerkDataExploration_ColumnShare', [$metric, $title]);
                $names[$suffix . '__change'] = Piwik::translate('FeinwerkDataExploration_ColumnChange', [$metric, $title]);
            }
        }
        foreach ($table->getRows() as $row) {
            $renamed = [];
            foreach ($row->getColumns() as $column => $value) {
                $renamed[$names[$column] ?? $column] = $value;
            }
            $row->setColumns($renamed);
        }
    }

    private function metricName(string $metricId, string $scope): string
    {
        $metric = $this->metrics->get($metricId);
        $key = $scope === QueryContext::SCOPE_ACTION && $metric->nameInActionScopeKey !== null ? $metric->nameInActionScopeKey : $metric->nameKey;
        return Piwik::translate($key);
    }

    /** @return array{0: ?string, 1: ?string} the raw keys of a row as fetchRows() returns it */
    public static function rowKeys(array $row, bool $twoDimensions): array
    {
        return [self::raw($row['fwde_k1'] ?? null), $twoDimensions ? self::raw($row['fwde_k2'] ?? null) : null];
    }

    private static function shiftKeys(array $keys, int $index, int $days): array
    {
        if ($keys[$index] !== null) {
            $keys[$index] = ComparisonTable::shiftDate($keys[$index], $days);
        }
        return $keys;
    }

    /**
     * The rows of a restricted query by the own row they belong to (fwde_row).
     *
     * @return array<int, array>
     */
    private static function byRow(array $rows): array
    {
        $byRow = [];
        foreach ($rows as $row) {
            $byRow[(int) $row['fwde_row']] = $row;
        }
        return $byRow;
    }

    /** A metric as the table writes it; a row missing in a column counts 0 for counts and sums, nothing otherwise. */
    public static function metricValue(string $metricId, $value)
    {
        if ($value === null) {
            return in_array($metricId, ComparisonTable::SHARE_METRICS, true) ? (in_array($metricId, self::INTEGER_METRICS, true) ? 0 : 0.0) : null;
        }
        return in_array($metricId, self::INTEGER_METRICS, true) ? (int) $value : (float) $value;
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

    /** The plugin's version: part of every cache key, so an update never serves results of the old code. */
    public static function pluginVersion(): string
    {
        $plugin = Manager::getInstance()->getLoadedPlugin('FeinwerkDataExploration');
        return $plugin === null ? '' : (string) $plugin->getVersion();
    }

    private static function raw($value): ?string
    {
        return $value === null ? null : (string) $value;
    }
}
