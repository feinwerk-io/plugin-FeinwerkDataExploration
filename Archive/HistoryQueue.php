<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Archive;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Option;
use Piwik\Plugins\FeinwerkDataExploration\Engine\RawDataRetention;
use Piwik\Segment;
use Piwik\Site;

/**
 * The history of archived explorations, filled in the background (design 2026-11-25, Peter: "2, wie empfohlen"). The
 * hourly task reconciles: an exploration whose version is new (saved, copied, its data changed, or every one once
 * after the update) gets a queue entry; the run then marks, for at most `archive_history_batch` entries, the days of
 * the exploration's own website from the latest of today minus `archive_history_months` months, the website's creation
 * and the raw data's start, to yesterday as invalid for the exploration's record only, and core:archive does the work.
 * Failed days are invalidated again (RetryList). Never in a web request, never for another website or record, segments
 * only with `archive_history_segments`.
 */
final class HistoryQueue
{
    private const VERSION = 'fw_dataexploration.archive_version.';
    private const QUEUE = 'fw_dataexploration.archive_history.';
    /** the websites that had archived explorations: the weekly cleanup scans only these (release review 2026-11-26) */
    private const SITES = 'fw_dataexploration.archive_sites';

    /** @var callable(int): ?string */
    private $rawDataStart;

    /** @param callable(int): ?string|null $rawDataStart the first day with complete raw data (null: no bound) */
    public function __construct(?callable $rawDataStart = null, private readonly ?RetryList $retries = null)
    {
        $this->rawDataStart = $rawDataStart ?? [RawDataRetention::class, 'earliestCompleteDay'];
    }

    /** @param list<ArchivedExploration> $all every archived exploration (ArchivedExplorations::forAllSites) */
    public function reconcile(array $all): void
    {
        $known = [];
        foreach (Option::getLike(self::VERSION . '%') as $name => $version) {
            $known[(int) substr((string) $name, strlen(self::VERSION))] = (string) $version;
        }
        $current = [];
        $sites = self::archivedSites();
        $knownSites = $sites;
        foreach ($all as $exploration) {
            $current[$exploration->idSaved] = true;
            $sites[] = $exploration->idSite;
            if (($known[$exploration->idSaved] ?? null) === $exploration->version) {
                continue;
            }
            Option::set(self::QUEUE . $exploration->idSaved, (string) json_encode([
                'site' => $exploration->idSite, 'version' => $exploration->version, 'queued' => microtime(true),
            ]));
            Option::set(self::VERSION . $exploration->idSaved, $exploration->version);
        }
        $sites = array_values(array_unique($sites));
        sort($sites);
        if ($sites !== $knownSites) {
            Option::set(self::SITES, (string) json_encode($sites));
        }
        foreach (array_keys($known) as $idSaved) {
            if (!isset($current[$idSaved])) {
                Option::delete(self::VERSION . $idSaved);
                Option::delete(self::QUEUE . $idSaved);
                $this->retries()->forget($idSaved);
            }
        }
    }

    /**
     * Queues the history of the oldest entries and the due retries.
     *
     * @param list<ArchivedExploration> $all every archived exploration
     * @return int the number of explorations whose history was queued
     */
    public function run(array $all, ArchiveSettings $settings, string $today): int
    {
        $byId = [];
        foreach ($all as $exploration) {
            $byId[$exploration->idSaved] = $exploration;
        }
        $entries = [];
        foreach (Option::getLike(self::QUEUE . '%') as $name => $value) {
            $entry = json_decode((string) $value, true);
            $entries[(int) substr((string) $name, strlen(self::QUEUE))] = is_array($entry) ? (float) ($entry['queued'] ?? 0) : 0.0;
        }
        asort($entries);
        $handled = 0;
        foreach (array_keys($entries) as $idSaved) {
            if ($handled >= $settings->historyBatch) {
                break;
            }
            Option::delete(self::QUEUE . $idSaved);
            $exploration = $byId[$idSaved] ?? null;
            if ($exploration === null || $settings->historyMonths <= 0) {
                continue;
            }
            $this->invalidate($exploration, $this->days($exploration->idSite, $settings->historyMonths, $today), $settings);
            $handled++;
        }
        foreach ($this->retries()->due() as $idSaved => $days) {
            $exploration = $byId[$idSaved] ?? null;
            if ($exploration === null) {
                $this->retries()->forget($idSaved);
                continue;
            }
            $this->invalidate($exploration, $days, $settings);
            $this->retries()->tried($idSaved, $days);
        }
        return $handled;
    }

