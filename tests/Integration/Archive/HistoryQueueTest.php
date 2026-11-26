<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration\Archive;

use Piwik\Cache;
use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedExploration;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedExplorations;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchiveSettings;
use Piwik\Plugins\FeinwerkDataExploration\Archive\HistoryQueue;
use Piwik\Plugins\FeinwerkDataExploration\Archive\RetryList;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationStore;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * History queued in the background (design 2026-11-25, Peter: "2, wie empfohlen"): a new or changed exploration's days
 * of its own website are invalidated for its own record only, within the plugin's months, the website's creation and
 * the raw data, a few explorations per run; failed days are tried again at most three times.
 *
 * @group FeinwerkDataExploration
 */
class HistoryQueueTest extends IntegrationTestCase
{
    private const TODAY = '2026-03-15';

    private SavedExplorationStore $store;

    public function provideContainerConfig()
    {
        return ['Piwik\\Access' => new FakeAccess()];
    }

    public function setUp(): void
    {
        parent::setUp();
        FakeAccess::$superUser = true;
        Config::getInstance()->FeinwerkDataExploration = [];
        Fixture::createWebsite('2025-06-01 00:00:00');
        Fixture::createWebsite('2026-03-10 00:00:00');
        $this->store = new SavedExplorationStore();
    }

