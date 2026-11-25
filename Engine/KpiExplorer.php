<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Date;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Saved\KpiCardSettings;
use Piwik\Site;

/**
 * A KPI card's numbers (design 2026-11-24): the metric's total for the period, its comparison and its line.
 *
 * The number and every comparison day or period are their own exact queries, the exploration's total row for those
 * days (Explorer::fetchRows totals, its guards and cache); the line is one query, the metric per day. A comparison
 * span before the website's first complete day of raw data (its creation, or the raw-data retention) and a value
 * without data (a rate without visits) are missing: the average takes the others. A refused line leaves the number
 * and the comparison and says why.
 */
final class KpiExplorer
{
    public function __construct(
        private readonly DimensionCatalog $dimensions,
        private readonly MetricCatalog $metrics,
        private readonly Limits $limits
    ) {
    }

    /**
     * @param string $nowLocal Y-m-d H:i:s in the website's timezone
     * @return array{metric: string, metricName: string, unit: string, value: int|float|null, changeAs: string, partial: bool, until: ?string,
     *     firstDay: string, lastDay: string, comparison: ?array, comparisonNote: ?string, line: ?list<array>, lineNote: ?string,
     *     lowerIsBetter: bool}
     */
    public function card(int $idSite, string $period, string $date, ExplorationDefinition $definition, string $segment, KpiCardSettings $settings, string $nowLocal): array
    {
        $window = ExplorationWindow::for($idSite, $period, $date, $this->limits);
        $value = KpiCalendar::value($period, $window->firstDay, $window->lastDay, $nowLocal);
        $spans = KpiCalendar::comparisons($period, $value, $settings->compare);
        $plain = $definition->withoutPivot();
        $dimensions = array_map(fn (string $id) => $this->dimensions->get($idSite, $id), $plain->dimensionIds);
        $explorer = new Explorer($this->dimensions, $this->metrics, $this->limits);
        $metric = $settings->metric;
        $earliest = self::earliestDay($idSite);

        $slot = new LazySlot($this->limits);
        try {
            $number = $this->total($explorer, $idSite, $value, $plain, $dimensions, $segment, $slot, $metric);
            $compared = [];
            $refused = null;
            foreach ($spans as $span) {
                $spanValue = null;
                if ($span->firstDay >= $earliest) {
                    try {
                        $spanValue = $this->total($explorer, $idSite, $span, $plain, $dimensions, $segment, $slot, $metric);
                    } catch (ExplorationException $e) {
                        // a comparison the guards refuse (a longer month than the plugin's days, the time limit, a busy
                        // database) is missing; the card keeps its number
                        $refused ??= $e->getMessage();
                    }
                }
                $compared[] = ['firstDay' => $span->firstDay, 'lastDay' => $span->lastDay, 'value' => $spanValue];
            }
            $line = null;
            $lineNote = null;
            if ($settings->line > 0) {
                try {
                    $line = $this->line($explorer, $idSite, $value, $settings->line, $plain, $dimensions, $segment, $slot, $metric, $earliest, $period === 'day' ? $spans : []);
                } catch (ExplorationException $e) {
                    $lineNote = $e->getMessage();
                }
            }
        } finally {
            $slot->release();
        }

        [$comparison, $comparisonNote] = self::comparison($settings, $period, $number, $compared);
        if ($comparison === null && $refused !== null) {
            $comparisonNote = $refused;
        }
        $scope = $plain->scope($this->dimensions, $this->metrics, $idSite);
        $unit = MetricCatalog::meta($metric)['unit'] ?? 'number';
        return [
            'metric' => $metric,
            'metricName' => $explorer->metricName($metric, $scope, $idSite),
            'unit' => (string) $unit,
            'value' => $number,
            'changeAs' => $settings->change,
            'partial' => $value->until !== null,
            'until' => $value->until === null ? null : substr($value->until, 0, 5),
            'firstDay' => $value->firstDay,
            'lastDay' => $window->lastDay,
            'comparison' => $comparison,
            'comparisonNote' => $comparisonNote,
            'line' => $line,
            'lineNote' => $lineNote,
            'lowerIsBetter' => MetricCatalog::lowerIsBetter($metric),
        ];
    }

