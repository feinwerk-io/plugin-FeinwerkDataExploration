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
 * Instead of only refusing a period that is too large: the most recent days of it that an exploration may read (at
 * most maxDays, at most maxVisits visits). A binary search over the number of days, each count capped and cached like
 * the cost check's, so offering costs a few cheap index counts.
 */
final class PeriodSuggestion
{
    public function __construct(private readonly CostGuard $guard, private readonly Limits $limits)
    {
    }

    /**
     * @return array{date: string, days: int}|array{} the range as "YYYY-MM-DD,YYYY-MM-DD", or nothing when even the
     *         last day has too many visits
     */
    public function suggest(int $idSite, string $period, string $date): array
    {
        // a year is never explored, but its last days are offered (e.g. coming from a yearly report)
        PeriodGuard::checkRequest($period, $date, true);

        $timezone = Site::getTimezoneFor($idSite);
        $built = PeriodFactory::makePeriodFromQueryParams($timezone, $period, $date);
        $today = Date::factory('today', $timezone);
        $end = $built->getDateEnd()->isLater($today) ? $today : $built->getDateEnd();
        $days = (int) floor(($end->getTimestamp() - $built->getDateStart()->getTimestamp()) / 86400) + 1;
        $days = max(1, min($days, $this->limits->maxDays));

        if (!$this->fits($idSite, $end, 1)) {
            return [];
        }
        $low = 1;
        $high = $days;
        while ($low < $high) {
            $middle = intdiv($low + $high + 1, 2);
            if ($this->fits($idSite, $end, $middle)) {
                $low = $middle;
            } else {
                $high = $middle - 1;
            }
        }
        return ['date' => $end->subDay($low - 1)->toString() . ',' . $end->toString(), 'days' => $low];
    }

    private function fits(int $idSite, Date $end, int $days): bool
    {
        $window = ExplorationWindow::for($idSite, 'range', $end->subDay($days - 1)->toString() . ',' . $end->toString(), $this->limits);
        return $this->guard->visits($idSite, $window->startUtc, $window->endUtc, $this->limits, $window->cacheTtl()) <= $this->limits->maxVisits;
    }
}
