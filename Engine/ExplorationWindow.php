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
        public readonly bool $includesToday
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
        return new self($timezone, $start, $end, $start->getDatetime(), $end->getDatetime(), $includesToday);
    }

    /** Seconds results of this window may be reused: one hour, five minutes while today's data still changes. */
    public function cacheTtl(): int
    {
        return (new ResultCache())->ttl($this->includesToday);
    }
}
