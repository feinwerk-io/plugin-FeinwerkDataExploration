<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration\Archive;

use Piwik\Archive;
use Piwik\Cache;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedExplorations;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchiveSettings;
use Piwik\Plugins\FeinwerkDataExploration\Archive\DayTree;
use Piwik\Plugins\FeinwerkDataExploration\Archive\RetryList;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationStore;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * A record per saved exploration and day (design 2026-11-25): the exploration's rows of the website's day with sub
 * tables per level and a marker that the day is archived; a failing exploration leaves only its own day out.
 *
 * @group FeinwerkDataExploration
 */
class RecordBuilderTest extends IntegrationTestCase
{
    private const DAY = '2026-03-27';
    private const FIREFOX = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0';
    private const CHROME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    private const CHROME_MOBILE = 'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36';

    public function provideContainerConfig()
    {
        return ['Piwik\\Access' => new FakeAccess()];
    }

    public function setUp(): void
    {
        parent::setUp();
        Fixture::createSuperUser(false);
        IPAnonymizer::deactivate();
        FakeAccess::$superUser = true;
        Cache::getLazyCache()->flushAll();
        Config::getInstance()->FeinwerkDataExploration = [];
    }

    public function tearDown(): void
    {
        unset($_GET['idSite'], $_GET['trigger']);
        Config::getInstance()->FeinwerkDataExploration = [];
        parent::tearDown();
    }

    public function testDayRecordAndMarker(): void
    {
        $this->site('UTC');
        $this->visits([[self::FIREFOX, self::DAY . ' 10:00:00', 1], [self::FIREFOX, self::DAY . ' 11:00:00', 3],
            [self::CHROME, self::DAY . ' 12:00:00', 2]]);
        $id = $this->save('browser', 'nb_visits,bounce_rate');
        $exploration = $this->exploration($id);

        $table = $this->record($exploration->recordName());
        // labels are folded as the database compares them; the spelling shown is kept (DayTree::RAW)
        $firefox = $table->getRowFromLabel('ff');
        $this->assertNotFalse($firefox, json_encode($table->getColumn('label')));
        $this->assertSame('FF', $firefox->getMetadata(DayTree::RAW));
        $this->assertEquals(2, $firefox->getColumn('nb_visits'));
        $this->assertEquals(1, $firefox->getColumn('bounce_rate__n'));
        $this->assertEquals(2, $firefox->getColumn('bounce_rate__d'));
        $this->assertEquals(1, $table->getRowFromLabel('ch')->getColumn('nb_visits'));
        $this->assertEquals(1, $this->numeric($exploration->markerName()));
    }

    public function testSubTablesPerLevel(): void
    {
        $this->site('UTC');
        $this->visits([[self::CHROME, self::DAY . ' 10:00:00', 1], [self::FIREFOX, self::DAY . ' 11:00:00', 1],
            [self::FIREFOX, self::DAY . ' 12:00:00', 1], [self::CHROME_MOBILE, self::DAY . ' 13:00:00', 1]]);
        $exploration = $this->exploration($this->save('deviceType,browser', 'nb_visits'));

        $table = $this->record($exploration->recordName(), true);
        // device type 0 is a desktop (Matomo's DeviceDetector code)
        $desktop = $table->getRowFromLabel('0');
        $this->assertNotFalse($desktop, json_encode($table->getColumn('label')));
        $this->assertEquals(3, $desktop->getColumn('nb_visits'));
        $browsers = $desktop->getSubtable();
        $this->assertInstanceOf(DataTable::class, $browsers);
        $this->assertEqualsCanonicalizing(['ch', 'ff'], $browsers->getColumn('label'));
        $this->assertEquals(2, $browsers->getRowFromLabel('ff')->getColumn('nb_visits'));
        $this->assertCount(2, $table->getRows(), 'desktop and smartphone');
    }

