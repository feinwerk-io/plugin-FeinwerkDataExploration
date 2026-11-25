<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;

/**
 * An exploration with a pivot dimension (rows and pivot, 2026-11-24): the rows as the exploration without the pivot
 * shows them (sort, row limit, filters), their metric columns the row's Total; per pivot value n the cell
 * {metric}__p{n}; the rest of the pivot values {metric}__p_others where it is exact; a total row first.
 *
 * Queries (all within the exploration's guards, cached): the rows and their totals (shared with the plain
 * exploration), the pivot's top values with their totals, the cells of the shown rows, and when values were cut and a
 * metric adds up across them, the rest per row and in total.
 */
final class PivotExplorer
{
    public function __construct(
        private readonly DimensionCatalog $dimensions,
        private readonly MetricCatalog $metrics,
        private readonly Limits $limits
    ) {
    }

    /**
     * The numbers of a pivoted exploration (explore() and the levels of its Matomo report): the row dimensions, the
     * pivot, the scope of its queries, the definition without the pivot; the rows (raw, as Explorer::fetchRows()) and
     * their totals; the pivot values shown (raw rows with every metric, the key in fwde_k{n+1}) and their raw keys;
     * the cells by row index and value index; the rest by row index and in total; whether there is a rest column and
     * for which metrics.
     *
     * @return array{dimensions: list<DimensionDefinition>, pivot: DimensionDefinition, pivotScope: string,
     *     plain: ExplorationDefinition, rows: list<array>, totals: array, values: list<array>, pivotKeys: list<?string>,
     *     cells: array<int, array<int, array>>, others: array<int, array>, othersTotal: array, withOthers: bool,
     *     othersMetrics: list<string>}
     */
    public function compute(int $idSite, string $period, string $date, ExplorationDefinition $definition, string $segment): array
    {
        $window = ExplorationWindow::for($idSite, $period, $date, $this->limits);
        $dimensions = array_map(fn (string $id) => $this->dimensions->get($idSite, $id), $definition->dimensionIds);
        $pivot = $this->dimensions->get($idSite, (string) $definition->pivot['dimension']);
        $weekday = $pivot->id === ExplorationDefinition::PIVOT_WEEKDAY;
        $plain = $definition->withoutPivot();
        $count = count($dimensions);
        $pivotKey = 'fwde_k' . ($count + 1);
        $explorer = new Explorer($this->dimensions, $this->metrics, $this->limits);
        $pivotScope = $explorer->pivotScope($definition, $pivot, $idSite);
        $othersMetrics = $weekday ? [] : array_values(array_filter(
            $definition->metricIds,
            fn (string $id): bool => Additivity::addsUpAcross($pivot, $this->metrics->get($id), $pivotScope)
        ));

        $slot = new LazySlot($this->limits);
        try {
            $rows = $explorer->fetchRows($idSite, $window, $plain, $dimensions, $segment, $slot);
            $totals = $explorer->fetchRows($idSite, $window, $plain, $dimensions, $segment, $slot, null, true)[0] ?? [];
            $limit = (int) $definition->pivot['limit'];
            // one more than shown tells whether values were cut
            $found = $explorer->fetchPivot('values', $idSite, $window, $definition, $dimensions, $pivot, $segment, $slot, [], [], $weekday ? null : $limit + 1);
            $cut = !$weekday && count($found) > $limit;
            $values = $weekday ? self::weekdays($found, $pivotKey) : array_slice($found, 0, $limit);
            $pivotKeys = array_map(static fn (array $value): ?string => $value[$pivotKey] === null ? null : (string) $value[$pivotKey], $values);
            $rowKeys = array_map(static fn (array $row): array => Explorer::rowKeys($row, $count), $rows);

            $cells = [];
            if ($rows !== [] && $pivotKeys !== []) {
                foreach ($explorer->fetchPivot('cells', $idSite, $window, $definition, $dimensions, $pivot, $segment, $slot, $rowKeys, $pivotKeys) as $cell) {
                    if ($cell['fwde_p'] !== null) {
                        $cells[(int) $cell['fwde_row']][(int) $cell['fwde_p']] = $cell;
                    }
                }
            }
            $others = [];
            $othersTotal = [];
            $withOthers = $cut && $othersMetrics !== [];
            if ($withOthers) {
                if ($rows !== []) {
                    foreach ($explorer->fetchPivot('others', $idSite, $window, $definition, $dimensions, $pivot, $segment, $slot, $rowKeys, $pivotKeys) as $rest) {
                        $others[(int) $rest['fwde_row']] = $rest;
                    }
                }
                $othersTotal = $explorer->fetchPivot('othersTotal', $idSite, $window, $definition, $dimensions, $pivot, $segment, $slot, [], $pivotKeys)[0] ?? [];
            }
        } finally {
            $slot->release();
        }

        return [
            'dimensions' => $dimensions, 'pivot' => $pivot, 'pivotScope' => $pivotScope, 'plain' => $plain, 'rows' => $rows,
            'totals' => $totals, 'values' => $values, 'pivotKeys' => $pivotKeys, 'cells' => $cells, 'others' => $others,
            'othersTotal' => $othersTotal, 'withOthers' => $withOthers, 'othersMetrics' => $othersMetrics,
        ];
    }

