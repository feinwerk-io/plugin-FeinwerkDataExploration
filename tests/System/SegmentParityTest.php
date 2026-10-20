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
use Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures\ExplorationFixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * Every result row carries the Matomo segment of the visits behind it ("show these visits"): Matomo's own visit count
 * with that segment must equal the row's visits (visit dimensions) or visits with that action (action dimensions).
 *
 * @group FeinwerkDataExploration
 */
class SegmentParityTest extends SystemTestCase
{
    /** @var ExplorationFixture */
    public static $fixture = null;

    /**
     * @dataProvider dimensions
     */
    public function testRowSegmentsSelectTheRowsVisits(string $dimension, int $minRows): void
    {
        if ($dimension === 'customDimension') {
            $dimension = 'customDimension' . self::$fixture->idVisitDimension;
        } elseif ($dimension === 'actionDimension') {
            $dimension = 'customDimension' . self::$fixture->idActionDimension;
        }
        $rows = $this->rows([$dimension]);
        $withSegment = array_filter($rows, fn (array $row) => $row['segment'] !== false);
        $this->assertGreaterThanOrEqual($minRows, count($withSegment), $dimension . ': rows with a segment');

        foreach ($withSegment as $label => $row) {
            $this->assertSame($row['nb_visits'], $this->visitsWith($row['segment']), $dimension . ' "' . $label . '": ' . $row['segment']);
        }
    }

    public static function dimensions(): array
    {
        return [
            'channel type' => ['channelType', 3],
            'referrer name' => ['referrerName', 2],
            'campaign name' => ['campaignName', 1],
            'visitor type' => ['visitorType', 2],
            'country' => ['country', 3],
            'region' => ['region', 3],
            'city' => ['city', 3],
            'language' => ['language', 3],
            'device type' => ['deviceType', 3],
            'operating system' => ['operatingSystem', 3],
            'browser' => ['browser', 3],
            'resolution' => ['resolution', 3],
            'entry page URL' => ['entryPageUrl', 3],
            'entry page title' => ['entryPageTitle', 3],
            'exit page URL' => ['exitPageUrl', 3],
            'exit page title' => ['exitPageTitle', 3],
            'visit converted' => ['visitConverted', 2],
            'page URL' => ['pageUrl', 3],
            'page title' => ['pageTitle', 3],
            'event category' => ['eventCategory', 1],
            'event action' => ['eventAction', 2],
            'event name' => ['eventName', 2],
            'download URL' => ['downloadUrl', 1],
            'outlink URL' => ['outlinkUrl', 1],
            'site search keyword' => ['siteSearchKeyword', 2],
            'custom dimension, visit' => ['customDimension', 2],
            'custom dimension, action' => ['actionDimension', 2],
        ];
    }

    public function testTwoDimensionsJoinTheirSegments(): void
    {
        foreach ($this->rows(['browser', 'country']) as $label => $row) {
            $this->assertStringContainsString(';', (string) $row['segment'], $label);
            $this->assertSame($row['nb_visits'], $this->visitsWith($row['segment']), $label);
        }
    }

    /** A row without a stored value ("… not defined") cannot be filtered to: the page disables that entry. */
    public function testRowsWithoutAValueAreMarked(): void
    {
        $table = Request::processRequest('FeinwerkDataExploration.getExploration', [
            'idSite' => 1, 'period' => 'range', 'date' => ExplorationFixture::RANGE,
            'definition' => json_encode(['dimensions' => ['referrerName', 'browser'], 'metrics' => ['nb_visits']]),
        ]);
        $marked = 0;
        foreach ($table->getRows() as $row) {
            // direct visits have no referrer name; every other row has a value and a segment
            $label = $row->getColumn('label') . ' | ' . $row->getColumn('secondary_label');
            $this->assertSame($row->getMetadata('segment') === false, (bool) $row->getMetadata('fwde_no_value'), $label);
            $marked += $row->getMetadata('fwde_no_value') ? 1 : 0;
        }
        $this->assertGreaterThan(0, $marked);

        $export = Request::processRequest('FeinwerkDataExploration.getExploration', [
            'idSite' => 1, 'period' => 'range', 'date' => ExplorationFixture::RANGE, 'readableColumns' => 1,
            'definition' => json_encode(['dimensions' => ['referrerName'], 'metrics' => ['nb_visits']]),
        ]);
        foreach ($export->getRows() as $row) {
            $this->assertFalse($row->getMetadata('fwde_no_value'), 'exports leave the marker out');
        }
    }

    public function testTimeDimensionsHaveNoSegment(): void
    {
        foreach (['date', 'weekday', 'hour'] as $dimension) {
            foreach ($this->rows([$dimension]) as $label => $row) {
                $this->assertFalse($row['segment'], $dimension . ' ' . $label);
            }
        }
    }

    public function testExportsLeaveTheSegmentOut(): void
    {
        $table = Request::processRequest('FeinwerkDataExploration.getExploration', [
            'idSite' => 1, 'period' => 'range', 'date' => ExplorationFixture::RANGE, 'readableColumns' => 1,
            'definition' => json_encode(['dimensions' => ['browser'], 'metrics' => ['nb_visits']]),
        ]);
        $this->assertFalse($table->getFirstRow()->getMetadata('segment'));
    }

    /**
     * @return array<string, array{nb_visits: int, segment: string|false}>
     */
    private function rows(array $dimensions): array
    {
        $table = Request::processRequest('FeinwerkDataExploration.getExploration', [
            'idSite' => 1, 'period' => 'range', 'date' => ExplorationFixture::RANGE,
            'definition' => json_encode(['dimensions' => $dimensions, 'metrics' => ['nb_visits'], 'limit' => 1000]),
        ]);
        $this->assertInstanceOf(DataTable::class, $table);
        $rows = [];
        foreach ($table->getRows() as $row) {
            $label = $row->getColumn('label') . ($row->getColumn('secondary_label') === false ? '' : ' | ' . $row->getColumn('secondary_label'));
            $rows[$label] = ['nb_visits' => (int) $row->getColumn('nb_visits'), 'segment' => $row->getMetadata('segment')];
        }
        return $rows;
    }

    private function visitsWith(string $segment): int
    {
        $table = Request::processRequest('VisitsSummary.get', [
            'idSite' => 1, 'period' => 'range', 'date' => ExplorationFixture::RANGE, 'segment' => $segment, 'format_metrics' => 0,
        ]);
        return (int) $table->getFirstRow()->getColumn('nb_visits');
    }
}

SegmentParityTest::$fixture = new ExplorationFixture();
