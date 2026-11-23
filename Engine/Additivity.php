<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;

/**
 * Whether the rows of a table add up to its total (design 2026-11-20): every counted unit of every chosen metric
 * belongs to exactly one row. Only then an "Others" row and a donut are exact; otherwise the table shows a hint. The
 * same rule as vue/src/additivity.ts.
 */
final class Additivity
{
    /** metrics that count something that can fall into several rows whatever the dimensions: the unit they count */
    private const NEVER_ADDING_UP = ['nb_uniq_visitors' => 'visitor', 'nb_uniq_pageviews' => 'visit'];

    /**
     * The unit that can count in several rows ("visit" or "visitor"), null when the rows add up.
     */
    public static function overcountUnit(ExplorationDefinition $definition, DimensionCatalog $dimensions, MetricCatalog $metrics, int $idSite): ?string
    {
        foreach ($definition->metricIds as $id) {
            if (isset(self::NEVER_ADDING_UP[$id])) {
                return self::NEVER_ADDING_UP[$id];
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
}
