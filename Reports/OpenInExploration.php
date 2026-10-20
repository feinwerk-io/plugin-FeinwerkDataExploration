<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Reports;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Engine\AccessPolicy;

/**
 * Adds "Open in exploration" to selected standard reports (spec 2.1): a datatable action that Matomo 5 shows as an
 * icon in the report footer and Matomo 6 as an item of the report menu. javascripts/exploreAction.js opens the
 * exploration with the dimension named in the action's second class (fwde-explore--<dimension>).
 */
final class OpenInExploration
{
    private ?DimensionCatalog $catalog;
    /** @var callable fn(int $idSite): bool */
    private $canUse;
    /** @var callable fn(string $name): mixed, the request parameters module, idSite, idDimension, disableLink and moduleToWidgetize */
    private $request;
    /** @var callable fn(string $key): string */
    private $translate;

    public function __construct(?DimensionCatalog $catalog = null, ?callable $canUse = null, ?callable $request = null, ?callable $translate = null)
    {
        $this->catalog = $catalog;
        $this->canUse = $canUse ?? [AccessPolicy::class, 'canUse'];
        $this->request = $request ?? static function (string $name) {
            $isText = in_array($name, ['module', 'moduleToWidgetize'], true);
            return Common::getRequestVar($name, $isText ? '' : 0, $isText ? 'string' : 'int');
        };
        $this->translate = $translate ?? [Piwik::class, 'translate'];
    }

    /**
     * Listener of ViewDataTable.configure.end; runs for every report view, so it returns early and cheaply.
     *
     * @param \Piwik\Plugin\ViewDataTable $view
     */
    public function configureView($view): void
    {
        $apiMethod = (string) ($view->requestConfig->apiMethodToRequestDataTable ?? '');
        if (!DimensionCatalog::linksReport($apiMethod)
            || !empty($view->requestConfig->idSubtable)
            // the row evolution popover renders the report again through CoreHome
            || ($view->config->controllerName ?? '') === 'CoreHome'
            || empty($view->config->show_footer)
            || empty($view->config->show_footer_icons)
            || !empty($view->config->show_visualization_only)
            // embedded widgets and exported dashboards: their reloads carry the report's module, but these two
            || ($this->request)('module') === 'Widgetize'
            || !empty(($this->request)('disableLink'))
            || ($this->request)('moduleToWidgetize') !== ''
        ) {
            return;
        }

        $idSite = (int) ($this->request)('idSite');
        if ($idSite < 1 || !($this->canUse)($idSite)) {
            return;
        }

        $dimension = $this->catalog()->reportLink($idSite, $apiMethod, ['idDimension' => ($this->request)('idDimension')]);
        if ($dimension === null) {
            return;
        }

        // a report of one channel adds its condition (fwde-scope--referrerType-search for referrerType==search)
        $scope = DimensionCatalog::reportScope($apiMethod);
        $view->config->datatable_actions[] = [
            'id' => 'fwde-explore fwde-explore--' . $dimension . ($scope === null ? '' : ' fwde-scope--' . str_replace('==', '-', $scope)),
            'title' => ($this->translate)('FeinwerkDataExploration_OpenInExploration'),
            'icon' => 'icon-lab',
        ];
    }

    private function catalog(): DimensionCatalog
    {
        return $this->catalog ??= new DimensionCatalog();
    }
}
