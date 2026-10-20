<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Date;
use Piwik\Period;

/**
 * Explorations run on exactly one period of at most Limits::$maxDays days (spec 3.1).
 */
final class PeriodGuard
{
    public const PERIODS = ['day', 'week', 'month', 'range'];

    public static function check(string $period, string $date, Date $start, Date $end, Limits $limits): void
    {
        self::checkRequest($period, $date);
        self::checkLength($start, $end, $limits);
    }

    /**
     * Before the period is built: one day, week, month or range, never several periods (e.g. period=day&date=last7).
     *
     * @param bool $allowYear a year too (the offer of its last days that fit, never an exploration)
     */
    public static function checkRequest(string $period, string $date, bool $allowYear = false): void
    {
        $periods = $allowYear ? [...self::PERIODS, 'year'] : self::PERIODS;
        if (!in_array($period, $periods, true) || Period::isMultiplePeriod($date, $period)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorPeriodNotSupported');
        }
    }

    public static function checkLength(Date $start, Date $end, Limits $limits): void
    {
        $days = (int) round((strtotime($end->toString()) - strtotime($start->toString())) / 86400) + 1;
        if ($days > $limits->maxDays) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorPeriodTooLong', [$limits->maxDays]);
        }
    }
}
