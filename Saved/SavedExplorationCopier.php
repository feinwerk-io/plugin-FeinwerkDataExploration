<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Saved;

use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\CustomDimensionsApiSource;
use Piwik\Plugins\FeinwerkDataExploration\Engine\AccessPolicy;
use Piwik\Plugins\FeinwerkDataExploration\Engine\SiteGoals;
use Piwik\Segment;
use Piwik\Site;

/**
 * Copies a saved exploration to other websites (with Matomo's entity duplicator). A copy keeps name,
 * state and segment and belongs to the user who copies; it is shared only where that user may share (write access),
 * otherwise private. What is numbered per website is mapped by name: the goal (also in a goal's metrics) to the goal of
 * the same name, a custom dimension to the active one of the same name and scope. A website is skipped (and named in
 * the message) when it does not exist or the user may not use explorations there, when it lacks such a goal or custom
 * dimension, when e-commerce metrics meet a website without e-commerce, when the segment does not work there, or when
 * the user's limit of saved explorations is reached. Nothing is half-written: a website is
 * either copied or skipped.
 */
final class SavedExplorationCopier
{
    /** @var callable fn(int $idSite): bool */
    private $canUse;
    /** @var callable fn(int $idSite): bool */
    private $canShare;
    /** @var callable fn(int $idSite): array<int, string> idgoal => name */
    private $goalsOf;
    /** @var callable fn(int $idSite): list<array{id: int, scope: string, name: string}> active custom dimensions */
    private $customDimensionsOf;
    /** @var callable fn(int $idSite): bool */
    private $ecommerceOf;
    /** @var array<int, array<int, string>> a target's goals (idgoal => name), read once per website */
    private $targetGoals = [];
    /** @var array<int, array<string, int>> a target's custom dimension ids by scope and name, read once per website */
    private $targetDimensionIds = [];

    /** a segment condition on a custom dimension: "dimension<id>" before its operator */
    private const SEGMENT_DIMENSION = '/(^|[;,])dimension(\d+)(?=[=!<>])/';
    /** a goal's metric (more metrics, 2026-11-24): goal_<idgoal>_<metric> */
    private const METRIC_GOAL = '/^goal_(\d+)_([a-z_]+)$/';
    /** state keys that name one metric or a comma list of metrics */
    private const METRIC_KEYS = ['fwdeMetrics', 'fwdeSort', 'fwdeChart', 'fwdeKpiMetric'];

    /** a segment condition on a converted goal by its id */
    private const SEGMENT_GOAL = '/(^|[;,])visitConvertedGoalId(==|!=)(\d+)(?=[;,]|$)/';

    public function __construct(
        private readonly SavedExplorationStore $store = new SavedExplorationStore(),
        ?callable $canUse = null,
        ?callable $canShare = null,
        ?callable $goalsOf = null,
        ?callable $customDimensionsOf = null,
        ?callable $ecommerceOf = null
    ) {
        $this->canUse = $canUse ?? [AccessPolicy::class, 'canUse'];
        $this->canShare = $canShare ?? [AccessPolicy::class, 'canShare'];
        $this->goalsOf = $goalsOf ?? [self::class, 'goalsOf'];
        $this->customDimensionsOf = $customDimensionsOf ?? [new CustomDimensionsApiSource(), 'activeFor'];
        $this->ecommerceOf = $ecommerceOf ?? static fn (int $idSite): bool => Site::isEcommerceEnabledFor($idSite);
    }

