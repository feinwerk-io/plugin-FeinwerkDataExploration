<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Query;

use Piwik\Plugins\FeinwerkDataExploration\Definition\FunnelDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Definition\FunnelStep;
use Piwik\Plugins\FeinwerkDataExploration\Engine\StepActionResolver;

/**
 * The funnel in one statement (spec 4, approach A), without CTEs or window functions (MySQL 5.7):
 *
 * - Level 1 gives each visit with a step-1 action (or conversion) in the period its earliest position.
 * - Level k keeps every visit of level k-1 and LEFT JOINs that visit's actions (or conversions) of step k after the
 *   position of step k-1; the earliest becomes position k (NULL when there is none, so no later level matches).
 * - The outer query counts the positions per level.
 *
 * A position orders the actions of a visit by (server_time, idlink_va), so actions of the same second count in their
 * real order: CONCAT of the time to the second and the zero-padded action id. A conversion's position starts with the
 * position of the action that triggered it (log_conversion.idlink_va; without one, after every action of its second)
 * and goes on with its goal and buster (log_conversion's key), so it sorts right after its action and every conversion
 * has its own position. Every step must be strictly later than the previous one: one action or conversion never counts
 * for two steps, and "thank-you page, then goal" still counts the conversion of that page view.
 */
final class FunnelQueryBuilder
{
    private const ACTION_POSITION = "CONCAT(DATE_FORMAT(fwde_a.server_time, '%Y%m%d%H%i%s'), LPAD(fwde_a.idlink_va, 20, '0'))";
    private const CONVERSION_POSITION = "CONCAT(DATE_FORMAT(fwde_c.server_time, '%Y%m%d%H%i%s'),"
        . " IF(fwde_c.idlink_va IS NULL, '99999999999999999999', LPAD(fwde_c.idlink_va, 20, '0')),"
        . " LPAD(fwde_c.idgoal + 1, 11, '0'), LPAD(fwde_c.buster, 10, '0'))";

    /** @var callable fn(string $table): string */
    private $prefixTable;

    public function __construct(callable $prefixTable)
    {
        $this->prefixTable = $prefixTable;
    }

    /**
     * @param array<int, list<int>|null> $resolved per text step (by index): its log_action ids, null to compare the
     *                                           texts through log_action (joined by its key per action row)
     * @param array{sql: string, bind: array}|null $segmentVisits the ids (column idvisit) of the segment's visits
     * @return array{sql: string, bind: array} one row: fwde_step1 … fwde_stepN, the visits per step
     */
    public function build(FunnelDefinition $definition, int $idSite, QueryContext $context, array $resolved, ?array $segmentVisits): array
    {
        $bind = [];
        $sql = $this->firstLevel($definition->steps[0], $idSite, $context, $resolved[0] ?? null, $segmentVisits, $bind);
        for ($k = 2; $k <= count($definition->steps); $k++) {
            $sql = $this->nextLevel($sql, $k, $definition->steps[$k - 1], $context, $resolved[$k - 1] ?? null, $bind);
        }

        $counts = ['COUNT(*) AS fwde_step1'];
        for ($k = 2; $k <= count($definition->steps); $k++) {
            $counts[] = 'COUNT(fwde_f.fwde_p' . $k . ') AS fwde_step' . $k;
        }
        return ['sql' => 'SELECT ' . implode(', ', $counts) . ' FROM (' . $sql . ') AS fwde_f', 'bind' => $bind];
    }

    private function firstLevel(FunnelStep $step, int $idSite, QueryContext $context, ?array $ids, ?array $segmentVisits, array &$bind): string
    {
        $alias = $step->isGoal() ? 'fwde_c' : 'fwde_a';
        $sql = 'SELECT ' . $alias . '.idvisit AS idvisit, MIN(' . ($step->isGoal() ? self::CONVERSION_POSITION : self::ACTION_POSITION) . ') AS fwde_p1'
            . ' FROM ' . $this->source($step, $ids);
        if ($segmentVisits !== null) {
            $sql .= ' INNER JOIN (' . $segmentVisits['sql'] . ') AS fwde_seg ON fwde_seg.idvisit = ' . $alias . '.idvisit';
            array_push($bind, ...$segmentVisits['bind']);
        }
        $sql .= ' WHERE ' . $alias . '.idsite = ? AND ' . $alias . '.server_time >= ? AND ' . $alias . '.server_time <= ?';
        array_push($bind, $idSite, $context->windowStartUtc, $context->windowEndUtc);
        $sql .= ' AND ' . $this->match($step, $ids, $bind);
        return $sql . ' GROUP BY ' . $alias . '.idvisit';
    }

