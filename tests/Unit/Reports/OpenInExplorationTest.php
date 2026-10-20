<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Reports;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\ColumnProbe;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\CustomDimensionSource;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\LabelFormatter;
use Piwik\Plugins\FeinwerkDataExploration\Reports\OpenInExploration;

/**
 * @group FeinwerkDataExploration
 */
class OpenInExplorationTest extends TestCase
{
    private const ACTION = ['id' => 'fwde-explore fwde-explore--browser', 'title' => 'FeinwerkDataExploration_OpenInExploration', 'icon' => 'icon-lab'];

    public function testAdmittedReportGetsTheAction(): void
    {
        $view = $this->view();
        $this->listener()->configureView($view);

        $this->assertSame([self::ACTION], $view->config->datatable_actions);
    }

    public function testKeepsOtherActions(): void
    {
        $view = $this->view();
        $view->config->datatable_actions = [['id' => 'other', 'title' => 'Other', 'icon' => 'icon-x']];
        $this->listener()->configureView($view);

        $this->assertSame([['id' => 'other', 'title' => 'Other', 'icon' => 'icon-x'], self::ACTION], $view->config->datatable_actions);
    }

    /**
     * @dataProvider skipCases
     */
    public function testSkipRules(array $requestConfig, array $config, array $request, bool $canUse): void
    {
        $view = $this->view($requestConfig, $config);
        $this->listener($request, $canUse)->configureView($view);

        $this->assertSame([], $view->config->datatable_actions);
    }

    public static function skipCases(): array
    {
        return [
            'unmapped report' => [['apiMethodToRequestDataTable' => 'Live.getLastVisitsDetails'], [], [], true],
            'subtable' => [['idSubtable' => 5], [], [], true],
            'row evolution popover' => [[], ['controllerName' => 'CoreHome'], [], true],
            'no footer' => [[], ['show_footer' => false], [], true],
            'no footer icons' => [[], ['show_footer_icons' => false], [], true],
            'visualization only' => [[], ['show_visualization_only' => true], [], true],
            'widget iframe' => [[], [], ['module' => 'Widgetize'], true],
            // reloads inside a widget iframe and the widgets of an exported dashboard carry the report's module
            'reload inside a widget iframe' => [[], [], ['disableLink' => 1, 'moduleToWidgetize' => 'DevicesDetection'], true],
            'exported dashboard' => [[], [], ['disableLink' => 1], true],
            'widgetized module only' => [[], [], ['moduleToWidgetize' => 'Dashboard'], true],
            'no site' => [[], [], ['idSite' => 0], true],
            'refused by the access policy' => [[], [], [], false],
            'dimension not available' => [['apiMethodToRequestDataTable' => 'CustomDimensions.getCustomDimension'], [], ['idDimension' => 99], true],
        ];
    }

    private function view(array $requestConfig = [], array $config = []): object
    {
        return (object) [
            'requestConfig' => (object) ($requestConfig + ['apiMethodToRequestDataTable' => 'DevicesDetection.getBrowsers', 'idSubtable' => false]),
            'config' => (object) ($config + [
                'controllerName' => 'DevicesDetection',
                'show_footer' => true,
                'show_footer_icons' => true,
                'show_visualization_only' => false,
                'datatable_actions' => [],
            ]),
        ];
    }

    private function listener(array $request = [], bool $canUse = true): OpenInExploration
    {
        $request += ['module' => 'CoreHome', 'idSite' => 1, 'idDimension' => 0, 'disableLink' => 0, 'moduleToWidgetize' => ''];
        $customDimensions = new class implements CustomDimensionSource {
            public function activeFor(int $idSite): array
            {
                return [['id' => 3, 'index' => 1, 'scope' => 'visit', 'name' => 'Customer type']];
            }
        };
        $catalog = new DimensionCatalog(
            fn (string $coreId) => $coreId,
            $customDimensions,
            new LabelFormatter(fn (string $key, array $params = []) => $key),
            new ColumnProbe(fn () => [])
        );

        return new OpenInExploration(
            $catalog,
            fn (int $idSite) => $canUse,
            fn (string $name) => $request[$name],
            fn (string $key) => $key
        );
    }
}