    /**
     * @param array $source a row of SavedExplorationStore::get()
     * @param array $destinations website ids as the request sends them (strings with spaces too)
     * @return array{success: bool, message: string, additionalData: array{copied: list<int>, skipped: list<array{idSite: int, reason: string}>}}
     */
    public function copy(array $source, array $destinations, string $login): array
    {
        $copied = [];
        $skipped = [];
        $messages = [];
        $sourceSite = (int) $source['idsite'];
        $targets = self::siteIds($destinations, $sourceSite);
        if ($targets === []) {
            // Matomo's dialog preselects the current website
            $messages[] = self::translate('FeinwerkDataExploration_CopyChooseOtherSite', []);
        }
        // what belongs to the source website is read once, not per target (house rule: no queries inside loops)
        $sourceSegment = (string) $source['segment'];
        $usesGoals = self::goalIds($source['state']) !== [] || preg_match(self::SEGMENT_GOAL, $sourceSegment) === 1;
        $sourceGoals = $usesGoals ? ($this->goalsOf)($sourceSite) : null;
        $sourceDimensions = $this->sourceCustomDimensions($source);
        $usesEcommerce = str_contains(',' . ($source['state']['fwdeMetrics'] ?? ''), ',goal_ecommerce');
        foreach ($targets as $idSite) {
            $reason = null;
            $state = $source['state'];
            $segment = $sourceSegment;
            if (!self::siteExists($idSite) || !($this->canUse)($idSite)) {
                $reason = 'access';
            } elseif ($sourceGoals !== null && ($state = $this->withTargetGoals($state, $sourceGoals, $idSite)) === null) {
                $reason = 'goal';
            } elseif ($usesEcommerce && !($this->ecommerceOf)($idSite)) {
                $reason = 'ecommerce';
            } elseif (self::customDimensionIds(self::stateDimensions($state)) !== []
                && ($state = $this->withTargetDimensions($state, $sourceDimensions, $idSite)) === null) {
                $reason = 'dimension';
            } elseif (($segment = $this->targetSegment($sourceSegment, $sourceDimensions, $sourceGoals, $idSite)) === null
                || !self::segmentWorks($segment, $idSite)) {
                $reason = 'segment';
            } elseif ($this->store->countFor($idSite, $login) >= SavedExplorationRules::MAX_PER_USER_AND_SITE) {
                $reason = 'limit';
            }
            if ($reason !== null) {
                $skipped[] = ['idSite' => $idSite, 'reason' => $reason];
                $messages[] = self::skippedText($reason, $idSite);
                continue;
            }
            // a menu place in Ecommerce needs an e-commerce website (saved reports, 2026-11-24)
            if (($state['fwdeMenu'] ?? '') === SavedExplorationRules::MENU_ECOMMERCE && !($this->ecommerceOf)($idSite)) {
                $state['fwdeMenu'] = SavedExplorationRules::MENU_EXPLORATIONS;
            }
            $shared = (bool) $source['shared'] && ($this->canShare)($idSite);
            $this->store->add($idSite, $login, (string) $source['name'], $state, $segment, $shared);
            $copied[] = $idSite;
        }
        if ($copied !== []) {
            array_unshift($messages, self::translate('FeinwerkDataExploration_CopyDone', [
                implode(', ', array_map([self::class, 'siteName'], $copied)),
            ]));
        }

        return [
            'success' => $copied !== [] && $skipped === [],
            'message' => implode(' ', $messages),
            'additionalData' => ['copied' => $copied, 'skipped' => $skipped],
        ];
    }

    /**
     * Website ids of the request: trimmed (Matomo 6 does not trim), positive, once each, never the source website.
     *
     * @return list<int>
     */
    public static function siteIds(array $raw, int $sourceSite): array
    {
        $ids = [];
        foreach ($raw as $value) {
            $value = trim((string) $value);
            if (ctype_digit($value) && (int) $value > 0 && (int) $value !== $sourceSite) {
                $ids[(int) $value] = (int) $value;
            }
        }
        return array_values($ids);
    }

    /**
     * The goal ids a state names: the table's goal (fwdeGoal), the goal steps of a funnel (fwdeSteps, "goal~~<id>") and
     * the goals' metrics (goal_<id>_<metric>) in the metric list, sorting, chart and row filters.
     *
     * @return list<int>
     */
    private static function goalIds(array $state): array
    {
        $ids = [];
        if (($state['fwdeGoal'] ?? '') !== '') {
            $ids[] = (int) $state['fwdeGoal'];
        }
        foreach (explode(';', (string) ($state['fwdeSteps'] ?? '')) as $step) {
            if (str_starts_with($step, 'goal~~') && ctype_digit(substr($step, 6))) {
                $ids[] = (int) substr($step, 6);
            }
        }
        self::mapMetricIds($state, function (string $metric) use (&$ids): string {
            if (preg_match(self::METRIC_GOAL, $metric, $m) === 1) {
                $ids[] = (int) $m[1];
            }
            return $metric;
        });
        return $ids;
    }

