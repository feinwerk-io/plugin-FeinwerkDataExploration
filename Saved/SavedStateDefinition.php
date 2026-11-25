<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Saved;

use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;

/**
 * A saved state (the page's link parameters) as the definition the page would send for it (saved reports, 2026-11-24):
 * the server's twin of vue/src/state.ts (parseState, withGoalMetrics, toDefinition) and vue/src/filterGroups.ts
 * (parseFilterParam, filterGroupsDefinition), so a report shows the table its page shows. Unlike the page, which falls
 * back to defaults, a dimension the website no longer offers or no metric left refuses the report: it never shows a
 * different table under the saved name.
 */
final class SavedStateDefinition
{
    /** vue/src/state.ts LIMIT_OPTIONS */
    private const LIMIT_OPTIONS = [10, 25, 50, 100, 250, 500, 1000];
    /** vue/src/state.ts ALL_GOALS_METRICS: what an older link narrowed with fwdeGoal */
    private const ALL_GOALS_METRICS = ['nb_conversions', 'conversion_rate', 'revenue'];
    /** vue/src/filterGroups.ts */
    private const MAX_FILTERS = 10;
    private const MAX_GROUPS = 5;
    private const MAX_FILTER_TEXT = 1000;
    private const MAX_PATTERN = 200;
    private const TEXT_OPERATORS = ['contains', 'notContains', 'equals', 'notEquals', 'startsWith', 'notStartsWith', 'endsWith',
        'notEndsWith', 'matches'];
    private const NUMBER_OPERATORS = ['gt', 'gte', 'lt', 'lte'];

    /**
     * @param array<string, string> $state a saved exploration's state
     * @return array{dimensions: string[], metrics: string[], sort: string, sortOrder: string, limit: int, filterGroups?: array}
     * @throws SavedReportUnavailable when a dimension is gone or no metric is left
     */
    public static function toDefinition(array $state, int $idSite, DimensionCatalog $dimensions, MetricCatalog $metrics, Limits $limits): array
    {
        $state = self::withGoalMetrics($state, $idSite, $metrics);

        $dimensionIds = self::ids($state['fwdeDims'] ?? '', ExplorationDefinition::MAX_DIMENSIONS);
        if ($dimensionIds === []) {
            throw new SavedReportUnavailable('FeinwerkDataExploration_ErrorSavedDimensionGone', ['']);
        }
        foreach ($dimensionIds as $id) {
            if ($dimensions->get($idSite, $id) === null) {
                throw new SavedReportUnavailable('FeinwerkDataExploration_ErrorSavedDimensionGone', [$id]);
            }
        }

        $metricIds = [];
        foreach (self::ids($state['fwdeMetrics'] ?? '', PHP_INT_MAX) as $id) {
            if (count($metricIds) < ExplorationDefinition::MAX_METRICS && $metrics->get($id, $idSite) !== null) {
                $metricIds[] = $id;
            }
        }
        if ($metricIds === []) {
            throw new SavedReportUnavailable('FeinwerkDataExploration_ErrorSavedNoMetric');
        }

        $labels = ExplorationDefinition::labelColumns(count($dimensionIds));
        $sort = (string) ($state['fwdeSort'] ?? '');
        $limit = (int) ($state['fwdeLimit'] ?? 0);
        $definition = [
            'dimensions' => $dimensionIds,
            'metrics' => $metricIds,
            'sort' => in_array($sort, array_merge($metricIds, $labels), true) ? $sort : $metricIds[0],
            'sortOrder' => ($state['fwdeOrder'] ?? '') === 'asc' ? 'asc' : 'desc',
            'limit' => in_array($limit, self::LIMIT_OPTIONS, true) && $limit <= $limits->maxRows ? $limit : $limits->defaultRows,
        ];
        $groups = self::filterGroups((string) ($state['fwdeFilters'] ?? ''), (string) ($state['fwdeMatch'] ?? ''), $labels, $metricIds);
        if ($groups !== null) {
            $definition['filterGroups'] = $groups;
        }
        $pivot = self::pivot($state, $dimensionIds, count($metricIds), $idSite, $dimensions);
        if ($pivot !== null) {
            $definition['pivot'] = $pivot;
        }
        return $definition;
    }

    /**
     * The pivot (rows and pivot, 2026-11-24) as the page reads it from a link (vue/src/pivot.ts): dropped when its
     * dimension is gone, a row dimension, Date or Hour, or (Weekday) when its seven days pass the cell limit; a limit
     * other than 3, 5 or 10 is 5, then the largest that keeps the cells within the limit.
     *
     * @return array{dimension: string, limit: int}|null
     */
    private static function pivot(array $state, array $dimensionIds, int $metricCount, int $idSite, DimensionCatalog $dimensions): ?array
    {
        $id = (string) ($state['fwdePivot'] ?? '');
        if ($id === '' || in_array($id, $dimensionIds, true) || in_array($id, ExplorationDefinition::NO_PIVOT, true) || $dimensions->get($idSite, $id) === null) {
            return null;
        }
        if ($id === ExplorationDefinition::PIVOT_WEEKDAY) {
            return 7 * $metricCount <= ExplorationDefinition::MAX_PIVOT_CELLS ? ['dimension' => $id, 'limit' => ExplorationDefinition::PIVOT_DEFAULT_LIMIT] : null;
        }
        $limit = (int) ($state['fwdePivotLimit'] ?? 0);
        $limit = in_array($limit, ExplorationDefinition::PIVOT_LIMITS, true) ? $limit : ExplorationDefinition::PIVOT_DEFAULT_LIMIT;
        foreach (array_reverse(ExplorationDefinition::PIVOT_LIMITS) as $fitting) {
            if ($fitting <= $limit && $fitting * $metricCount <= ExplorationDefinition::MAX_PIVOT_CELLS) {
                return ['dimension' => $id, 'limit' => $fitting];
            }
        }
        return null;
    }

