<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Widgets;

use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationRules;
use Piwik\Piwik;
use Piwik\Common;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ComparisonList;
use Piwik\Plugins\FeinwerkDataExploration\Engine\AccessPolicy;
use Piwik\Plugins\FeinwerkDataExploration\Engine\LimitsProvider;
use Piwik\Plugins\FeinwerkDataExploration\Engine\TrendExplorer;
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
    private const MENU_NAMES = ['General_Actions' => 'Actions_Behaviour', 'Referrers_Referrers' => 'Referrers_Acquisition'];

    /**
     * Matomo's sections a saved exploration can show in as a report (saved reports, 2026-11-24), by Matomo's names;
     * Ecommerce only on an e-commerce website.
     *
     * @return list<array{id: string, name: string}>
     */
    public static function menuSections(int $idSite): array
    {
        $sections = [];
        foreach (SavedExplorationRules::MENU_SECTIONS as $id) {
            if ($id === SavedExplorationRules::MENU_ECOMMERCE && !Site::isEcommerceEnabledFor($idSite)) {
                continue;
            }
            // Matomo's menu names two sections differently from their ids (ActionsCategory, ReferrersCategory)
            $sections[] = ['id' => $id, 'name' => Piwik::translate(self::MENU_NAMES[$id] ?? $id)];
        }
        return $sections;
    }

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
        $config->setClientSideProps(WidgetProps::common($idSite, $limits) + [
            'maxRows' => $limits->maxRows,
            'maxCompareColumns' => ComparisonList::maxColumns($limits),
            'maxTrendLines' => TrendExplorer::maxLines($limits),
            'defaultRows' => $limits->defaultRows,
            'currencySymbol' => Site::getCurrencySymbolFor($idSite),
            'hourLabels' => self::hourLabels(),
            'menuSections' => self::menuSections($idSite),
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
