<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration\Archive;

use Piwik\Db;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedExploration;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedExplorations;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchiveSettings;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationStore;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * A website's archived explorations (design 2026-11-25, Peter: "1, wie empfohlen"): every saved table, private and
 * shared, at most archive_max_explorations_per_site of them, shared ones first, each by most recent change.
 *
 * @group FeinwerkDataExploration
 */
class ArchivedExplorationsTest extends IntegrationTestCase
{
    private SavedExplorationStore $store;

    public function provideContainerConfig()
    {
        return ['Piwik\\Access' => new FakeAccess()];
    }

    public function setUp(): void
    {
        parent::setUp();
        FakeAccess::$superUser = true;
        Fixture::createWebsite('2026-01-01 00:00:00');
        Fixture::createWebsite('2026-01-01 00:00:00');
        $this->store = new SavedExplorationStore();
    }

    public function testSharedFirstThenNewest(): void
    {
        $old = $this->save(1, 'Old private', false, '2026-11-01 10:00:00');
        $new = $this->save(1, 'New private', false, '2026-11-03 10:00:00');
        $shared = $this->save(1, 'Shared', true, '2026-10-01 10:00:00');
        $this->save(2, 'Other website', true, '2026-11-05 10:00:00');

        $ids = array_map(static fn (ArchivedExploration $e): int => $e->idSaved, $this->explorations()->forSite(1));
        $this->assertSame([$shared, $new, $old], $ids);
    }

    public function testCap(): void
    {
        $old = $this->save(1, 'Old private', false, '2026-11-01 10:00:00');
        $new = $this->save(1, 'New private', false, '2026-11-03 10:00:00');
        $shared = $this->save(1, 'Shared', true, '2026-10-01 10:00:00');

        $capped = $this->explorations(new ArchiveSettings(maxExplorationsPerSite: 2));
        $this->assertSame([$shared, $new], array_map(static fn (ArchivedExploration $e): int => $e->idSaved, $capped->forSite(1)));
        $this->assertNull($capped->find(1, $old), 'over the cap');
        $this->assertNotNull($capped->find(1, $new));
        $this->assertNull($capped->find(2, $new), 'another website');
        $this->assertCount(3, $this->explorations(new ArchiveSettings(maxExplorationsPerSite: 0))->forSite(1), 'no cap');
    }

    public function testSkipsKpiAndFunnels(): void
    {
        $table = $this->save(1, 'Table', true, '2026-11-01 10:00:00');
        $this->save(1, 'Card', true, '2026-11-02 10:00:00', ['fwdeKind' => 'kpi', 'fwdeKpiMetric' => 'nb_visits']);
        $this->save(1, 'Funnel', true, '2026-11-03 10:00:00', ['fwdeKind' => 'funnel']);

        $this->assertSame([$table], array_map(static fn (ArchivedExploration $e): int => $e->idSaved, $this->explorations()->forSite(1)));
    }

    public function testOffWhenReportsOff(): void
    {
        $this->save(1, 'Table', true, '2026-11-01 10:00:00');
        $off = $this->explorations(new ArchiveSettings(reports: false));
        $this->assertSame([], $off->forSite(1));
        $this->assertSame([], $off->forAllSites());
    }

    public function testAllSitesAndOneQuery(): void
    {
        $this->save(1, 'One', true, '2026-11-01 10:00:00');
        $this->save(2, 'Two', true, '2026-11-01 10:00:00');
        $calls = 0;
        $explorations = new ArchivedExplorations(
            new ArchiveSettings(),
            function (int $idSite) use (&$calls): array {
                $calls++;
                return $this->store->listAll($idSite);
            },
            function () use (&$calls): array {
                $calls++;
                return $this->store->listEverySite();
            }
        );
        $this->assertCount(2, $explorations->forAllSites());
        $this->assertSame(1, $calls);
        $explorations->forSite(1);
        $this->assertSame(2, $calls);
    }

    /** final review 2026-11-26: a flat report asks every sub table; the website's list is read once per request */
    public function testCachedPerRequest(): void
    {
        $first = $this->save(1, 'First', true, '2026-11-01 10:00:00');
        $settings = new ArchiveSettings();
        $this->assertNotNull(ArchivedExplorations::cachedFind(1, $first, $settings));
        $second = $this->save(1, 'Second', true, '2026-11-02 10:00:00');
        $this->assertNull(ArchivedExplorations::cachedFind(1, $second, $settings), 'the list of this request');
        \Piwik\Cache::getTransientCache()->flushAll();
        $this->assertNotNull(ArchivedExplorations::cachedFind(1, $second, $settings));
    }

    private function explorations(?ArchiveSettings $settings = null): ArchivedExplorations
    {
        return new ArchivedExplorations($settings ?? new ArchiveSettings());
    }

    private function save(int $idSite, string $name, bool $shared, string $updated, array $extra = []): int
    {
        $id = $this->store->add($idSite, 'anna', $name, ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits'] + $extra, '', $shared);
        Db::query('UPDATE `' . $this->store->table() . '` SET ts_updated = ? WHERE idsaved = ?', [$updated, $id]);
        return $id;
    }
}
