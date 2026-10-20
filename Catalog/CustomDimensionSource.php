<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Catalog;

/**
 * The active custom dimensions of a site.
 */
interface CustomDimensionSource
{
    /**
     * @return array<int, array{id: int, index: int, scope: string, name: string}>
     */
    public function activeFor(int $idSite): array;
}
