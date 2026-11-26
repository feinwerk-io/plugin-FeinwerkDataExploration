<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Archive;

use Piwik\Config;

/**
 * How saved explorations are archived (design 2026-11-25), from the plugin's own section `[FeinwerkDataExploration]` of
 * config.ini.php (Peter, 2026-11-25: "bitte eigene Config-Parameter für das Plugin anlegen, da diese von den matomo
 * standardeinstellungen abweichen können"). Matomo's own `rearchive_reports_in_past_last_n_months` and row limits do
 * not apply. An invalid value falls back to its default.
 */
final class ArchiveSettings
{
    private const PLUGIN = 'FeinwerkDataExploration';

    public function __construct(
        /** archive_reports: saved explorations are archived; off, they stay live reports (31 days) */
        public readonly bool $reports = true,
        /** archive_history_months: how far back a new or changed exploration's history is filled; 0: none */
        public readonly int $historyMonths = 6,
        /** archive_history_segments: history also for the website's pre-processed segments */
        public readonly bool $historySegments = false,
        /** archive_segments: records built in core:archive's runs for pre-processed segments too */
        public readonly bool $segments = false,
        /** archive_history_batch: explorations whose history the hourly task queues per run */
        public readonly int $historyBatch = 5,
        /** archive_max_explorations_per_site: archived explorations per website; 0: no cap */
        public readonly int $maxExplorationsPerSite = 50,
        /** archive_max_rows: rows of an archive's first level; the rest is "Others" */
        public readonly int $maxRows = 1000,
        /** archive_max_rows_subtable: rows per sub table (second and third dimension, pivot) */
        public readonly int $maxRowsSubtable = 100,
        /** archive_query_seconds: statement time limit of one exploration's day query in core:archive */
        public readonly int $querySeconds = 300
    ) {
    }

    public static function fromConfig(): self
    {
        try {
            $section = Config::getInstance()->{self::PLUGIN};
        } catch (\Throwable $e) {
            $section = [];
        }
        return self::fromSection(is_array($section) ? $section : []);
    }

    /** @param array<string, mixed> $section the plugin's config.ini section */
    public static function fromSection(array $section): self
    {
        $d = new self();
        return new self(
            self::flag($section, 'archive_reports', $d->reports),
            self::number($section, 'archive_history_months', $d->historyMonths, 0),
            self::flag($section, 'archive_history_segments', $d->historySegments),
            self::flag($section, 'archive_segments', $d->segments),
            self::number($section, 'archive_history_batch', $d->historyBatch, 1),
            self::number($section, 'archive_max_explorations_per_site', $d->maxExplorationsPerSite, 0),
            self::number($section, 'archive_max_rows', $d->maxRows, 1),
            self::number($section, 'archive_max_rows_subtable', $d->maxRowsSubtable, 1),
            self::number($section, 'archive_query_seconds', $d->querySeconds, 1)
        );
    }

    /** on: 1, on, yes, true; off: 0, off, no, false, empty (any case; PHP booleans from Matomo's INI reader too) */
    private static function flag(array $section, string $key, bool $default): bool
    {
        if (!array_key_exists($key, $section)) {
            return $default;
        }
        $value = $section[$key];
        if (is_bool($value)) {
            return $value;
        }
        $text = strtolower(trim((string) $value));
        if (in_array($text, ['1', 'on', 'yes', 'true'], true)) {
            return true;
        }
        if (in_array($text, ['0', 'off', 'no', 'false', ''], true)) {
            return false;
        }
        return $default;
    }

    private static function number(array $section, string $key, int $default, int $min): int
    {
        if (!array_key_exists($key, $section)) {
            return $default;
        }
        $value = is_int($value = $section[$key]) ? (string) $value : trim((string) $value);
        if (preg_match('/^\d+$/', $value) !== 1 || (int) $value < $min) {
            return $default;
        }
        return (int) $value;
    }
}
