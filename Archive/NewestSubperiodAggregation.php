<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Archive;

use Piwik\Archive;
use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\DataTableInterface;
use Piwik\DataTable\Manager;

/**
 * Weeks, months, years and ranges add up the NEWEST archive of each sub-period (release check 2026-11-26; the house
 * pattern for Matomo's shared archive, matomo#25501, open on Matomo 5 and 6).
 *
 * The history builds an exploration's days for this plugin only, after Matomo's full archives of those days, which hold
 * no record of a new exploration; Matomo's own aggregation for a full archive (the current week, month and year on every
 * core:archive run) reads the sub-periods through the archive it shares with every plugin, so it can miss the newer
 * plugin archives or add up an invalidated one. Here every record is read once through a fresh archive of the
 * sub-periods asked for this plugin: Matomo's Loader returns the newest of the full and this plugin's archive of each
 * sub-period, archives the ones that are missing and rebuilds invalidated ones, so a day whose visits were all deleted
 * adds nothing (a read without launching would add up its invalidated archive). Builds whose sub-periods are looked up
 * under this plugin's own done flag (browser-only segments) keep Matomo's aggregation, which reads this plugin's
 * archives there.
 */
trait NewestSubperiodAggregation
{
    /** Whether the sub-periods are looked up under the all-plugins done flag, the case Matomo's shared archive gets wrong. */
    private function subperiodsShareTheAllPluginsArchive(Parameters $params): bool
    {
        $subPeriods = $params->getSubPeriods();
        $label = $subPeriods === [] ? 'day' : reset($subPeriods)->getLabel();
        $segment = $params->getSegment();
        return Rules::getDoneStringFlagFor($params->getIdSites(), $segment, $label, $this->getPluginName())
            === Rules::getDoneFlagArchiveContainsAllPlugins($segment);
    }

    /**
     * Adds up the records from one launching archive of all sub-periods, stored like Matomo stores them (truncated
     * tables, sub tables, numeric sums).
     *
     * @param Record[] $records
     */
    private function aggregateNewestSubperiods(ArchiveProcessor $archiveProcessor, array $records): void
    {
        $params = $archiveProcessor->getParams();
        $archive = Archive::factory($params->getSegment(), $params->getSubPeriods(), $params->getIdSites());
        $numerics = [];
        foreach ($records as $record) {
            $name = $record->getName();
            if ($record->getType() !== Record::TYPE_BLOB) {
                $numerics[$name] = 0;
                continue;
            }
            $ops = (array) ($record->getBlobColumnAggregationOps() ?? $this->columnAggregationOps ?? []);
            $latestUsedTableId = Manager::getInstance()->getMostRecentTableId();
            $table = new DataTable();
            $table->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $ops);
            $this->addSubperiodTables($table, $archive->getDataTableExpanded($name, null, null, false), $ops);
            $archiveProcessor->insertBlobRecord($name, $table->getSerialized(
                $record->getMaxRowsInTable() ?? $this->maxRowsInTable,
                $record->getMaxRowsInSubtable() ?? $this->maxRowsInSubtable,
                $record->getColumnToSortByBeforeTruncation() ?? $this->columnToSortByBeforeTruncation
            ));
            Common::destroy($table);
            Manager::getInstance()->deleteAll($latestUsedTableId);
        }
        if ($numerics === []) {
            return;
        }
        $latestUsedTableId = Manager::getInstance()->getMostRecentTableId();
        self::addSubperiodNumerics($numerics, $archive->getDataTableFromNumeric(array_keys($numerics)));
        Manager::getInstance()->deleteAll($latestUsedTableId);
        foreach ($numerics as $name => $value) {
            $archiveProcessor->insertNumericRecord($name, $value == (int) $value ? (int) $value : $value);
        }
    }

    /** Adds a table, or every table of a (nested) map of sites and sub-periods, to the sum. */
    private function addSubperiodTables(DataTable $sum, DataTableInterface $tables, array $ops): void
    {
        if ($tables instanceof DataTable\Map) {
            foreach ($tables->getDataTables() as $child) {
                $this->addSubperiodTables($sum, $child, $ops);
            }
        } elseif ($tables instanceof DataTable) {
            self::setSubperiodAggregationOps($tables, $ops);
            $sum->addDataTable($tables);
        }
    }

    /** @param array<string, int|float> $sums */
    private static function addSubperiodNumerics(array &$sums, DataTableInterface $tables): void
    {
        if ($tables instanceof DataTable\Map) {
            foreach ($tables->getDataTables() as $child) {
                self::addSubperiodNumerics($sums, $child);
            }
            return;
        }
        if (!$tables instanceof DataTable) {
            return;
        }
        foreach ($tables->getRows() as $row) {
            foreach ($row->getColumns() as $name => $value) {
                if (isset($sums[$name]) && is_numeric($value)) {
                    $sums[$name] += (float) $value;
                }
            }
        }
    }

    /** Sub tables are added up with the operations stored on the incoming table (see Row::sumSubtable). */
    private static function setSubperiodAggregationOps(DataTable $table, array $ops): void
    {
        $table->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $ops);
        foreach ($table->getRows() as $row) {
            $subtable = $row->getSubtable();
            if ($subtable instanceof DataTable) {
                self::setSubperiodAggregationOps($subtable, $ops);
            }
        }
    }
}
