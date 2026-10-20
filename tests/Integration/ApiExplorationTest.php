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
use Piwik\Db;
use Piwik\DataTable;
use Piwik\NoAccessException;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group FeinwerkDataExploration
 */
class ApiExplorationTest extends IntegrationTestCase
{
    private const BROWSER_VISITS = '{"dimensions": ["browser"], "metrics": ["nb_visits", "nb_actions"]}';

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
        Fixture::createSuperUser(false);
        IPAnonymizer::deactivate();
        FakeAccess::$superUser = true;
        Cache::getLazyCache()->flushAll(); // results are cached across tests of one PHP process
        Fixture::createWebsite('2026-01-01 00:00:00', 0, 'Shop', 'https://example.com', 1, null, null, 'UTC');

        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
        ];
        foreach ($userAgents as $i => $userAgent) {
            $t = Fixture::getTracker(1, '2026-03-27 1' . $i . ':00:00', true, true);
            $t->setNewVisitorId();
            $t->setIp('198.51.100.' . ($i + 1));
            $t->setUserAgent($userAgent);
            $t->setUrl('https://example.com/page' . ($i % 2));
            Fixture::checkResponse($t->doTrackPageView('Page'));
            $t->setForceVisitDateTime('2026-03-27 1' . $i . ':01:00');
            $t->setUrl('https://example.com/other');
            Fixture::checkResponse($t->doTrackPageView('Other'));
        }
    }

    public function testReturnsDataTableWithLabelAndMetricColumns(): void
    {
        $table = API::getInstance()->getExploration(1, 'day', '2026-03-27', self::BROWSER_VISITS);

        $this->assertInstanceOf(DataTable::class, $table);
        $rows = $table->getRows();
        $this->assertSame(['label', 'nb_visits', 'nb_actions'], array_keys($rows[0]->getColumns()));
        $this->assertSame(['label' => 'Chrome', 'nb_visits' => 2, 'nb_actions' => 4], $rows[0]->getColumns());
        $this->assertSame(['Chrome', 'Firefox', 'Safari'], $table->getColumn('label'));
    }

    public function testTwoDimensionsAddSecondaryLabel(): void
    {
        $table = API::getInstance()->getExploration(1, 'day', '2026-03-27', '{"dimensions": ["browser", "pageUrl"], "metrics": ["nb_pageviews"], "sort": "label", "sortOrder": "asc"}');

        $this->assertSame(['label', 'secondary_label', 'nb_pageviews'], array_keys($table->getFirstRow()->getColumns()));
        $this->assertSame(
            ['Chrome | example.com/other | 2', 'Chrome | example.com/page0 | 1', 'Chrome | example.com/page1 | 1'],
            array_slice(array_map(fn ($row) => implode(' | ', $row->getColumns()), $table->getRows()), 0, 3)
        );
    }

    public function testDefinitionWithEscapedQuotesIsAccepted(): void
    {
        foreach ([self::BROWSER_VISITS, htmlspecialchars(self::BROWSER_VISITS, ENT_QUOTES)] as $definition) {
            $table = Request::processRequest('FeinwerkDataExploration.getExploration', [
                'idSite' => 1, 'period' => 'day', 'date' => '2026-03-27', 'definition' => $definition,
            ]);
            $this->assertSame(3, $table->getRowsCount());
        }
    }

    public function testFilterLimitDoesNotCutResult(): void
    {
        $table = Request::processRequest('FeinwerkDataExploration.getExploration', [
            'idSite' => 1, 'period' => 'day', 'date' => '2026-03-27', 'definition' => self::BROWSER_VISITS, 'filter_limit' => 2,
        ]);

        $this->assertSame(3, $table->getRowsCount());
    }

    /** A plain API export keeps each row's segment, as Matomo's own reports do; the page's export leaves it out. */
    public function testCsvExportHasHeaderRow(): void
    {
        $csv = Request::processRequest('FeinwerkDataExploration.getExploration', [
            'idSite' => 1, 'period' => 'day', 'date' => '2026-03-27', 'definition' => self::BROWSER_VISITS, 'format' => 'csv', 'convertToUnicode' => 0,
        ]);

        $this->assertStringStartsWith("label,nb_visits,nb_actions,metadata_segment\nChrome,2,4,browserCode==CH\n", str_replace("\r", '', $csv));
    }

    public function testEmptyPeriodGivesEmptyTable(): void
    {
        $table = API::getInstance()->getExploration(1, 'day', '2026-02-10', self::BROWSER_VISITS);

        $this->assertSame(0, $table->getRowsCount());
    }

    public function testUnknownValuesFormOneRow(): void
    {
        // the Firefox visit gets DeviceDetector's code for "unknown", the Safari visit no value at all
        Db::query('UPDATE ' . Common::prefixTable('log_visit') . " SET config_browser_name = 'UNK' WHERE idvisit = 3");
        Db::query('UPDATE ' . Common::prefixTable('log_visit') . " SET config_browser_name = '' WHERE idvisit = 4");

        $table = API::getInstance()->getExploration(1, 'day', '2026-03-27', self::BROWSER_VISITS);

        $visits = array_combine($table->getColumn('label'), $table->getColumn('nb_visits'));
        ksort($visits);
        $this->assertSame(['Chrome' => 2, 'Unknown' => 2], $visits);
    }

    public function testReadableColumnsUseTranslatedNames(): void
    {
        $table = API::getInstance()->getExploration(1, 'day', '2026-03-27', self::BROWSER_VISITS, '', 1);

        $this->assertSame(['Browser', 'Visits', 'Actions'], array_keys($table->getFirstRow()->getColumns()));
    }

    public function testAvailableListsCarryActionKinds(): void
    {
        $dimensions = array_column(API::getInstance()->getAvailableDimensions(1), 'kinds', 'id');
        $metrics = array_column(API::getInstance()->getAvailableMetrics(1), 'kind', 'id');

        $this->assertSame(['pageview', 'event'], $dimensions['pageUrl']);
        $this->assertSame([], $dimensions['browser']);
        $this->assertSame('download', $metrics['nb_downloads']);
        $this->assertNull($metrics['nb_visits']);
    }

    public function testUnknownRegionAndCityCanBeFiltered(): void
    {
        // the visits of this test have no location: every row is "Unknown"
        $city = '{"dimensions": ["city"], "metrics": ["nb_visits"], "filters": [{"target": "label", "operator": "notContains", "value": "unknown"}]}';
        $region = '{"dimensions": ["region"], "metrics": ["nb_visits"], "filters": [{"target": "label", "operator": "equals", "value": "unknown"}]}';

        $this->assertSame(0, API::getInstance()->getExploration(1, 'day', '2026-03-27', $city)->getRowsCount());
        $this->assertSame([4], API::getInstance()->getExploration(1, 'day', '2026-03-27', $region)->getColumn('nb_visits'));
    }

    public function testViewUserIsAllowed(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'viewer');

        $this->assertSame(3, API::getInstance()->getExploration(1, 'day', '2026-03-27', self::BROWSER_VISITS)->getRowsCount());
    }

    public function testAnonymousIsRejectedEvenWithViewAccess(): void
    {
        // decision (Peter, 2026-10-19): explorations query raw data, so anonymous users never run them
        FakeAccess::clearAccess(false, [], [1], 'anonymous');

        $this->expectException(NoAccessException::class);
        API::getInstance()->getExploration(1, 'day', '2026-03-27', self::BROWSER_VISITS);
    }

    public function testNoViewAccessThrows(): void
    {
        FakeAccess::$superUser = false;
        FakeAccess::$idSitesView = [];

        $this->expectException(NoAccessException::class);
        API::getInstance()->getExploration(1, 'day', '2026-03-27', self::BROWSER_VISITS);
    }
}
