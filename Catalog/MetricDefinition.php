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
    public function __construct(
        public readonly string $id,
        public readonly string $scope,
        public readonly string $nameKey,
        public readonly ?string $nameInActionScopeKey = null,
        public readonly ?string $kind = null
    ) {
    }
}
