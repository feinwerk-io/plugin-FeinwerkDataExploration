<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration\Archive;

use Piwik\Cache;
use Piwik\Config;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationStore;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Pivots in the archive (design 2026-11-25, Peter: "2a"): every pivot value per row is stored, and a period shows its
 * own top values under every row, with an exact "Others" where every metric adds up.
 *
 * @group FeinwerkDataExploration
 */
class PivotArchiveTest extends IntegrationTestCase
{
    private const FIREFOX = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0';

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
        // day 1: A three visits, B one; day 2: B three visits, C one. Month: B 4, A 3, C 1
        $visits = [['2026-03-10', 'a', 3], ['2026-03-10', 'b', 1], ['2026-03-11', 'b', 3], ['2026-03-11', 'c', 1]];
        $n = 0;
        foreach ($visits as [$day, $page, $count]) {
            for ($i = 0; $i < $count; $i++) {
                $t = Fixture::getTracker(1, $day . ' 1' . $i . ':00:00', true, true);
                $t->setNewVisitorId();
                $t->setIp('198.51.100.' . (++$n));
                $t->setUserAgent(self::FIREFOX);
                $t->setUrl('https://example.com/' . $page);
                Fixture::checkResponse($t->doTrackPageView('Page ' . strtoupper($page)));
            }
        }
    }

    public function tearDown(): void
    {
        unset($_GET['idSite']);
        parent::tearDown();
    }

    public function testMonthTopValuesDifferFromDays(): void
    {
        $id = (new SavedExplorationStore())->add(1, 'superUserLogin', 'Pages by browser',
            ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits', 'fwdePivot' => 'pageTitle', 'fwdePivotLimit' => '3'], '', true);
        Cache::getTransientCache()->flushAll();
        $top = API::getInstance()->getSavedReport(1, 'month', '2026-03-15', $id);
        $this->assertTrue((bool) $top->getMetadata('fwde_archived'));
        $firefox = $top->getRowFromLabel('Firefox');
        $this->assertEquals(8, $firefox->getColumn('nb_visits'));

        $pages = API::getInstance()->getSavedReport(1, 'month', '2026-03-15', $id, '', (int) $firefox->getIdSubDataTable());
        $cells = [];
        foreach ($pages->getRows() as $row) {
            $cells[$row->getColumn('label')] = $row->getColumn('nb_visits');
        }
        // the month's three values (no "Others": nothing is cut)
        $this->assertSame(['Page B' => 4, 'Page A' => 3, 'Page C' => 1], $cells);
    }

    public function testOthersWhenCut(): void
    {
        // pageviews add up across pages (visits do not: one visit can see several pages, so they get no "Others")
        $id = (new SavedExplorationStore())->add(1, 'superUserLogin', 'Pages by browser',
            ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_pageviews', 'fwdePivot' => 'pageTitle', 'fwdePivotLimit' => '3'], '', true);
        // a fourth page in the month: three shown, the rest as "Others"
        $t = Fixture::getTracker(1, '2026-03-12 10:00:00', true, true);
        $t->setNewVisitorId();
        $t->setIp('198.51.100.99');
        $t->setUserAgent(self::FIREFOX);
        $t->setUrl('https://example.com/d');
        Fixture::checkResponse($t->doTrackPageView('Page D'));
        Cache::getTransientCache()->flushAll();

        $top = API::getInstance()->getSavedReport(1, 'month', '2026-03-15', $id);
        $pages = API::getInstance()->getSavedReport(1, 'month', '2026-03-15', $id, '', (int) $top->getRowFromLabel('Firefox')->getIdSubDataTable());
        $cells = [];
        foreach ($pages->getRows() as $row) {
            $cells[$row->getColumn('label')] = $row->getColumn('nb_pageviews');
        }
        $this->assertSame(['Page B' => 4, 'Page A' => 3, 'Page C' => 1, Piwik::translate('General_Others') => 1], $cells);
    }
}
