<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\API\Request;
use Piwik\Cache;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ComparisonTable;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures\ExplorationFixture;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Comparison columns of getExploration (comparison design). The existing exploration is the oracle (it is
 * parity-tested against Matomo's reports): each comparison column must show what an exploration with that column's
 * segment or period shows for the same row.
 *
 * @group FeinwerkDataExploration
 */
class ApiComparisonTest extends IntegrationTestCase
{
    /** @var ExplorationFixture */
    public static $fixture = null;

    private const BROWSERS = ['dimensions' => ['browser'], 'metrics' => ['nb_visits', 'bounce_rate'], 'sort' => 'nb_visits', 'limit' => 1000];

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

    public function testWithoutComparisonsTheResultIsUnchanged(): void
    {
        $plain = $this->table(self::BROWSERS, 'range', ExplorationFixture::RANGE);
        foreach (['', '[]'] as $comparisons) {
            $other = $this->table(self::BROWSERS, 'range', ExplorationFixture::RANGE, $comparisons);
            $this->assertSame(self::dump($plain), self::dump($other), var_export($comparisons, true));
        }
    }

    public function testSegmentColumnHasTheSegmentsValuesForTheOwnRows(): void
    {
        $segment = 'countryCode==us';
        $own = ['sort' => 'nb_visits', 'limit' => 2] + self::BROWSERS;
        $oracle = $this->byLabel($this->table(self::BROWSERS, 'range', ExplorationFixture::RANGE, '', $segment));
        $oracleTop = array_slice(array_keys($oracle), 0, 2);
        $ownRows = $this->dataRows($this->table($own, 'range', ExplorationFixture::RANGE));
        $this->assertNotEmpty(array_diff(array_column($ownRows, 'label'), $oracleTop), 'precondition: an own row outside the segment\'s top 2');

        $compared = $this->dataRows($this->table($own, 'range', ExplorationFixture::RANGE, self::comparisons([$segment, 'range', ExplorationFixture::RANGE])));

        $this->assertSame(array_column($ownRows, 'label'), array_column($compared, 'label'));
        foreach ($compared as $row) {
            $this->assertSame($oracle[$row['label']]['nb_visits'] ?? 0, $row['nb_visits__c1'], $row['label']);
            $this->assertSame($oracle[$row['label']]['bounce_rate'] ?? null, $row['bounce_rate__c1'], $row['label']);
            $this->assertArrayHasKey('nb_visits__c1__share', $row);
            $this->assertArrayNotHasKey('bounce_rate__c1__share', $row);
        }
    }

    public function testTotalRowCountsWhatTheTableCovers(): void
    {
        $own = ['dimensions' => ['pageUrl'], 'metrics' => ['nb_visits'], 'filters' => [
            ['target' => 'label', 'operator' => 'contains', 'value' => '/blog'],
            ['target' => 'nb_visits', 'operator' => 'gt', 'value' => 1],
        ]];
        $table = $this->table($own, 'range', ExplorationFixture::RANGE, self::comparisons(['browserCode==FF', 'range', ExplorationFixture::RANGE]));
        $total = $table->getFirstRow();

        $this->assertTrue((bool) $total->getMetadata('fwde_total'));
        $this->assertSame(Piwik::translate('General_Total'), $total->getColumn('label'));
        $visitsWithBlogPages = array_sum(array_column(self::rows($this->table(
            ['dimensions' => ['browser'], 'metrics' => ['nb_visits'], 'limit' => 1000],
            'range',
            ExplorationFixture::RANGE,
            '',
            'pageUrl=@%252Fblog'
        )), 'nb_visits'));
        $this->assertGreaterThan(0, $visitsWithBlogPages);
        $this->assertSame($visitsWithBlogPages, $total->getColumn('nb_visits'), 'number filters and the row limit do not change the total');
        foreach ($this->dataRows($table) as $row) {
            $this->assertSame(ComparisonTable::share($row['nb_visits'], $visitsWithBlogPages), $row['nb_visits__share']);
        }
    }

    public function testTotalRowShowsThePeriodsChange(): void
    {
        $table = $this->table(self::BROWSERS, 'day', '2026-03-28', self::comparisons(['', 'day', '2026-03-27']));
        $total = $table->getFirstRow();
        $this->assertSame(
            ComparisonTable::change($total->getColumn('nb_visits'), $total->getColumn('nb_visits__c1')),
            $total->getColumn('nb_visits__c1__change')
        );
        // getColumn() gives false for a null value: read the columns
        $this->assertArrayHasKey('nb_visits__share', $total->getColumns());
        $this->assertNull($total->getColumns()['nb_visits__share']);
    }

    public function testPeriodColumnShowsTheChange(): void
    {
        $oracle = $this->byLabel($this->table(self::BROWSERS, 'day', '2026-03-27'));
        $rows = $this->dataRows($this->table(self::BROWSERS, 'day', '2026-03-28', self::comparisons(['', 'day', '2026-03-27'])));

        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $compared = $oracle[$row['label']]['nb_visits'] ?? 0;
            $this->assertSame($compared, $row['nb_visits__c1'], $row['label']);
            $this->assertSame(ComparisonTable::change($row['nb_visits'], $compared), $row['nb_visits__c1__change'], $row['label']);
            $this->assertArrayNotHasKey('nb_visits__c1__share', $row);
        }
    }

    public function testDatesLineUpByPosition(): void
    {
        $dates = ['dimensions' => ['date'], 'metrics' => ['nb_visits'], 'sort' => 'label', 'sortOrder' => 'asc'];
        $oracle = $this->byLabel($this->table($dates, 'day', '2026-03-27'));
        $rows = $this->byLabel($this->table($dates, 'range', '2026-03-29,2026-03-30', self::comparisons(['', 'day', '2026-03-27'])));

        $this->assertGreaterThan(0, $oracle['2026-03-27']['nb_visits']);
        $this->assertSame($oracle['2026-03-27']['nb_visits'], $rows['2026-03-29']['nb_visits__c1'], 'day 1 next to day 1');
        $this->assertNull($rows['2026-03-30']['nb_visits__c1'], 'the shorter period has no second day: empty, not 0');
        $this->assertNull($rows['2026-03-30']['nb_visits__c1__change']);
    }

    public function testDateAsSecondDimensionLinesUpByPosition(): void
    {
        $definition = ['dimensions' => ['browser', 'date'], 'metrics' => ['nb_visits'], 'limit' => 1000];
        $oracle = [];
        foreach (self::rows($this->table($definition, 'day', '2026-03-27')) as $row) {
            $oracle[$row['label'] . '|' . $row['secondary_label']] = $row['nb_visits'];
        }
        $rows = $this->dataRows($this->table($definition, 'range', '2026-03-29,2026-03-29', self::comparisons(['', 'day', '2026-03-27'])));

        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertSame('2026-03-29', $row['secondary_label']);
            $this->assertSame($oracle[$row['label'] . '|2026-03-27'] ?? 0, $row['nb_visits__c1'], $row['label']);
        }
    }

    public function testUnknownValueRowIsCompared(): void
    {
        $definition = ['dimensions' => ['referrerName'], 'metrics' => ['nb_visits'], 'limit' => 1000];
        $segment = 'browserCode==CH';
        $oracle = $this->byLabel($this->table($definition, 'range', ExplorationFixture::RANGE, '', $segment));
        $table = $this->table($definition, 'range', ExplorationFixture::RANGE, self::comparisons([$segment, 'range', ExplorationFixture::RANGE]));

        $unknown = null;
        foreach ($table->getRows() as $row) {
            if ($row->getMetadata('fwde_no_value')) {
                $unknown = $row;
            }
        }
        $this->assertNotNull($unknown, 'precondition: a row without a referrer name');
        $this->assertGreaterThan(0, $oracle[$unknown->getColumn('label')]['nb_visits'] ?? 0, 'precondition: the segment has such visits');
        $this->assertSame($oracle[$unknown->getColumn('label')]['nb_visits'], $unknown->getColumn('nb_visits__c1'));
    }

    /** The page's date comes resolved (e.g. "last7" as two days), a comparison's as Matomo wrote it. */
    public function testSamePeriodWrittenDifferentlyIsASegmentColumn(): void
    {
        $rows = $this->dataRows($this->table(self::BROWSERS, 'day', '2026-03-28', self::comparisons(['browserCode==FF', 'range', '2026-03-28,2026-03-28'])));

        $this->assertNotEmpty($rows);
        $this->assertArrayHasKey('nb_visits__c1__share', $rows[0]);
        $this->assertArrayNotHasKey('nb_visits__c1__change', $rows[0]);
    }

    /** The page asks its own view as column 1: that column is the own rows, without another query. */
    public function testTheOwnViewAsAColumnNeedsNoQuery(): void
    {
        $own = $this->dataRows($this->table(self::BROWSERS, 'range', ExplorationFixture::RANGE));
        // the own totals come with any comparison; then the log data goes
        $this->table(self::BROWSERS, 'range', ExplorationFixture::RANGE, self::comparisons(['browserCode==FF', 'range', ExplorationFixture::RANGE]));
        Db::query('DELETE FROM ' . Common::prefixTable('log_visit') . ' WHERE idsite = 1');
        Db::query('DELETE FROM ' . Common::prefixTable('log_link_visit_action') . ' WHERE idsite = 1');

        $rows = $this->dataRows($this->table(self::BROWSERS, 'range', ExplorationFixture::RANGE, self::comparisons(['', 'range', ExplorationFixture::RANGE])));

        $this->assertSame(array_column($own, 'nb_visits'), array_column($rows, 'nb_visits__c1'));
    }

    /** The page merges the column requests by the rows' keys (labels are not unique: "Unknown" for several values). */
    public function testRowsCarryTheirKeysForThePage(): void
    {
        $table = $this->table(self::BROWSERS, 'range', ExplorationFixture::RANGE, self::comparisons(['browserCode==FF', 'range', ExplorationFixture::RANGE]));
        $keys = [];
        foreach ($table->getRows() as $row) {
            if (!$row->getMetadata('fwde_total')) {
                $keys[] = $row->getMetadata('fwde_key');
            }
        }
        $this->assertNotContains(false, $keys);
        $this->assertSame(count($keys), count(array_unique($keys)));
        $this->assertIsArray(json_decode($keys[0], true));
    }

    public function testInvalidSegmentColumnIsRefusedWithItsName(): void
    {
        $this->assertRefused('FeinwerkDataExploration_ErrorComparisonColumn', 400, 'noSuchSegment==1', fn () =>
            $this->table(self::BROWSERS, 'range', ExplorationFixture::RANGE, self::comparisons(['noSuchSegment==1', 'range', ExplorationFixture::RANGE])));
    }

    public function testTooLongComparedPeriodIsRefused(): void
    {
        $tooLong = Piwik::translate('FeinwerkDataExploration_ErrorPeriodTooLong', [31]);
        $this->assertRefused('FeinwerkDataExploration_ErrorComparisonColumn', 400, $tooLong, fn () =>
            $this->table(self::BROWSERS, 'range', ExplorationFixture::RANGE, self::comparisons(['', 'range', '2026-01-01,2026-02-09'])));
    }

    public function testTooManyComparisons(): void
    {
        $column = ['', 'day', '2026-03-27'];
        $this->assertRefused('FeinwerkDataExploration_ErrorComparisonCount', 400, null, fn () =>
            $this->table(self::BROWSERS, 'range', ExplorationFixture::RANGE, self::comparisons($column, $column, $column, $column)));
    }

    public function testMoreColumnsThroughTheLimitsEvent(): void
    {
        Piwik::addAction('FeinwerkDataExploration.getLimits', function (Limits &$limits) {
            $limits = new Limits(maxCompareColumns: 5);
        });
        $rows = $this->dataRows($this->table(self::BROWSERS, 'range', ExplorationFixture::RANGE, self::comparisons(
            ['browserCode==FF', 'range', ExplorationFixture::RANGE],
            ['browserCode==CH', 'range', ExplorationFixture::RANGE],
            ['browserCode==SF', 'range', ExplorationFixture::RANGE],
            ['', 'day', '2026-03-27']
        )));
        $this->assertArrayHasKey('nb_visits__c4', $rows[0]);
        $this->assertArrayHasKey('nb_visits__c4__change', $rows[0]);
    }

    public function testReadableColumnsNameEveryColumn(): void
    {
        $definition = ['dimensions' => ['browser'], 'metrics' => ['nb_visits']];
        $table = $this->table($definition, 'range', ExplorationFixture::RANGE, self::comparisons(['browserCode==FF', 'range', ExplorationFixture::RANGE]), '', 1);
        $first = $table->getRowFromId(1); // after the total row

        $this->assertSame(
            ['Visits (All visits)', 'Visits, share (All visits)', 'Visits (browserCode==FF)', 'Visits, share (browserCode==FF)'],
            array_slice(array_keys($first->getColumns()), 1)
        );
        // exports take the column order from the first row: the total row has every column
        $this->assertSame(array_keys($first->getColumns()), array_keys($table->getFirstRow()->getColumns()));
        // exports: no metadata column for the total row's marker (the label says "Total")
        $this->assertSame([], $table->getFirstRow()->getMetadata());
        $this->assertSame('Total', $table->getFirstRow()->getColumn('Browser'));
    }

    public function testParametersAreTrimmedThroughProcessRequest(): void
    {
        $definition = json_encode(self::BROWSERS);
        $direct = $this->table(self::BROWSERS, 'range', ExplorationFixture::RANGE, self::comparisons(['browserCode==FF', 'range', ExplorationFixture::RANGE]));
        $request = Request::processRequest('FeinwerkDataExploration.getExploration', [
            'idSite' => 1, 'period' => 'range', 'date' => ExplorationFixture::RANGE, 'definition' => $definition,
            'comparisons' => ' [{"segment": " browserCode==FF ", "period": " range ", "date": " ' . ExplorationFixture::RANGE . ' "}] ',
        ]);
        $this->assertSame(self::dump($direct), self::dump($request));
    }

    public function testEachColumnHasItsOwnCacheEntry(): void
    {
        $firefox = ['browserCode==FF', 'range', ExplorationFixture::RANGE];
        $chrome = ['browserCode==CH', 'range', ExplorationFixture::RANGE];
        $before = $this->dataRows($this->table(self::BROWSERS, 'range', ExplorationFixture::RANGE, self::comparisons($firefox, $chrome)));
        Db::query('DELETE FROM ' . Common::prefixTable('log_visit') . ' WHERE idsite = 1');

        $again = $this->dataRows($this->table(self::BROWSERS, 'range', ExplorationFixture::RANGE, self::comparisons($firefox, $chrome)));
        $chromeOnly = $this->dataRows($this->table(self::BROWSERS, 'range', ExplorationFixture::RANGE, self::comparisons($chrome)));

        $this->assertSame($before, $again);
        $this->assertSame(array_column($before, 'nb_visits__c2'), array_column($chromeOnly, 'nb_visits__c1'));
        $this->assertGreaterThan(0, array_sum(array_column($chromeOnly, 'nb_visits__c1')));
    }

    /** @param array{0: string, 1: string, 2: string} ...$columns segment, period, date */
    private static function comparisons(array ...$columns): string
    {
        return json_encode(array_map(static fn (array $c): array => ['segment' => $c[0], 'period' => $c[1], 'date' => $c[2]], $columns));
    }

    private function table(array $definition, string $period, string $date, string $comparisons = '', string $segment = '', int $readable = 0): DataTable
    {
        return API::getInstance()->getExploration(1, $period, $date, json_encode($definition), $segment, $readable, $comparisons);
    }

    private static function rows(DataTable $table): array
    {
        return array_map(static fn ($row) => $row->getColumns(), $table->getRows());
    }

    /** the rows without the total row */
    private function dataRows(DataTable $table): array
    {
        $rows = [];
        foreach ($table->getRows() as $row) {
            if (!$row->getMetadata('fwde_total')) {
                $rows[] = $row->getColumns();
            }
        }
        return $rows;
    }

    private function byLabel(DataTable $table): array
    {
        return array_column(self::rows($table), null, 'label');
    }

    private static function dump(DataTable $table): array
    {
        return array_map(static fn ($row) => [$row->getColumns(), $row->getMetadata()], $table->getRows());
    }

    private function assertRefused(string $key, int $code, ?string $inMessage, callable $call): void
    {
        try {
            $call();
            $this->fail('expected ' . $key);
        } catch (ExplorationException $e) {
            $this->assertSame($key, $e->getTranslationKey());
            $this->assertSame($code, $e->getCode(), $key);
            if ($inMessage !== null) {
                $this->assertStringContainsString($inMessage, $e->getMessage());
            }
        }
    }
}

ApiComparisonTest::$fixture = new ExplorationFixture();
