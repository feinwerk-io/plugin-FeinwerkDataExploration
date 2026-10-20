<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Widgets;

use Piwik\Common;
use Piwik\Plugins\FeinwerkDataExploration\Engine\AccessPolicy;
use Piwik\Plugins\FeinwerkDataExploration\Engine\LimitsProvider;
use Piwik\Plugins\FeinwerkDataExploration\Engine\RawDataRetention;
use Piwik\Site;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

/**
 * The exploration page: a client-rendered Vue app inside Matomo's reporting page, so the period selector and the
 * segment editor drive it. configure() runs whenever the widget list is built, so it stays cheap; the app loads the
 * dimension, metric and goal lists itself.
 */
class GetExploration extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('FeinwerkDataExploration_Explorations');
        $config->setSubcategoryId('FeinwerkDataExploration_FreeFormTable');
        $config->setName('FeinwerkDataExploration_FreeFormTable');
        $config->setOrder(1);
        $config->setIsWide();
        $config->setIsNotWidgetizable();
        $config->setClientSideComponent('FeinwerkDataExploration', 'ExplorationPage');

        $idSite = Common::getRequestVar('idSite', 0, 'int');
        if ($idSite < 1 || !AccessPolicy::canUse($idSite)) {
            $config->disable();
            return;
        }

        $limits = (new LimitsProvider())->get();
        $config->setClientSideProps([
            'idSite' => $idSite,
            'maxRows' => $limits->maxRows,
            'defaultRows' => $limits->defaultRows,
            'maxDays' => $limits->maxDays,
            'rawDataFrom' => RawDataRetention::earliestCompleteDay($idSite),
            'currencySymbol' => Site::getCurrencySymbolFor($idSite),
            'hourLabels' => self::hourLabels(),
            'canShare' => AccessPolicy::canShare($idSite),
        ]);
    }

    /**
     * The 24 hour labels as the exploration API writes them (VisitTime, the user's 12- or 24-hour clock); the chart
     * orders the hours by them. Empty without VisitTime (then there is no hour dimension).
     *
     * @return string[]
     */
    private static function hourLabels(): array
    {
        if (!function_exists('Piwik\Plugins\VisitTime\getTimeLabel')) {
            return [];
        }
        return array_map(static fn (int $hour): string => \Piwik\Plugins\VisitTime\getTimeLabel($hour), range(0, 23));
    }
}
