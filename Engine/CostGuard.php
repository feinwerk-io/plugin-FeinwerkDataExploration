<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Common;
use Piwik\NumberFormatter;
use Piwik\Plugins\FeinwerkDataExploration\Query\QueryRunner;

/**
 * Refuses periods with more visits than Limits::$maxVisits before the exploration query runs (spec 3.2). The count
 * stops at the threshold, so a large site costs no more than a range scan of maxVisits + 1 index entries.
 */
final class CostGuard
{
    private Remember $remember;
    /** @var callable fn(string $table): string */
    private $prefixTable;

    public function __construct(private readonly QueryRunner $runner, ?Remember $remember = null, ?callable $prefixTable = null)
    {
        $this->remember = $remember ?? new Remember();
        $this->prefixTable = $prefixTable ?? [Common::class, 'prefixTable'];
    }

    /**
     * @param int $ttl how long the visit count of this window may be reused (0: count now)
     * @throws ExplorationException when the window has more visits than the threshold
     */
    public function check(int $idSite, string $startUtc, string $endUtc, Limits $limits, int $ttl = 0): void
    {
        if ($this->visits($idSite, $startUtc, $endUtc, $limits, $ttl) > $limits->maxVisits) {
            throw new ExplorationException(RoleText::key('FeinwerkDataExploration_ErrorTooManyVisits'), [self::formatNumber($limits->maxVisits)]);
        }
    }

    /**
     * The visits of the site in the window, counted over the index (idsite, visit_last_action_time) and only up to one
     * above the threshold, so the count of a huge period stays cheap.
     */
    public function visits(int $idSite, string $startUtc, string $endUtc, Limits $limits, int $ttl = 0): int
    {
        $key = 'fwde_visits_' . sha1(json_encode([$idSite, $startUtc, $endUtc, $limits->maxVisits]));
        return (int) $this->remember->get($key, $ttl, function () use ($idSite, $startUtc, $endUtc, $limits): int {
            $sql = 'SELECT COUNT(*) AS visits FROM (SELECT 1 FROM ' . ($this->prefixTable)('log_visit')
                . ' WHERE idsite = ? AND visit_last_action_time >= ? AND visit_last_action_time <= ?'
                . ' LIMIT ' . ($limits->maxVisits + 1) . ') AS fwde_cost';
            $rows = $this->runner->fetchAll(['sql' => $sql, 'bind' => [$idSite, $startUtc, $endUtc]]);
            return (int) ($rows[0]['visits'] ?? 0);
        });
    }

    private static function formatNumber(int $number): string
    {
        try {
            return NumberFormatter::getInstance()->formatNumber($number);
        } catch (\Throwable $e) {
            return (string) $number;
        }
    }
}
