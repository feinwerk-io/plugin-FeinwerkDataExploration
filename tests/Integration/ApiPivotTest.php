<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Cache;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures\ExplorationFixture;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * A pivot dimension (rows and pivot, 2026-11-24): its top values become column groups, followed by Others and Total.
 * The plain explorations are the oracle: a cell is the row of an exploration with the pivot dimension as one more row
 * dimension, Total the row of the exploration without the pivot.
 *
 * Page titles over the fixture's range by pageviews: Home, Blog A and Shop 6 each, Blog B 5, Product 2, Tom & Jerry 1.
 *
 * @group FeinwerkDataExploration
 */
class ApiPivotTest extends IntegrationTestCase
{
    /** @var ExplorationFixture */
    public static $fixture = null;

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->extraTestEnvVars['loadRealTranslations'] = true;
    }

    public function provideContainerConfig()
    {
        return ['Piwik\\Access' => new FakeAccess()];
    }

    public function setUp(): void
    {
        parent::setUp();
        Fixture::loadAllTranslations();
        FakeAccess::$superUser = true;
        Cache::getLazyCache()->flushAll(); // results are cached across tests of one PHP process
    }

    public function testTopValuesInOrderAndLimit(): void
    {
        $table = $this->table(['dimensions' => ['deviceType'], 'metrics' => ['nb_pageviews'], 'pivot' => ['dimension' => 'pageTitle', 'limit' => 3]]);
        $oracle = self::rows($this->table(['dimensions' => ['pageTitle'], 'metrics' => ['nb_pageviews'], 'sort' => 'nb_pageviews', 'limit' => 3]));

        $pivot = $table->getFirstRow()->getMetadata('fwde_pivot');
        $this->assertSame('pageTitle', $pivot['dimension']);
        $this->assertSame(array_column($oracle, 'label'), array_column($pivot['values'], 'label'));
        $this->assertSame(['Blog A', 'Home', 'Shop'], array_column($pivot['values'], 'label'), 'ties by label');
        foreach ($pivot['values'] as $value) {
            $this->assertSame(['label', 'value', 'segment'], array_keys($value));
        }
        $columns = array_keys($table->getFirstRow()->getColumns());
        $this->assertSame(['label', 'nb_pageviews__p1', 'nb_pageviews__p2', 'nb_pageviews__p3', 'nb_pageviews__p_others', 'nb_pageviews'], $columns);
    }

    public function testCellsEqualTheRowsWithThePivotAsRowDimension(): void
    {
        $metrics = ['nb_pageviews', 'nb_visits', 'bounce_rate'];
        $table = $this->table(['dimensions' => ['browser'], 'metrics' => $metrics, 'pivot' => ['dimension' => 'pageTitle', 'limit' => 3]]);
        $oracle = [];
        foreach (self::rows($this->table(['dimensions' => ['browser', 'pageTitle'], 'metrics' => $metrics, 'sort' => 'nb_pageviews', 'limit' => 1000])) as $row) {
            $oracle[$row['label']][$row['secondary_label']] = $row;
        }
        $values = array_column($table->getFirstRow()->getMetadata('fwde_pivot')['values'], 'label');

        $checked = 0;
        foreach (self::dataRows($table) as $row) {
            foreach ($values as $n => $title) {
                foreach ($metrics as $metric) {
                    $expected = $oracle[$row['label']][$title][$metric] ?? ($metric === 'bounce_rate' ? null : 0);
                    $this->assertEquals($expected, $row[$metric . '__p' . ($n + 1)], $row['label'] . ' / ' . $title . ' / ' . $metric);
                    $checked++;
                }
            }
        }
        $this->assertGreaterThan(20, $checked);
    }

    public function testTotalIsThePlainExploration(): void
    {
        $definition = ['dimensions' => ['browser'], 'metrics' => ['nb_visits', 'bounce_rate', 'nb_uniq_visitors'], 'sort' => 'nb_visits', 'limit' => 1000];
        $plain = self::rows($this->table($definition));
        $pivoted = self::dataRows($this->table($definition + ['pivot' => ['dimension' => 'deviceType']]));

        $strip = static fn (array $row): array => array_intersect_key($row, array_flip(['label', 'nb_visits', 'bounce_rate', 'nb_uniq_visitors']));
        $this->assertSame($plain, array_map($strip, $pivoted));
    }

    public function testOthersIsTotalMinusTheCellsForSums(): void
    {
        $table = $this->table(['dimensions' => ['deviceType'], 'metrics' => ['nb_pageviews'], 'pivot' => ['dimension' => 'pageTitle', 'limit' => 3]]);
        foreach ($table->getRows() as $row) {
            $columns = $row->getColumns();
            $cells = $columns['nb_pageviews__p1'] + $columns['nb_pageviews__p2'] + $columns['nb_pageviews__p3'];
            $this->assertSame($columns['nb_pageviews'] - $cells, $columns['nb_pageviews__p_others'], $columns['label']);
        }
        $total = $table->getFirstRow()->getColumns();
        $this->assertSame(26, $total['nb_pageviews']);
        $this->assertSame(18, $total['nb_pageviews__p1'] + $total['nb_pageviews__p2'] + $total['nb_pageviews__p3']);
        $this->assertSame(8, $total['nb_pageviews__p_others']);
    }

    /** review focus 3: a visit sees several pages, so visits have no Others under a page pivot; their Total stays exact */
    public function testActionPivotHasNoOthersForVisits(): void
    {
        $definition = ['dimensions' => ['deviceType'], 'metrics' => ['nb_pageviews', 'nb_visits'], 'sort' => 'nb_visits', 'limit' => 1000];
        $table = $this->table($definition + ['pivot' => ['dimension' => 'pageUrl', 'limit' => 3]]);
        $plain = array_column(self::rows($this->table($definition)), 'nb_visits', 'label');

        $this->assertArrayHasKey('nb_pageviews__p_others', $table->getFirstRow()->getColumns());
        $this->assertArrayNotHasKey('nb_visits__p_others', $table->getFirstRow()->getColumns());
        foreach (self::dataRows($table) as $row) {
            $this->assertArrayNotHasKey('nb_visits__p_others', $row);
            $this->assertSame($plain[$row['label']], $row['nb_visits'], $row['label']);
        }
        $this->assertSame(17, $table->getFirstRow()->getColumn('nb_visits'), 'every visit once');
    }

    /** review focus 1: fewer values than the limit, nothing left for Others */
    public function testFewerValuesThanTheLimit(): void
    {
        $table = $this->table(['dimensions' => ['browser'], 'metrics' => ['nb_visits'], 'pivot' => ['dimension' => 'deviceType', 'limit' => 5]]);
        $pivot = $table->getFirstRow()->getMetadata('fwde_pivot');

        $this->assertCount(3, $pivot['values']);
        $this->assertFalse($pivot['others']);
        $this->assertSame(['label', 'nb_visits__p1', 'nb_visits__p2', 'nb_visits__p3', 'nb_visits'], array_keys($table->getFirstRow()->getColumns()));
    }

    /** review focus 2: a row without visits under a pivot value: 0 for counts, nothing for rates */
    public function testMissingCell(): void
    {
        $table = $this->table(['dimensions' => ['browser'], 'metrics' => ['nb_visits', 'bounce_rate'], 'pivot' => ['dimension' => 'deviceType']]);
        $values = array_column($table->getFirstRow()->getMetadata('fwde_pivot')['values'], 'value');
        $smartphone = array_search('1', array_map('strval', $values), true);
        $this->assertNotFalse($smartphone, 'Matomo stores smartphones as device type 1');
        $firefox = array_values(array_filter(self::dataRows($table), static fn (array $row) => $row['label'] === 'Firefox'))[0];

        $this->assertSame(0, $firefox['nb_visits__p' . ($smartphone + 1)]);
        $this->assertNull($firefox['bounce_rate__p' . ($smartphone + 1)]);
    }

    public function testTotalRowIsFirstAlsoWithoutRows(): void
    {
        $table = $this->table(['dimensions' => ['browser'], 'metrics' => ['nb_visits'], 'pivot' => ['dimension' => 'deviceType'],
            'filters' => [['target' => 'label', 'operator' => 'equals', 'value' => 'No such browser']]]);

        $this->assertSame(1, $table->getRowsCount());
        $total = $table->getFirstRow();
        $this->assertTrue($total->getMetadata('fwde_total'));
        $this->assertSame(Piwik::translate('General_Total'), $total->getColumn('label'));
        $this->assertSame([], $total->getMetadata('fwde_pivot')['values']);
        $this->assertSame(0, $total->getColumn('nb_visits'));
    }

    public function testValuesCarryTheirSegment(): void
    {
        $table = $this->table(['dimensions' => ['browser'], 'metrics' => ['nb_visits'], 'pivot' => ['dimension' => 'deviceType']]);
        $first = $table->getFirstRow()->getMetadata('fwde_pivot')['values'][0];

        $this->assertSame('Desktop', $first['label']);
        $this->assertStringStartsWith('deviceType==', $first['segment']);
    }

    public function testWeekdayShowsAllSevenDaysInOrder(): void
    {
        $table = $this->table(['dimensions' => ['deviceType'], 'metrics' => ['nb_visits'], 'pivot' => ['dimension' => 'weekday', 'limit' => 3]]);
        $pivot = $table->getFirstRow()->getMetadata('fwde_pivot');

        $this->assertSame(['1', '2', '3', '4', '5', '6', '7'], array_map('strval', array_column($pivot['values'], 'value')));
        $this->assertSame(Piwik::translate('Intl_Day_Long_StandAlone_1'), $pivot['values'][0]['label']);
        $this->assertFalse($pivot['others']);
        $total = $table->getFirstRow()->getColumns();
        $this->assertArrayHasKey('nb_visits__p7', $total);
        $this->assertArrayNotHasKey('nb_visits__p_others', $total);
        // the range is Friday to Monday: no visits from Tuesday to Thursday
        $this->assertSame(0, $total['nb_visits__p2']);
        $this->assertSame(17, $total['nb_visits__p1'] + $total['nb_visits__p5'] + $total['nb_visits__p6'] + $total['nb_visits__p7']);
    }

    public function testRefusedWithComparisons(): void
    {
        try {
            API::getInstance()->getExploration(1, 'range', ExplorationFixture::RANGE, json_encode(['dimensions' => ['browser'], 'metrics' => ['nb_visits'],
                'pivot' => ['dimension' => 'deviceType']]), '', 0, json_encode([['segment' => 'browserCode==FF', 'period' => 'range', 'date' => ExplorationFixture::RANGE]]));
            $this->fail('a pivot and comparisons are exclusive');
        } catch (ExplorationException $e) {
            $this->assertSame('FeinwerkDataExploration_ErrorPivotWithComparison', $e->getTranslationKey());
            $this->assertSame(400, $e->getCode());
            $this->assertStringContainsString('pivot', $e->getMessage());
            $this->assertStringContainsString('comparisons', $e->getMessage());
        }
    }

    public function testRefusedPivots(): void
    {
        $cases = [
            'a row dimension' => [['dimension' => 'browser'], 'FeinwerkDataExploration_ErrorPivotDimension'],
            'more than 60 cells' => [['dimension' => 'deviceType', 'limit' => 10], 'FeinwerkDataExploration_ErrorPivotCells'],
        ];
        foreach ($cases as $name => [$pivot, $key]) {
            try {
                $this->table(['dimensions' => ['browser'], 'metrics' => ['nb_visits', 'nb_actions', 'nb_pageviews', 'bounce_rate', 'avg_time_on_site', 'nb_uniq_visitors', 'sum_visit_length'], 'pivot' => $pivot]);
                $this->fail($name);
            } catch (ExplorationException $e) {
                $this->assertSame($key, $e->getTranslationKey(), $name);
            }
        }
    }

    public function testReadableNames(): void
    {
        $table = $this->table(['dimensions' => ['browser'], 'metrics' => ['nb_pageviews'], 'pivot' => ['dimension' => 'pageTitle', 'limit' => 3]], 1);
        $metric = array_keys($this->table(['dimensions' => ['browser'], 'metrics' => ['nb_pageviews']], 1)->getFirstRow()->getColumns())[1];

        $this->assertSame([
            array_keys($this->table(['dimensions' => ['browser'], 'metrics' => ['nb_pageviews']], 1)->getFirstRow()->getColumns())[0],
            'Blog A: ' . $metric, 'Home: ' . $metric, 'Shop: ' . $metric,
            Piwik::translate('General_Others') . ': ' . $metric,
            Piwik::translate('General_Total') . ': ' . $metric,
        ], array_keys($table->getFirstRow()->getColumns()));
        $this->assertSame([], $table->getFirstRow()->getMetadata(), 'exports carry no metadata');
    }

    private function table(array $definition, int $readable = 0): DataTable
    {
        return API::getInstance()->getExploration(1, 'range', ExplorationFixture::RANGE, json_encode($definition), '', $readable);
    }

    private static function rows(DataTable $table): array
    {
        return array_map(static fn ($row) => $row->getColumns(), $table->getRows());
    }

    /** the rows without the total row */
    private static function dataRows(DataTable $table): array
    {
        $rows = [];
        foreach ($table->getRows() as $row) {
            if (!$row->getMetadata('fwde_total')) {
                $rows[] = $row->getColumns();
            }
        }
        return $rows;
    }
}

ApiPivotTest::$fixture = new ExplorationFixture();
