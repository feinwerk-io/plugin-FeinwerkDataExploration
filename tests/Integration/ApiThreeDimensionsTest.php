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
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures\ExplorationFixture;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Three row dimensions (rows and pivot, 2026-11-24): everything that works with two works with three. The
 * two-dimension exploration is the oracle (it is parity-tested against Matomo's reports).
 *
 * @group FeinwerkDataExploration
 */
class ApiThreeDimensionsTest extends IntegrationTestCase
{
    /** @var ExplorationFixture */
    public static $fixture = null;

    private const THREE = ['dimensions' => ['browser', 'deviceType', 'pageTitle'], 'metrics' => ['nb_pageviews'], 'sort' => 'nb_pageviews', 'limit' => 1000];

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

    public function testRowsAddUpToTheTwoDimensionTable(): void
    {
        $three = self::rows($this->table(self::THREE));
        $two = self::rows($this->table(['dimensions' => ['browser', 'deviceType']] + self::THREE));

        $this->assertSame(['label', 'secondary_label', 'tertiary_label', 'nb_pageviews'], array_keys($three[0]));
        $this->assertGreaterThan(count($two), count($three), 'precondition: the third dimension splits rows');
        $byPair = [];
        foreach ($three as $row) {
            $pair = $row['label'] . '|' . $row['secondary_label'];
            $byPair[$pair] = ($byPair[$pair] ?? 0) + $row['nb_pageviews'];
        }
        $expected = [];
        foreach ($two as $row) {
            $expected[$row['label'] . '|' . $row['secondary_label']] = $row['nb_pageviews'];
        }
        ksort($byPair);
        ksort($expected);
        $this->assertSame($expected, $byPair);
    }

    public function testRowsCarryTheSegmentAndStoredValueOfEachDimension(): void
    {
        $table = $this->table(['filters' => [['target' => 'tertiary_label', 'operator' => 'equals', 'value' => 'Blog A']]] + self::THREE);
        $row = $table->getFirstRow();

        $this->assertSame(3, count(explode(';', (string) $row->getMetadata('segment'))), (string) $row->getMetadata('segment'));
        $this->assertSame('Blog A', $row->getMetadata('fwde_value3'));
    }

    public function testOthersRowWithThreeDimensions(): void
    {
        $cut = self::rows($this->table(['limit' => 3] + self::THREE));
        $full = self::rows($this->table(self::THREE));
        $others = API::getInstance()->getOthers(1, 'range', ExplorationFixture::RANGE, json_encode(['limit' => 3] + self::THREE))['others'];

        $this->assertCount(3, $cut);
        $this->assertSame(array_sum(array_column($full, 'nb_pageviews')), array_sum(array_column($cut, 'nb_pageviews')) + $others['nb_pageviews']);
    }

    /** review focus 5: Page URL and Page title of one pageview add up; a visit across several of them does not */
    public function testPageUrlByPageTitleOvercountsVisitsOnly(): void
    {
        $definition = ['dimensions' => ['pageUrl', 'pageTitle', 'deviceType'], 'metrics' => ['nb_pageviews'], 'sort' => 'nb_pageviews', 'limit' => 2];
        $full = self::rows($this->table(['limit' => 1000] + $definition));
        $cut = self::rows($this->table($definition));
        $others = API::getInstance()->getOthers(1, 'range', ExplorationFixture::RANGE, json_encode($definition))['others'];
        $this->assertSame(array_sum(array_column($full, 'nb_pageviews')), array_sum(array_column($cut, 'nb_pageviews')) + $others['nb_pageviews']);

        try {
            API::getInstance()->getOthers(1, 'range', ExplorationFixture::RANGE, json_encode(['metrics' => ['nb_visits'], 'sort' => 'nb_visits'] + $definition));
            $this->fail('visits across several pages have no exact rest');
        } catch (ExplorationException $e) {
            $this->assertSame('FeinwerkDataExploration_ErrorOthersNotAddingUp', $e->getTranslationKey());
        }
    }

    public function testSegmentComparisonWithThreeDimensions(): void
    {
        $segment = 'browserCode==CH';
        $comparisons = json_encode([['segment' => $segment, 'period' => 'range', 'date' => ExplorationFixture::RANGE]]);
        $table = $this->table(self::THREE, $comparisons);
        $oracle = [];
        foreach (self::rows($this->table(self::THREE, '', $segment)) as $row) {
            $oracle[$row['label'] . '|' . $row['secondary_label'] . '|' . $row['tertiary_label']] = $row['nb_pageviews'];
        }

        $total = $table->getFirstRow();
        $this->assertTrue((bool) $total->getMetadata('fwde_total'));
        $this->assertSame('', $total->getColumn('tertiary_label'));
        $this->assertSame(array_sum($oracle), $total->getColumn('nb_pageviews__c1'));
        $compared = 0;
        foreach ($table->getRows() as $row) {
            if ($row->getMetadata('fwde_total')) {
                continue;
            }
            $this->assertCount(3, json_decode((string) $row->getMetadata('fwde_key'), true));
            $key = $row->getColumn('label') . '|' . $row->getColumn('secondary_label') . '|' . $row->getColumn('tertiary_label');
            $this->assertSame($oracle[$key] ?? 0, $row->getColumn('nb_pageviews__c1'), $key);
            $compared += $oracle[$key] ?? 0;
        }
        $this->assertGreaterThan(0, $compared);
    }

    public function testTrendLinesNameAllThreeDimensions(): void
    {
        $definition = ['limit' => 2] + self::THREE;
        $rows = self::rows($this->table($definition));
        $trend = API::getInstance()->getTrend(1, 'range', ExplorationFixture::RANGE, json_encode($definition));

        $lines = [];
        foreach ($trend->getRows() as $row) {
            $line = $row->getMetadata('fwde_line');
            $lines[$line] = [$row->getColumn('label'), $row->getColumn('secondary_label'), $row->getColumn('tertiary_label')];
            $this->assertCount(3, json_decode((string) $row->getMetadata('fwde_key'), true));
            $sums[$line] = ($sums[$line] ?? 0) + $row->getColumn('nb_pageviews');
        }
        $this->assertSame(array_map(static fn (array $row) => [$row['label'], $row['secondary_label'], $row['tertiary_label']], $rows), array_values($lines));
        $this->assertSame(array_column($rows, 'nb_pageviews'), array_values($sums));
    }

    /** a stored text (titles are stored HTML-escaped) and a coded dimension (browser) as the third */
    public function testFiltersOnTheThirdDimension(): void
    {
        $titles = self::rows($this->table(['filters' => [['target' => 'tertiary_label', 'operator' => 'equals', 'value' => "Tom & Jerry's tips"]]] + self::THREE));
        $this->assertNotEmpty($titles);
        // the label as Matomo's reports return it (stored HTML-escaped)
        $this->assertSame(['Tom &amp; Jerry&#039;s tips'], array_values(array_unique(array_column($titles, 'tertiary_label'))));

        $definition = ['dimensions' => ['pageTitle', 'deviceType', 'browser'], 'filters' => [['target' => 'tertiary_label', 'operator' => 'contains', 'value' => 'firefox']]] + self::THREE;
        $browsers = self::rows($this->table($definition));
        $this->assertNotEmpty($browsers);
        $this->assertSame(['Firefox'], array_values(array_unique(array_column($browsers, 'tertiary_label'))));
    }

    public function testReadableNamesNameTheThirdDimension(): void
    {
        $two = $this->table(['dimensions' => ['browser', 'pageTitle']] + self::THREE, '', '', 1)->getFirstRow()->getColumns();
        $three = $this->table(self::THREE, '', '', 1)->getFirstRow()->getColumns();

        $this->assertSame(array_keys($two)[1], array_keys($three)[2]);
        $this->assertNotSame('tertiary_label', array_keys($three)[2]);
        $this->assertCount(4, $three);
    }

    private function table(array $definition, string $comparisons = '', string $segment = '', int $readable = 0): DataTable
    {
        return API::getInstance()->getExploration(1, 'range', ExplorationFixture::RANGE, json_encode($definition), $segment, $readable, $comparisons);
    }

    private static function rows(DataTable $table): array
    {
        return array_map(static fn ($row) => $row->getColumns(), $table->getRows());
    }
}

ApiThreeDimensionsTest::$fixture = new ExplorationFixture();
