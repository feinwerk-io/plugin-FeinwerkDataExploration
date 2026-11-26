<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Archive;

use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricDefinition;

/**
 * What an archived exploration stores per metric (design 2026-11-25): sums and maxima as they are, rates and averages as
 * their numerator (`<id>__n`) and denominator (`<id>__d`) so every period is exact when Matomo adds up the days, and
 * distinct counts (unique visitors, unique pageviews) for a day only, because days cannot be added up for them.
 */
final class StoredMetrics
{
    /** decimals of a rate or average when read, as the live SQL rounds it (Query/ExplorationQueryBuilder::metricSql) */
    public const PRECISION = [
        'bounce_rate' => 2, 'avg_time_on_site' => 0, 'nb_actions_per_visit' => 1, 'conversion_rate' => 4,
        'revenue_per_visit' => 2, 'avg_event_value' => 2, 'goal_conversion_rate' => 4, 'goal_avg_order_revenue' => 2,
    ];

    /** @return list<string> the columns an archive stores for the metric */
    public static function columns(string $metricId): array
    {
        return self::isPair($metricId) ? [$metricId . '__n', $metricId . '__d'] : [$metricId];
    }

    /**
     * How Matomo combines each stored column when it adds up days into a longer period.
     *
     * @param list<string> $metricIds
     * @return array<string, string> stored column => "sum", "max" or "skip"
     */
    public static function aggregationOps(array $metricIds): array
    {
        $ops = [];
        foreach ($metricIds as $metricId) {
            $aggregation = self::aggregation($metricId);
            $op = $aggregation === MetricDefinition::AGG_MAX ? 'max' : ($aggregation === MetricDefinition::AGG_DISTINCT ? 'skip' : 'sum');
            foreach (self::columns($metricId) as $column) {
                $ops[$column] = $op;
            }
        }
        return $ops;
    }

    /**
     * A metric's value from a row's stored columns: a rate or average from its pair (0 without a denominator, as the live
     * SQL answers), a distinct count only for a day (null otherwise), any other metric as stored (0 when missing).
     *
     * @param array<string, mixed> $columns
     */
    public static function value(string $metricId, array $columns, bool $day): int|float|null
    {
        if (self::isPair($metricId)) {
            $denominator = (float) ($columns[$metricId . '__d'] ?? 0);
            if ($denominator <= 0) {
                return 0.0;
            }
            return round((float) ($columns[$metricId . '__n'] ?? 0) / $denominator, self::PRECISION[self::precisionKey($metricId)] ?? 2);
        }
        if (self::aggregation($metricId) === MetricDefinition::AGG_DISTINCT && !$day) {
            return null;
        }
        $value = $columns[$metricId] ?? 0;
        return is_int($value) || is_float($value) ? $value : (is_numeric($value) ? $value + 0 : 0);
    }

    /** the key of PRECISION for a metric id: goal and e-commerce metrics share their kind's entry */
    public static function precisionKey(string $metricId): string
    {
        if (preg_match('/^goal_(?:\d+|ecommerceOrder|ecommerceAbandonedCart)_(conversion_rate|avg_order_revenue)$/', $metricId, $m) === 1) {
            return 'goal_' . $m[1];
        }
        return $metricId;
    }

    public static function isPair(string $metricId): bool
    {
        return in_array(self::aggregation($metricId), [MetricDefinition::AGG_RATIO, MetricDefinition::AGG_AVERAGE], true);
    }

    private static function aggregation(string $metricId): string
    {
        return MetricCatalog::meta($metricId)['aggregation'] ?? MetricDefinition::AGG_SUM;
    }
}
