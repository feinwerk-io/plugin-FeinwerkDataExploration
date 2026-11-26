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
use Piwik\Archive\ArchiveInvalidator;
use Piwik\Cache;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\CoreAdminHome\API as CoreAdminHomeApi;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedExploration;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedExplorations;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchiveSettings;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationStore;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Weeks, months and years that Matomo builds for all plugins (the current ones on every core:archive run) add up the
 * newest day of each exploration's record (release check 2026-11-26, Matomo's shared archive, matomo#25501): the history
 * builds the days for this plugin only, after Matomo's full archives of those days, which hold no record of a new
 * exploration. A day whose visits were deleted afterwards adds nothing.
 *
 * @group FeinwerkDataExploration
 */
class NewestSubperiodTest extends IntegrationTestCase
{
    private const MONDAY = '2026-03-02';
    private const DAYS = ['2026-03-02', '2026-03-03', '2026-03-04'];
    private const FIREFOX = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0';
    private const CHROME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

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
    }

    public function tearDown(): void
    {
        unset($_GET['idSite'], $_GET['trigger'], $_GET['pluginOnly']);
        Rules::setBrowserTriggerArchiving(true);
        Config::getInstance()->FeinwerkDataExploration = [];
        parent::tearDown();
    }

    /** the history's archives are partial (one record); a whole archive of this plugin (core:invalidate-report-data --plugin) */
    public function plugin(): array
    {
        return ['partial (the history)' => [true], 'whole plugin archive' => [false]];
    }

    /** @dataProvider plugin */
    public function testAllPluginsWeekAddsUpTheNewestDays(bool $partial): void
    {
        $this->visits([[self::FIREFOX, '2026-03-02 10:00:00'], [self::FIREFOX, '2026-03-02 11:00:00'],
            [self::CHROME, '2026-03-03 10:00:00'], [self::CHROME, '2026-03-04 10:00:00']]);
        // Matomo's full archives of the days, made before the exploration existed: no record of it
        foreach (self::DAYS as $day) {
            $this->archive('day', $day);
        }
        $exploration = $this->exploration($this->save('browser', 'nb_visits'));
        // its history: newer archives of this plugin with the record, as core:archive builds them from the queue
        foreach (self::DAYS as $day) {
            $this->archive('day', $day, $partial ? $exploration->recordName() : '');
        }
        // Wednesday's visits are deleted afterwards (Matomo invalidates the day)
        $this->deleteVisitsOf(self::DAYS[2]);

        // the week as core:archive builds it for all plugins
        $this->archive('week', self::MONDAY);

        $week = $this->read($exploration->recordName());
        $this->assertSame(3, (int) array_sum($week->getColumn('nb_visits')), 'Monday and Tuesday, not Wednesday');
        $this->assertSame(2.0, $this->marker($exploration->markerName()));
    }

    /**
     * PluginLead's check of the load test (2026-11-26): days whose record sits in Matomo's full archive (archived after the
     * exploration was saved) next to a day whose record is only in a newer archive of this plugin
     */
    public function testMixedDaysAddUp(): void
    {
        $this->visits([[self::FIREFOX, '2026-03-02 10:00:00'], [self::FIREFOX, '2026-03-02 11:00:00'],
            [self::CHROME, '2026-03-03 10:00:00'], [self::CHROME, '2026-03-04 10:00:00']]);
        // Monday archived for all plugins before the exploration exists, Tuesday and Wednesday after
        $this->archive('day', self::DAYS[0]);
        $exploration = $this->exploration($this->save('browser', 'nb_visits'));
        $this->archive('day', self::DAYS[1]);
        $this->archive('day', self::DAYS[2]);
        // Monday's record comes with the history, a whole archive of this plugin
        $this->archive('day', self::DAYS[0], '');

        $this->archive('week', self::MONDAY);

        $this->assertSame(4, (int) array_sum($this->read($exploration->recordName())->getColumn('nb_visits')));
        $this->assertSame(3.0, $this->marker($exploration->markerName()));
    }

    /** an archive as core:archive asks for it: all plugins, or this plugin's record only (pluginOnly) */
    private function archive(string $period, string $date, ?string $record = null): void
    {
        Cache::getTransientCache()->flushAll();
        $_GET['trigger'] = 'archivephp';
        if ($record === null) {
            unset($_GET['pluginOnly']);
            CoreAdminHomeApi::getInstance()->archiveReports(1, $period, $date);
        } else {
            $_GET['pluginOnly'] = 1;
            CoreAdminHomeApi::getInstance()->archiveReports(1, $period, $date, false, 'FeinwerkDataExploration', $record === '' ? false : $record);
        }
        unset($_GET['trigger'], $_GET['pluginOnly']);
    }

    /** the week's record as stored, without archiving on read */
    private function read(string $name): DataTable
    {
        Cache::getTransientCache()->flushAll();
        Rules::setBrowserTriggerArchiving(false);
        $table = Archive::build(1, 'week', self::MONDAY)->getDataTable($name);
        $this->assertInstanceOf(DataTable::class, $table);
        return $table;
    }

    private function marker(string $name): float
    {
        Cache::getTransientCache()->flushAll();
        Rules::setBrowserTriggerArchiving(false);
        $value = Archive::build(1, 'week', self::MONDAY)->getNumeric($name);
        // one name still comes back as an array by name
        return (float) (is_array($value) ? ($value[$name] ?? 0) : $value);
    }

    private function deleteVisitsOf(string $day): void
    {
        $from = $day . ' 00:00:00';
        $to = Date::factory($day)->addDay(1)->toString() . ' 00:00:00';
        Db::query('DELETE FROM ' . Common::prefixTable('log_link_visit_action') . ' WHERE idsite = 1 AND server_time >= ? AND server_time < ?', [$from, $to]);
        Db::query('DELETE FROM ' . Common::prefixTable('log_visit') . ' WHERE idsite = 1 AND visit_last_action_time >= ? AND visit_last_action_time < ?', [$from, $to]);
        StaticContainer::get(ArchiveInvalidator::class)->markArchivesAsInvalidated([1], [Date::factory($day)], 'day');
    }

    private function visits(array $visits): void
    {
        foreach ($visits as $n => [$userAgent, $time]) {
            $t = Fixture::getTracker(1, $time, true, true);
            $t->setNewVisitorId();
            $t->setIp('198.51.100.' . ($n + 1));
            $t->setUserAgent($userAgent);
            $t->setForceVisitDateTime($time);
            $t->setUrl('https://example.com/page');
            Fixture::checkResponse($t->doTrackPageView('Page'));
        }
    }

    private function save(string $dimensions, string $metrics): int
    {
        return (new SavedExplorationStore())->add(1, 'superUserLogin', 'Exploration ' . $dimensions,
            ['fwdeDims' => $dimensions, 'fwdeMetrics' => $metrics], '', true);
    }

    private function exploration(int $id): ArchivedExploration
    {
        $exploration = (new ArchivedExplorations(new ArchiveSettings()))->find(1, $id);
        $this->assertNotNull($exploration);
        return $exploration;
    }
}
