<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Categories;

use Piwik\Category\Category;

/**
 * The top-level "Explorationen" entry of the reporting menu.
 */
class ExplorationsCategory extends Category
{
    protected $id = 'FeinwerkDataExploration_Explorations';
    protected $order = 90;
    protected $icon = 'icon-table';
}
