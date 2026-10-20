<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;

class FreeFormTableSubcategory extends Subcategory
{
    protected $categoryId = 'FeinwerkDataExploration_Explorations';
    protected $id = 'FeinwerkDataExploration_FreeFormTable';
    protected $name = 'FeinwerkDataExploration_FreeFormTable';
    protected $order = 1;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('FeinwerkDataExploration_FreeFormTableHelp') . '</p>';
    }
}
