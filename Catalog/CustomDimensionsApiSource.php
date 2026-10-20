<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Catalog;

use Piwik\Plugin\Manager;
use Piwik\Plugins\CustomDimensions\API as CustomDimensionsApi;

/**
 * Reads the custom dimensions of a site from the CustomDimensions plugin (none when it is not activated).
 */
final class CustomDimensionsApiSource implements CustomDimensionSource
{
    public function activeFor(int $idSite): array
    {
        if (!Manager::getInstance()->isPluginActivated('CustomDimensions')) {
            return [];
        }

        try {
            $configured = CustomDimensionsApi::getInstance()->getConfiguredCustomDimensions($idSite);
        } catch (\Throwable $e) {
            // a failing CustomDimensions costs only its dimensions, not the exploration page
            return [];
        }
        $dimensions = [];
        foreach ($configured as $dimension) {
            if (empty($dimension['active']) || !in_array($dimension['scope'], ['visit', 'action'], true)) {
                continue;
            }
            $dimensions[] = [
                'id' => (int) $dimension['idcustomdimension'],
                'index' => (int) $dimension['index'],
                'scope' => (string) $dimension['scope'],
                'name' => (string) $dimension['name'],
            ];
        }
        return $dimensions;
    }
}
