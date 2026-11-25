<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;

/**
 * An exploration as the levels of a Matomo report (saved reports, 2026-11-24; up to four levels since rows and pivot):
 * one level per row dimension, then the pivot dimension (P2: the pivot as rows, not columns).
 *
 * - The deepest row level holds the explored combinations as the page shows them (sort, row limit, filters), with
 *   their metrics and row metadata (segment, stored values).
 * - Each upper level holds the prefixes of those combinations in their order, each with its totals over what the text
 *   filters let through (number filters, sorting and the row limit apply to the combinations only). The database
 *   decides which prefixes are one: "Shoes" and "shoes" of one collation group are one value even when two
 *   combinations carry different spellings (a spelling the database matched to an earlier one joins that one; among
 *   earlier spellings the one equal without case and accents is taken).
 * - With a pivot, every combination holds the exploration's top pivot values (the same for every row, as the page's
 *   columns, a value without data with 0 for counts) and an "Others" row when values were cut and every metric adds
 *   up across them (Matomo's reports show a missing number as 0, so a rest that is not exact gets no row).
 *
 * A node: label, columns (metric id => value), metadata (row metadata of a combination or a pivot value: segment),
 * children (the next level's nodes).
 */
final class ReportLevels
{
    public function __construct(
        private readonly DimensionCatalog $dimensions,
        private readonly MetricCatalog $metrics,
        private readonly Limits $limits
    ) {
    }

    /**
     * @return list<array{label: string, columns: array<string, int|float|null>, metadata: array, children: list<array>}>
     */
    public function tree(int $idSite, string $period, string $date, ExplorationDefinition $definition, string $segment): array
    {
        $explorer = new Explorer($this->dimensions, $this->metrics, $this->limits);
        $dimensions = array_map(fn (string $id) => $this->dimensions->get($idSite, $id), $definition->dimensionIds);
        $count = count($dimensions);
        $plain = $definition->withoutPivot();
        $pivot = $definition->pivot !== null ? (new PivotExplorer($this->dimensions, $this->metrics, $this->limits))
            ->compute($idSite, $period, $date, $definition, $segment) : null;

        $window = ExplorationWindow::for($idSite, $period, $date, $this->limits);
        $slot = new LazySlot($this->limits);
        try {
            $rows = $pivot !== null ? $pivot['rows'] : $explorer->fetchRows($idSite, $window, $plain, $dimensions, $segment, $slot);
            $rowKeys = array_map(static fn (array $row): array => Explorer::rowKeys($row, $count), $rows);
            // per upper level: the distinct prefixes in order, each row's prefix, the totals the database grouped
            $levels = [];
            for ($depth = 1; $depth < $count; $depth++) {
                $prefixes = [];
                $prefixOf = [];
                foreach ($rowKeys as $index => $keys) {
                    $prefix = array_slice($keys, 0, $depth);
                    $at = array_search($prefix, $prefixes, true);
                    if ($at === false) {
                        $at = count($prefixes);
                        $prefixes[] = $prefix;
                    }
                    $prefixOf[$index] = $at;
                }
                $totals = [];
                if ($prefixes !== []) {
                    foreach ($explorer->fetchRows($idSite, $window, $plain, $dimensions, $segment, $slot, null, false, null, null, $prefixes) as $total) {
                        $totals[(int) $total['fwde_row']] = $total;
                    }
                }
                $levels[$depth] = ['prefixes' => $prefixes, 'prefixOf' => $prefixOf, 'groupOf' => self::groups($prefixes, $totals), 'totals' => $totals];
            }
        } finally {
            $slot->release();
        }

        $table = $explorer->toDataTable($rows, $plain, $dimensions, $idSite, true);
        $labelColumns = ExplorationDefinition::labelColumns($count);
        $roots = [];
        $nodes = [];
        foreach ($table->getRows() as $index => $row) {
            $siblings = &$roots;
            for ($depth = 1; $depth < $count; $depth++) {
                $level = $levels[$depth];
                $group = $level['groupOf'][$level['prefixOf'][$index]];
                if (!isset($nodes[$depth][$group])) {
                    $prefix = $level['prefixes'][$group];
                    $columns = [];
                    foreach ($definition->metricIds as $metricId) {
                        $columns[$metricId] = Explorer::metricValue($metricId, $level['totals'][$group][$metricId] ?? null);
                    }
                    $siblings[] = [
                        'label' => (string) $dimensions[$depth - 1]->formatLabel($prefix[$depth - 1], $idSite),
                        'columns' => $columns, 'metadata' => [], 'children' => [],
                    ];
                    $nodes[$depth][$group] = &$siblings[count($siblings) - 1];
                }
                $siblings = &$nodes[$depth][$group]['children'];
            }
            $values = $row->getColumns();
            $siblings[] = [
                'label' => (string) $values[$labelColumns[$count - 1]],
                'columns' => array_diff_key($values, array_flip($labelColumns)),
                'metadata' => $row->getMetadata(),
                'children' => $pivot !== null ? $this->pivotLevel($pivot, $definition, $index, $row->getMetadata('segment'), $idSite) : [],
            ];
            unset($siblings);
        }
        unset($nodes);
        return $roots;
    }

