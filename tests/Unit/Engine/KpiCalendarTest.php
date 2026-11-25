<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Engine\KpiCalendar;
use Piwik\Plugins\FeinwerkDataExploration\Engine\KpiSpan;

/**
 * Which days or periods a KPI card's number, comparison and line cover (design 2026-11-24): calendar days in the
 * website's timezone; a span that includes today ends at the current time, and its comparisons at the same point.
 *
 * @group FeinwerkDataExploration
 */
class KpiCalendarTest extends TestCase
{
    private const EARLIER = '2026-11-24 12:00:00';

    public function testDayComparisons(): void
    {
        $day = KpiCalendar::value('day', '2026-11-18', '2026-11-18', self::EARLIER);
        $this->assertSame([['2026-11-18', '2026-11-18', null]], self::spans([$day]));
        $this->assertSame([['2026-11-11', '2026-11-11', null], ['2026-11-04', '2026-11-04', null], ['2026-10-28', '2026-10-28', null],
            ['2026-10-21', '2026-10-21', null]], self::spans(KpiCalendar::comparisons('day', $day, 'average4')));
        $this->assertSame([['2026-11-11', '2026-11-11', null]], self::spans(KpiCalendar::comparisons('day', $day, 'weekday')));
        $this->assertSame([['2026-11-17', '2026-11-17', null]], self::spans(KpiCalendar::comparisons('day', $day, 'previous')));
        $this->assertSame([], KpiCalendar::comparisons('day', $day, 'none'));
    }

    /** review focus 1: today ends now, and so does every comparison day */
    public function testTodayCutsEveryDayAtTheSameLocalTime(): void
    {
        $today = KpiCalendar::value('day', '2026-11-18', '2026-11-18', '2026-11-18 09:30:00');
        $this->assertSame([['2026-11-18', '2026-11-18', '09:30:00']], self::spans([$today]));
        foreach (KpiCalendar::comparisons('day', $today, 'average4') as $span) {
            $this->assertSame('09:30:00', $span->until);
        }
        $this->assertSame('09:30:00', KpiCalendar::comparisons('day', $today, 'previous')[0]->until);
    }

    public function testWeekComparisons(): void
    {
        $week = KpiCalendar::value('week', '2026-11-16', '2026-11-22', self::EARLIER);
        $this->assertSame([['2026-11-09', '2026-11-15', null], ['2026-11-02', '2026-11-08', null], ['2026-10-26', '2026-11-01', null],
            ['2026-10-19', '2026-10-25', null]], self::spans(KpiCalendar::comparisons('week', $week, 'average4')));
        $this->assertSame([['2026-11-09', '2026-11-15', null]], self::spans(KpiCalendar::comparisons('week', $week, 'weekday')));

        $soFar = KpiCalendar::value('week', '2026-11-16', '2026-11-22', '2026-11-18 09:30:00');
        $this->assertSame([['2026-11-16', '2026-11-18', '09:30:00']], self::spans([$soFar]));
        $this->assertSame([['2026-11-09', '2026-11-11', '09:30:00'], ['2026-11-02', '2026-11-04', '09:30:00'],
            ['2026-10-26', '2026-10-28', '09:30:00'], ['2026-10-19', '2026-10-21', '09:30:00']],
            self::spans(KpiCalendar::comparisons('week', $soFar, 'average4')));
    }

    /** review focus 2: a shorter comparison month counts whole */
    public function testMonthSoFarAgainstAShorterMonth(): void
    {
        $month = KpiCalendar::value('month', '2026-03-01', '2026-03-31', '2026-03-31 10:00:00');
        $this->assertSame([['2026-03-01', '2026-03-31', '10:00:00']], self::spans([$month]));
        $this->assertSame([['2026-02-01', '2026-02-28', null]], self::spans(KpiCalendar::comparisons('month', $month, 'previous')));
        $this->assertSame([['2026-02-01', '2026-02-28', null], ['2026-01-01', '2026-01-31', '10:00:00'], ['2025-12-01', '2025-12-31', '10:00:00'],
            ['2025-11-01', '2025-11-30', null]], self::spans(KpiCalendar::comparisons('month', $month, 'average4')));

        $whole = KpiCalendar::value('month', '2026-03-01', '2026-03-31', self::EARLIER);
        $this->assertSame([['2026-02-01', '2026-02-28', null]], self::spans(KpiCalendar::comparisons('month', $whole, 'weekday')));
    }

    public function testRangeHasNoComparison(): void
    {
        $range = KpiCalendar::value('range', '2026-11-02', '2026-11-18', self::EARLIER);
        $this->assertSame([], KpiCalendar::comparisons('range', $range, 'average4'));
    }

    public function testLineDays(): void
    {
        $day = KpiCalendar::value('day', '2026-11-18', '2026-11-18', self::EARLIER);
        $thirty = KpiCalendar::lineDays($day, 30);
        $this->assertCount(30, $thirty);
        $this->assertSame(['2026-10-20', '2026-11-18'], [$thirty[0], $thirty[29]]);
        $this->assertSame('2026-11-12', KpiCalendar::lineDays($day, 7)[0]);
        $this->assertSame([], KpiCalendar::lineDays($day, 0));
    }

    /** calendar days, not 24-hour steps: Europe's clock change on 2026-10-25 does not shift a weekday */
    public function testAcrossTheClockChange(): void
    {
        $day = KpiCalendar::value('day', '2026-11-04', '2026-11-04', self::EARLIER);
        $days = array_map(static fn (KpiSpan $span): string => $span->firstDay, KpiCalendar::comparisons('day', $day, 'average4'));
        $this->assertSame(['2026-10-28', '2026-10-21', '2026-10-14', '2026-10-07'], $days);
    }

    /** @param list<KpiSpan> $spans */
    private static function spans(array $spans): array
    {
        return array_map(static fn (KpiSpan $span): array => [$span->firstDay, $span->lastDay, $span->until], $spans);
    }
}
