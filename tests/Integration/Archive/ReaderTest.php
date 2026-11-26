<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration\Archive;

use Piwik\Archive;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Cache;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedExploration;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedExplorations;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedReportReader;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchiveSettings;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationStore;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Reading an archived exploration for any period (design 2026-11-25): days added up by Matomo, rates from their pairs,
 * distinct counts only for a day, the coverage of a period, labels formatted, the saved sort and row limit.
 *
 * @group FeinwerkDataExploration
 */
class ReaderTest extends IntegrationTestCase
{
    private const FIREFOX = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0';
    private const CHROME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

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
        Cache::getLazyCache()->flushAll();
        Config::getInstance()->FeinwerkDataExploration = [];
        Fixture::createWebsite('2026-01-01 00:00:00', 0, 'Shop', 'https://example.com', 1, null, null, 'UTC');
        $_GET['idSite'] = 1;
        $this->visits([
            [self::FIREFOX, '2026-02-10 10:00:00', 1],
            [self::FIREFOX, '2026-03-27 10:00:00', 1],
            [self::FIREFOX, '2026-03-27 11:00:00', 3],
            [self::FIREFOX, '2026-03-28 11:00:00', 2],
            [self::CHROME, '2026-03-27 12:00:00', 2],
        ]);
    }

    public function tearDown(): void
    {
        unset($_GET['idSite']);
        parent::tearDown();
    }

    public function testYearSumsDays(): void
    {
        $e = $this->exploration($this->save(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits']));
        $table = $this->reader()->read($e, 'year', '2026-03-27', '', 0, null);
        $this->assertEquals(4, $this->row($table, 'Firefox')->getColumn('nb_visits'));
        $this->assertEquals(1, $this->row($table, 'Chrome')->getColumn('nb_visits'));
        // the sandbox check (2026-11-26): no internal metadata in the API's output
        $this->assertFalse($this->row($table, 'Chrome')->getMetadata('fwde_level'));
        $this->assertFalse($this->row($table, 'Chrome')->getMetadata('fwde_raw'));
    }

    public function testRatesFromPairs(): void
    {
        $e = $this->exploration($this->save(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits,bounce_rate']));
        // in March Firefox has 3 visits, one bounced: 0.33 (the mean of the days 0.5 and 0 would be 0.25)
        $firefox = $this->row($this->reader()->read($e, 'month', '2026-03-27', '', 0, null), 'Firefox');
        $this->assertEquals(0.33, $firefox->getColumn('bounce_rate'));
        $this->assertFalse($firefox->getColumn('bounce_rate__n'));
        $this->assertFalse($firefox->getColumn('bounce_rate__d'));
    }

    public function testDistinctOnlyOnADay(): void
    {
        $e = $this->exploration($this->save(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits,nb_uniq_visitors']));
        $this->assertEquals(2, $this->row($this->reader()->read($e, 'day', '2026-03-27', '', 0, null), 'Firefox')->getColumn('nb_uniq_visitors'));
        $this->assertFalse($this->row($this->reader()->read($e, 'month', '2026-03-27', '', 0, null), 'Firefox')->getColumn('nb_uniq_visitors'));
    }

    public function testCoverage(): void
    {
        // the plugin's day archive of 27 March (with visits) exists before the exploration is saved: complete without
        // its record; days without visits never get a marker (Matomo skips plugins there) and need none
        Archive::build(1, 'day', '2026-03-27')->getNumeric('FeinwerkDataExploration_nothing');
        $e = $this->exploration($this->save(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits']));
        $this->assertSame([1, 2], $this->reader()->coverage($e, 'week', '2026-03-27', ''));
        $this->assertSame([1, 1], $this->reader()->coverage($e, 'day', '2026-03-28', ''));
        $this->assertSame([1, 2], $this->reader()->coverage($e, 'range', '2026-03-27,2026-03-28', ''));
        $this->assertSame([1, 1], $this->reader()->coverage($e, 'month', '2026-02-15', ''), 'one day with visits in February');
        $this->assertSame([0, 0], $this->reader()->coverage($e, 'day', '2026-03-23', ''), 'a day without visits needs nothing');
    }

    /**
     * release review 2026-11-26: a day Matomo has not archived yet (cron archiving, before its first run of the day, or
     * imported logs) is not a day without visits: it needs the record, so a short period is answered live, not empty
     */
    public function testDayNotArchivedYetIsNeeded(): void
    {
        $e = $this->exploration($this->save(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits']));
        Rules::setBrowserTriggerArchiving(false);
        try {
            Cache::getTransientCache()->flushAll();
            $this->assertSame([0, 1], $this->reader()->coverage($e, 'day', '2026-03-28', ''));
        } finally {
            Rules::setBrowserTriggerArchiving(true);
        }
    }

    /** final review 2026-11-26: a report's source is chosen once per request (each sub table asks it again) */
    public function testSourceCachedPerRequest(): void
    {
        $e = $this->exploration($this->save(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits']));
        $reader = $this->reader();
        $source = $reader->source($e, 'month', '2026-03-27', '');
        $this->assertTrue(Cache::getTransientCache()->contains(ArchivedReportReader::sourceKey($e, 'month', '2026-03-27', '')));
        $this->assertSame($source, $reader->source($e, 'month', '2026-03-27', ''));
    }

    public function testStaleSubtableIsEmpty(): void
    {
        $e = $this->exploration($this->save(['fwdeDims' => 'browser,deviceType', 'fwdeMetrics' => 'nb_visits']));
        $this->assertSame(0, $this->reader()->read($e, 'month', '2026-03-27', '', 987654, null)->getRowsCount());
    }

    public function testSortAndLimitOnFirstLevel(): void
    {
        $e = $this->exploration($this->save(['fwdeDims' => 'browser,deviceType', 'fwdeMetrics' => 'nb_visits',
            'fwdeSort' => 'nb_visits', 'fwdeOrder' => 'asc']));
        $table = $this->reader()->read($e, 'month', '2026-03-27', '', 0, null);
        $this->assertSame(['Chrome', 'Firefox'], $table->getColumn('label'));
        // the sub table of a first-level row keeps its rows (Matomo's ids)
        $idSubtable = (int) $table->getFirstRow()->getIdSubDataTable();
        $this->assertGreaterThan(0, $idSubtable);
        $devices = $this->reader()->read($e, 'month', '2026-03-27', '', $idSubtable, null);
        $this->assertSame(['Desktop'], $devices->getColumn('label'));

        // the row limit cuts the first level
        $this->visits([[self::FIREFOX, '2026-03-26 10:00:00', 11]]);
        $pages = $this->exploration($this->save(['fwdeDims' => 'pageTitle', 'fwdeMetrics' => 'nb_visits', 'fwdeLimit' => '10']));
        $this->assertCount(10, $this->reader()->read($pages, 'month', '2026-03-27', '', 0, null)->getRows());
    }

    private function reader(): ArchivedReportReader
    {
        Cache::getTransientCache()->flushAll();
        return new ArchivedReportReader();
    }

    private function row(DataTable $table, string $label): DataTable\Row
    {
        $row = $table->getRowFromLabel($label);
        $this->assertNotFalse($row, $label . ' in ' . json_encode($table->getColumn('label')));
        return $row;
    }

    private function visits(array $visits): void
    {
        foreach ($visits as $n => [$userAgent, $time, $pageviews]) {
            $t = Fixture::getTracker(1, $time, true, true);
            $t->setNewVisitorId();
            $t->setIp('198.51.100.' . ($n + 1));
            $t->setUserAgent($userAgent);
            for ($p = 0; $p < $pageviews; $p++) {
                $t->setForceVisitDateTime(date('Y-m-d H:i:s', strtotime($time) + 60 * $p));
                $t->setUrl('https://example.com/page' . $p);
                Fixture::checkResponse($t->doTrackPageView('Page ' . $p));
            }
        }
    }

    private function save(array $state): int
    {
        return (new SavedExplorationStore())->add(1, 'superUserLogin', 'Exploration', $state, '', true);
    }

    private function exploration(int $id): ArchivedExploration
    {
        Cache::getTransientCache()->flushAll();
        $exploration = (new ArchivedExplorations(new ArchiveSettings()))->find(1, $id);
        $this->assertNotNull($exploration);
        return $exploration;
    }
}