    /** Unique ids of a comma list, at most $max. */
    private static function ids(string $value, int $max): array
    {
        $ids = [];
        foreach (explode(',', $value) as $id) {
            if ($id !== '' && !in_array($id, $ids, true) && count($ids) < $max) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    /** A chosen goal of an older link: the all-goals metrics become that goal's own, wherever metrics are named. */
    private static function withGoalMetrics(array $state, int $idSite, MetricCatalog $metrics): array
    {
        $goal = $state['fwdeGoal'] ?? '';
        if (!ctype_digit($goal) || (int) $goal < 1 || $metrics->get('goal_' . (int) $goal . '_nb_conversions', $idSite) === null) {
            return $state;
        }
        $map = static fn (string $id): string => in_array($id, self::ALL_GOALS_METRICS, true) ? 'goal_' . (int) $goal . '_' . $id : $id;
        foreach (['fwdeMetrics', 'fwdeSort', 'fwdeChart'] as $key) {
            if (($state[$key] ?? '') !== '') {
                $state[$key] = implode(',', array_map($map, explode(',', $state[$key])));
            }
        }
        if (($state['fwdeFilters'] ?? '') !== '') {
            $groups = [];
            foreach (explode('|', $state['fwdeFilters']) as $group) {
                $prefix = preg_match('/^(all|any):/', $group, $m) === 1 ? $m[0] : '';
                $conditions = [];
                foreach (explode(';', substr($group, strlen($prefix))) as $condition) {
                    $cut = strpos($condition, '~');
                    $conditions[] = $cut === false ? $condition : $map(substr($condition, 0, $cut)) . substr($condition, $cut);
                }
                $groups[] = $prefix . implode(';', $conditions);
            }
            $state['fwdeFilters'] = implode('|', $groups);
        }
        return $state;
    }

    /**
     * The API's filterGroups of fwdeFilters and fwdeMatch, null without a valid condition: groups by "|", each
     * "[all:|any:]condition;condition"; a value with neither "|" nor a prefix is a link from before filter groups (one
     * group whose switch is fwdeMatch). Broken conditions, the groups they empty and what passes the limits are dropped;
     * rates are entered in percent and sent as fractions.
     *
     * @param string[] $labels label targets of the dimensions
     * @param string[] $metricIds
     */
    private static function filterGroups(string $raw, string $match, array $labels, array $metricIds): ?array
    {
        $parts = $raw === '' ? [] : explode('|', $raw);
        $legacy = count($parts) === 1 && preg_match('/^(all|any):/', $parts[0]) !== 1;
        $groups = [];
        $count = 0;
        foreach ($parts as $part) {
            if (count($groups) >= self::MAX_GROUPS) {
                break;
            }
            $prefixed = preg_match('/^(all|any):/', $part, $m) === 1;
            $switch = $legacy ? ($match === 'any' ? 'any' : 'all') : ($prefixed && $m[1] === 'any' ? 'any' : 'all');
            $filters = [];
            foreach (explode(';', $prefixed ? substr($part, strlen($m[0])) : $part) as $entry) {
                $condition = self::condition($entry, $labels, $metricIds);
                if ($condition !== null && $count < self::MAX_FILTERS) {
                    $filters[] = $condition;
                    $count++;
                }
            }
            if ($filters !== []) {
                $groups[] = ['match' => $switch, 'filters' => $filters];
            }
        }
        if ($groups === []) {
            return null;
        }
        return ['match' => !$legacy && count($groups) > 1 && $match === 'any' ? 'any' : 'all', 'groups' => $groups];
    }

    /** One condition "target~operator~value" (value URI-encoded, only the first two "~" separate), null when invalid. */
    private static function condition(string $entry, array $labels, array $metricIds): ?array
    {
        $parts = explode('~', $entry, 3);
        if (count($parts) < 3 || !self::decodable($parts[2])) {
            return null;
        }
        [$target, $operator, $encoded] = $parts;
        $value = trim(rawurldecode($encoded));
        if (in_array($operator, self::TEXT_OPERATORS, true)) {
            $max = $operator === 'matches' ? self::MAX_PATTERN : self::MAX_FILTER_TEXT;
            if (!in_array($target, $labels, true) || $value === '' || mb_strlen($value) > $max) {
                return null;
            }
            // a pattern the server cannot compile is broken as well (release review 2026-11-25)
            if ($operator === 'matches' && !ExplorationDefinition::isPattern($value)) {
                return null;
            }
            return ['target' => $target, 'operator' => $operator, 'value' => $value];
        }
        if (in_array($operator, self::NUMBER_OPERATORS, true) && in_array($target, $metricIds, true) && is_numeric($value)
            && is_finite((float) $value)) {
            $number = (float) $value;
            $rate = (MetricCatalog::meta($target)['unit'] ?? '') === 'rate';
            return ['target' => $target, 'operator' => $operator, 'value' => $rate ? $number / 100 : $number];
        }
        return null;
    }

    /** What decodeURIComponent accepts: every "%" starts a pair of hex digits, and the result is UTF-8. */
    private static function decodable(string $encoded): bool
    {
        return preg_match('/%(?![0-9A-Fa-f]{2})/', $encoded) !== 1 && mb_check_encoding(rawurldecode($encoded), 'UTF-8');
    }
}