    /**
     * Columns of every row: the label columns; per pivot value n (in the pivot's order) and metric {metric}__p{n};
     * {metric}__p_others for the metrics whose rest is exact, when values were cut; {metric} (Total). A cell without
     * visits is 0 for counts and sums, null for rates and averages.
     *
     * The first row is the total row (label "Total", the other label columns empty): the pivot values' totals, the
     * rest and the Total over what the text filters let through; metadata fwde_total and fwde_pivot = {dimension,
     * values: [{label, value, segment}], others: bool}, both left out with $readableColumns, which names the columns
     * "<value>: <metric>", "Others: <metric>" and "Total: <metric>" in the user's language.
     *
     * Weekday shows its seven days in weekday order and no rest.
     */
    public function explore(int $idSite, string $period, string $date, ExplorationDefinition $definition, string $segment, bool $readableColumns): DataTable
    {
        [
            'dimensions' => $dimensions, 'pivot' => $pivot, 'pivotScope' => $pivotScope, 'plain' => $plain, 'rows' => $rows,
            'totals' => $totals, 'values' => $values, 'pivotKeys' => $pivotKeys, 'cells' => $cells, 'others' => $others,
            'othersTotal' => $othersTotal, 'withOthers' => $withOthers, 'othersMetrics' => $othersMetrics,
        ] = $this->compute($idSite, $period, $date, $definition, $segment);
        $count = count($dimensions);
        $explorer = new Explorer($this->dimensions, $this->metrics, $this->limits);
        $labelColumns = ExplorationDefinition::labelColumns($count);
        $table = new DataTable();
        $totalLabels = ['label' => Piwik::translate('General_Total')];
        foreach (array_slice($labelColumns, 1) as $labelColumn) {
            $totalLabels[$labelColumn] = '';
        }
        $table->addRow(new DataTable\Row([
            DataTable\Row::COLUMNS => $this->columns($totalLabels, $definition, $values, $withOthers ? $othersTotal : null, $othersMetrics, $totals),
            DataTable\Row::METADATA => $readableColumns ? [] : [
                'fwde_total' => true,
                'fwde_pivot' => [
                    'dimension' => $pivot->id,
                    'values' => array_map(static fn (?string $key): array => [
                        'label' => (string) $pivot->formatLabel($key, $idSite),
                        'value' => $key,
                        'segment' => $pivot->segmentFor($key),
                    ], $pivotKeys),
                    'others' => $withOthers,
                ],
            ],
        ]));

        $own = $explorer->toDataTable($rows, $plain, $dimensions, $idSite, !$readableColumns);
        foreach ($own->getRows() as $index => $row) {
            $values = $row->getColumns();
            $labels = array_intersect_key($values, array_flip($labelColumns));
            $rowCells = [];
            foreach (array_keys($pivotKeys) as $n) {
                $rowCells[$n] = $cells[$index][$n] ?? [];
            }
            $row->setColumns($this->columns($labels, $definition, $rowCells, $withOthers ? ($others[$index] ?? []) : null, $othersMetrics, $values));
            $table->addRow($row);
        }
        $table->setMetadata(DataTable::GENERIC_FILTERS_TO_DISABLE_METADATA_NAME, ['Sort', 'Limit', 'Truncate']);

        if ($readableColumns) {
            $this->useReadableNames($table, $definition, $dimensions, $pivot, $pivotKeys, $pivotScope, $idSite);
        }
        return $table;
    }