    /**
     * Each prefix's group: its own, or the earlier one the database put it with (it then has no totals of its own).
     *
     * @param list<list<?string>> $prefixes
     * @return array<int, int> prefix index => index of the prefix that names the group
     */
    private static function groups(array $prefixes, array $totals): array
    {
        $groupOf = [];
        foreach ($prefixes as $i => $prefix) {
            $groupOf[$i] = $i;
            if (isset($totals[$i])) {
                continue;
            }
            foreach ($prefixes as $j => $earlier) {
                if ($j < $i && isset($totals[$j]) && array_map([self::class, 'fold'], $earlier) === array_map([self::class, 'fold'], $prefix)) {
                    $groupOf[$i] = $groupOf[$j];
                    break;
                }
            }
        }
        return $groupOf;
    }

    /** The pivot level under one combination: the top values, then the rest when it is exact for every metric. */
    private function pivotLevel(array $pivot, ExplorationDefinition $definition, int $index, $rowSegment, int $idSite): array
    {
        $children = [];
        foreach ($pivot['pivotKeys'] as $n => $key) {
            $columns = [];
            foreach ($definition->metricIds as $metricId) {
                $columns[$metricId] = Explorer::metricValue($metricId, $pivot['cells'][$index][$n][$metricId] ?? null);
            }
            $valueSegment = $pivot['pivot']->segmentFor($key);
            $children[] = [
                'label' => (string) $pivot['pivot']->formatLabel($key, $idSite),
                'columns' => $columns,
                'metadata' => is_string($rowSegment) && $rowSegment !== '' && $valueSegment !== null ? ['segment' => $rowSegment . ';' . $valueSegment] : [],
                'children' => [],
            ];
        }
        if ($pivot['withOthers'] && count($pivot['othersMetrics']) === count($definition->metricIds)) {
            $columns = [];
            foreach ($definition->metricIds as $metricId) {
                $columns[$metricId] = Explorer::metricValue($metricId, $pivot['others'][$index][$metricId] ?? null);
            }
            $children[] = ['label' => Piwik::translate('General_Others'), 'columns' => $columns, 'metadata' => [], 'children' => []];
        }
        return $children;
    }

    /** A key without case and accents (only to choose among spellings the database already put together). */
    private static function fold(?string $key): ?string
    {
        if ($key === null) {
            return null;
        }
        $key = mb_strtolower($key);
        if (class_exists(\Normalizer::class)) {
            $key = (string) preg_replace('/\p{Mn}/u', '', (string) \Normalizer::normalize($key, \Normalizer::FORM_D));
        }
        return $key;
    }
}
