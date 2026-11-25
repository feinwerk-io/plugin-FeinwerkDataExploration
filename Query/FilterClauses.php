<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Query;

use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;

/**
 * The row filters as the clauses of the top-level AND (filter groups, 2026-11-23). Outer "all" (or a single group): a
 * group on "all" gives one clause per condition, a group on "any" one clause; outer "any" with two groups or more is
 * one clause. A clause with only text conditions runs before the grouping (stage A), one with a number condition on
 * the grouped rows (HAVING). Totals, "Others", comparison rows and trend lines use the stage A part only.
 */
final class FilterClauses
{
    /**
     * @return list<array{groups: list<array{join: string, indexes: list<int>}>, number: bool}> a clause is its groups
     *         joined by OR, each group's conditions (indexes into $definition->filters) joined by its join
     */
    public static function of(ExplorationDefinition $definition): array
    {
        $byGroup = [];
        foreach ($definition->filters as $i => $filter) {
            $byGroup[$filter['group']][] = $i;
        }
        $join = fn (int $group): string => ($definition->groupMatches[$group] ?? ExplorationDefinition::MATCH_ALL) === ExplorationDefinition::MATCH_ANY ? 'OR' : 'AND';
        $clause = function (array $groups) use ($definition): array {
            $number = false;
            foreach ($groups as $group) {
                foreach ($group['indexes'] as $i) {
                    $number = $number || in_array($definition->filters[$i]['operator'], ExplorationDefinition::NUMBER_OPERATORS, true);
                }
            }
            return ['groups' => $groups, 'number' => $number];
        };

        if ($definition->match === ExplorationDefinition::MATCH_ANY && count($byGroup) > 1) {
            $groups = [];
            foreach ($byGroup as $group => $indexes) {
                $groups[] = ['join' => $join($group), 'indexes' => $indexes];
            }
            return [$clause($groups)];
        }

        $clauses = [];
        foreach ($byGroup as $group => $indexes) {
            if ($join($group) === 'OR') {
                $clauses[] = $clause([['join' => 'OR', 'indexes' => $indexes]]);
                continue;
            }
            foreach ($indexes as $i) {
                $clauses[] = $clause([['join' => 'AND', 'indexes' => [$i]]]);
            }
        }
        return $clauses;
    }
}
