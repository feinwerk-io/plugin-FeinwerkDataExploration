<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Common;
use Piwik\Plugins\FeinwerkDataExploration\Definition\FunnelStep;

/**
 * Turns a text step of a funnel into the ids of the matching actions of one site and period (spec 4), so the funnel
 * query compares ids on log_link_visit_action instead of joining texts. Only the actions of the period are read (by
 * index_idsite_servertime, each joined with its log_action row by primary key), never all of log_action, which has no
 * index for texts and holds every site's actions. Comparison is that of the existing filters (the database collation,
 * i.e. without case); LIKE wildcards in the text are matched literally. Titles, event texts and search keywords are
 * stored HTML-escaped, so the text is escaped the same way first. More than $maxIds different matches give null: the
 * funnel query then compares the texts itself (FunnelQueryBuilder).
 */
final class StepActionResolver
{
    public const MAX_IDS = 10000;

    /** step type => [column of log_link_visit_action, log_action type, stored HTML-escaped] */
    public const ACTIONS = [
        'pageUrl' => ['idaction_url', 1, false],
        'pageTitle' => ['idaction_name', 4, true],
        'eventCategory' => ['idaction_event_category', 10, true],
        'eventAction' => ['idaction_event_action', 11, true],
        'eventName' => ['idaction_name', 12, true],
        'siteSearch' => ['idaction_name', 8, true],
        'download' => ['idaction_url', 3, false],
        'outlink' => ['idaction_url', 2, false],
    ];

    /** @var callable fn(array{sql: string, bind: array}): array */
    private $fetchAll;
    /** @var callable fn(string $table): string */
    private $prefixTable;
    private Remember $remember;

    public function __construct(callable $fetchAll, callable $prefixTable, ?Remember $remember = null, private readonly int $maxIds = self::MAX_IDS)
    {
        $this->fetchAll = $fetchAll;
        $this->prefixTable = $prefixTable;
        $this->remember = $remember ?? new Remember();
    }

    /**
     * @param int $ttl seconds the ids are remembered (0: not at all), the period's cache time
     * @return list<int>|null null when more than $maxIds different actions match
     */
    public function resolve(FunnelStep $step, int $idSite, string $startUtc, string $endUtc, int $ttl): ?array
    {
        $key = 'fwde_step_' . sha1(json_encode([$idSite, $startUtc, $endUtc, $step->type, $step->operator, $step->value, $this->maxIds]));
        // wrapped, so that "too many" (null) is remembered too
        $result = $this->remember->get($key, $ttl, function () use ($step, $idSite, $startUtc, $endUtc): array {
            $column = 'fwde_a.' . self::ACTIONS[$step->type][0];
            $bind = [$idSite, $startUtc, $endUtc, self::ACTIONS[$step->type][1]];
            $sql = 'SELECT DISTINCT ' . $column . ' AS idaction FROM ' . ($this->prefixTable)('log_link_visit_action') . ' AS fwde_a'
                . ' INNER JOIN ' . ($this->prefixTable)('log_action') . ' AS fwde_la ON fwde_la.idaction = ' . $column
                . ' WHERE fwde_a.idsite = ? AND fwde_a.server_time >= ? AND fwde_a.server_time <= ?'
                . ' AND fwde_la.type = ? AND ' . self::nameCondition($step, 'fwde_la.name', $bind) . ' LIMIT ' . ($this->maxIds + 1);
            $rows = ($this->fetchAll)(['sql' => $sql, 'bind' => $bind]);
            if (count($rows) > $this->maxIds) {
                return ['ids' => null];
            }
            return ['ids' => array_map(static fn (array $row): int => (int) $row['idaction'], $rows)];
        });
        return $result['ids'];
    }

    /**
     * The condition of a text step on a log_action name column; appends its value to $bind.
     */
    public static function nameCondition(FunnelStep $step, string $column, array &$bind): string
    {
        $value = self::ACTIONS[$step->type][2] ? Common::sanitizeInputValue($step->value) : $step->value;
        if ($step->operator === 'equals') {
            $bind[] = $value;
            return $column . ' = ?';
        }
        // LIKE treats % and _ as wildcards and \ as escape character; the text is matched literally
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
        $bind[] = $step->operator === 'startsWith' ? $escaped . '%' : '%' . $escaped . '%';
        return $column . ' LIKE ?';
    }
}