    /** @return list<int> the websites that had archived explorations, in order */
    public static function archivedSites(): array
    {
        $sites = json_decode((string) Option::get(self::SITES), true);
        if (!is_array($sites)) {
            return [];
        }
        $sites = array_values(array_unique(array_map('intval', $sites)));
        sort($sites);
        return $sites;
    }

    /** @param list<int> $idSites websites the cleanup found clean and without explorations */
    public static function forgetSites(array $idSites): void
    {
        $left = array_values(array_diff(self::archivedSites(), array_map('intval', $idSites)));
        if ($left === []) {
            Option::delete(self::SITES);
            return;
        }
        Option::set(self::SITES, (string) json_encode($left));
    }

    /** every option of the archive (versions, queue, retry list): the plugin's uninstall (release review 2026-11-26) */
    public static function deleteOptions(): void
    {
        Option::deleteLike('fw_dataexploration.archive%');
    }

    public function isPending(int $idSaved): bool
    {
        return Option::get(self::QUEUE . $idSaved) !== false;
    }

    /**
     * The first day a rebuilt history of the website reaches when earlier days of it cannot come back (the save panel
     * warns before a change starts the history again): today minus `archive_history_months` months or the raw data's
     * start, whichever is later; today when no history is rebuilt; null when the history reaches the website's creation.
     */
    public function lostBefore(int $idSite, int $months, string $today): ?string
    {
        $start = $this->bound($idSite, max(0, $months), $today);
        return $start->isLater($this->created($idSite)) ? $start->toString() : null;
    }

    /** @return list<string> the history days of a website, oldest first */
    private function days(int $idSite, int $months, string $today): array
    {
        $end = Date::factory($today)->subDay(1);
        $start = $this->bound($idSite, $months, $today);
        if ($this->created($idSite)->isLater($start)) {
            $start = $this->created($idSite);
        }
        $days = [];
        for ($day = $start; !$day->isLater($end); $day = $day->addDay(1)) {
            $days[] = $day->toString();
        }
        return $days;
    }

    /** today minus the months or the raw data's start, whichever is later */
    private function bound(int $idSite, int $months, string $today): Date
    {
        $start = Date::factory(self::monthsBefore($today, $months));
        $raw = ($this->rawDataStart)($idSite);
        if (is_string($raw) && $raw !== '' && Date::factory($raw)->isLater($start)) {
            $start = Date::factory($raw);
        }
        return $start;
    }

    /** the website's creation day as Matomo keeps it (a day early, for timezones) */
    private function created(int $idSite): Date
    {
        return Date::factory(substr((string) Site::getCreationDateFor($idSite), 0, 10));
    }

    /** the same day $months months earlier, the last day of a shorter month (Matomo's subMonth goes to the 1st) */
    public static function monthsBefore(string $day, int $months): string
    {
        [$year, $month, $date] = array_map('intval', explode('-', $day));
        $index = $year * 12 + ($month - 1) - $months;
        $year = intdiv($index, 12);
        $month = $index % 12 + 1;
        $date = min($date, (int) date('t', gmmktime(0, 0, 0, $month, 1, $year)));
        return sprintf('%04d-%02d-%02d', $year, $month, $date);
    }

    /** @param list<string> $days */
    private function invalidate(ArchivedExploration $exploration, array $days, ArchiveSettings $settings): void
    {
        if ($days === []) {
            return;
        }
        $dates = array_map(static fn (string $day): Date => Date::factory($day), $days);
        $name = 'FeinwerkDataExploration.' . $exploration->recordName();
        $invalidator = StaticContainer::get(ArchiveInvalidator::class);
        $invalidator->markArchivesAsInvalidated([$exploration->idSite], $dates, 'day', null, false, false, $name);
        if ($settings->historySegments) {
            foreach (Rules::getSegmentsToProcess([$exploration->idSite]) as $definition) {
                $invalidator->markArchivesAsInvalidated([$exploration->idSite], $dates, 'day', new Segment($definition, [$exploration->idSite]), false, false, $name);
            }
        }
    }

    private function retries(): RetryList
    {
        return $this->retries ?? new RetryList();
    }
}
