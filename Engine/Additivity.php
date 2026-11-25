<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;

/**
 * Whether the rows of a table add up to its total (design 2026-11-20): every counted unit of every chosen metric
 * belongs to exactly one row. Only then an "Others" row and a donut are exact; otherwise the table shows a hint. The
 * same rule as vue/src/additivity.ts.
 */
final class Additivity
{
    /** what a distinct count counts (it can fall into several rows whatever the dimensions); "visit" otherwise */
    private const DISTINCT_UNIT = ['nb_uniq_visitors' => 'visitor', 'nb_uniq_pageviews' => 'visit'];

    /**
     * The unit that can count in several rows ("visit" or "visitor"), null when the rows add up.
     */
    public static function overcountUnit(ExplorationDefinition $definition, DimensionCatalog $dimensions, MetricCatalog $metrics, int $idSite): ?string
    {
        foreach ($definition->metricIds as $id) {
            if ($metrics->get($id)->aggregation === MetricDefinition::AGG_DISTINCT) {
                return self::DISTINCT_UNIT[$id] ?? 'visit';
            }
        }

        $actionTable = $definition->scope($dimensions, $metrics, $idSite) === DimensionCatalog::SCOPE_ACTION;
        $actionDimension = false;
        foreach ($definition->dimensionIds as $id) {
            $dimension = $dimensions->get($idSite, $id);
            // the time dimensions read the action time in an action table (QueryContext), so a visit can span several
            if ($dimension->scope === DimensionCatalog::SCOPE_ACTION || ($actionTable && $dimension->group === 'time')) {
                $actionDimension = true;
            }
        }
        if (!$actionDimension) {
            return null;
        }
        foreach ($definition->metricIds as $id) {
            if ($metrics->get($id)->scope !== MetricCatalog::SCOPE_ACTION) {
                return 'visit';
            }
        }
        return null;
    }

    /**
     * Whether a metric's cells add up across the values of a pivot dimension (rows and pivot, 2026-11-24), so the
     * "Others" column of the rest is exact: a sum, and no visit counted under two values (an action pivot, or a time
     * pivot read at the action time, counts a visit under each of its values unless the metric counts actions).
     *
     * @param string $pivotScope the scope of the pivot's queries (Explorer::pivotScope())
     */
    public static function addsUpAcross(DimensionDefinition $pivot, MetricDefinition $metric, string $pivotScope): bool
    {
        if (!$metric->addsUp()) {
            return false;
        }
        $perAction = $pivot->scope === DimensionCatalog::SCOPE_ACTION
            || ($pivotScope === DimensionCatalog::SCOPE_ACTION && $pivot->group === 'time');
        return !$perAction || $metric->scope === MetricCatalog::SCOPE_ACTION;
    }
}
