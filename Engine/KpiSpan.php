<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

/**
 * Days a KPI card's number or one of its comparisons covers (2026-11-24): from the first day's start to the end of the
 * last day, or to the last day at a time of day (a span that includes today, and its comparisons), in the website's
 * timezone.
 */
final class KpiSpan
{
    public function __construct(
        /** YYYY-MM-DD */
        public readonly string $firstDay,
        /** YYYY-MM-DD */
        public readonly string $lastDay,
        /** HH:MM:SS on the last day, null for the whole day */
        public readonly ?string $until = null
    ) {
    }
}
