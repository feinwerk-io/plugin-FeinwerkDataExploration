<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Saved;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Period;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Site;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedExploration;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedReportReader;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Additivity;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationWindow;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Explorer;
use Piwik\Plugins\FeinwerkDataExploration\Engine\LazySlot;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ReportLevels;
use Piwik\Plugins\FeinwerkDataExploration\SavedReport\SavedReportMetric;

/**
 * The table of a saved exploration as a Matomo report (saved reports, 2026-11-24). One dimension without a pivot: the
 * explored table. Otherwise one level per row dimension and one for the pivot (ReportLevels, up to four): the first
 * level's values with their totals over what the text filters let through, in the order of the explored combinations,
 * each with a sub table id from its value path (subtableId()); idSubtable returns that value's next level from the
 * same (cached) result. Each column says how Matomo may combine it in an "Others" row or a total: summed where the
 * rows add up, skipped otherwise. Matomo's own sorting, paging and cutting apply as for any report.
 */
final class SavedReportTable
{
    public function __construct(
        private readonly Explorer $explorer,
        private readonly ReportLevels $levels,
        private readonly DimensionCatalog $dimensions,
        private readonly MetricCatalog $metrics,
        private readonly Limits $limits = new Limits()
    ) {
    }

    /**
     * @param string $savedSegment the exploration's own segment; the request's narrows it (both apply), and a row's
     *                             visitor log keeps it
     * @param int $idSubtable 0 for the report, else the sub table id of a row with a next level
     * @param KpiCardSettings|null $card a KPI card: one row with its metric for the period, or one table per day for a
     *                                   run of days (period day with a date range or lastN)
     */
    public function build(int $idSite, string $period, string $date, ExplorationDefinition $definition, string $savedSegment, string $requestSegment, int $idSubtable = 0, ?KpiCardSettings $card = null, ?ArchivedExploration $archived = null, ?ArchivedReportReader $reader = null): DataTable\DataTableInterface
    {
        $segment = self::segment($savedSegment, $requestSegment);
        if ($card !== null) {
            return $this->card($idSite, $period, $date, $definition->withoutPivot(), $segment, $card->metric);
        }
        // a distinct metric (unique visitors) cannot be added up across days: periods of several days up to maxDays stay
        // live as before (final review 2026-11-26)
        if ($archived !== null && $archived->distinctMetrics() !== [] && $period !== 'day' && !Period::isMultiplePeriod($date, $period)
            && self::periodDays($idSite, $period, $date) <= $this->limits->maxDays) {
            $archived = null;
        }
        if ($archived !== null && !Period::isMultiplePeriod($date, $period)) {
            // archived (design 2026-11-25): complete periods and long ones from the archive (a long incomplete one with its
            // coverage), an incomplete one of up to maxDays days live as before, so nothing is emptier than before
            $reader ??= new ArchivedReportReader($this->dimensions);
            // the period's own archive, or a range of its days when that covers more (Matomo 5, see source())
            [$fromPeriod, $fromDate, $covered, $needed] = $reader->source($archived, $period, $date, trim($requestSegment));
            if ($covered >= $needed || self::periodDays($idSite, $period, $date) > $this->limits->maxDays) {
                $table = $reader->read($archived, $fromPeriod, $fromDate, trim($requestSegment), $idSubtable, $archived->readFilters());
                $table->setMetadata('fwde_archived', true);
                if ($covered < $needed) {
                    $table->setMetadata('fwde_archive_coverage', [$covered, $needed]);
                }
                $this->prefixRowSegments($table, $savedSegment);
                $this->decorate($table, $idSite, PeriodFactory::makePeriodFromQueryParams(Site::getTimezoneFor($idSite), $period, $date), $definition, $definition->metricIds);
                return $table;
            }
        }
        if (count($definition->dimensionIds) < 2 && $definition->pivot === null) {
            $table = $this->explorer->explore($idSite, $period, $date, $definition, $segment);
        } else {
            $table = self::level($this->levels->tree($idSite, $period, $date, $definition, $segment), $idSubtable);
        }
        $this->prefixRowSegments($table, $savedSegment);
        $this->decorate($table, $idSite, PeriodFactory::makePeriodFromQueryParams(Site::getTimezoneFor($idSite), $period, $date), $definition, $definition->metricIds);
        return $table;
    }

