<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Archive;

use Piwik\Archive;
use Piwik\Cache;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Db;
use Piwik\Period;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Additivity;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Explorer;
use Piwik\Plugins\FeinwerkDataExploration\Engine\LimitsProvider;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ReportLevels;
use Piwik\Site;

/**
 * An archived exploration for any period (design 2026-11-25): Matomo's record of the period (its days added up), the
 * level of $idSubtable with Matomo's own sub table ids, each rate and average from its pair, distinct counts only for a
 * day, labels formatted from the stored values, the conditions applied to the period, the saved sort on every level
 * and the saved row limit on the first one.
 */
final class ArchivedReportReader
{
    /** @var array<string, DimensionDefinition|null> */
    private array $dimensionCache = [];

    public function __construct(private readonly ?DimensionCatalog $catalog = null)
    {
    }

    /**
     * How many of the period's days are archived, and how many need to be: the period's days from the website's
     * creation to today (website's timezone), or, when fewer are archived, the days with visits among them (Matomo
     * runs no plugin's archiving for a day without visits, so such a day has no marker and needs none) and the days
     * Matomo has not archived yet. The period is complete when the first number reaches the second.
     *
     * @return array{0: int, 1: int}
     */
    public function coverage(ArchivedExploration $exploration, string $period, string $date, string $segment): array
    {
        $name = $exploration->markerName();
        $covered = (int) round(self::numeric(Archive::build($exploration->idSite, $period, $date, $segment)->getNumeric($name), $name));
        [$start, $end] = self::span($exploration->idSite, $period, $date);
        $days = $end < $start ? 0 : (int) round((strtotime($end . ' 00:00:00 UTC') - strtotime($start . ' 00:00:00 UTC')) / 86400) + 1;
        if ($covered >= $days || $days === 0) {
            return [$covered, $days];
        }
        $withVisits = 0;
        $unknown = [];
        $visits = Archive::build($exploration->idSite, 'day', $start . ',' . $end, $segment)->getDataTableFromNumeric('nb_visits');
        $tables = $visits instanceof DataTable\Map ? $visits->getDataTables() : [$start => $visits];
        foreach ($tables as $day => $table) {
            $row = $table instanceof DataTable ? $table->getFirstRow() : false;
            if ($row !== false && (float) $row->getColumn('nb_visits') > 0) {
                $withVisits++;
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $day)) {
                $unknown[] = (string) $day;
            }
        }
        // a day the archive counts no visits for: not archived yet (cron archiving before its run, imported logs) or
        // really without visits; the raw data tells, so a short period with such a day stays live instead of empty
        // (release review 2026-11-26)
        return [$covered, $withVisits + self::daysWithRawVisits($exploration->idSite, $unknown)];
    }

    /**
     * Which archive answers a period (sandbox check 2026-11-26): its own, or, when that is incomplete, a range of the
     * same days if that covers more. Matomo 5 rebuilds a period that contains today for all plugins without a plugin's
     * newer partial day archives, so the current year lost the explorations' records; a range is built per plugin.
     *
     * @return array{0: string, 1: string, 2: int, 3: int} period, date, covered days, needed days
     */
    public function source(ArchivedExploration $exploration, string $period, string $date, string $segment): array
    {
        // once per request: a flat or expanded report asks every sub table (final review 2026-11-26)
        $cache = Cache::getTransientCache();
        $key = self::sourceKey($exploration, $period, $date, $segment);
        $cached = $cache->fetch($key);
        if (is_array($cached)) {
            return $cached;
        }
        $source = $this->chooseSource($exploration, $period, $date, $segment);
        $cache->save($key, $source);
        return $source;
    }

    public static function sourceKey(ArchivedExploration $exploration, string $period, string $date, string $segment): string
    {
        return 'FeinwerkDataExploration_source_' . md5((string) json_encode([$exploration->recordName(), $period, $date, $segment]));
    }

    /** @return array{0: string, 1: string, 2: int, 3: int} */
    private function chooseSource(ArchivedExploration $exploration, string $period, string $date, string $segment): array
    {
        [$covered, $needed] = $this->coverage($exploration, $period, $date, $segment);
        $own = [$period, $date, $covered, $needed];
        if ($covered >= $needed || $period === 'day' || $period === 'range') {
            return $own;
        }
        [$start, $end] = self::span($exploration->idSite, $period, $date);
        if ($end < $start) {
            return $own;
        }
        $range = $start . ',' . $end;
        [$rangeCovered, $rangeNeeded] = $this->coverage($exploration, 'range', $range, $segment);
        return self::better($own, ['range', $range, $rangeCovered, $rangeNeeded]);
    }

    /**
     * The source that covers more of its days (the range only when strictly better).
     *
     * @param array{0: string, 1: string, 2: int, 3: int} $own
     * @param array{0: string, 1: string, 2: int, 3: int} $range
     * @return array{0: string, 1: string, 2: int, 3: int}
     */
    public static function better(array $own, array $range): array
    {
        if ($range[3] <= 0) {
            return $own;
        }
        $ownShare = $own[3] > 0 ? $own[2] / $own[3] : 1.0;
        return $range[2] / $range[3] > $ownShare ? $range : $own;
    }

    /** Matomo answers a numeric record by name (with _metadata), or with the value alone */
    private static function numeric(mixed $value, string $name): float
    {
        $value = is_array($value) ? ($value[$name] ?? 0) : $value;
        return is_numeric($value) ? (float) $value : 0.0;
    }

    /**
     * @param array{match: string, groups: list<array>}|null $readFilters the conditions applied to the period
     */
    public function read(ArchivedExploration $exploration, string $period, string $date, string $segment, int $idSubtable, ?array $readFilters): DataTable
    {
        try {
            $table = Archive::build($exploration->idSite, $period, $date, $segment)
                ->getDataTable($exploration->recordName(), $idSubtable > 0 ? $idSubtable : null);
        } catch (\Throwable $e) {
            // a sub table id of an older version or of a row that is gone
            return new DataTable();
        }
        if (!$table instanceof DataTable) {
            return new DataTable();
        }
        $definition = $exploration->definition;
        $isDay = $period === 'day' && !Period::isMultiplePeriod($date, $period);
        $first = $table->getFirstRow();
        if ($definition->pivot !== null && $first !== false
            && (int) $first->getColumn(DayTree::LEVEL) === count($definition->dimensionIds) + 1) {
            return $this->pivotLevel($table, $exploration, $period, $date, $segment, $isDay);
        }
        // an upper level (release review 2026-11-26): number conditions apply to the combinations, as live; a row stays
        // when one of its combinations passes, with its own totals
        $byCombinations = $readFilters !== null && $first !== false
            && (int) $first->getColumn(DayTree::LEVEL) < count($definition->dimensionIds)
            ? $this->rowsWithAPassingCombination($exploration, $period, $date, $segment, $idSubtable, $readFilters, $isDay)
            : null;
        foreach ($table->getRows() as $id => $row) {
            $stored = (string) $row->getColumn('label');
            $this->prepare($row, $exploration, $isDay);
            if ($byCombinations !== null ? !isset($byCombinations[$stored]) : ($readFilters !== null && !$this->keeps($row, $readFilters))) {
                $table->deleteRow($id);
                continue;
            }
            // the level was only for the filters; it is no part of the report (the API would show it)
            $row->deleteMetadata(DayTree::LEVEL);
            $row->deleteMetadata('fwde_empty');
        }
        $sort = $this->sortColumn($definition, $isDay);
        $table->filter('Sort', [$sort, strtolower($definition->sortOrder) === 'asc' ? 'asc' : 'desc', $sort === 'label']);
        if ($idSubtable <= 0 && $definition->limit > 0) {
            $table->filter('Limit', [0, $definition->limit]);
        }
        return $table;
    }

    /**
     * The stored labels of an upper level's rows that have a combination (a row of the deepest row level below them)
     * passing the read conditions, from one expanded read of the level.
     *
     * @return array<string, true>
     */
    private function rowsWithAPassingCombination(ArchivedExploration $exploration, string $period, string $date, string $segment, int $idSubtable, array $readFilters, bool $isDay): array
    {
        try {
            $tree = Archive::build($exploration->idSite, $period, $date, $segment)
                ->getDataTableExpanded($exploration->recordName(), $idSubtable > 0 ? $idSubtable : null, null, false);
        } catch (\Throwable $e) {
            return [];
        }
        $passing = [];
        foreach ($tree instanceof DataTable ? $tree->getRows() : [] as $row) {
            $stored = (string) $row->getColumn('label');
            if ($this->hasPassingCombination($row, $exploration, $readFilters, $isDay)) {
                $passing[$stored] = true;
            }
        }
        return $passing;
    }

    private function hasPassingCombination(DataTable\Row $row, ArchivedExploration $exploration, array $readFilters, bool $isDay): bool
    {
        $level = (int) $row->getColumn(DayTree::LEVEL);
        $subtable = $row->getSubtable();
        if ($level >= count($exploration->definition->dimensionIds) || !$subtable instanceof DataTable) {
            $this->prepare($row, $exploration, $isDay);
            return $this->keeps($row, $readFilters);
        }
        foreach ($subtable->getRows() as $child) {
            if ($this->hasPassingCombination($child, $exploration, $readFilters, $isDay)) {
                return true;
            }
        }
        return false;
    }

    /**
     * The pivot level under one row (rows and pivot, archived, Peter: "2a"): the period's top values (from the totals
     * per value Matomo added up for the period; every weekday in order for the weekday pivot) under every row, a value
     * the row lacks with the metric's empty value, then "Others" with the row's rest where every metric adds up across
     * the values, as the live report (Engine/ReportLevels::pivotLevel).
     */
    private function pivotLevel(DataTable $cells, ArchivedExploration $exploration, string $period, string $date, string $segment, bool $isDay): DataTable
    {
        $definition = $exploration->definition;
        $idSite = $exploration->idSite;
        $catalog = $this->catalog ?? new DimensionCatalog();
        $pivot = $catalog->get($idSite, (string) $definition->pivot['dimension']);
        $limit = (int) $definition->pivot['limit'];
        $first = $definition->metricIds[0];

        $values = [];
        if ($pivot !== null && $pivot->id === ExplorationDefinition::PIVOT_WEEKDAY) {
            foreach (range(1, 7) as $day) {
                $values[] = ['label' => (string) $day, 'raw' => (string) $day];
            }
            $cut = false;
        } else {
            $totals = Archive::build($idSite, $period, $date, $segment)->getDataTable($exploration->pivotTotalsName());
            foreach ($totals instanceof DataTable ? $totals->getRows() : [] as $row) {
                $label = (string) $row->getColumn('label');
                if ($label === (string) DataTable::LABEL_SUMMARY_ROW) {
                    continue;
                }
                $stored = $row->getMetadata(DayTree::RAW);
                $values[] = ['label' => $label, 'raw' => is_string($stored) ? $stored : DayTree::raw($label),
                    'value' => (float) StoredMetrics::value($first, $row->getColumns(), $isDay)];
            }
            usort($values, static fn (array $a, array $b): int => [$b['value'], $a['label']] <=> [$a['value'], $b['label']]);
            $cut = count($values) > $limit;
            $values = array_slice($values, 0, $limit);
        }

        $byLabel = [];
        foreach ($cells->getRows() as $row) {
            $byLabel[(string) $row->getColumn('label')] = $row->getColumns();
        }
        $out = new DataTable();
        $shown = [];
        foreach ($values as $value) {
            $label = DayTree::label(ReportLevels::fold($value['raw']));
            $shown[$label] = true;
            $columns = $byLabel[$label] ?? null;
            $row = ['label' => $pivot !== null ? $pivot->formatLabel($value['raw'], $idSite) : (string) $value['raw']];
            foreach ($definition->metricIds as $metricId) {
                $row[$metricId] = $columns === null ? Explorer::metricValue($metricId, null) : StoredMetrics::value($metricId, $columns, $isDay);
            }
            $out->addRow(new DataTable\Row([DataTable\Row::COLUMNS => $row]));
        }

        $metrics = new MetricCatalog();
        $pivotScope = $pivot !== null ? (new Explorer($catalog, $metrics, (new LimitsProvider())->get()))->pivotScope($definition, $pivot, $idSite) : '';
        $othersMetrics = $pivot === null || $pivot->id === ExplorationDefinition::PIVOT_WEEKDAY ? [] : array_filter(
            $definition->metricIds,
            static fn (string $id): bool => ($metric = $metrics->get($id, $idSite)) !== null && Additivity::addsUpAcross($pivot, $metric, $pivotScope)
        );
        if ($cut && $othersMetrics !== [] && count($othersMetrics) === count($definition->metricIds)) {
            $rest = [];
            foreach ($byLabel as $label => $columns) {
                if (isset($shown[$label])) {
                    continue;
                }
                foreach ($columns as $column => $number) {
                    if ($column !== 'label' && $column !== DayTree::LEVEL && is_numeric($number)) {
                        $rest[$column] = ($rest[$column] ?? 0) + $number;
                    }
                }
            }
            $row = ['label' => Piwik::translate('General_Others')];
            foreach ($definition->metricIds as $metricId) {
                $row[$metricId] = StoredMetrics::value($metricId, $rest, $isDay);
            }
            $out->addRow(new DataTable\Row([DataTable\Row::COLUMNS => $row]));
        }
        return $out;
    }

    /** a row's label formatted, its stored value kept, its metrics computed, the stored columns removed */
    private function prepare(DataTable\Row $row, ArchivedExploration $exploration, bool $isDay): void
    {
        $columns = $row->getColumns();
        $level = max(1, (int) ($columns[DayTree::LEVEL] ?? 1));
        $label = (string) $row->getColumn('label');
        if ($label === (string) DataTable::LABEL_SUMMARY_ROW) {
            $row->setColumn('label', Piwik::translate('General_Others'));
        } else {
            $stored = $row->getMetadata(DayTree::RAW);
            $raw = is_string($stored) ? $stored : DayTree::raw($label);
            $row->deleteMetadata(DayTree::RAW);
            $dimension = $this->dimension($exploration, $level);
            $row->setColumn('label', $dimension !== null ? $dimension->formatLabel($raw, $exploration->idSite) : (string) $raw);
            $row->setMetadata($level === 1 ? 'fwde_value' : 'fwde_value' . $level, $raw);
            // the visitor log of a first-level row (a deeper row read by its sub table id knows no parent value)
            $rowSegment = $level === 1 && $dimension !== null ? $dimension->segmentFor($raw) : null;
            if ($rowSegment !== null) {
                $row->setMetadata('segment', $rowSegment);
            }
            if ($dimension !== null && ($raw === null || $raw === '' || $row->getColumn('label') === $dimension->formatLabel(null, $exploration->idSite))) {
                $row->setMetadata('fwde_empty', true);
            }
        }
        $row->setMetadata(DayTree::LEVEL, $level);
        foreach ($exploration->definition->metricIds as $metricId) {
            $value = StoredMetrics::value($metricId, $columns, $isDay);
            foreach (StoredMetrics::columns($metricId) as $stored) {
                $row->deleteColumn($stored);
            }
            if ($value !== null) {
                $row->setColumn($metricId, $value);
            }
        }
        $row->deleteColumn(DayTree::LEVEL);
    }

    private function keeps(DataTable\Row $row, array $readFilters): bool
    {
        $level = (int) $row->getMetadata(DayTree::LEVEL);
        $column = ExplorationDefinition::LABEL_COLUMNS[$level - 1] ?? null;
        $text = function (string $target) use ($row, $column): ?string {
            if ($target !== $column || $row->getMetadata('fwde_empty')) {
                return null;
            }
            return (string) $row->getColumn('label');
        };
        $value = static fn (string $metricId) => is_numeric($v = $row->getColumn($metricId)) ? $v + 0 : null;
        return ReadFilter::keep($readFilters, $text, $value);
    }

    /** the saved sort; a distinct metric outside a day sorts by the first stored sum, a label column by the label */
    private function sortColumn(ExplorationDefinition $definition, bool $isDay): string
    {
        $sort = $definition->sortColumn;
        if (in_array($sort, ExplorationDefinition::LABEL_COLUMNS, true)) {
            return 'label';
        }
        if (in_array($sort, $definition->metricIds, true) && ($isDay || StoredMetrics::value($sort, [$sort => 1], false) !== null)) {
            return $sort;
        }
        foreach ($definition->metricIds as $metricId) {
            if (StoredMetrics::value($metricId, [$metricId => 1], false) !== null) {
                return $metricId;
            }
        }
        return $definition->metricIds[0] ?? 'label';
    }

    private function dimension(ArchivedExploration $exploration, int $level): ?DimensionDefinition
    {
        $id = $exploration->definition->dimensionIds[$level - 1] ?? null;
        if ($id === null) {
            return null;
        }
        $key = $exploration->idSite . '/' . $id;
        if (!array_key_exists($key, $this->dimensionCache)) {
            $this->dimensionCache[$key] = ($this->catalog ?? new DimensionCatalog())->get($exploration->idSite, $id);
        }
        return $this->dimensionCache[$key];
    }

    /**
     * How many of these days (the website's timezone) have visits in the raw data: one indexed query (idsite, visit time)
     * for all of them.
     *
     * @param list<string> $days
     */
    private static function daysWithRawVisits(int $idSite, array $days): int
    {
        if ($days === []) {
            return 0;
        }
        $timezone = Site::getTimezoneFor($idSite);
        $cases = [];
        $ranges = [];
        $caseBind = [];
        $rangeBind = [];
        foreach (array_values($days) as $n => $day) {
            $from = Date::factory($day . ' 00:00:00', $timezone)->getDatetime();
            $to = Date::factory($day . ' 00:00:00', $timezone)->addDay(1)->getDatetime();
            $cases[] = 'WHEN visit_last_action_time >= ? AND visit_last_action_time < ? THEN ' . $n;
            $ranges[] = '(visit_last_action_time >= ? AND visit_last_action_time < ?)';
            array_push($caseBind, $from, $to);
            array_push($rangeBind, $from, $to);
        }
        $rows = Db::fetchAll(
            'SELECT CASE ' . implode(' ', $cases) . ' END AS fwde_day FROM ' . Common::prefixTable('log_visit')
                . ' WHERE idsite = ? AND (' . implode(' OR ', $ranges) . ') GROUP BY fwde_day',
            array_merge($caseBind, [$idSite], $rangeBind)
        );
        return count($rows);
    }

    /**
     * The period's first and last day from the website's creation to today, in the website's timezone.
     *
     * @return array{0: string, 1: string}
     */
    private static function span(int $idSite, string $period, string $date): array
    {
        $timezone = Site::getTimezoneFor($idSite);
        $built = PeriodFactory::makePeriodFromQueryParams($timezone, $period, $date);
        $created = substr((string) Site::getCreationDateFor($idSite), 0, 10);
        $today = Date::factory('today', $timezone)->toString();
        return [max($built->getDateStart()->toString(), $created), min($built->getDateEnd()->toString(), $today)];
    }
}
