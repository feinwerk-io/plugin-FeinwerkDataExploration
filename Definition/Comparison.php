<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Definition;

/**
 * One comparison column of the free-form table: a segment ('' for all visits), a period and a date, as Matomo's
 * comparison controls set them.
 */
final class Comparison
{
    public function __construct(
        public readonly string $segment,
        public readonly string $period,
        public readonly string $date
    ) {
    }
}