    /**
     * A KPI card as a report (2026-11-24): one row with its metric over the period (the exploration's total row), or
     * for period day with a date range or lastN one such table per day from one query (Matomo's e-mail graph and Email
     * Reports Pro's line ask runs of days). Runs of other periods are refused, as for every report.
     */
    private function card(int $idSite, string $period, string $date, ExplorationDefinition $definition, string $segment, string $metric): DataTable\DataTableInterface
    {
        $dimensions = array_map(fn (string $id) => $this->dimensions->get($idSite, $id), $definition->dimensionIds);
        $slot = new LazySlot($this->limits);
        try {
            if ($period === 'day' && Period::isMultiplePeriod($date, $period)) {
                $window = ExplorationWindow::for($idSite, 'range', $date, $this->limits);
                $byDay = [];
                foreach ($this->explorer->fetchRows($idSite, $window, $definition, $dimensions, $segment, $slot, null, true, $this->dimensions->get($idSite, 'date')) as $row) {
                    $byDay[(string) $row['fwde_t']] = $row;
                }
                $map = new DataTable\Map();
                $map->setKeyName('date');
                for ($day = $window->firstDay; $day <= $window->lastDay; $day = date('Y-m-d', strtotime($day . ' +1 day'))) {
                    $table = new DataTable();
                    $table->addRow(new Row([Row::COLUMNS => [$metric => Explorer::metricValue($metric, $byDay[$day][$metric] ?? null)]]));
                    $this->decorate($table, $idSite, PeriodFactory::build('day', $day), $definition, [$metric]);
                    $map->addTable($table, $day);
                }
                return $map;
            }
            $window = ExplorationWindow::for($idSite, $period, $date, $this->limits);
            $totals = $this->explorer->fetchRows($idSite, $window, $definition, $dimensions, $segment, $slot, null, true)[0] ?? [];
        } finally {
            $slot->release();
        }
        $table = new DataTable();
        $table->addRow(new Row([Row::COLUMNS => [$metric => Explorer::metricValue($metric, $totals[$metric] ?? null)]]));
        $this->decorate($table, $idSite, PeriodFactory::makePeriodFromQueryParams(Site::getTimezoneFor($idSite), $period, $date), $definition, [$metric]);
        return $table;
    }

