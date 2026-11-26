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
        public readonly int $maxCompareColumns = 4,
        /** lines of a line chart (trend and time dimensions); TrendExplorer::maxLines() clamps it to 1..10 */
        public readonly int $maxTrendLines = 5,
        /**
         * Seconds a call waits for a free slot (ConcurrencyGate) before it answers "busy": 0 for the page, which shows
         * the message at once; saved reports wait (2026-11-24: a dashboard loads its widgets in parallel, Matomo's
         * e-mail drops a report that fails).
         */
        public readonly int $slotWait = 0
    ) {
    }

    /** The same limits with another statement time limit (archived reports, 2026-11-25: archive_query_seconds). */
    public function withTimeoutSeconds(int $seconds): self
    {
        $values = get_object_vars($this);
        $values['timeoutSeconds'] = max(1, $seconds);
        return new self(...$values);
    }

    /** The same limits with another wait for a slot. */
    public function withSlotWait(int $seconds): self
    {
        $values = get_object_vars($this);
        $values['slotWait'] = max(0, $seconds);
        return new self(...$values);
    }
}
