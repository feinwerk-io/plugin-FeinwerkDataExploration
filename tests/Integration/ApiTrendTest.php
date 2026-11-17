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
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures\ExplorationFixture;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Log\LoggerInterface;
use Piwik\Log\NullLogger;

/**
 * getTrend (trend design): the first rows of the table, or the total of each comparison column, per day or hour. The
 * exploration with "Date" or "Hour" as a dimension is the oracle: the trend counts days and hours as they do.
 *
 * @group FeinwerkDataExploration
 */
class ApiTrendTest extends IntegrationTestCase
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

    public function testLinesEqualTheDateTableDayByDay(): void
    {
        // over the DST switch of Europe/Berlin (2026-03-29)
        $oracle = [];
        foreach ($this->table(['dimensions' => ['date', 'browser'], 'metrics' => ['nb_visits'], 'limit' => 1000])->getRows() as $row) {
            $oracle[$row->getColumn('secondary_label')][$row->getColumn('label')] = $row->getColumn('nb_visits');
        }
        $lines = $this->lines($this->trend(self::BROWSERS, 'range', ExplorationFixture::RANGE));
        $this->assertNotEmpty($lines);
        foreach ($lines as $line) {
            $this->assertSame(ExplorationFixture::DATES, array_map('strval', array_keys($line)), 'every day once, in order');
            foreach ($line as $day => $columns) {
                $this->assertEquals($oracle[$columns['label']][$day] ?? 0, $columns['nb_visits'], $columns['label'] . ' ' . $day);
            }
        }
    }

    /**
     * Matomo's API adds a totals row by summing every column of a result: the trend's time and label columns hold text
     * and are skipped. Without that every trend request logged warnings ("Trying to add two strings"; release check L7,
     * 2026-11-17).
     */
    public function testMatomosTotalsRowLogsNoWarnings(): void
    {
        $messages = [];
        // Matomo's logger (Piwik\Log\LoggerInterface); this signature fits psr/log 1 (Matomo 5) and 3 (Matomo 6)
        $logger = new class ($messages) extends NullLogger {
            /** @var array */
            private $sink;

            public function __construct(array &$sink)
            {
                $this->sink = &$sink;
            }

            public function log($level, $message, array $context = []): void
            {
                $exception = $context['exception'] ?? null;
                $this->sink[] = $level . ' ' . ($exception instanceof \Throwable ? $exception->getMessage() : (string) $message);
            }
        };
        $container = StaticContainer::getContainer();
        $previous = $container->get(LoggerInterface::class);
        $container->set(LoggerInterface::class, $logger);
        try {
            // every result the page asks for: the table (two dimensions, with comparisons), the trend and the funnel
            $twoDimensions = json_encode(['dimensions' => ['browser', 'deviceType'], 'metrics' => ['nb_visits']]);
            $compared = self::comparisons(['', 'range', '2026-03-23,2026-03-26']);
            $funnel = json_encode(['steps' => [['type' => 'pageUrl', 'operator' => 'contains', 'value' => '/'], ['type' => 'anyGoal']]]);
            foreach ([
                ['getExploration', $twoDimensions, '', 0],
                ['getExploration', $twoDimensions, '', 1],
                ['getExploration', $twoDimensions, $compared, 0],
                ['getTrend', $twoDimensions, '', 0],
                ['getTrend', $twoDimensions, $compared, 0],
                ['getFunnel', $funnel, '', 0],
                ['getFunnel', $funnel, '', 1],
            ] as [$method, $definition, $comparisons, $readableColumns]) {
                Request::processRequest('FeinwerkDataExploration.' . $method, [
                    'idSite' => 1,
                    'period' => 'range',
                    'date' => ExplorationFixture::RANGE,
                    'definition' => $definition,
                    'comparisons' => $comparisons,
                    'readableColumns' => $readableColumns,
                    'totals' => 1,
                    'filter_limit' => -1,
                    'format' => 'original',
                ], []);
            }
        } finally {
            $container->set(LoggerInterface::class, $previous);
        }
        $this->assertSame([], array_values(array_filter($messages, static fn (string $m): bool => strpos($m, 'warning') === 0)));
    }

    public function testLinesAreTheFirstRowsOfTheTable(): void
    {
        $table = $this->table(self::BROWSERS);
        $expected = array_slice(array_map(static fn ($row) => $row->getColumn('label'), $table->getRows()), 0, 5);
        $lines = $this->lines($this->trend(self::BROWSERS, 'range', ExplorationFixture::RANGE));
        $this->assertSame($expected, array_map(static fn (array $line) => reset($line)['label'], array_values($lines)));
    }

    /** Sorted by name, the table shows its rows in the order of the visible labels (the page sorts them): so does the trend. */
    public function testLabelSortTakesTheRowsTheTableShows(): void
    {
        Piwik::addAction('FeinwerkDataExploration.getLimits', function (Limits &$limits) {
            $limits = new Limits(maxTrendLines: 1);
        });
        $definition = ['dimensions' => ['country'], 'metrics' => ['nb_visits'], 'sort' => 'label', 'sortOrder' => 'asc', 'limit' => 100];
        $labels = array_map(static fn ($row) => $row->getColumn('label'), $this->table($definition)->getRows());
        $this->assertGreaterThan(1, count($labels));
        natcasesort($labels);
        $lines = $this->lines($this->trend($definition, 'range', ExplorationFixture::RANGE));
        $this->assertSame(reset($labels), reset($lines[0])['label'], 'the first row by its visible label ("France" before "Germany", whose code "de" sorts first)');
    }

    public function testTextFiltersChooseTheRowsAndNumberFiltersDoNotCutDays(): void
    {
        $definition = self::BROWSERS + ['filters' => [
            ['target' => 'label', 'operator' => 'contains', 'value' => 'Fire'],
            ['target' => 'nb_visits', 'operator' => 'gt', 'value' => 1],
        ]];
        $lines = $this->lines($this->trend($definition, 'range', ExplorationFixture::RANGE));
        $this->assertCount(1, $lines);
        $line = reset($lines);
        $this->assertStringContainsString('Fire', reset($line)['label']);
        $values = array_column($line, 'nb_visits');
        $this->assertCount(4, $values);
        $this->assertTrue(min($values) <= 1, 'days at or below the number filter keep their value: ' . json_encode($values));
    }

    public function testRatesHaveNoValueOnEmptyDays(): void
    {
        $empty = 0;
        foreach ($this->lines($this->trend(self::BROWSERS, 'range', ExplorationFixture::RANGE)) as $line) {
            foreach ($line as $columns) {
                if ($columns['nb_visits'] === 0) {
                    $empty++;
                    $this->assertNull($columns['bounce_rate']);
                }
            }
        }
        $this->assertGreaterThan(0, $empty, 'the fixture has days without visits of a browser');
    }

    public function testOneDayShowsItsHoursInTheSiteTimezone(): void
    {
        // oracle: the table "Hour x Browser" for the DST day; its labels are Matomo's hour labels
        $oracle = [];
        foreach ($this->table(['dimensions' => ['hour', 'browser'], 'metrics' => ['nb_visits'], 'limit' => 1000], 'day', '2026-03-29')->getRows() as $row) {
            $oracle[$row->getColumn('secondary_label')][$row->getColumn('label')] = $row->getColumn('nb_visits');
        }
        $lines = $this->lines($this->trend(self::BROWSERS, 'day', '2026-03-29'));
        $this->assertNotEmpty($lines);
        foreach ($lines as $line) {
            $this->assertSame(array_map('strval', range(0, 23)), array_map('strval', array_keys($line)));
            foreach ($line as $hour => $columns) {
                $expected = $oracle[$columns['label']][\Piwik\Plugins\VisitTime\getTimeLabel((int) $hour)] ?? 0;
                $this->assertEquals($expected, $columns['nb_visits'], $columns['label'] . ' ' . $hour . 'h');
            }
        }
    }

    public function testTwoDimensionsGiveCombinations(): void
    {
        $definition = ['dimensions' => ['browser', 'deviceType'], 'metrics' => ['nb_visits'], 'limit' => 1000];
        $table = $this->table($definition);
        $lines = $this->lines($this->trend($definition, 'range', ExplorationFixture::RANGE));
        $first = reset($lines);
        $this->assertSame($table->getFirstRow()->getColumn('secondary_label'), reset($first)['secondary_label']);
        $this->assertEquals($table->getFirstRow()->getColumn('nb_visits'), array_sum(array_column($first, 'nb_visits')));
    }

    public function testUnknownValueRowHasItsLine(): void
    {
        $definition = ['dimensions' => ['referrerName'], 'metrics' => ['nb_visits'], 'limit' => 1000];
        $table = $this->table($definition);
        $position = null;
        foreach (array_values($table->getRows()) as $i => $row) {
            if ($row->getMetadata('fwde_no_value')) {
                $position = $i;
            }
        }
        $this->assertNotNull($position, 'the fixture has visits without a referrer name');
        Piwik::addAction('FeinwerkDataExploration.getLimits', function (Limits &$limits) {
            $limits = new Limits(maxTrendLines: 10);
        });
        $this->assertLessThan(10, $position);
        $lines = array_values($this->lines($this->trend($definition, 'range', ExplorationFixture::RANGE)));
        $this->assertEquals($table->getRows()[$position]->getColumn('nb_visits'), array_sum(array_column($lines[$position], 'nb_visits')));
    }

    public function testComparisonsGiveOneTotalLinePerColumn(): void
    {
        $lines = $this->lines($this->trend(self::BROWSERS, 'range', ExplorationFixture::RANGE, self::comparisons(['browserCode==FF', 'range', ExplorationFixture::RANGE])));
        $this->assertCount(2, $lines);
        $this->assertSame(['own', 'segment'], [reset($lines[0])['fwde_kind'], reset($lines[1])['fwde_kind']]);
        // oracle: the table "Date" without the segment (line 0) and with it (line 1)
        $this->assertEquals($this->dateTotals(''), array_column($lines[0], 'nb_visits', 'time'));
        $this->assertEquals($this->dateTotals('browserCode==FF'), array_column($lines[1], 'nb_visits', 'time'));
    }

    public function testComparedPeriodHasItsOwnDays(): void
    {
        $lines = $this->lines($this->trend(self::BROWSERS, 'range', '2026-03-29,2026-03-30', self::comparisons(['', 'range', '2026-03-27,2026-03-28'])));
        $this->assertSame(['2026-03-29', '2026-03-30'], array_map('strval', array_keys($lines[0])));
        $this->assertSame(['2026-03-27', '2026-03-28'], array_map('strval', array_keys($lines[1])), 'real dates; the page lines them up by position');
        $this->assertSame('period', reset($lines[1])['fwde_kind']);
    }

    public function testFailingColumnIsRefusedWithItsName(): void
    {
        $this->assertRefused('FeinwerkDataExploration_ErrorComparisonColumn', 400, fn () =>
            $this->trend(self::BROWSERS, 'range', ExplorationFixture::RANGE, self::comparisons(['', 'range', '2026-01-01,2026-03-30'])));
    }

    public function testMoreLinesThroughTheLimitsEvent(): void
    {
        Piwik::addAction('FeinwerkDataExploration.getLimits', function (Limits &$limits) {
            $limits = new Limits(maxTrendLines: 1);
        });
        $this->assertCount(1, $this->lines($this->trend(self::BROWSERS, 'range', ExplorationFixture::RANGE)));
    }

    public function testParametersAreTrimmedThroughProcessRequest(): void
    {
        $table = Request::processRequest('FeinwerkDataExploration.getTrend', [
            'idSite' => 1, 'period' => ' range ', 'date' => ' ' . ExplorationFixture::RANGE . ' ',
            'definition' => json_encode(self::BROWSERS), 'format' => 'original',
        ]);
        $this->assertGreaterThan(0, $table->getRowsCount());
    }

    private static function comparisons(array ...$columns): string
    {
        return json_encode(array_map(static fn (array $c): array => ['segment' => $c[0], 'period' => $c[1], 'date' => $c[2]], $columns));
    }

    private function trend(array $definition, string $period, string $date, string $comparisons = '', string $segment = ''): DataTable
    {
        return API::getInstance()->getTrend(1, $period, $date, json_encode($definition), $segment, $comparisons);
    }

    private function table(array $definition, string $period = 'range', string $date = ExplorationFixture::RANGE, string $segment = ''): DataTable
    {
        return API::getInstance()->getExploration(1, $period, $date, json_encode($definition), $segment);
    }

    /** @return array<int, array<string, array>> by line, then by time unit: the row's columns and its fwde_kind */
    private function lines(DataTable $table): array
    {
        $lines = [];
        foreach ($table->getRows() as $row) {
            $lines[$row->getMetadata('fwde_line')][$row->getColumn('time')] = $row->getColumns() + ['fwde_kind' => $row->getMetadata('fwde_kind')];
        }
        return $lines;
    }

    /** @return array<string, int> visits per day (table "Date") */
    private function dateTotals(string $segment): array
    {
        $totals = array_fill_keys(ExplorationFixture::DATES, 0);
        foreach ($this->table(['dimensions' => ['date'], 'metrics' => ['nb_visits'], 'limit' => 1000], 'range', ExplorationFixture::RANGE, $segment)->getRows() as $row) {
            $totals[$row->getColumn('label')] = $row->getColumn('nb_visits');
        }
        return $totals;
    }

    private function assertRefused(string $key, int $code, callable $call): void
    {
        try {
            $call();
            $this->fail('expected ' . $key);
        } catch (ExplorationException $e) {
            $this->assertSame($key, $e->getTranslationKey());
            $this->assertSame($code, $e->getCode(), $key);
        }
    }
}

ApiTrendTest::$fixture = new ExplorationFixture();
