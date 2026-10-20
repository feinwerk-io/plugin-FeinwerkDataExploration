<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Query;

/**
 * What the SQL fragments of dimensions and metrics depend on: the query scope ("visit": base table log_visit,
 * "action": base table log_link_visit_action), the site timezone and the query window in UTC.
 */
final class QueryContext
{
    public const SCOPE_VISIT = 'visit';
    public const SCOPE_ACTION = 'action';

    public function __construct(
        public readonly string $scope,
        public readonly string $timezone,
        public readonly string $windowStartUtc,
        public readonly string $windowEndUtc
    ) {
    }
}
