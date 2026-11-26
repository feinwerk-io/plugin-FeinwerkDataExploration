<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Archive;

use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Db;
use Piwik\Plugins\SitesManager\Model as SitesModel;

/**
 * Removes the archive records no saved exploration reads any more (design 2026-11-25): an older version's or a deleted
 * exploration's, never a current record, never another plugin's. Per archive table and website it takes the website's
 * archive ids (Matomo's index on idsite) in chunks and deletes only the plugin's saved-exploration names among them
 * whose record is not current (a record's sub table rows "<name>_chunk_X_Y" belong to it), at most $maxRows rows per
 * run. Weekly in command line runs (Tasks::archiveCleanup), never in a web request.
 */
final class RecordCleanup
{
    private const NAME_PATTERN = 'FeinwerkDataExploration\\_saved%';
    private const CHUNK = 1000;

    /** the record a row belongs to: its name without Matomo's sub table suffix "_chunk_X_Y" */
    public static function recordOf(string $name): string
    {
        return (string) preg_replace('/_chunk_\d+_\d+$/', '', $name);
    }

    /**
     * @param list<ArchivedExploration> $current every archived exploration (their records are kept)
     * @param list<int> $keepAll explorations whose every record stays (they exist but do not resolve for now)
     * @param list<int>|null $idSites the websites to scan (null: every website)
     * @return int the rows deleted
     */
    public function run(array $current, int $maxRows = 10000, array $keepAll = [], ?array $idSites = null): int
    {
        $idSites ??= array_map('intval', (new SitesModel())->getSitesId());
        $keepAll = array_flip(array_map('intval', $keepAll));
        $keep = [];
        foreach ($current as $exploration) {
            foreach ([$exploration->recordName(), $exploration->markerName(), $exploration->pivotTotalsName()] as $name) {
                $keep[$exploration->idSite][] = $name;
            }
        }
        $blobTables = array_flip(ArchiveTableCreator::getTablesArchivesInstalled(ArchiveTableCreator::BLOB_TABLE));
        $deleted = 0;
        foreach (ArchiveTableCreator::getTablesArchivesInstalled(ArchiveTableCreator::NUMERIC_TABLE) as $numericTable) {
            $blobTable = str_replace('archive_numeric_', 'archive_blob_', $numericTable);
            $tables = isset($blobTables[$blobTable]) ? [$numericTable, $blobTable] : [$numericTable];
            foreach ($idSites as $idSite) {
                $ids = array_map('intval', array_column(Db::fetchAll(
                    'SELECT idarchive FROM ' . $numericTable . " WHERE idsite = ? AND name LIKE 'done%'",
                    [(int) $idSite]
                ), 'idarchive'));
                $kept = array_flip($keep[(int) $idSite] ?? []);
                foreach (array_chunk($ids, self::CHUNK) as $chunk) {
                    foreach ($tables as $table) {
                        $remaining = $maxRows - $deleted;
                        if ($remaining <= 0) {
                            return $deleted;
                        }
                        $in = implode(', ', $chunk);
                        // Matomo keeps a record's sub tables in "<name>_chunk_X_Y" rows: decided by the record they belong to
                        $gone = [];
                        foreach (Db::fetchAll('SELECT DISTINCT name FROM ' . $table . ' WHERE idarchive IN (' . $in . ') AND name LIKE ?', [self::NAME_PATTERN]) as $row) {
                            $name = (string) $row['name'];
                            if (isset($kept[self::recordOf($name)])) {
                                continue;
                            }
                            if (preg_match('/^FeinwerkDataExploration_saved(\d+)_/', $name, $m) === 1 && isset($keepAll[(int) $m[1]])) {
                                continue;
                            }
                            $gone[] = $name;
                        }
                        if ($gone === []) {
                            continue;
                        }
                        $sql = 'DELETE FROM ' . $table . ' WHERE idarchive IN (' . $in . ') AND name IN ('
                            . implode(', ', array_fill(0, count($gone), '?')) . ') LIMIT ' . (int) $remaining;
                        $deleted += Db::query($sql, $gone)->rowCount();
                    }
                }
            }
        }
        return $deleted;
    }
}
