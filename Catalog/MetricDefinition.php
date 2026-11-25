<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Catalog;

/**
 * One metric of the positive list. Action metrics have a kind (pageview, event, download, outlink): the kind of action
 * rows they count. Scope "visit" metrics next to an action dimension count the visits that contain
 * such an action, so they carry a second name that says so.
 */
final class MetricDefinition
{
    public const UNIT_COUNT = 'count';
    public const UNIT_MONEY = 'money';
    /** a fraction from 0 to 1 */
    public const UNIT_RATE = 'rate';
    public const UNIT_SECONDS = 'seconds';
    public const UNIT_NUMBER = 'number';
    /** rows add up to the total */
    public const AGG_SUM = 'sum';
    /** a count that can count one visitor or visit in several rows */
    public const AGG_DISTINCT = 'distinct';
    public const AGG_RATIO = 'ratio';
    public const AGG_AVERAGE = 'average';
    public const AGG_MAX = 'max';

    /**
     * @param string $unit count, money (the website's currency), rate (0 to 1), seconds or number (2026-11-24)
     * @param string $aggregation sum, distinct, ratio, average or max: how rows combine
     * @param string $group visits, goals, ecommerce or actions: the heading in the metric list
     * @param string|null $goalKey the goal of a per-goal metric ("3"), "ecommerceOrder", "ecommerceAbandonedCart"
     * @param string|null $goalName the goal's name (per-goal metrics of a website)
     */
    public function __construct(
        public readonly string $id,
        public readonly string $scope,
        public readonly string $nameKey,
        public readonly ?string $nameInActionScopeKey = null,
        public readonly ?string $kind = null,
        public readonly string $unit = self::UNIT_COUNT,
        public readonly string $aggregation = self::AGG_SUM,
        public readonly string $group = 'visits',
        public readonly ?string $goalKey = null,
        public readonly ?string $goalName = null
    ) {
    }

    /**
     * The name people read: "<goal name>: <column name>" for a goal's metric, the "these visits" name of a visit metric
     * next to an action dimension.
     *
     * @param callable $translate fn(string $key): string
     */
    public function label(callable $translate, bool $actionScope = false): string
    {
        $key = $actionScope && $this->nameInActionScopeKey !== null ? $this->nameInActionScopeKey : $this->nameKey;
        $name = $translate($key);
        return $this->goalName === null ? $name : $this->goalName . ': ' . $name;
    }

    /** a share of the column total and a bar in the cell make sense; a missing row counts 0 */
    public function hasShare(): bool
    {
        return in_array($this->aggregation, [self::AGG_SUM, self::AGG_DISTINCT], true);
    }

    /** the rows add up to the total ("Others", the donut) */
    public function addsUp(): bool
    {
        return $this->aggregation === self::AGG_SUM;
    }

    /** a missing point of a line is 0 (counts and sums), not a gap */
    public function zeroForMissing(): bool
    {
        return $this->hasShare();
    }

    /** whole numbers: counts, seconds (rounded) and maxima */
    public function isInteger(): bool
    {
        return in_array($this->unit, [self::UNIT_COUNT, self::UNIT_SECONDS], true) || $this->aggregation === self::AGG_MAX;
    }
}