    public function testOnlyOwnSiteAndRecord(): void
    {
        $e = $this->explorations([$this->save(1)])[0];
        $queue = $this->queue();
        $queue->reconcile([$e]);
        $this->assertTrue($queue->isPending($e->idSaved));
        $this->assertSame(1, $queue->run([$e], new ArchiveSettings(historyMonths: 1), self::TODAY));
        $this->assertFalse($queue->isPending($e->idSaved));

        $rows = $this->invalidations();
        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            // Matomo keeps the plugin in the archive name and the record as the report
            $this->assertSame(1, (int) $row['idsite']);
            $this->assertSame($e->recordName(), $row['report_name']);
            $this->assertStringEndsWith('.FeinwerkDataExploration', $row['name']);
        }
        $days = $this->days($rows);
        $this->assertSame('2026-02-15', $days[0]);
        $this->assertSame('2026-03-14', end($days));
    }

    public function testBounds(): void
    {
        // website 2 was created on 10 March; raw data of website 1 starts on 1 March
        [$one, $two] = $this->explorations([$this->save(1), $this->save(2)]);
        $queue = $this->queue(static fn (int $idSite): ?string => $idSite === 1 ? '2026-03-01' : null);
        $queue->reconcile([$one, $two]);
        $queue->run([$one, $two], new ArchiveSettings(historyMonths: 6), self::TODAY);
        $this->assertSame('2026-03-01', $this->days($this->invalidations(1))[0]);
        // Matomo keeps a website's creation a day early (timezones); the bound is what it keeps
        $this->assertSame(substr((string) \Piwik\Site::getCreationDateFor(2), 0, 10), $this->days($this->invalidations(2))[0]);
        $this->assertGreaterThan('2026-02-28', $this->days($this->invalidations(2))[0]);
    }

    /** final review 2026-11-26: the save panel names the first day a rebuilt history reaches, null when nothing is lost */
    public function testLostBefore(): void
    {
        $this->assertSame('2025-09-15', $this->queue()->lostBefore(1, 6, self::TODAY));
        $this->assertSame('2026-03-01', $this->queue(static fn (int $idSite): ?string => '2026-03-01')->lostBefore(1, 6, self::TODAY));
        // website 2 was created on 10 March: six months reach back past its first day
        $this->assertNull($this->queue()->lostBefore(2, 6, self::TODAY));
        // no history is rebuilt at all
        $this->assertSame(self::TODAY, $this->queue()->lostBefore(1, 0, self::TODAY));
    }

    public function testBatch(): void
    {
        $all = $this->explorations([$this->save(1), $this->save(1), $this->save(1)]);
        $queue = $this->queue();
        $queue->reconcile($all);
        $this->assertSame(2, $queue->run($all, new ArchiveSettings(historyMonths: 1, historyBatch: 2), self::TODAY));
        $this->assertSame(1, $queue->run($all, new ArchiveSettings(historyMonths: 1, historyBatch: 2), self::TODAY));
        $this->assertSame(0, $queue->run($all, new ArchiveSettings(historyMonths: 1, historyBatch: 2), self::TODAY));
    }

    public function testMonthsZeroQueuesNothing(): void
    {
        $e = $this->explorations([$this->save(1)])[0];
        $queue = $this->queue();
        $queue->reconcile([$e]);
        $queue->run([$e], new ArchiveSettings(historyMonths: 0), self::TODAY);
        $this->assertSame([], $this->invalidations());
        $this->assertFalse($queue->isPending($e->idSaved));
    }

    public function testSegmentsOnlyWithSwitch(): void
    {
        // a pre-processed segment needs browser archiving off
        \Piwik\ArchiveProcessor\Rules::setBrowserTriggerArchiving(false);
        \Piwik\Plugins\SegmentEditor\API::getInstance()->add('Firefox', 'browserCode==FF', 1, true, true);
        $e = $this->explorations([$this->save(1)])[0];
        $queue = $this->queue();
        $queue->reconcile([$e]);
        $queue->run([$e], new ArchiveSettings(historyMonths: 1), self::TODAY);
        // the segment is part of Matomo's archive name: "done" for all visits, "done<hash>" for a segment
        $this->assertSame(['done'], array_values(array_unique(array_map([$this, 'segmentPart'], $this->invalidations()))));

        $changed = $this->explorations([$this->save(1, 'deviceType')])[0];
        $queue->reconcile([$e, $changed]);
        $queue->run([$e, $changed], new ArchiveSettings(historyMonths: 1, historySegments: true), self::TODAY);
        $this->assertCount(2, array_unique(array_map([$this, 'segmentPart'], $this->invalidations(1, $changed))));
    }

    public function testChangedVersionRequeued(): void
    {
        $id = $this->save(1);
        $e = $this->explorations([$id])[0];
        $queue = $this->queue();
        $queue->reconcile([$e]);
        $queue->run([$e], new ArchiveSettings(historyMonths: 1), self::TODAY);
        $queue->reconcile([$e]);
        $this->assertFalse($queue->isPending($id), 'same version: nothing new');

        $this->store->update($id, 'Renamed', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits', 'fwdeMenu' => 'Goals_Goals'], '', true);
        $renamed = $this->explorations([$id])[0];
        $queue->reconcile([$renamed]);
        $this->assertFalse($queue->isPending($id), 'a rename or menu place is no new version');

        $this->store->update($id, 'Renamed', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits,nb_actions'], '', true);
        $changed = $this->explorations([$id])[0];
        $queue->reconcile([$changed]);
        $this->assertTrue($queue->isPending($id));
    }

    public function testDeletedExplorationDropped(): void
    {
        $e = $this->explorations([$this->save(1)])[0];
        $queue = $this->queue();
        $queue->reconcile([$e]);
        (new RetryList())->add($e->idSaved, '2026-03-02');
        $queue->reconcile([]);
        $this->assertFalse($queue->isPending($e->idSaved));
        $this->assertSame([], (new RetryList())->due());
        $this->assertSame(0, $queue->run([], new ArchiveSettings(), self::TODAY));
        $this->assertSame([], $this->invalidations());
    }

    /** release review 2026-11-26: uninstalling leaves none of the archive's options behind */
    public function testDeleteOptions(): void
    {
        $e = $this->explorations([$this->save(1)])[0];
        $this->queue()->reconcile([$e]);
        (new RetryList())->add($e->idSaved, '2026-03-02');
        $this->assertNotEmpty(\Piwik\Option::getLike('fw_dataexploration.archive%'));
        HistoryQueue::deleteOptions();
        $this->assertSame([], \Piwik\Option::getLike('fw_dataexploration.archive%'));
    }

    public function testRetriesAtMostThree(): void
    {
        $e = $this->explorations([$this->save(1)])[0];
        $queue = $this->queue();
        $queue->reconcile([$e]);
        $queue->run([$e], new ArchiveSettings(historyMonths: 0), self::TODAY);
        (new RetryList())->add($e->idSaved, '2026-03-02');
        for ($i = 0; $i < 4; $i++) {
            $queue->run([$e], new ArchiveSettings(historyMonths: 0), self::TODAY);
        }
        $this->assertSame([], (new RetryList())->due());
        $this->assertContains('2026-03-02', $this->days($this->invalidations()));
    }

    /** the hourly task (command line runs only) reconciles and queues */
    public function testHourlyTask(): void
    {
        $id = $this->save(1);
        (new \Piwik\Plugins\FeinwerkDataExploration\Tasks())->archiveHistory();
        $this->assertNotEmpty($this->invalidations(1));
        $this->assertFalse($this->queue()->isPending($id));
    }

    /** a report says while its history is queued (design 2026-11-25) */
    public function testPendingNoteInTheView(): void
    {
        $id = $this->save(1);
        $this->queue()->reconcile($this->explorations([$id]));
        $_GET = array_merge($_GET, ['idSite' => 1, 'period' => 'month', 'date' => '2026-03-15', 'idSaved' => $id]);
        try {
            Cache::getTransientCache()->flushAll();
            $view = \Piwik\ViewDataTable\Factory::build('table', 'FeinwerkDataExploration.getSavedReport', 'FeinwerkDataExploration.getSavedReport', true);
        } finally {
            unset($_GET['idSite'], $_GET['period'], $_GET['date'], $_GET['idSaved']);
        }
        $this->assertStringContainsString(\Piwik\Piwik::translate('FeinwerkDataExploration_ArchiveHistoryPending'), (string) $view->config->show_footer_message);
    }

    private function queue(?callable $rawDataStart = null): HistoryQueue
    {
        return new HistoryQueue($rawDataStart ?? static fn (int $idSite): ?string => null);
    }

    private function save(int $idSite, string $dimensions = 'browser'): int
    {
        return $this->store->add($idSite, 'anna', 'Exploration', ['fwdeDims' => $dimensions, 'fwdeMetrics' => 'nb_visits'], '', true);
    }

    /** @return list<ArchivedExploration> */
    private function explorations(array $ids): array
    {
        Cache::getTransientCache()->flushAll();
        $all = (new ArchivedExplorations(new ArchiveSettings(maxExplorationsPerSite: 0)))->forAllSites();
        $byId = [];
        foreach ($all as $e) {
            $byId[$e->idSaved] = $e;
        }
        return array_map(static fn (int $id): ArchivedExploration => $byId[$id], $ids);
    }

    private function invalidations(?int $idSite = null, ?ArchivedExploration $only = null): array
    {
        $rows = Db::fetchAll('SELECT idsite, date1, period, `report` AS report_name, `name` FROM ' . Common::prefixTable('archive_invalidations'));
        return array_values(array_filter($rows, static fn (array $row): bool => ($idSite === null || (int) $row['idsite'] === $idSite)
            && ($only === null || $row['report_name'] === $only->recordName())));
    }

    /** the archive name's "done" part, which holds the segment's hash */
    public function segmentPart(array $row): string
    {
        return explode('.', (string) $row['name'])[0];
    }

    /** @return list<string> the invalidated days, sorted */
    private function days(array $rows): array
    {
        $days = array_values(array_unique(array_map(static fn (array $row): string => $row['date1'], array_filter($rows,
            static fn (array $row): bool => (int) $row['period'] === 1))));
        sort($days);
        return $days;
    }
}
