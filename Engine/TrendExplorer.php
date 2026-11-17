<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\Comparison;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;

/**
 * The trend of an exploration (trend design): the first rows of the table per day or hour, or with comparisons the
 * total of each comparison column per day or hour. A period of one day has hours, a longer one days, counted as the
 * dimensions "Hour" and "Date" count them. Every unit of a line's period is in the result, in order; a unit without
 * data has 0 for counts and sums and no value for the other metrics.
 */
final class TrendExplorer
{
    public const UNIT_DAY = 'day';
    public const UNIT_HOUR = 'hour';

    private const MAX_LINES = 10;

    /** now in the site's time ("YYYY-MM-DD HH"), set per trend() */
    private string $now = '';

    public function __construct(
        private readonly DimensionCatalog $dimensions,
        private readonly MetricCatalog $metrics,
        private readonly Limits $limits
    ) {
    }

    /** Lines of a line chart: the free version has 5, another plugin may allow up to 10. */
    public static function maxLines(Limits $limits): int
    {
        return min(self::MAX_LINES, max(1, $limits->maxTrendLines));
    }

    /**
     * Every unit of a period: its days (YYYY-MM-DD) or the hours "0" to "23".
     *
     * @return list<string>
     */
    public static function units(string $unit, string $firstDay, string $lastDay): array
    {
        if ($unit === self::UNIT_HOUR) {
            return array_map('strval', range(0, 23));
        }
        $utc = new \DateTimeZone('UTC');
        $day = new \DateTimeImmutable($firstDay . ' 12:00:00', $utc);
        $last = new \DateTimeImmutable($lastDay . ' 12:00:00', $utc);
        $units = [];
        while ($day <= $last && count($units) < 400) {
            $units[] = $day->format('Y-m-d');
            $day = $day->modify('+1 day');
        }
        return $units;
    }

    /**
     * Whether a day or hour lies after now (site time, "YYYY-MM-DD HH"): it has no data yet, so no value, not 0.
     *
     * @param string $day the day of an hour (hour unit; ignored for days)
     */
    public static function isFuture(string $unit, string $time, string $day, string $now): bool
    {
        $today = substr($now, 0, 10);
        if ($unit === self::UNIT_DAY) {
            return $time > $today;
        }
        return $day > $today || ($day === $today && (int) $time > (int) substr($now, 11, 2));
    }

    /**
     * Rows (one per line and unit): label (and secondary_label for two dimensions) of the table row, or the title of
     * the comparison column; time; the definition's metrics. Metadata fwde_line (0-based), fwde_key (raw keys of the
     * table row, JSON) or fwde_kind (own, segment, period, both).
     *
     * @param list<Comparison> $comparisons
     */
    public function trend(int $idSite, string $period, string $date, ExplorationDefinition $definition, string $segment, array $comparisons = []): DataTable
    {
        $window = ExplorationWindow::for($idSite, $period, $date, $this->limits);
        $dimensions = array_map(fn (string $id) => $this->dimensions->get($idSite, $id), $definition->dimensionIds);
        $unit = $window->firstDay === $window->lastDay ? self::UNIT_HOUR : self::UNIT_DAY;
        $explorer = new Explorer($this->dimensions, $this->metrics, $this->limits);
        $slot = new LazySlot($this->limits);
        $this->now = Date::now()->setTimezone($window->timezone)->toString('Y-m-d H');
        try {
            $table = $comparisons === []
                ? $this->rowLines($explorer, $idSite, $window, $definition, $dimensions, $segment, $unit, $slot)
                : $this->columnLines($explorer, $idSite, $period, $date, $window, $definition, $dimensions, $segment, $unit, $comparisons, $slot);
        } finally {
            $slot->release();
        }
        $table->setMetadata(DataTable::GENERIC_FILTERS_TO_DISABLE_METADATA_NAME, ['Sort', 'Limit', 'Truncate']);
        return $table;
    }

    /** The first rows of the table (in its sort and with its text filters) per unit, without its number filters. */
    private function rowLines(Explorer $explorer, int $idSite, ExplorationWindow $window, ExplorationDefinition $definition, array $dimensions, string $segment, string $unit, LazySlot $slot): DataTable
    {
        $twoDimensions = count($dimensions) === 2;
        $own = $explorer->fetchRows($idSite, $window, $definition, $dimensions, $segment, $slot);
        $own = array_slice($this->inShownOrder($own, $definition, $dimensions, $idSite), 0, self::maxLines($this->limits));
        $keys = array_map(static fn (array $row): array => Explorer::rowKeys($row, $twoDimensions), $own);
        $values = [];
        foreach ($explorer->fetchRows($idSite, $window, $definition, $dimensions, $segment, $slot, $keys, false, $this->timeDimension($idSite, $unit)) as $row) {
            $values[(int) $row['fwde_row']][(string) $row['fwde_t']] = $row;
        }

        $table = new DataTable();
        $units = self::units($unit, $window->firstDay, $window->lastDay);
        foreach ($keys as $line => $rowKeys) {
            $labels = ['label' => $dimensions[0]->formatLabel($rowKeys[0], $idSite)];
            if ($twoDimensions) {
                $labels['secondary_label'] = $dimensions[1]->formatLabel($rowKeys[1], $idSite);
            }
            $this->addLine($table, $definition, $labels, $unit, $window->firstDay, $units, $values[$line] ?? [], ['fwde_line' => $line, 'fwde_key' => json_encode($rowKeys)]);
        }
        return $table;
    }

