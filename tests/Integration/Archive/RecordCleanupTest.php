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
use Piwik\Common;
use Piwik\Config;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedExploration;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedExplorations;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchiveSettings;
use Piwik\Plugins\FeinwerkDataExploration\Archive\RecordCleanup;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationStore;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Old records removed in small chunks (design 2026-11-25): the records of an older version or of a deleted exploration,
 * never a current one, never another plugin's.
 *
 * @group FeinwerkDataExploration
 */
class RecordCleanupTest extends IntegrationTestCase
{
    private const DAY = '2026-03-27';

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
        Fixture::createWebsite('2026-01-01 00:00:00', 0, 'Shop', 'https://example.com', 1, null, null, 'UTC');
        $_GET['idSite'] = 1;
        foreach (['2026-03-26', self::DAY] as $n => $day) {
            $t = Fixture::getTracker(1, $day . ' 10:00:00', true, true);
            $t->setNewVisitorId();
            $t->setIp('198.51.100.' . ($n + 1));
            $t->setUrl('https://example.com/');
            Fixture::checkResponse($t->doTrackPageView('Home'));
        }
    }

    public function tearDown(): void
    {
        unset($_GET['idSite']);
        parent::tearDown();
    }

    public function testOldVersionRemovedCurrentKept(): void
    {
        $store = new SavedExplorationStore();
        $id = $store->add(1, 'superUserLogin', 'Pages', ['fwdeDims' => 'pageTitle', 'fwdeMetrics' => 'nb_visits'], '', true);
        // the older version archived on 26 March; after the change 27 March is archived (a complete day archive is not
        // built again for the new version until the history task invalidates it)
        $old = $this->archive($id, '2026-03-26');
        $store->update($id, 'Pages', ['fwdeDims' => 'pageTitle', 'fwdeMetrics' => 'nb_visits,nb_actions'], '', true);
        $new = $this->archive($id);
        $this->assertNotSame($old->recordName(), $new->recordName());
        $visitsBefore = $this->rowsLike('VisitsSummary%') + $this->rowsLike('done%');

        $deleted = (new RecordCleanup())->run([$new]);
        $this->assertGreaterThan(0, $deleted);
        $this->assertSame(0, $this->rowsLike($old->recordName() . '%'));
        $this->assertGreaterThan(0, $this->rowsLike($new->recordName()));
        $this->assertGreaterThan(0, $this->rowsLike($new->markerName()));
        $this->assertSame($visitsBefore, $this->rowsLike('VisitsSummary%') + $this->rowsLike('done%'), 'other records untouched');
    }

    /** security review 2026-11-26: Matomo keeps sub tables in "<record>_chunk_X_Y" rows; a current one's stay */
    public function testCurrentSubTablesKept(): void
    {
        $store = new SavedExplorationStore();
        $id = $store->add(1, 'superUserLogin', 'Pages by browser', ['fwdeDims' => 'pageTitle,browser', 'fwdeMetrics' => 'nb_visits'], '', true);
        $current = $this->archive($id);
        $chunks = $this->rowsLike($current->recordName() . '\\_chunk\\_%');
        $this->assertGreaterThan(0, $chunks, 'precondition: the sub tables are stored as chunks');

        (new RecordCleanup())->run([$current]);
        $this->assertSame($chunks, $this->rowsLike($current->recordName() . '\\_chunk\\_%'));
        $this->assertGreaterThan(0, $this->rowsLike($current->recordName()));
    }

    /**
     * final review 2026-11-26: history that cannot be rebuilt stays: an exploration over the cap, one that does not
     * resolve for a while, and everything while archiving is off
     */
    public function testExistingExplorationsKept(): void
    {
        $store = new SavedExplorationStore();
        $one = $this->archive($store->add(1, 'superUserLogin', 'One', ['fwdeDims' => 'pageTitle', 'fwdeMetrics' => 'nb_visits'], '', true));
        // another day: the first day's archive is complete without the second exploration
        $two = $this->archive($store->add(1, 'superUserLogin', 'Two', ['fwdeDims' => 'pageTitle', 'fwdeMetrics' => 'nb_actions'], '', true), '2026-03-26');
        $this->assertGreaterThan(0, $this->rowsLike($two->recordName()), 'precondition');
        $rows = $this->rowsLike('FeinwerkDataExploration\\_saved%');

        // over the cap: the weekly task ignores the cap
        Config::getInstance()->FeinwerkDataExploration = ['archive_max_explorations_per_site' => 1];
        (new \Piwik\Plugins\FeinwerkDataExploration\Tasks())->archiveCleanup();
        $this->assertSame($rows, $this->rowsLike('FeinwerkDataExploration\\_saved%'));

        // archiving off: nothing is cleaned up
        Config::getInstance()->FeinwerkDataExploration = ['archive_reports' => 0];
        (new \Piwik\Plugins\FeinwerkDataExploration\Tasks())->archiveCleanup();
        $this->assertSame($rows, $this->rowsLike('FeinwerkDataExploration\\_saved%'));

        // one that does not resolve (not among the current ones) but still exists keeps every record
        Config::getInstance()->FeinwerkDataExploration = [];
        (new RecordCleanup())->run([$one], 10000, [$two->idSaved]);
        $this->assertGreaterThan(0, $this->rowsLike($two->recordName()));
    }

    public function testDeletedExplorationAndMax(): void
    {
        $store = new SavedExplorationStore();
        $id = $store->add(1, 'superUserLogin', 'Pages', ['fwdeDims' => 'pageTitle', 'fwdeMetrics' => 'nb_visits'], '', true);
        $gone = $this->archive($id, '2026-03-26');
        $this->archive($id);
        $store->delete($id);
        $rows = $this->rowsLike($gone->recordName() . '%');
        $this->assertGreaterThanOrEqual(4, $rows, 'two days, table record and marker each');

        $this->assertSame(1, (new RecordCleanup())->run([], 1));
        $this->assertSame($rows - 1, $this->rowsLike($gone->recordName() . '%'));
        (new RecordCleanup())->run([]);
        $this->assertSame(0, $this->rowsLike($gone->recordName() . '%'));
    }

    /**
     * release review 2026-11-26: the weekly task scans only websites that had archived explorations (none: no query per
     * archive table and website) and forgets a website without explorations once it is clean
     */
    public function testTaskScansOnlyArchivedSites(): void
    {
        $store = new SavedExplorationStore();
        $id = $store->add(1, 'superUserLogin', 'Pages', ['fwdeDims' => 'pageTitle', 'fwdeMetrics' => 'nb_visits'], '', true);
        $gone = $this->archive($id);
        $store->delete($id);
        (new \Piwik\Plugins\FeinwerkDataExploration\Tasks())->archiveCleanup();
        $this->assertGreaterThan(0, $this->rowsLike($gone->recordName() . '%'), 'no website known yet: nothing scanned');

        (new \Piwik\Plugins\FeinwerkDataExploration\Archive\HistoryQueue())->reconcile([$gone]);
        $this->assertSame([1], \Piwik\Plugins\FeinwerkDataExploration\Archive\HistoryQueue::archivedSites());
        (new \Piwik\Plugins\FeinwerkDataExploration\Tasks())->archiveCleanup();
        $this->assertSame(0, $this->rowsLike($gone->recordName() . '%'));
        $this->assertSame([], \Piwik\Plugins\FeinwerkDataExploration\Archive\HistoryQueue::archivedSites(), 'clean and without explorations');
    }

    private function archive(int $id, string $day = self::DAY): ArchivedExploration
    {
        Cache::getTransientCache()->flushAll();
        $e = (new ArchivedExplorations(new ArchiveSettings()))->find(1, $id);
        $this->assertNotNull($e);
        Archive::build(1, 'day', $day)->getDataTable($e->recordName());
        return $e;
    }

    /** rows of the archive tables of March 2026 whose name is like $pattern */
    private function rowsLike(string $pattern): int
    {
        $total = 0;
        foreach ([ArchiveTableCreator::getNumericTable(Date::factory(self::DAY)), ArchiveTableCreator::getBlobTable(Date::factory(self::DAY))] as $table) {
            $total += (int) Db::fetchOne('SELECT COUNT(*) FROM ' . $table . ' WHERE name LIKE ?', [$pattern]);
        }
        return $total;
    }
}
