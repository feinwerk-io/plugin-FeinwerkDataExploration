<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration;

use Piwik\Common;
use Piwik\Date;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedExplorations;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchiveSettings;
use Piwik\Plugins\FeinwerkDataExploration\Archive\HistoryQueue;
use Piwik\Plugins\FeinwerkDataExploration\Archive\RecordCleanup;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationStore;

/**
 * Background work of archived saved explorations (design 2026-11-25): every hour their history is queued for
 * core:archive, every week the records no exploration reads any more are removed. Only in command line runs (core:archive, scheduled-tasks:run): Matomo may run scheduled tasks at the end
 * of a web request where browser archiving is on, and no invalidation may happen there.
 */
class Tasks extends \Piwik\Plugin\Tasks
{
    public function schedule()
    {
        $this->hourly('archiveHistory');
        $this->weekly('archiveCleanup');
    }

    /**
     * Removes old records weekly, in small chunks (Archive/RecordCleanup.php): only an older version's and a deleted
     * exploration's. History that cannot be rebuilt stays (final review 2026-11-26): nothing while archiving is off, the
     * cap is ignored, and an exploration that exists but does not resolve for now keeps every record.
     */
    public function archiveCleanup(): void
    {
        if (!Common::isPhpCliMode() || !ArchiveSettings::fromConfig()->reports) {
            return;
        }
        // only the websites that had archived explorations (release review 2026-11-26): none, nothing to scan
        $sites = HistoryQueue::archivedSites();
        if ($sites === []) {
            return;
        }
        $current = (new ArchivedExplorations(new ArchiveSettings(maxExplorationsPerSite: 0)))->forAllSites();
        $resolved = array_flip(array_map(static fn ($exploration): int => $exploration->idSaved, $current));
        $unresolved = [];
        $withExplorations = [];
        foreach ((new SavedExplorationStore())->listEverySite() as $row) {
            $withExplorations[(int) $row['idsite']] = true;
            if (!isset($resolved[(int) $row['idsaved']])) {
                $unresolved[] = (int) $row['idsaved'];
            }
        }
        $max = 10000;
        if ((new RecordCleanup())->run($current, $max, $unresolved, $sites) < $max) {
            // a website without explorations is clean now: the next cleanups leave it out
            HistoryQueue::forgetSites(array_values(array_filter($sites, static fn (int $idSite): bool => !isset($withExplorations[$idSite]))));
        }
    }

    public function archiveHistory(): void
    {
        if (!Common::isPhpCliMode()) {
            return;
        }
        $settings = ArchiveSettings::fromConfig();
        $all = (new ArchivedExplorations($settings))->forAllSites();
        $queue = new HistoryQueue();
        $queue->reconcile($all);
        if ($settings->reports) {
            $queue->run($all, $settings, Date::factory('today')->toString());
        }
    }
}
