<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Date;
use Piwik\Plugins\FeinwerkDataExploration\Definition\Comparison;

/**
 * The arithmetic of comparison columns (comparison design 3): which kind a column is, shares of a column total for
 * counts and sums, changes for periods (how the page's period differs from the column's), and the day offset that
 * lines dates up by position.
 */
final class ComparisonTable
{
    public const KIND_SEGMENT = 'segment';
    public const KIND_PERIOD = 'period';
    public const KIND_BOTH = 'both';

    /** A column with another period than the page's is a period column; with also another segment, both. */
    public static function kind(Comparison $comparison, string $segment, string $period, string $date): string
    {
        $otherPeriod = $comparison->period !== trim($period) || $comparison->date !== trim($date);
        if (!$otherPeriod) {
            return self::KIND_SEGMENT;
        }
        return $comparison->segment === trim($segment) ? self::KIND_PERIOD : self::KIND_BOTH;
    }

    /**
     * The kind by the resolved periods: a column with the page's period is a segment column, whatever the dates were
     * written like ("last7" or two dates).
     */
    public static function kindOf(Comparison $comparison, string $segment, bool $samePeriod): string
    {
        if ($samePeriod) {
            return self::KIND_SEGMENT;
        }
        return $comparison->segment === trim($segment) ? self::KIND_PERIOD : self::KIND_BOTH;
    }

    /** Days from one YYYY-MM-DD to another (negative when the second is earlier). */
    public static function daysBetween(string $from, string $to): int
    {
        return (int) round((strtotime($to . ' 12:00:00 UTC') - strtotime($from . ' 12:00:00 UTC')) / 86400);
    }

    /** The value's share of the total in percent, one decimal; null without a total. */
    public static function share($value, $total): ?float
    {
        if ($value === null || $total === null || (float) $total == 0.0) {
            return null;
        }
        return round(100 * (float) $value / (float) $total, 1);
    }

    /** How the current value differs from the compared one, in percent, one decimal; null without a compared value. */
    public static function change($current, $compared): ?float
    {
        if ($current === null || $compared === null || (float) $compared == 0.0) {
            return null;
        }
        return round(100 * ((float) $current - (float) $compared) / (float) $compared, 1);
    }

    public static function shiftDate(string $ymd, int $days): string
    {
        return Date::factory($ymd)->addDay($days)->toString();
    }
}