    /** The metric over a span: the exploration's total row for those days. */
    private function total(Explorer $explorer, int $idSite, KpiSpan $span, ExplorationDefinition $definition, array $dimensions, string $segment, LazySlot $slot, string $metric)
    {
        $window = ExplorationWindow::span($idSite, $span, $this->limits);
        $totals = $explorer->fetchRows($idSite, $window, $definition, $dimensions, $segment, $slot, null, true)[0] ?? [];
        return Explorer::metricValue($metric, $totals[$metric] ?? null);
    }

    /**
     * The metric per day over the line's days (one query), the comparison days marked; days before the earliest
     * complete day have no value.
     *
     * @param list<KpiSpan> $marked the day comparisons
     * @return list<array{day: string, value: int|float|null, baseline: bool}>
     */
    private function line(Explorer $explorer, int $idSite, KpiSpan $value, int $days, ExplorationDefinition $definition, array $dimensions, string $segment, LazySlot $slot, string $metric, string $earliest, array $marked): array
    {
        $lineDays = KpiCalendar::lineDays($value, $days);
        $window = ExplorationWindow::span($idSite, new KpiSpan($lineDays[0], $value->lastDay, $value->until), $this->limits);
        $byDay = [];
        foreach ($explorer->fetchRows($idSite, $window, $definition, $dimensions, $segment, $slot, null, true, $this->dimensions->get($idSite, 'date')) as $row) {
            $byDay[(string) $row['fwde_t']] = $row;
        }
        $markedDays = array_map(static fn (KpiSpan $span): string => $span->firstDay, $marked);
        return array_map(static fn (string $day): array => [
            'day' => $day,
            'value' => $day < $earliest ? null : Explorer::metricValue($metric, $byDay[$day][$metric] ?? null),
            'baseline' => in_array($day, $markedDays, true),
        ], $lineDays);
    }

    /**
     * The comparison of the number with the mean of the comparison values that have one, or the reason there is none.
     *
     * @param list<array{firstDay: string, lastDay: string, value: int|float|null}> $compared
     * @return array{0: ?array, 1: ?string}
     */
    private static function comparison(KpiCardSettings $settings, string $period, $number, array $compared): array
    {
        if ($settings->compare === 'none') {
            return [null, null];
        }
        if ($compared === []) {
            return [null, Piwik::translate('FeinwerkDataExploration_KpiNoComparisonRange')];
        }
        $values = array_values(array_filter(array_column($compared, 'value'), static fn ($v) => $v !== null));
        if ($values === []) {
            return [null, Piwik::translate('FeinwerkDataExploration_KpiNoComparisonData')];
        }
        // one value is the baseline as it is (a count stays whole); several are averaged
        $baseline = count($values) === 1 ? $values[0] : array_sum($values) / count($values);
        $change = $number === null || (float) $baseline === 0.0 ? null : ($number - $baseline) / $baseline;
        return [[
            'kind' => $settings->compare,
            'period' => $period,
            'baseline' => $baseline,
            'used' => count($values),
            'wanted' => count($compared),
            'spans' => $compared,
            'change' => $change,
            'absolute' => $number === null ? null : $number - $baseline,
        ], null];
    }

    /** The first day with complete raw data: the website's creation day, or later with deleted raw data. */
    private static function earliestDay(int $idSite): string
    {
        $timezone = Site::getTimezoneFor($idSite);
        // the creation time is UTC: its day in the website's timezone
        $created = Date::factory(Site::getCreationDateFor($idSite), $timezone)->toString('Y-m-d');
        $retained = RawDataRetention::earliestCompleteDay($idSite);
        return $retained !== null && $retained > $created ? $retained : $created;
    }
}
