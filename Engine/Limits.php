<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

/**
 * The limits that protect the instance (spec 3). The defaults are those of the free version.
 */
final class Limits
{
    public function __construct(
        public readonly int $maxDays = 31,
        public readonly int $defaultRows = 100,
        public readonly int $maxRows = 1000,
        public readonly int $timeoutSeconds = 30,
        public readonly int $maxVisits = 2000000,
        public readonly int $maxConcurrent = 2,
        public readonly bool $useReader = true,
        /** steps of a funnel; the free version has 3, FunnelDefinition::maxSteps() clamps it to 2..10 */
        public readonly int $maxFunnelSteps = 3,
        /** columns per metric when comparing, the own one included; ComparisonList::maxColumns() clamps it to 1..10 */
        public readonly int $maxCompareColumns = 3,
        /** lines of a line chart (trend and time dimensions); TrendExplorer::maxLines() clamps it to 1..10 */
        public readonly int $maxTrendLines = 5
    ) {
    }
}
