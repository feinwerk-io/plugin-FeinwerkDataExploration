<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\System;

use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures\SpecialValuesFixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * "Filter to this value" as the row menu builds it: an "equals" filter on the row's stored value where the API sends
 * one (fwde_value, fwde_value2), else on its label. Every row must find itself, also when Matomo shows the label
 * decoded (a literal "+", percent sequences, HTML-escaped titles).
 *
 * @group FeinwerkDataExploration
 */
class RowFilterTest extends SystemTestCase
{
    /** @var SpecialValuesFixture */
    public static $fixture = null;

    /**
     * @dataProvider dimensions
     */
    public function testEveryRowFindsItself(array $dimensions): void
    {
        $rows = $this->explore($dimensions, []);
        $this->assertGreaterThan(0, $rows->getRowsCount());

        foreach ($rows->getRows() as $row) {
            if ($row->getMetadata('fwde_no_value')) {
                continue;
            }
            $filters = [['target' => 'label', 'operator' => 'equals', 'value' => $this->filterValue($row, 'fwde_value', 'label')]];
            if (count($dimensions) === 2) {
                $filters[] = ['target' => 'secondary_label', 'operator' => 'equals', 'value' => $this->filterValue($row, 'fwde_value2', 'secondary_label')];
            }
            $found = $this->explore($dimensions, $filters);
            $label = $row->getColumn('label') . ' | ' . $row->getColumn('secondary_label');
            $this->assertSame(1, $found->getRowsCount(), $label);
            $this->assertSame($row->getColumn('nb_visits'), $found->getFirstRow()->getColumn('nb_visits'), $label);
        }
    }

    public static function dimensions(): array
    {
        return [
            'page URL' => [['pageUrl']],
            'page title' => [['pageTitle']],
            'event category' => [['eventCategory']],
            'event name' => [['eventName']],
            'outlink URL' => [['outlinkUrl']],
            'site search keyword' => [['siteSearchKeyword']],
            'page title x page URL' => [['pageTitle', 'pageUrl']],
            'browser (label filter on a coded dimension)' => [['browser']],
        ];
    }

    private function filterValue(DataTable\Row $row, string $valueKey, string $labelColumn): string
    {
        $value = $row->getMetadata($valueKey);
        return (string) ($value === false ? $row->getColumn($labelColumn) : $value);
    }

    private function explore(array $dimensions, array $filters): DataTable
    {
        return Request::processRequest('FeinwerkDataExploration.getExploration', [
            'idSite' => 1, 'period' => 'day', 'date' => SpecialValuesFixture::DATE,
            'definition' => json_encode(['dimensions' => $dimensions, 'metrics' => ['nb_visits'], 'filters' => $filters]),
        ]);
    }
}

RowFilterTest::$fixture = new SpecialValuesFixture();
