<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\DataTable;

/**
 * Matomo's API adds a totals row to a result by summing every column but "label". The plugin's results also carry text
 * in other columns (the second dimension, the trend's time, renamed label columns of exports); summing them logs a
 * "Trying to add two strings" warning per row and request. Text columns are marked "skip" for that sum.
 */
final class TextColumns
{
    public static function skipInTotals(DataTable $table): DataTable
    {
        $operations = $table->getMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME);
        $operations = is_array($operations) ? $operations : [];
        foreach ($table->getRows() as $row) {
            foreach ($row->getColumns() as $column => $value) {
                if ($column !== 'label' && is_string($value) && !isset($operations[$column])) {
                    $operations[$column] = 'skip';
                }
            }
        }
        if ($operations !== []) {
            $table->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $operations);
        }
        return $table;
    }
}
