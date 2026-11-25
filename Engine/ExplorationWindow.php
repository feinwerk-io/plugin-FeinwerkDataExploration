<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Date;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Site;

/**
 * The time window of an exploration: the checked period of one site as UTC times, and how long results of it may be
 * cached (spec 3.5).
 */
final class ExplorationWindow
{
    private function __construct(
        public readonly string $timezone,
        public readonly Date $start,
        public readonly Date $end,
        public readonly string $startUtc,
        public readonly string $endUtc,
        public readonly bool $includesToday,
        /** the period's first and last day in the site's timezone (YYYY-MM-DD) */
        public readonly string $firstDay = '',
        public readonly string $lastDay = ''
    ) {
    }

    /**
     * @throws ExplorationException for a period an exploration does not cover
     */
    public static function for(int $idSite, string $period, string $date, Limits $limits): self
    {
        PeriodGuard::checkRequest($period, $date);

        $timezone = Site::getTimezoneFor($idSite);
        $builtPeriod = PeriodFactory::makePeriodFromQueryParams($timezone, $period, $date);
        PeriodGuard::checkLength($builtPeriod->getDateStart(), $builtPeriod->getDateEnd(), $limits);

        $start = $builtPeriod->getDateTimeStart()->setTimezone($timezone);
        $end = $builtPeriod->getDateTimeEnd()->setTimezone($timezone);
        $includesToday = $builtPeriod->getDateEnd()->toString() >= Date::factory('today', $timezone)->toString();
        return new self(
            $timezone,
            $start,
            $end,
            $start->getDatetime(),
            $end->getDatetime(),
            $includesToday,
            $builtPeriod->getDateStart()->toString(),
            $builtPeriod->getDateEnd()->toString()
        );
    }

    /**
     * The days of a KPI card's span (2026-11-24): from the first day's start to the end of the last day, or to the last
     * day at the span's time of day, in the website's timezone; guarded as a range of those days.
     *
     * @throws ExplorationException for days an exploration does not cover
     */
    public static function span(int $idSite, KpiSpan $span, Limits $limits): self
    {
        $whole = self::for($idSite, 'range', $span->firstDay . ',' . $span->lastDay, $limits);
        if ($span->until === null) {
            return $whole;
        }
        // the time of day in the website's timezone, as for() builds its bounds (Date::factory with a timezone would
        // read the string as UTC)
        $end = Date::factory($span->lastDay . ' ' . $span->until)->setTimezone($whole->timezone);
        return new self($whole->timezone, $whole->start, $end, $whole->startUtc, $end->getDatetime(), $whole->includesToday,
            $whole->firstDay, $whole->lastDay);
    }

    /** Seconds results of this window may be reused: one hour, five minutes while today's data still changes. */
    public function cacheTtl(): int
    {
        return (new ResultCache())->ttl($this->includesToday);
    }
}
