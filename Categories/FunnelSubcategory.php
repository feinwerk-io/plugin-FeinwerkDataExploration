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

class FunnelSubcategory extends Subcategory
{
    protected $categoryId = 'FeinwerkDataExploration_Explorations';
    protected $id = 'FeinwerkDataExploration_Funnel';
    protected $name = 'FeinwerkDataExploration_Funnel';
    protected $order = 2;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('FeinwerkDataExploration_FunnelHelp') . '</p>';
    }
}