    /**
     * The state with each metric id passed through $map wherever the state names metrics: the metric list, the sorting,
     * the chart's metric and the targets of the row filters (fwdeFilters: groups by "|", an optional "all:"/"any:"
     * prefix, conditions by ";", each "target~operator~value"). Null when $map returns null for one.
     *
     * @param callable $map fn(string $metricId): ?string
     */
    private static function mapMetricIds(array $state, callable $map): ?array
    {
        foreach (self::METRIC_KEYS as $key) {
            if (($state[$key] ?? '') === '') {
                continue;
            }
            $ids = [];
            foreach (explode(',', (string) $state[$key]) as $id) {
                if (($ids[] = $map($id)) === null) {
                    return null;
                }
            }
            $state[$key] = implode(',', $ids);
        }
        if (($state['fwdeFilters'] ?? '') !== '') {
            $groups = [];
            foreach (explode('|', (string) $state['fwdeFilters']) as $group) {
                $prefix = preg_match('/^(all|any):/', $group, $m) === 1 ? $m[0] : '';
                $conditions = [];
                foreach (explode(';', substr($group, strlen($prefix))) as $condition) {
                    $cut = strpos($condition, '~');
                    if ($cut !== false) {
                        $target = $map(substr($condition, 0, $cut));
                        if ($target === null) {
                            return null;
                        }
                        $condition = $target . substr($condition, $cut);
                    }
                    $conditions[] = $condition;
                }
                $groups[] = $prefix . implode(';', $conditions);
            }
            $state['fwdeFilters'] = implode('|', $groups);
        }
        return $state;
    }

    /**
     * The state with every goal id replaced by the target's goal of the same name, or null when the target lacks one
     * (or the goal no longer exists on the source website). The target's goals are read once.
     *
     * @param array<int, string> $sourceGoals idgoal => name
     */
    private function withTargetGoals(array $state, array $sourceGoals, int $idSite): ?array
    {
        $map = function (int $idGoal) use ($sourceGoals, $idSite): ?int {
            return $this->targetGoalId($idGoal, $sourceGoals, $idSite);
        };
        if (($state['fwdeGoal'] ?? '') !== '') {
            $goal = $map((int) $state['fwdeGoal']);
            if ($goal === null) {
                return null;
            }
            $state['fwdeGoal'] = (string) $goal;
        }
        $state = self::mapMetricIds($state, function (string $metric) use ($map): ?string {
            if (preg_match(self::METRIC_GOAL, $metric, $m) !== 1) {
                return $metric;
            }
            $goal = $map((int) $m[1]);
            return $goal === null ? null : 'goal_' . $goal . '_' . $m[2];
        });
        if ($state === null) {
            return null;
        }
        if (($state['fwdeSteps'] ?? '') !== '') {
            $steps = [];
            foreach (explode(';', $state['fwdeSteps']) as $step) {
                if (str_starts_with($step, 'goal~~') && ctype_digit(substr($step, 6))) {
                    $goal = $map((int) substr($step, 6));
                    if ($goal === null) {
                        return null;
                    }
                    $step = 'goal~~' . $goal;
                }
                $steps[] = $step;
            }
            $state['fwdeSteps'] = implode(';', $steps);
        }
        return $state;
    }

    /** The target's goal with the name of the source's goal, or null (no such goal on either website). */
    private function targetGoalId(int $idGoal, array $sourceGoals, int $idSite): ?int
    {
        $this->targetGoals[$idSite] ??= ($this->goalsOf)($idSite);
        $name = $sourceGoals[$idGoal] ?? null;
        $match = $name === null ? false : array_search($name, $this->targetGoals[$idSite], true);
        return $match === false ? null : (int) $match;
    }

    /**
     * The segment with the target's ids where it names things per website: custom dimensions ("dimension<id>") by
     * name and scope, converted goals ("visitConvertedGoalId") by name. Null when the target lacks one: a segment that
     * is merely valid there could filter on another dimension or goal with the same id.
     *
     * @param array<int, array{name: string, scope: string}|null> $sourceDimensions
     * @param array<int, string>|null $sourceGoals
     */
    private function targetSegment(string $segment, array $sourceDimensions, ?array $sourceGoals, int $idSite): ?string
    {
        $missing = false;
        $segment = (string) preg_replace_callback(self::SEGMENT_DIMENSION, function (array $m) use ($sourceDimensions, $idSite, &$missing): string {
            $target = $this->targetDimensionId($sourceDimensions[(int) $m[2]] ?? null, $idSite);
            $missing = $missing || $target === null;
            return $m[1] . 'dimension' . ($target ?? $m[2]);
        }, $segment);
        $segment = (string) preg_replace_callback(self::SEGMENT_GOAL, function (array $m) use ($sourceGoals, $idSite, &$missing): string {
            $target = $sourceGoals === null ? null : $this->targetGoalId((int) $m[3], $sourceGoals, $idSite);
            $missing = $missing || $target === null;
            return $m[1] . 'visitConvertedGoalId' . $m[2] . ($target ?? $m[3]);
        }, $segment);
        return $missing ? null : $segment;
    }

