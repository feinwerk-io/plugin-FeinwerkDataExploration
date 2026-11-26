<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Archive;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Explorer;
use Piwik\Plugins\FeinwerkDataExploration\Engine\LimitsProvider;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedReportTable;
use Piwik\SettingsServer;
use Psr\Log\LoggerInterface;

/**
 * One saved exploration's record per website and day (design 2026-11-25): the day's rows with a sub table per level
 * (Archive/DayTree.php) and a numeric marker (1) that the day is archived; Matomo adds up weeks, months, years and
 * ranges with the stored columns' operations (Archive/StoredMetrics.php). It never throws: a failing or refused day is
 * logged, gets no marker and goes on the retry list, and the website's other records are still written.
 */
final class ExplorationRecordBuilder extends RecordBuilder
{
    use NewestSubperiodAggregation;

    public function __construct(private readonly ArchivedExploration $exploration, private readonly ArchiveSettings $settings)
    {
        $ops = StoredMetrics::aggregationOps($exploration->definition->metricIds);
        $sums = array_keys(array_filter($ops, static fn (string $op): bool => $op === 'sum'));
        parent::__construct($settings->maxRows, $settings->maxRowsSubtable, $sums[0] ?? null, $ops + [DayTree::LEVEL => 'max']);
    }

    /**
     * Its website's archives only; in core:archive's runs for Matomo's pre-processed segments only with
     * archive_segments = 1 (they would multiply the work on every run).
     */
    public function isEnabled(ArchiveProcessor $archiveProcessor): bool
    {
        $params = $archiveProcessor->getParams();
        if (!$this->settings->reports || (int) $params->getSite()->getId() !== $this->exploration->idSite) {
            return false;
        }
        return $params->getSegment()->isEmpty() || !SettingsServer::isArchivePhpTriggered() || $this->settings->segments;
    }

    /**
     * Weeks, months, years and ranges. A report-specific invalidation (the history queue) names the exploration's table
     * record; its marker and pivot totals belong to it, so they are added up too. Where the sub-periods are Matomo's
     * full archives, every record adds up the newest archive of each sub-period (NewestSubperiodAggregation): the
     * history's days are newer archives of this plugin only.
     */
    public function buildForNonDayPeriod(ArchiveProcessor $archiveProcessor): void
    {
        $params = $archiveProcessor->getParams();
        $requested = $params->getArchiveOnlyReportAsArray();
        $records = $this->getRecordMetadata($archiveProcessor);
        $own = array_map(static fn (Record $record): string => $record->getName(), $records);
        if ($requested !== [] && array_intersect($requested, $own) === []) {
            // another record was asked for: Matomo builds none of these
            parent::buildForNonDayPeriod($archiveProcessor);
            return;
        }
        if ($this->subperiodsShareTheAllPluginsArchive($params)) {
            if ($this->isEnabled($archiveProcessor)) {
                $this->aggregateNewestSubperiods($archiveProcessor, $records);
            }
            return;
        }
        if ($requested === []) {
            parent::buildForNonDayPeriod($archiveProcessor);
            return;
        }
        $before = $params->getArchiveOnlyReport();
        $params->setArchiveOnlyReport(array_values(array_unique(array_merge($requested, $own))));
        try {
            parent::buildForNonDayPeriod($archiveProcessor);
        } finally {
            $params->setArchiveOnlyReport($before);
        }
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return array_merge([
            Record::make(Record::TYPE_BLOB, $this->exploration->recordName())
                ->setMaxRowsInTable($this->settings->maxRows)
                ->setMaxRowsInSubtable($this->settings->maxRowsSubtable)
                ->setColumnToSortByBeforeTruncation($this->columnToSortByBeforeTruncation)
                ->setBlobColumnAggregationOps($this->columnAggregationOps),
            Record::make(Record::TYPE_NUMERIC, $this->exploration->markerName()),
        ], $this->exploration->definition->pivot === null ? [] : [
            Record::make(Record::TYPE_BLOB, $this->exploration->pivotTotalsName())
                ->setMaxRowsInTable($this->settings->maxRowsSubtable)
                ->setColumnToSortByBeforeTruncation($this->columnToSortByBeforeTruncation)
                ->setBlobColumnAggregationOps($this->columnAggregationOps),
        ]);
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $params = $archiveProcessor->getParams();
        $day = $params->getPeriod()->getDateStart()->toString();
        $exploration = $this->exploration;
        try {
            $segment = SavedReportTable::segment($exploration->segment, (string) $params->getSegment()->getString());
            $limits = (new LimitsProvider())->get();
            $run = SettingsServer::isArchivePhpTriggered() ? ArchiveRun::forCli($this->settings, $limits) : ArchiveRun::forBrowser($limits);
            [$definition] = FilterSplit::split($exploration->definition->withoutPivot(), FilterSplit::isMetricTarget(...));
            $catalog = new DimensionCatalog();
            $explorer = new Explorer($catalog, new MetricCatalog(), $run->limits);
            $count = count($definition->dimensionIds);
            // a pivot (Peter: "2a"): every value under each row as the last level, and the totals per value
            $pivot = $exploration->definition->pivot !== null ? $catalog->get($exploration->idSite, (string) $exploration->definition->pivot['dimension']) : null;
            [$levels, $totals] = $run->withLanguage($exploration->owner, static function () use ($explorer, $exploration, $day, $definition, $segment, $run, $count, $pivot): array {
                // the row levels in the exploration's own scope (as the live rows), the pivot level in the pivot's
                $levels = [];
                for ($level = 1; $level <= $count; $level++) {
                    $levels[] = $explorer->archiveDay($exploration->idSite, $day, $definition, $segment, $run, $level === $count ? 0 : $level);
                }
                if ($pivot !== null) {
                    $levels[] = $explorer->archiveDay($exploration->idSite, $day, $definition, $segment, $run, 0, $pivot);
                }
                $totals = $pivot !== null ? $explorer->archiveDay($exploration->idSite, $day, $definition, $segment, $run, 0, $pivot, true) : [];
                return [$levels, $totals];
            });
            $columns = [];
            foreach ($definition->metricIds as $metricId) {
                $columns = array_merge($columns, StoredMetrics::columns($metricId));
            }
            $records = [
                $exploration->recordName() => DayTree::build($levels, $columns),
                $exploration->markerName() => 1,
            ];
            if ($pivot !== null) {
                $key = 'fwde_k' . ($count + 1);
                $records[$exploration->pivotTotalsName()] = DayTree::build([array_map(
                    static fn (array $row): array => ['fwde_k1' => $row[$key] ?? null] + $row,
                    $totals
                )], $columns);
            }
            return $records;
        } catch (\Throwable $e) {
            StaticContainer::get(LoggerInterface::class)->warning('Feinwerk Data Exploration: saved exploration {id} not archived for {day}: {message}', [
                'id' => $exploration->idSaved, 'day' => $day, 'message' => $e->getMessage(),
            ]);
            try {
                (new RetryList())->add($exploration->idSaved, $day);
            } catch (\Throwable $ignored) {
                // the retry list is a help; the archive of the website's other explorations must go on
            }
            return [];
        }
    }
}
