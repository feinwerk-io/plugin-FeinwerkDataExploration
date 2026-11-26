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
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationStore;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedReportUnavailable;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Which way a saved report answers (design 2026-11-25): from the archive when the period is complete there, live while
 * an archive of 31 days or less is incomplete, from the archive with a coverage note when a longer one is, and refused
 * for long periods when the exploration is not archived.
 *
 * @group FeinwerkDataExploration
 */
class ReportPathTest extends IntegrationTestCase
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
        foreach ([[self::FIREFOX, '2026-02-10 10:00:00'], [self::FIREFOX, '2026-03-27 10:00:00'], [self::CHROME, '2026-03-28 10:00:00']] as $n => [$ua, $time]) {
            $t = Fixture::getTracker(1, $time, true, true);
            $t->setNewVisitorId();
            $t->setIp('198.51.100.' . ($n + 1));
            $t->setUserAgent($ua);
            $t->setUrl('https://example.com/');
            Fixture::checkResponse($t->doTrackPageView('Home'));
        }
    }

    public function tearDown(): void
    {
        unset($_GET['idSite']);
        Config::getInstance()->FeinwerkDataExploration = [];
        parent::tearDown();
    }

    public function testCompleteFromArchive(): void
    {
        $id = $this->save();
        $table = $this->report($id, 'month', '2026-03-15');
        $this->assertTrue((bool) $table->getMetadata('fwde_archived'));
        $this->assertEmpty($table->getMetadata('fwde_archive_coverage'));
        $this->assertEquals(1, $table->getRowFromLabel('Firefox')->getColumn('nb_visits'));
        $this->assertSame('browserCode==FF', $table->getRowFromLabel('Firefox')->getMetadata('segment'));
    }

    /** final review 2026-11-26: a dimension and a metric condition in the usual single "all" group */
    public function testFilteredTwoDimensions(): void
    {
        $id = (new SavedExplorationStore())->add(1, 'superUserLogin', 'Desktop browsers', ['fwdeDims' => 'browser,deviceType',
            'fwdeMetrics' => 'nb_visits', 'fwdeFilters' => 'secondary_label~equals~Desktop;nb_visits~gt~0'], '', true);
        $table = $this->report($id, 'month', '2026-03-15');
        $this->assertTrue((bool) $table->getMetadata('fwde_archived'));
        $this->assertEqualsCanonicalizing(['Firefox', 'Chrome'], $table->getColumn('label'));
        $devices = API::getInstance()->getSavedReport(1, 'month', '2026-03-15', $id, '', (int) $table->getRowFromLabel('Firefox')->getIdSubDataTable());
        $this->assertSame(['Desktop'], $devices->getColumn('label'));
    }

    /**
     * release review 2026-11-26: a number condition applies to the combinations, as live: an upper row stays when one of
     * its combinations passes, with its own totals (Desktop has 2 visits in March, each browser under it 1)
     */
    public function testNumberConditionOnTheCombinations(): void
    {
        $id = (new SavedExplorationStore())->add(1, 'superUserLogin', 'Small combinations', ['fwdeDims' => 'deviceType,browser',
            'fwdeMetrics' => 'nb_visits', 'fwdeFilters' => 'nb_visits~lt~2'], '', true);
        $table = $this->report($id, 'month', '2026-03-15');
        $this->assertTrue((bool) $table->getMetadata('fwde_archived'));
        $this->assertSame(['Desktop'], $table->getColumn('label'));
        $this->assertEquals(2, $table->getRowFromLabel('Desktop')->getColumn('nb_visits'), 'its totals stay');
        $browsers = API::getInstance()->getSavedReport(1, 'month', '2026-03-15', $id, '', (int) $table->getRowFromLabel('Desktop')->getIdSubDataTable());
        $this->assertEqualsCanonicalizing(['Firefox', 'Chrome'], $browsers->getColumn('label'));
    }

    /**
     * final review 2026-11-26: unique visitors cannot be added up across days; a week or month answers them live as
     * before, a longer period from the archive without that column
     */
    public function testDistinctMetricsShortLiveLongWithout(): void
    {
        $id = (new SavedExplorationStore())->add(1, 'superUserLogin', 'Uniques', ['fwdeDims' => 'browser',
            'fwdeMetrics' => 'nb_visits,nb_uniq_visitors'], '', true);
        $month = $this->report($id, 'month', '2026-03-15');
        $this->assertEmpty($month->getMetadata('fwde_archived'));
        $this->assertEquals(1, $month->getRowFromLabel('Firefox')->getColumn('nb_uniq_visitors'));

        $year = $this->report($id, 'year', '2026-03-15');
        $this->assertTrue((bool) $year->getMetadata('fwde_archived'));
        $this->assertFalse($year->getRowFromLabel('Firefox')->getColumn('nb_uniq_visitors'));
        $this->assertNotContains('nb_uniq_visitors', $this->view($id, 'year', '2026-03-15')->config->columns_to_display);
        $this->assertContains('nb_uniq_visitors', $this->view($id, 'month', '2026-03-15')->config->columns_to_display);
    }

    public function testIncompleteShortIsLive(): void
    {
        $this->archiveBefore('2026-03-27');
        $id = $this->save();
        $table = $this->report($id, 'week', '2026-03-27');
        $this->assertEmpty($table->getMetadata('fwde_archived'));
        $this->assertEquals(1, $table->getRowFromLabel('Firefox')->getColumn('nb_visits'), 'live answers what the archive lacks');
    }

    public function testIncompleteLongHasNote(): void
    {
        $this->archiveBefore('2026-03-27');
        $id = $this->save();
        $table = $this->report($id, 'year', '2026-03-27');
        $this->assertTrue((bool) $table->getMetadata('fwde_archived'));
        $this->assertSame([2, 3], $table->getMetadata('fwde_archive_coverage'));
    }

    public function testRangeCoverage(): void
    {
        $this->archiveBefore('2026-03-27');
        $id = $this->save();
        // 33 days: longer than an exploration, from the archive with the note
        $long = $this->report($id, 'range', '2026-02-24,2026-03-28');
        $this->assertSame([1, 2], $long->getMetadata('fwde_archive_coverage'));
        // 2 days: live
        $this->assertEmpty($this->report($id, 'range', '2026-03-27,2026-03-28')->getMetadata('fwde_archived'));
    }

    public function testNotArchivedLongRefused(): void
    {
        Config::getInstance()->FeinwerkDataExploration = ['archive_reports' => 0];
        $id = $this->save();
        try {
            $this->report($id, 'year', '2026-03-27');
            $this->fail('expected a refusal');
        } catch (SavedReportUnavailable $e) {
            $this->assertSame('FeinwerkDataExploration_ArchiveNotArchived', $e->getTranslationKey());
            // release review 2026-11-26 (AI and MCP rule): the message names the period and what to do
            $this->assertStringContainsString('Choose a shorter period', $e->getMessage());
        }
        // a month still answers live
        $this->assertEquals(1, $this->report($id, 'month', '2026-03-15')->getRowFromLabel('Firefox')->getColumn('nb_visits'));
    }

    /**
     * final review 2026-11-26: Matomo's row evolution asks several periods at once, which a saved report does not
     * answer (archived or not): it stays off
     */
    public function testRowEvolutionStaysOff(): void
    {
        $id = $this->save();
        $this->assertTrue($this->view($id)->config->disable_row_evolution);
        Config::getInstance()->FeinwerkDataExploration = ['archive_reports' => 0];
        Cache::getTransientCache()->flushAll();
        $this->assertTrue($this->view($id)->config->disable_row_evolution);
    }

    public function testCoverageNoteInTheView(): void
    {
        $this->archiveBefore('2026-03-27');
        $id = $this->save();
        $message = (string) $this->view($id, 'year', '2026-03-27')->config->show_footer_message;
        $this->assertStringContainsString('2', $message);
        $this->assertStringContainsString('3', $message);
        $this->assertSame('', (string) $this->view($id, 'month', '2026-02-15')->config->show_footer_message);
    }

    /** the plugin's day archive of $day exists before the exploration is saved: complete without its record */
    private function archiveBefore(string $day): void
    {
        Archive::build(1, 'day', $day)->getNumeric('FeinwerkDataExploration_nothing');
    }

    private function save(): int
    {
        return (new SavedExplorationStore())->add(1, 'superUserLogin', 'Browsers', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits'], '', true);
    }

    private function report(int $id, string $period, string $date): DataTable
    {
        Cache::getTransientCache()->flushAll();
        return API::getInstance()->getSavedReport(1, $period, $date, $id);
    }

    private function view(int $id, string $period = 'month', string $date = '2026-03-15'): \Piwik\Plugin\ViewDataTable
    {
        Cache::getTransientCache()->flushAll();
        $params = ['idSite' => 1, 'period' => $period, 'date' => $date, 'idSaved' => $id];
        $_GET = array_merge($_GET, $params);
        try {
            return \Piwik\ViewDataTable\Factory::build('table', 'FeinwerkDataExploration.getSavedReport', 'FeinwerkDataExploration.getSavedReport', true);
        } finally {
            foreach (['period', 'date', 'idSaved'] as $key) {
                unset($_GET[$key]);
            }
        }
    }
}