    /**
     * The metadata of a report's table: as an archived report's (Matomo's flattener asks the sub tables for the same
     * period and website), how each column may be combined, and one metric object per column (Matomo's widgets and
     * format_metrics=1 format metric objects only, by the metric's unit).
     *
     * @param list<string> $metricIds
     */
    private function decorate(DataTable $table, int $idSite, $period, ExplorationDefinition $definition, array $metricIds): void
    {
        $table->setMetadata(DataTable::GENERIC_FILTERS_TO_DISABLE_METADATA_NAME, []);
        $table->setMetadata('period', $period);
        $table->setMetadata('site', new Site($idSite));
        $table->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, array_intersect_key($this->aggregation($idSite, $definition), array_flip($metricIds)));
        $formatted = [];
        foreach ($metricIds as $metricId) {
            $formatted[] = new SavedReportMetric($metricId, $metricId, $idSite);
        }
        $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, $formatted);
    }

    /** a row's visitor log keeps the exploration's own segment */
    private function prefixRowSegments(DataTable $table, string $savedSegment): void
    {
        if (trim($savedSegment) === '') {
            return;
        }
        foreach ($table->getRows() as $row) {
            $rowSegment = $row->getMetadata('segment');
            if (is_string($rowSegment) && $rowSegment !== '') {
                $row->setMetadata('segment', trim($savedSegment) . ';' . $rowSegment);
            }
        }
    }

    /** the number of days of a period as asked (a year 365 or 366), before any bound to the website or today */
    public static function periodDays(int $idSite, string $period, string $date): int
    {
        $built = PeriodFactory::makePeriodFromQueryParams(Site::getTimezoneFor($idSite), $period, $date);
        return (int) round((strtotime($built->getDateEnd()->toString() . ' 00:00:00 UTC') - strtotime($built->getDateStart()->toString() . ' 00:00:00 UTC')) / 86400) + 1;
    }

    /**
     * Seconds a report waits for a slot: as long as two statements at the time limit may hold the slots (a two-dimension
     * report runs two), at least 20; a scheduler run (Matomo's e-mails) twice as long, at least 120.
     */
    public static function slotWait(Limits $limits, bool $runningTask): int
    {
        if ($runningTask) {
            return $limits->timeoutSeconds > 0 ? max(120, 4 * $limits->timeoutSeconds + 5) : 120;
        }
        return $limits->timeoutSeconds > 0 ? max(20, 2 * $limits->timeoutSeconds + 5) : 20;
    }

    /**
     * The id of a row's sub table: from the labels of its value path (the first level's value, then the next ones), not
     * its position, so a result calculated again in another order (after the cache) still opens the row the user
     * expanded. A first-level value keeps the id of its label alone. Positive, never 0.
     */
    public static function subtableId(string ...$labels): int
    {
        return (crc32(implode("\x1f", $labels)) & 0x7fffffff) ?: 1;
    }

    /**
     * The saved segment and the request's (dashboard, e-mail), both applying: joined with ";" (AND), which binds weaker
     * than "," (OR) in Matomo's segments, so the saved OR keeps its meaning.
     */
    public static function segment(string $saved, string $requested): string
    {
        $saved = trim($saved);
        $requested = trim($requested);
        if ($saved === '' || $requested === '' || $saved === $requested) {
            return $saved !== '' ? $saved : $requested;
        }
        return $saved . ';' . $requested;
    }

    /**
     * How Matomo may combine each column in a total or an "Others" row of any level: summed only where the rows add up,
     * across the pivot's values too when the pivot is a level (a visit seeing several pages under a page pivot is
     * never summed).
     *
     * @return array<string, string> metric id => "sum" or "skip"
     */
    private function aggregation(int $idSite, ExplorationDefinition $definition): array
    {
        $overcounts = Additivity::overcountUnit($definition, $this->dimensions, $this->metrics, $idSite) !== null;
        $pivot = $definition->pivot !== null ? $this->dimensions->get($idSite, (string) $definition->pivot['dimension']) : null;
        $pivotScope = $pivot !== null ? $this->explorer->pivotScope($definition, $pivot, $idSite) : '';
        $operations = [];
        foreach ($definition->metricIds as $metricId) {
            $metric = MetricCatalog::byId($metricId);
            $sums = !$overcounts && $metric !== null && $metric->addsUp()
                && ($pivot === null || Additivity::addsUpAcross($pivot, $this->metrics->get($metricId, $idSite) ?? $metric, $pivotScope));
            $operations[$metricId] = $sums ? 'sum' : 'skip';
        }
        return $operations;
    }

    /**
     * One level of the tree: the roots, or the next level of the row whose value path has the sub table id (empty when
     * none has it, e.g. after the data changed).
     */
    private static function level(array $tree, int $idSubtable): DataTable
    {
        [$nodes, $path] = $idSubtable > 0 ? (self::find($tree, [], $idSubtable) ?? [[], []]) : [$tree, []];
        $table = new DataTable();
        foreach ($nodes as $node) {
            $row = new Row([Row::COLUMNS => ['label' => $node['label']] + $node['columns'], Row::METADATA => $node['metadata']]);
            if ($node['children'] !== []) {
                $row->setNonLoadedSubtableId(self::subtableId(...array_merge($path, [$node['label']])));
            }
            $table->addRow($row);
        }
        return $table;
    }

    /** @return array{0: list<array>, 1: list<string>}|null the next level of the row with that sub table id, and its path */
    private static function find(array $nodes, array $path, int $idSubtable): ?array
    {
        foreach ($nodes as $node) {
            if ($node['children'] === []) {
                continue;
            }
            $nodePath = array_merge($path, [$node['label']]);
            if (self::subtableId(...$nodePath) === $idSubtable) {
                return [$node['children'], $nodePath];
            }
            $found = self::find($node['children'], $nodePath, $idSubtable);
            if ($found !== null) {
                return $found;
            }
        }
        return null;
    }
}