    /**
     * One row's columns in the table's order: labels, the cells per pivot value, the rest, the Total.
     *
     * @param list<array> $cells raw values per pivot value
     * @param array|null $rest raw values of the rest, null without a rest column
     * @param array $total the Total (raw or already written)
     */
    private function columns(array $labels, ExplorationDefinition $definition, array $cells, ?array $rest, array $othersMetrics, array $total): array
    {
        $columns = $labels;
        foreach ($cells as $n => $cell) {
            foreach ($definition->metricIds as $metricId) {
                $columns[$metricId . '__p' . ($n + 1)] = Explorer::metricValue($metricId, $cell[$metricId] ?? null);
            }
        }
        if ($rest !== null) {
            foreach ($othersMetrics as $metricId) {
                $columns[$metricId . '__p_others'] = Explorer::metricValue($metricId, $rest[$metricId] ?? null);
            }
        }
        foreach ($definition->metricIds as $metricId) {
            $columns[$metricId] = Explorer::metricValue($metricId, $total[$metricId] ?? null);
        }
        return $columns;
    }

    /** The seven days in weekday order (keys 1 to 7, Monday first), each with its values or none. */
    private static function weekdays(array $found, string $pivotKey): array
    {
        $byDay = [];
        foreach ($found as $value) {
            $byDay[(string) $value[$pivotKey]] = $value;
        }
        $days = [];
        foreach (range(1, 7) as $day) {
            $days[] = ($byDay[(string) $day] ?? []) + [$pivotKey => (string) $day];
        }
        return $days;
    }

    /** "<value>: <metric>", "Others: <metric>", "Total: <metric>"; a value label used twice gets its position. */
    private function useReadableNames(DataTable $table, ExplorationDefinition $definition, array $dimensions, DimensionDefinition $pivot, array $pivotKeys, string $pivotScope, int $idSite): void
    {
        $explorer = new Explorer($this->dimensions, $this->metrics, $this->limits);
        $scope = $definition->scope($this->dimensions, $this->metrics, $idSite);
        $names = Explorer::labelNames($dimensions);
        $used = [];
        foreach ($pivotKeys as $n => $key) {
            $label = (string) $pivot->formatLabel($key, $idSite);
            $label = isset($used[$label]) ? $label . ' (' . ($n + 1) . ')' : $label;
            $used[$label] = true;
            foreach ($definition->metricIds as $metricId) {
                $names[$metricId . '__p' . ($n + 1)] = Piwik::translate('FeinwerkDataExploration_ColumnPivot', [$label, $explorer->metricName($metricId, $pivotScope, $idSite)]);
            }
        }
        foreach ($definition->metricIds as $metricId) {
            $names[$metricId . '__p_others'] = Piwik::translate('FeinwerkDataExploration_ColumnPivot', [
                Piwik::translate('General_Others'), $explorer->metricName($metricId, $pivotScope, $idSite),
            ]);
            $names[$metricId] = Piwik::translate('FeinwerkDataExploration_ColumnPivot', [
                Piwik::translate('General_Total'), $explorer->metricName($metricId, $scope, $idSite),
            ]);
        }
        foreach ($table->getRows() as $row) {
            $renamed = [];
            foreach ($row->getColumns() as $column => $value) {
                $renamed[$names[$column] ?? $column] = $value;
            }
            $row->setColumns($renamed);
        }
    }
}
