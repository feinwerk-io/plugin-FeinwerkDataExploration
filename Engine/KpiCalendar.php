<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

/**
 * Which days a KPI card covers (design 2026-11-24), in calendar days of the website's timezone (a clock change never
 * shifts a weekday):
 * - the number: the period, ending now when it includes today ("so far");
 * - the comparison: on a day the day before (previous), 7 days before (weekday) or 7, 14, 21 and 28 days before
 *   (average4); on a week or month the week or month before (previous and weekday) or the four before (average4), each
 *   cut at the same point when the number is "so far" (weeks: the same weekday and time; months: the same number of
 *   elapsed days and time, a shorter month whole); a range has none;
 * - the line: the 7 or 30 days ending with the number's last day.
 */
final class KpiCalendar
{
    /**
     * @param string $period day, week, month or range
     * @param string $nowLocal Y-m-d H:i:s in the website's timezone
     */
    public static function value(string $period, string $firstDay, string $lastDay, string $nowLocal): KpiSpan
    {
        $today = substr($nowLocal, 0, 10);
        if ($firstDay <= $today && $today <= $lastDay) {
            return new KpiSpan($firstDay, $today, substr($nowLocal, 11, 8));
        }
        return new KpiSpan($firstDay, $lastDay);
    }

    /**
     * @param string $compare KpiCardSettings::COMPARES
     * @return list<KpiSpan> oldest last
     */
    public static function comparisons(string $period, KpiSpan $value, string $compare): array
    {
        if ($compare === 'none' || !in_array($period, ['day', 'week', 'month'], true)) {
            return [];
        }
        $steps = $compare === 'average4' ? [1, 2, 3, 4] : [1];
        $spans = [];
        foreach ($steps as $k) {
            if ($period === 'day') {
                $back = $compare === 'previous' ? $k : 7 * $k;
                $day = self::addDays($value->firstDay, -$back);
                $spans[] = new KpiSpan($day, $day, $value->until);
            } elseif ($period === 'week') {
                $spans[] = new KpiSpan(self::addDays($value->firstDay, -7 * $k), self::addDays($value->lastDay, -7 * $k), $value->until);
            } else {
                $spans[] = self::monthBack($value, $k);
            }
        }
        return $spans;
    }

    /** @return list<string> the days of the line, oldest first; none for 0 */
    public static function lineDays(KpiSpan $value, int $days): array
    {
        $line = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $line[] = self::addDays($value->lastDay, -$i);
        }
        return $line;
    }

    /** The month $k months before the number's; cut at the same elapsed days and time when the number is so far. */
    private static function monthBack(KpiSpan $value, int $k): KpiSpan
    {
        $first = (new \DateTimeImmutable(substr($value->firstDay, 0, 7) . '-01', new \DateTimeZone('UTC')))->modify('-' . $k . ' month');
        $firstDay = $first->format('Y-m-d');
        $monthEnd = $first->format('Y-m-t');
        if ($value->until === null) {
            return new KpiSpan($firstDay, $monthEnd);
        }
        $cut = self::addDays($firstDay, self::daysBetween($value->firstDay, $value->lastDay));
        return $cut > $monthEnd ? new KpiSpan($firstDay, $monthEnd) : new KpiSpan($firstDay, $cut, $value->until);
    }

    private static function addDays(string $day, int $days): string
    {
        return (new \DateTimeImmutable($day, new \DateTimeZone('UTC')))->modify(($days >= 0 ? '+' : '') . $days . ' day')->format('Y-m-d');
    }

    private static function daysBetween(string $from, string $to): int
    {
        $utc = new \DateTimeZone('UTC');
        return (int) (new \DateTimeImmutable($from, $utc))->diff(new \DateTimeImmutable($to, $utc))->format('%r%a');
    }
}