    public function testTimezone(): void
    {
        $this->site('Australia/Brisbane');
        // 23:30 UTC on the day before is 09:30 on DAY in Brisbane (UTC+10)
        $this->visits([[self::FIREFOX, '2026-03-26 23:30:00', 1], [self::FIREFOX, self::DAY . ' 15:00:00', 1]]);
        $exploration = $this->exploration($this->save('browser', 'nb_visits'));

        $this->assertEquals(1, $this->record($exploration->recordName())->getRowFromLabel('ff')->getColumn('nb_visits'));
    }

    public function testFailureLeavesNoMarker(): void
    {
        $this->site('UTC');
        $this->visits([[self::FIREFOX, self::DAY . ' 10:00:00', 1]]);
        $broken = $this->exploration($this->save('browser', 'nb_visits', 'noSuchSegmentDimension==1'));
        $fine = $this->exploration($this->save('deviceType', 'nb_visits'));

        $this->assertSame(0, $this->record($broken->recordName())->getRowsCount());
        $this->assertEquals(0, $this->numeric($broken->markerName()));
        $this->assertSame([$broken->idSaved => [self::DAY]], (new RetryList())->due());
        $this->assertGreaterThan(0, $this->record($fine->recordName())->getRowsCount());
        $this->assertEquals(1, $this->numeric($fine->markerName()));
    }

    public function testPreprocessedSegmentSkipped(): void
    {
        $this->site('UTC');
        $this->visits([[self::FIREFOX, self::DAY . ' 10:00:00', 1]]);
        $exploration = $this->exploration($this->save('browser', 'nb_visits'));
        $_GET['trigger'] = 'archivephp';

        $this->assertSame(0, $this->record($exploration->recordName(), false, 'browserCode==FF')->getRowsCount());

        // the segment archive above is complete without the record; Matomo does not build it again (the report then
        // answers live up to 31 days, design 2026-11-25), so the switch is checked with another segment
        Config::getInstance()->FeinwerkDataExploration = ['archive_segments' => 1];
        $this->assertSame(1, $this->record($exploration->recordName(), false, 'browserCode==FF;deviceType==desktop')->getRowsCount());
    }

    /** release check 2026-11-26: Matomo 6 no longer trims request values; a single idSites value still names the site */
    public function testRecordBuildersForAnUntrimmedIdSites(): void
    {
        $this->site('UTC');
        unset($_GET['idSite']);
        $this->save('browser', 'nb_visits');
        $_GET['idSites'] = ' 1 ';
        $builders = [];
        try {
            (new \Piwik\Plugins\FeinwerkDataExploration\FeinwerkDataExploration())->addRecordBuilders($builders);
        } finally {
            unset($_GET['idSites']);
        }
        $this->assertCount(1, $builders);
    }

    private function site(string $timezone): void
    {
        Fixture::createWebsite('2026-01-01 00:00:00', 0, 'Shop', 'https://example.com', 1, null, null, $timezone);
        $_GET['idSite'] = 1;
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

    private function save(string $dimensions, string $metrics, string $segment = ''): int
    {
        return (new SavedExplorationStore())->add(1, 'superUserLogin', 'Exploration ' . $dimensions,
            ['fwdeDims' => $dimensions, 'fwdeMetrics' => $metrics], $segment, true);
    }

    private function exploration(int $id): \Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedExploration
    {
        $exploration = (new ArchivedExplorations(new ArchiveSettings()))->find(1, $id);
        $this->assertNotNull($exploration);
        return $exploration;
    }

    private function record(string $name, bool $expanded = false, string $segment = ''): DataTable
    {
        Cache::getTransientCache()->flushAll();
        $archive = Archive::build(1, 'day', self::DAY, $segment);
        $table = $expanded ? $archive->getDataTableExpanded($name, null, null, null, true) : $archive->getDataTable($name);
        $this->assertInstanceOf(DataTable::class, $table);
        return $table;
    }

    private function numeric(string $name): float
    {
        Cache::getTransientCache()->flushAll();
        return (float) Archive::build(1, 'day', self::DAY)->getNumeric($name);
    }
}