    private function nextLevel(string $previous, int $k, FunnelStep $step, QueryContext $context, ?array $ids, array &$bind): string
    {
        $carried = [];
        for ($i = 1; $i < $k; $i++) {
            $carried[] = 'fwde_s.fwde_p' . $i;
        }
        $alias = $step->isGoal() ? 'fwde_c' : 'fwde_a';
        $position = $step->isGoal() ? self::CONVERSION_POSITION : self::ACTION_POSITION;

        $select = ['fwde_s.idvisit AS idvisit'];
        foreach ($carried as $i => $column) {
            $select[] = $column . ' AS fwde_p' . ($i + 1);
        }
        $select[] = 'MIN(' . $position . ') AS fwde_p' . $k;

        $sql = 'SELECT ' . implode(', ', $select) . ' FROM (' . $previous . ') AS fwde_s'
            . ' LEFT JOIN ' . $this->source($step, $ids) . ' ON ' . $alias . '.idvisit = fwde_s.idvisit'
            . ' AND ' . $alias . '.server_time >= ? AND ' . $alias . '.server_time <= ?';
        array_push($bind, $context->windowStartUtc, $context->windowEndUtc);
        $sql .= ' AND ' . $this->match($step, $ids, $bind) . ' AND ' . $position . ' > fwde_s.fwde_p' . ($k - 1);

        // every carried column is grouped: MySQL 5.7 with ONLY_FULL_GROUP_BY refuses them otherwise
        return $sql . ' GROUP BY fwde_s.idvisit, ' . implode(', ', $carried);
    }

    /**
     * The rows a step reads: conversions, actions, or (for a text step without resolved ids) actions joined with their
     * log_action row by its primary key, so the texts are compared only for the actions the query reads anyway.
     */
    private function source(FunnelStep $step, ?array $ids): string
    {
        if ($step->isGoal()) {
            return ($this->prefixTable)('log_conversion') . ' AS fwde_c';
        }
        $actions = ($this->prefixTable)('log_link_visit_action') . ' AS fwde_a';
        if ($ids !== null) {
            return $actions;
        }
        return '(' . $actions . ' INNER JOIN ' . ($this->prefixTable)('log_action') . ' AS fwde_la'
            . ' ON fwde_la.idaction = fwde_a.' . StepActionResolver::ACTIONS[$step->type][0] . ')';
    }

    /** The condition a row of the step's table must meet. */
    private function match(FunnelStep $step, ?array $ids, array &$bind): string
    {
        if ($step->type === 'anyGoal') {
            // ecommerce orders (0) count as conversions, abandoned carts (-1) do not, as in Matomo's goal reports
            return 'fwde_c.idgoal >= 0';
        }
        if ($step->type === 'goal') {
            $bind[] = $step->idGoal;
            return 'fwde_c.idgoal = ?';
        }

        $column = 'fwde_a.' . StepActionResolver::ACTIONS[$step->type][0];
        if ($ids === null) {
            $bind[] = StepActionResolver::ACTIONS[$step->type][1];
            $condition = 'fwde_la.type = ? AND ' . StepActionResolver::nameCondition($step, 'fwde_la.name', $bind);
        } elseif ($ids === []) {
            $condition = '1 = 0';
        } else {
            $condition = $column . ' IN (' . implode(', ', array_map('intval', $ids)) . ')';
        }
        if (in_array($step->type, ['pageUrl', 'pageTitle'], true)) {
            // page views only: events carry the URL of their page in idaction_url
            $condition .= ' AND fwde_a.idaction_event_category IS NULL';
        }
        return $condition;
    }
}