    /**
     * The own view's total (line 0) and each comparison's total (line n) per unit, each in its own period: the page
     * lines them up by position. In hours a compared period counts its first day.
     *
     * @param list<Comparison> $comparisons
     */
    private function columnLines(Explorer $explorer, int $idSite, string $period, string $date, ExplorationWindow $window, ExplorationDefinition $definition, array $dimensions, string $segment, string $unit, array $comparisons, LazySlot $slot): DataTable
    {
        $time = $this->timeDimension($idSite, $unit);
        $table = new DataTable();
        $own = $this->byTime($explorer->fetchRows($idSite, $window, $definition, $dimensions, $segment, $slot, null, true, $time));
        $this->addLine(
            $table,
            $definition,
            ['label' => ComparisonTitles::column($idSite, ComparisonTable::KIND_SEGMENT, $segment, $period, $date)],
            $unit,
            $window->firstDay,
            self::units($unit, $window->firstDay, $window->lastDay),
            $own,
            ['fwde_line' => 0, 'fwde_kind' => 'own']
        );

        foreach ($comparisons as $i => $comparison) {
            $kind = ComparisonTable::kind($comparison, $segment, $period, $date);
            try {
                $columnWindow = ExplorationWindow::for($idSite, $comparison->period, $comparison->date, $this->limits);
                $kind = ComparisonTable::kindOf($comparison, $segment, $columnWindow->startUtc === $window->startUtc && $columnWindow->endUtc === $window->endUtc);
                if ($unit === self::UNIT_HOUR && $columnWindow->firstDay !== $columnWindow->lastDay) {
                    $columnWindow = ExplorationWindow::for($idSite, 'day', $columnWindow->firstDay, $this->limits);
                }
                $values = $this->byTime($explorer->fetchRows($idSite, $columnWindow, $definition, $dimensions, $comparison->segment, $slot, null, true, $time));
            } catch (\Throwable $e) {
                $title = ComparisonTitles::column($idSite, $kind, $comparison->segment, $comparison->period, $comparison->date);
                $code = $e instanceof ExplorationException && $e->getCode() > 0 ? $e->getCode() : 400;
                throw new ExplorationException('FeinwerkDataExploration_ErrorComparisonColumn', [$title, $e->getMessage()], $code);
            }
            $this->addLine(
                $table,
                $definition,
                ['label' => ComparisonTitles::column($idSite, $kind, $comparison->segment, $comparison->period, $comparison->date)],
                $unit,
                $columnWindow->firstDay,
                self::units($unit, $columnWindow->firstDay, $columnWindow->lastDay),
                $values,
                ['fwde_line' => $i + 1, 'fwde_kind' => $kind]
            );
        }
        return $table;
    }

    /** @return array<string, array> rows by their time key */
    private function byTime(array $rows): array
    {
        $byTime = [];
        foreach ($rows as $row) {
            $byTime[(string) $row['fwde_t']] = $row;
        }
        return $byTime;
    }

    /**
     * One row per unit of the line, a missing unit counted as Explorer::metricValue() counts a missing row; a unit after
     * now has no value at all.
     *
     * @param string $firstDay the line's first day (the day of its hours)
     * @param list<string> $units
     * @param array<string, array> $values by unit
     */
    private function addLine(DataTable $table, ExplorationDefinition $definition, array $labels, string $unitKind, string $firstDay, array $units, array $values, array $metadata): void
    {
        foreach ($units as $unit) {
            $columns = $labels + ['time' => $unit];
            $future = self::isFuture($unitKind, $unit, $firstDay, $this->now);
            foreach ($definition->metricIds as $metricId) {
                $columns[$metricId] = $future ? null : Explorer::metricValue($metricId, $values[$unit][$metricId] ?? null);
            }
            $table->addRow(new DataTable\Row([DataTable\Row::COLUMNS => $columns, DataTable\Row::METADATA => $metadata]));
        }
    }

    /**
     * The rows in the order the table shows them: sorted by a label, a complete result is sorted by the page by the
     * visible label (case-insensitive, numbers in natural order, vue/src/sorting.ts), not by the stored value.
     */
    private function inShownOrder(array $rows, ExplorationDefinition $definition, array $dimensions, int $idSite): array
    {
        $labelColumns = [ExplorationDefinition::SORT_LABEL => 0, ExplorationDefinition::SORT_SECONDARY_LABEL => 1];
        if (!isset($labelColumns[$definition->sortColumn]) || count($rows) >= $definition->limit) {
            return $rows;
        }
        $index = $labelColumns[$definition->sortColumn];
        $twoDimensions = count($dimensions) === 2;
        $labels = [];
        foreach ($rows as $i => $row) {
            $labels[$i] = $dimensions[$index]->formatLabel(Explorer::rowKeys($row, $twoDimensions)[$index], $idSite);
        }
        $collator = self::collator();
        $direction = $definition->sortOrder === 'asc' ? 1 : -1;
        $order = array_keys($rows);
        usort($order, static function (int $a, int $b) use ($labels, $collator, $direction): int {
            $compared = $collator !== null ? (int) $collator->compare($labels[$a], $labels[$b]) : strnatcasecmp($labels[$a], $labels[$b]);
            return $direction * $compared;
        });
        return array_map(static fn (int $i): array => $rows[$i], $order);
    }

    /** The user's language, case-insensitive with numbers in natural order (as the page's localeCompare). */
    private static function collator(): ?\Collator
    {
        if (!class_exists(\Collator::class)) {
            return null;
        }
        try {
            $collator = new \Collator((string) StaticContainer::get(\Piwik\Translation\Translator::class)->getCurrentLanguage());
            $collator->setStrength(\Collator::PRIMARY);
            $collator->setAttribute(\Collator::NUMERIC_COLLATION, \Collator::ON);
            return $collator;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function timeDimension(int $idSite, string $unit)
    {
        return $this->dimensions->get($idSite, $unit === self::UNIT_HOUR ? 'hour' : 'date');
    }
}
