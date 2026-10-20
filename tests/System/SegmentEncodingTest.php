<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\System;

use Piwik\API\Request;
use Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures\SpecialValuesFixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * Row segments of values with a literal "+", percent sequences, commas and semicolons select exactly the row's
 * visits. Matomo decodes a segment value twice (the segment string, then the value), so the value is encoded twice,
 * as Matomo's own Actions reports do.
 *
 * @group FeinwerkDataExploration
 */
class SegmentEncodingTest extends SystemTestCase
{
    /** @var SpecialValuesFixture */
    public static $fixture = null;

    /**
     * @dataProvider dimensions
     */
    public function testRowSegmentsCarrySpecialCharacters(string $dimension, int $rowsWithSegment): void
    {
        $table = Request::processRequest('FeinwerkDataExploration.getExploration', [
            'idSite' => 1, 'period' => 'day', 'date' => SpecialValuesFixture::DATE,
            'definition' => json_encode(['dimensions' => [$dimension], 'metrics' => ['nb_visits']]),
        ]);

        $checked = 0;
        foreach ($table->getRows() as $row) {
            $segment = $row->getMetadata('segment');
            if ($segment === false) {
                continue;
            }
            $visits = Request::processRequest('VisitsSummary.get', [
                'idSite' => 1, 'period' => 'day', 'date' => SpecialValuesFixture::DATE, 'segment' => $segment, 'format_metrics' => 0,
            ])->getFirstRow()->getColumn('nb_visits');
            $this->assertSame((int) $row->getColumn('nb_visits'), (int) $visits, $dimension . ' "' . $row->getColumn('label') . '": ' . $segment);
            $checked++;
        }
        $this->assertGreaterThanOrEqual($rowsWithSegment, $checked, $dimension . ': rows with a segment');
    }

    public static function dimensions(): array
    {
        return [
            'page URL' => ['pageUrl', 3],
            'page title' => ['pageTitle', 3],
            'entry page URL' => ['entryPageUrl', 3],
            'entry page title' => ['entryPageTitle', 3],
            'exit page URL' => ['exitPageUrl', 2],
            'exit page title' => ['exitPageTitle', 2],
            'download URL' => ['downloadUrl', 1],
            'outlink URL' => ['outlinkUrl', 1],
            'event category' => ['eventCategory', 2],
            'event name' => ['eventName', 2],
            'site search keyword' => ['siteSearchKeyword', 1],
        ];
    }
}

SegmentEncodingTest::$fixture = new SpecialValuesFixture();