    /** The target's custom dimension with the source dimension's name and scope, or null. */
    private function targetDimensionId(?array $source, int $idSite): ?int
    {
        if (!isset($this->targetDimensionIds[$idSite])) {
            $this->targetDimensionIds[$idSite] = [];
            foreach (($this->customDimensionsOf)($idSite) as $dimension) {
                $this->targetDimensionIds[$idSite][$dimension['scope'] . "\n" . $dimension['name']] ??= (int) $dimension['id'];
            }
        }
        return $source === null ? null : ($this->targetDimensionIds[$idSite][$source['scope'] . "\n" . $source['name']] ?? null);
    }

    /**
     * The source website's custom dimensions the state (fwdeDims, fwdePivot) and the segment ("dimension<id>") use, by id: name
     * and scope. A custom dimension that is no longer active on the source maps to nothing, so every target is skipped.
     *
     * @return array<int, array{name: string, scope: string}|null>
     */
    private function sourceCustomDimensions(array $source): array
    {
        preg_match_all(self::SEGMENT_DIMENSION, (string) $source['segment'], $inSegment);
        $ids = array_merge(
            self::customDimensionIds(self::stateDimensions($source['state'])),
            array_map('intval', $inSegment[2])
        );
        if ($ids === []) {
            return [];
        }
        $active = [];
        foreach (($this->customDimensionsOf)((int) $source['idsite']) as $dimension) {
            $active[(int) $dimension['id']] = ['name' => (string) $dimension['name'], 'scope' => (string) $dimension['scope']];
        }
        $used = [];
        foreach ($ids as $id) {
            $used[$id] = $active[$id] ?? null;
        }
        return $used;
    }

    /** The dimensions of a state: its row dimensions (fwdeDims) and its pivot (fwdePivot), comma separated. */
    private static function stateDimensions(array $state): string
    {
        return implode(',', array_filter([(string) ($state['fwdeDims'] ?? ''), (string) ($state['fwdePivot'] ?? '')], 'strlen'));
    }

    /**
     * The state with the target's custom dimension ids in fwdeDims and fwdePivot, or null when the target lacks one of
     * the same name and scope.
     */
    private function withTargetDimensions(array $state, array $sourceDimensions, int $idSite): ?array
    {
        foreach (['fwdeDims', 'fwdePivot'] as $key) {
            if (($state[$key] ?? '') === '') {
                continue;
            }
            $dims = [];
            foreach (explode(',', (string) $state[$key]) as $dim) {
                if (preg_match('/^customDimension(\d+)$/', $dim, $m)) {
                    $target = $this->targetDimensionId($sourceDimensions[(int) $m[1]] ?? null, $idSite);
                    if ($target === null) {
                        return null;
                    }
                    $dim = 'customDimension' . $target;
                }
                $dims[] = $dim;
            }
            $state[$key] = implode(',', $dims);
        }
        return $state;
    }

    /** @return list<int> */
    private static function customDimensionIds(string $dims): array
    {
        preg_match_all('/(?:^|,)customDimension(\d+)(?=,|$)/', $dims, $m);
        return array_map('intval', $m[1]);
    }

    private static function siteExists(int $idSite): bool
    {
        try {
            Site::getNameFor($idSite);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Segments name things per website (custom dimensions, goals): the segment must be valid on the target. */
    private static function segmentWorks(string $segment, int $idSite): bool
    {
        if ($segment === '') {
            return true;
        }
        try {
            new Segment($segment, [$idSite]);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** @return array<int, string> idgoal => name */
    public static function goalsOf(int $idSite): array
    {
        return SiteGoals::names($idSite);
    }

    private static function skippedText(string $reason, int $idSite): string
    {
        $key = [
            'access' => 'FeinwerkDataExploration_CopySkippedAccess',
            'goal' => 'FeinwerkDataExploration_CopySkippedGoal',
            'ecommerce' => 'FeinwerkDataExploration_CopySkippedEcommerce',
            'dimension' => 'FeinwerkDataExploration_CopySkippedDimension',
            'segment' => 'FeinwerkDataExploration_CopySkippedSegment',
            'limit' => 'FeinwerkDataExploration_CopySkippedLimit',
        ][$reason];
        return self::translate($key, [self::siteName($idSite), (string) SavedExplorationRules::MAX_PER_USER_AND_SITE]);
    }

    private static function siteName(int $idSite): string
    {
        try {
            return Site::getNameFor($idSite) . ' (' . $idSite . ')';
        } catch (\Throwable $e) {
            return (string) $idSite;
        }
    }

    private static function translate(string $key, array $params): string
    {
        try {
            return Piwik::translate($key, $params);
        } catch (\Throwable $e) {
            return $key;
        }
    }
}
