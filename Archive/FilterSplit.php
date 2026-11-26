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
 * Which conditions an archive applies and which wait for the added-up period (design 2026-11-25). A condition on a
 * dimension holds for a row on every day alike, so the archive applies it. A condition on a metric ("visits > 100")
 * holds only for the period's total: under a top-level "all" an "all" group keeps its dimension conditions in the
 * archive and applies its metric conditions to the period, an "any" group with a metric condition moves whole to
 * reading; under a top-level "any" any such condition moves the whole filter to reading.
 */
final class FilterSplit
{
    /**
     * @param callable(string): bool $isMetric whether a condition's target is a metric
     * @return array{0: ExplorationDefinition, 1: array{match: string, groups: list<array{match: string, filters: list<array>}>}|null}
     *         the definition with the archive's conditions, and the groups applied when reading (null: none)
     */
    public static function split(ExplorationDefinition $definition, callable $isMetric): array
    {
        if ($definition->filters === []) {
            return [$definition, null];
        }
        $metricGroups = [];
        foreach ($definition->filters as $filter) {
            if ($isMetric((string) $filter['target'])) {
                $metricGroups[(int) $filter['group']] = true;
            }
        }
        if ($metricGroups === []) {
            return [$definition, null];
        }
        $anyOnTop = $definition->match === ExplorationDefinition::MATCH_ANY;
        $plain = static fn (array $f): array => ['target' => $f['target'], 'operator' => $f['operator'], 'value' => $f['value']];

        $archive = [];
        $archiveMatches = [];
        $read = [];
        foreach ($definition->groupMatches as $group => $groupMatch) {
            $filters = array_values(array_filter($definition->filters, static fn (array $f): bool => (int) $f['group'] === $group));
            $kept = $filters;
            if ($anyOnTop) {
                // under "any" nothing can be split: the whole filter waits for the period
                $read[] = ['match' => $groupMatch, 'filters' => array_map($plain, $filters)];
                continue;
            }
            if (isset($metricGroups[$group])) {
                if ($groupMatch === ExplorationDefinition::MATCH_ANY) {
                    // an "any" group with a metric condition: one condition may hold where another does not
                    $read[] = ['match' => $groupMatch, 'filters' => array_map($plain, $filters)];
                    continue;
                }
                // an "all" group (final review 2026-11-26): its dimension conditions stay in the archive, its metric
                // conditions apply to the period
                $metric = array_values(array_filter($filters, static fn (array $f): bool => $isMetric((string) $f['target'])));
                $read[] = ['match' => $groupMatch, 'filters' => array_map($plain, $metric)];
                $kept = array_values(array_filter($filters, static fn (array $f): bool => !$isMetric((string) $f['target'])));
                if ($kept === []) {
                    continue;
                }
            }
            $index = count($archiveMatches);
            $archiveMatches[] = $groupMatch;
            foreach ($kept as $f) {
                $archive[] = $plain($f) + ['group' => $index];
            }
        }
        return [
            $definition->withFilters($archive, $definition->match, $archiveMatches),
            ['match' => $definition->match, 'groups' => $read],
        ];
    }

    /** a condition's target is a metric unless it is one of the label columns */
    public static function isMetricTarget(string $target): bool
    {
        return !in_array($target, ExplorationDefinition::LABEL_COLUMNS, true);
    }
}
