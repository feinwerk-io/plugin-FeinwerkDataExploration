<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Archive;

use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;

/**
 * The conditions an archived report applies to the added-up table of a period (design 2026-11-25): metric conditions
 * hold for the period's total, and the text conditions of the same groups behave as the live SQL does
 * (Query/ExplorationQueryBuilder::textFilterSql): case-insensitive, % and _ literal, a missing value fails a positive
 * condition and passes a negated one, "matches" with the case-insensitive pattern of ExplorationDefinition::pcre().
 */
final class ReadFilter
{
    private const NEGATED = ['notContains', 'notEquals', 'notStartsWith', 'notEndsWith'];

    /**
     * @param array{match: string, groups: list<array{match: string, filters: list<array{target: string, operator: string, value: mixed}>}>} $groups
     * @param callable(string): ?string $text a label column's text (null: no value)
     * @param callable(string): (int|float|null) $value a metric's value for the period (null: none)
     */
    public static function keep(array $groups, callable $text, callable $value): bool
    {
        $results = [];
        foreach ($groups['groups'] as $group) {
            $conditions = array_map(static fn (array $f): bool => self::holds($f, $text, $value), $group['filters']);
            $results[] = $group['match'] === ExplorationDefinition::MATCH_ANY ? in_array(true, $conditions, true) : !in_array(false, $conditions, true);
        }
        if ($results === []) {
            return true;
        }
        return $groups['match'] === ExplorationDefinition::MATCH_ANY ? in_array(true, $results, true) : !in_array(false, $results, true);
    }

    private static function holds(array $filter, callable $text, callable $value): bool
    {
        $operator = (string) $filter['operator'];
        if (in_array($operator, ExplorationDefinition::NUMBER_OPERATORS, true)) {
            $actual = $value((string) $filter['target']);
            if ($actual === null) {
                return false;
            }
            $expected = (float) $filter['value'];
            return match ($operator) {
                'gt' => $actual > $expected,
                'gte' => $actual >= $expected,
                'lt' => $actual < $expected,
                'lte' => $actual <= $expected,
            };
        }
        $actual = $text((string) $filter['target']);
        if ($operator === 'empty' || $operator === 'notEmpty') {
            $isEmpty = $actual === null || $actual === '';
            return $operator === 'empty' ? $isEmpty : !$isEmpty;
        }
        if ($actual === null) {
            return in_array($operator, self::NEGATED, true);
        }
        $needle = mb_strtolower((string) $filter['value']);
        $haystack = mb_strtolower($actual);
        return match ($operator) {
            'contains' => str_contains($haystack, $needle),
            'notContains' => !str_contains($haystack, $needle),
            'equals' => $haystack === $needle,
            'notEquals' => $haystack !== $needle,
            'startsWith' => str_starts_with($haystack, $needle),
            'notStartsWith' => !str_starts_with($haystack, $needle),
            'endsWith' => str_ends_with($haystack, $needle),
            'notEndsWith' => !str_ends_with($haystack, $needle),
            'matches' => ExplorationDefinition::isPattern((string) $filter['value'])
                && preg_match(ExplorationDefinition::pcre((string) $filter['value']), $actual) === 1,
            default => false,
        };
    }
}
