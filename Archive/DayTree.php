<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Archive;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ReportLevels;

/**
 * An archived day as Matomo stores it (design 2026-11-25): first-level rows per value of the first dimension, a sub
 * table per row for the next level. Labels are the stored values folded as the database compares them (case and
 * accents), so Matomo adds up "Newsletter" and "newsletter" of different days into one row as the live report groups
 * them; the spelling shown is kept as row metadata (RAW) and formatted when reading, stable across languages. A value
 * that is NULL gets NULL_LABEL. Each level comes from its own query, so a visit is counted once per row of every level
 * as the live report counts it.
 */
final class DayTree
{
    public const NULL_LABEL = '__fwde_null__';

    /** a row's stored value as the database returned it (the label is its folded form) */
    public const RAW = 'fwde_raw';

    /** each row's level (1: the first dimension), so reading knows which dimension formats a sub table's labels */
    public const LEVEL = 'fwde_level';

    /**
     * @param list<list<array<string, mixed>>> $levels the rows of level 1, 2, ... (keys fwde_k1 .. fwde_k{level})
     * @param list<string> $columns the stored columns
     */
    public static function build(array $levels, array $columns): DataTable
    {
        $root = new DataTable();
        $parents = ['' => $root];
        foreach ($levels as $index => $rows) {
            $depth = $index + 1;
            $next = [];
            foreach ($rows as $row) {
                $path = [];
                for ($k = 1; $k <= $depth; $k++) {
                    $path[] = self::label(ReportLevels::fold(isset($row['fwde_k' . $k]) ? (string) $row['fwde_k' . $k] : null));
                }
                $raw = isset($row['fwde_k' . $depth]) ? (string) $row['fwde_k' . $depth] : null;
                $label = array_pop($path);
                $parentKey = implode("\x1f", $path);
                if (!isset($parents[$parentKey])) {
                    // a deeper combination whose upper row was cut by the day query's limit
                    continue;
                }
                $values = ['label' => $label, self::LEVEL => $depth];
                foreach ($columns as $column) {
                    $values[$column] = isset($row[$column]) ? $row[$column] + 0 : 0;
                }
                $tableRow = new Row([Row::COLUMNS => $values]);
                if ($raw !== null) {
                    // the spelling shown: the first one Matomo keeps when it adds up days
                    $tableRow->setMetadata(self::RAW, $raw);
                }
                $parent = $parents[$parentKey];
                if ($parent instanceof Row) {
                    $sub = $parent->getSubtable();
                    if (!$sub) {
                        $sub = new DataTable();
                        $parent->setSubtable($sub);
                    }
                    $sub->addRow($tableRow);
                } else {
                    $parent->addRow($tableRow);
                }
                $next[implode("\x1f", array_merge($path, [$label]))] = $tableRow;
            }
            $parents = $next;
        }
        return $root;
    }

    public static function label(mixed $raw): string
    {
        return $raw === null ? self::NULL_LABEL : (string) $raw;
    }

    /** the raw stored value of an archived label */
    public static function raw(string $label): ?string
    {
        return $label === self::NULL_LABEL ? null : $label;
    }
}
