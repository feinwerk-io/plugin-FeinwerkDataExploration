<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Widgets;

use Piwik\Common;
use Piwik\Plugins\FeinwerkDataExploration\Definition\FunnelDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\AccessPolicy;
use Piwik\Plugins\FeinwerkDataExploration\Engine\LimitsProvider;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

/**
 * The funnel page (spec 3): a client-rendered Vue app inside Matomo's reporting page, like the free-form table; no
 * dashboard widget.
 */
class GetFunnel extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('FeinwerkDataExploration_Explorations');
        $config->setSubcategoryId('FeinwerkDataExploration_Funnel');
        $config->setName('FeinwerkDataExploration_Funnel');
        $config->setOrder(1);
        $config->setIsWide();
        $config->setIsNotWidgetizable();
        $config->setClientSideComponent('FeinwerkDataExploration', 'FunnelPage');

        $idSite = Common::getRequestVar('idSite', 0, 'int');
        if ($idSite < 1 || !AccessPolicy::canUse($idSite)) {
            $config->disable();
            return;
        }

        $limits = (new LimitsProvider())->get();
        $config->setClientSideProps(WidgetProps::common($idSite, $limits) + [
            'maxSteps' => FunnelDefinition::maxSteps($limits),
        ]);
    }
}
