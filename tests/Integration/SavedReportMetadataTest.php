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
use Piwik\Container\StaticContainer;
use Piwik\Context;
use Piwik\Plugins\API\API as CoreApi;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Plugins\ScheduledReports\API as ScheduledReportsApi;
use Piwik\Plugins\UsersManager\API as UsersManagerApi;
use Piwik\Scheduler\Scheduler;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache as TrackerCache;

/**
 * Saved explorations as Matomo reports (2026-11-24): report lists per user and in Matomo's scheduler run, processed
 * reports, scheduled e-mails filtered by their owner, menu entries. Website 1 (no e-commerce) has three visits.
 *
 * @group FeinwerkDataExploration
 */
class SavedReportMetadataTest extends IntegrationTestCase
{
    private const DAY = '2026-03-27';

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
        Fixture::createWebsite('2026-01-01 00:00:00', 0, 'Shop', 'https://example.com', 1, null, null, 'UTC');
        GoalsApi::getInstance()->addGoal(1, 'Newsletter', 'manually', '', 'contains');
        TrackerCache::deleteCacheWebsiteAttributes(1);
        TrackerCache::clearCacheGeneral();
        foreach (['anna', 'bert'] as $login) {
            UsersManagerApi::getInstance()->addUser($login, 'secret-password-1', $login . '@example.com');
            UsersManagerApi::getInstance()->setUserAccess($login, 'write', [1]);
        }
        $visits = [
            ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', true],
            ['Mozilla/5.0 (X11; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0', false],
            ['Mozilla/5.0 (X11; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0', true],
        ];
        foreach ($visits as $n => [$userAgent, $converts]) {
            $t = Fixture::getTracker(1, self::DAY . ' 1' . $n . ':00:00', true, true);
            $t->setVisitorId(sprintf('c%015x', $n + 1));
            $t->setIp('198.51.100.' . ($n + 1));
            $t->setUserAgent($userAgent);
            $t->setUrl('https://example.com/home');
            Fixture::checkResponse($t->doTrackPageView('Home'));
            $t->setForceVisitDateTime(self::DAY . ' 1' . $n . ':01:00');
            $t->setUrl('https://example.com/shop');
            Fixture::checkResponse($t->doTrackPageView('Shop'));
            if ($converts) {
                Fixture::checkResponse($t->doTrackGoal(1, 5));
            }
        }
    }

    /**
     * Gallery shots of 2026-11-25: a saved exploration with a rate next to revenue failed as a widget ("Trying to round
     * unsupported operands ... 11.85% (string)"): Matomo's table relates every metric of getMetrics() to the website's
     * total from API.get, where Goals answers conversion_rate as a formatted string. Only metrics that add up across rows
     * have such a share; the metadata keeps every metric in the saved order.
     */
    public function testATableWithARateAndRevenueRendersAsAWidget(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'anna', [1]);
        $id = (int) API::getInstance()->addSavedExploration(1, 'Rate and revenue',
            '{"fwdeDims":"browser","fwdeMetrics":"nb_visits,conversion_rate,revenue"}', '', 1)['id'];
        FakeAccess::$superUser = true;
        $_GET = ['module' => 'FeinwerkDataExploration', 'action' => 'getSavedReport', 'idSite' => 1, 'period' => 'day',
            'date' => self::DAY, 'idSaved' => $id, 'widget' => 1] + $_GET;
        try {
            $html = \Piwik\ViewDataTable\Factory::build('table', 'FeinwerkDataExploration.getSavedReport', 'FeinwerkDataExploration.getSavedReport', true)
                ->render();
        } finally {
            unset($_GET['module'], $_GET['action'], $_GET['period'], $_GET['date'], $_GET['idSaved'], $_GET['widget']);
        }
        $this->assertStringContainsString('Firefox', $html);
        $this->assertStringNotContainsString('unsupported operands', $html);

        $metadata = array_values(array_filter(CoreApi::getInstance()->getReportMetadata(1),
            fn ($report) => ($report['parameters']['idSaved'] ?? 0) === $id));
        $this->assertSame(['nb_visits', 'conversion_rate', 'revenue'], array_keys($metadata[0]['metrics']));
    }

    public function tearDown(): void
    {
        unset($_GET['idSite']);
        $this->schedulerRun(false);
        parent::tearDown();
    }

    public function testReportListsPerUser(): void
    {
        $mine = $this->save('anna', 'Anna alone', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits']);
        $team = $this->save('anna', 'Team view', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits'], 1);

        $this->assertSame(['Anna alone', 'Team view'], $this->reportNames('anna'));
        $this->assertSame(['Team view'], $this->reportNames('bert'));
        // super users see no other user's private exploration
        $this->assertSame(['Team view'], $this->reportNames('root', true));
        // Matomo's scheduler run builds scheduled e-mails as super user: every one of the website
        $this->schedulerRun(true);
        $this->assertSame(['Anna alone', 'Team view'], $this->reportNames('root', true));

        $metadata = $this->metadataOf('anna', $mine);
        // not in the menu: no menu entry (no sub category), listed under Explorations
        $this->assertSame(['Explorations', null, ['idSaved' => $mine], 'Browser'],
            [$metadata['category'], $metadata['subcategory'] ?? null, $metadata['parameters'], $metadata['dimension']]);
        $this->assertSame(['nb_visits' => 'Visits'], $metadata['metrics']);
        $this->assertSame('number', $metadata['metricTypes']['nb_visits']);
        $this->assertNotNull($team);
    }

    /**
     * Email Reports Pro names the owner around its catalogue and data calls with Matomo's
     * Context::executeWithQueryParameters (5.0 to 6): a super user then sees the owner's private explorations and
     * shared ones, never another user's private one.
     */
    public function testEmailReportsProNamesTheOwner(): void
    {
        $private = $this->save('anna', 'Anna alone', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits']);
        $this->save('bert', 'Bert shared', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits'], 1);
        $asOwner = fn (string $owner, callable $call) => Context::executeWithQueryParameters(['fwReportOwner' => $owner], $call);

        $this->assertSame(['Anna alone', 'Bert shared'], $asOwner('anna', fn () => $this->reportNames('root', true)));
        $this->assertSame(['Bert shared'], $asOwner('bert', fn () => $this->reportNames('root', true)));

        $report = $asOwner('anna', fn () => Request::processRequest('API.getProcessedReport', ['idSite' => 1, 'period' => 'day',
            'date' => self::DAY, 'apiModule' => 'FeinwerkDataExploration', 'apiAction' => 'getSavedReport',
            'apiParameters' => ['idSaved' => $private], 'fwReportOwner' => 'anna']));
        $this->assertSame(2, $report['reportData']->getRowsCount());
    }

    /** final review: the saved sort survives Matomo's post-processing (its generic filters sort by visits otherwise) */
    public function testSavedSortSurvivesMatomosPostProcessing(): void
    {
        $id = $this->save('anna', 'By name', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits', 'fwdeSort' => 'label',
            'fwdeOrder' => 'asc']);
        FakeAccess::clearAccess(false, [], [1], 'anna');
        Cache::getTransientCache()->flushAll();

        $report = Request::processRequest('API.getProcessedReport', ['idSite' => 1, 'period' => 'day', 'date' => self::DAY,
            'apiModule' => 'FeinwerkDataExploration', 'apiAction' => 'getSavedReport', 'apiParameters' => ['idSaved' => $id]]);
        $this->assertSame(['Chrome', 'Firefox'], array_map(fn ($row) => $row->getColumn('label'), $report['reportData']->getRows()));
    }

    /** final review: the save panel names Matomo's sections as Matomo's menu does */
    public function testMenuSectionsCarryMatomosMenuNames(): void
    {
        $this->assertSame(['Visitors', 'Behaviour', 'Acquisition', 'Goals', 'Explorations'],
            array_column(\Piwik\Plugins\FeinwerkDataExploration\Widgets\GetExploration::menuSections(1), 'name'));
    }

    /** final review: a request that names one exploration (a widget, a sub table) still lists every visible one */
    public function testARequestNamingOneExplorationListsAll(): void
    {
        $first = $this->save('anna', 'First', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits']);
        $this->save('anna', 'Second', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits']);
        $_GET['idSite'] = 1;
        $_GET['idSaved'] = $first;
        try {
            $this->assertSame(['First', 'Second'], $this->reportNames('anna'));
        } finally {
            unset($_GET['idSaved']);
        }
    }

    /** final review: the saved explorations are read once per request, however often Matomo asks */
    public function testSavedExplorationsAreReadOncePerRequest(): void
    {
        $this->save('anna', 'First', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits']);
        Cache::getTransientCache()->flushAll();
        $reads = 0;
        $visibility = new \Piwik\Plugins\FeinwerkDataExploration\Saved\ReportVisibility(
            function (int $idSite) use (&$reads): array {
                $reads++;
                return (new \Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationStore())->listAll($idSite);
            },
            fn () => ['login' => 'anna', 'superUser' => false, 'runningTask' => false, 'owner' => ''],
            null,
            fn (int $idSite) => true
        );
        FakeAccess::clearAccess(false, [], [1], 'anna');
        (new \Piwik\Plugins\FeinwerkDataExploration\SavedReport\SavedReports($visibility))->reportsFor(1);
        (new \Piwik\Plugins\FeinwerkDataExploration\SavedReport\SavedReports($visibility))->subcategoriesFor(1);
        (new \Piwik\Plugins\FeinwerkDataExploration\SavedReport\SavedReports($visibility))->reportsFor(1);
        $this->assertSame(1, $reads);
    }

    public function testMetadataOfTwoDimensionsAndGoalMetrics(): void
    {
        $id = $this->save('anna', 'Pages by browser', ['fwdeDims' => 'pageTitle,browser',
            'fwdeMetrics' => 'nb_visits,bounce_rate,goal_1_revenue,avg_time_on_site']);
        $metadata = $this->metadataOf('anna', $id);

        $this->assertSame('getSavedReport', $metadata['actionToLoadSubTables']);
        $this->assertSame(['Page Title', 'Browser'], array_values($metadata['dimensions']));
        $this->assertSame(['goal_1_revenue' => 'Newsletter: Revenue'], $metadata['metricsGoal']);
        $this->assertSame(['number', 'percent', 'money', 'duration_s'], [$metadata['metricTypes']['nb_visits'],
            $metadata['metricTypes']['bounce_rate'], $metadata['metricTypes']['goal_1_revenue'], $metadata['metricTypes']['avg_time_on_site']]);
    }

    /** rows and pivot (2026-11-24): one dimension per level, the pivot last */
    public function testMetadataOfThreeDimensionsAndAPivot(): void
    {
        $id = $this->save('anna', 'Pages by browser, device and system', ['fwdeDims' => 'pageTitle,browser,deviceType',
            'fwdeMetrics' => 'nb_visits', 'fwdePivot' => 'operatingSystem']);
        $metadata = $this->metadataOf('anna', $id);
        $catalog = new DimensionCatalog();

        $this->assertSame('getSavedReport', $metadata['actionToLoadSubTables']);
        $this->assertSame(array_map(fn (string $id) => $catalog->get(1, $id)->name, ['pageTitle', 'browser', 'deviceType', 'operatingSystem']),
            array_values($metadata['dimensions']));
    }

    /** KPI cards (2026-11-24): a report without a dimension, its metric only, a card widget, never a menu entry */
    public function testCardIsAReportWithoutDimension(): void
    {
        $id = $this->save('anna', 'Visits card', ['fwdeKind' => 'kpi', 'fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits,bounce_rate',
            'fwdeKpiMetric' => 'bounce_rate', 'fwdeMenu' => 'Goals_Goals']);
        $metadata = $this->metadataOf('anna', $id);

        $this->assertArrayNotHasKey('dimension', $metadata);
        $this->assertSame(['bounce_rate'], array_keys($metadata['metrics']));
        $this->assertSame('Explorations', $metadata['category'], 'listed under Explorations, never in another section');
        $this->assertNull($metadata['subcategory'] ?? null);
        $this->assertArrayNotHasKey('actionToLoadSubTables', array_filter($metadata));
    }

    /** a card's graph in Matomo's e-mail asks runs of days only: none for weeks and months */
    public function testCardGraphOnlyForDays(): void
    {
        $id = $this->save('anna', 'Visits card', ['fwdeKind' => 'kpi', 'fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits']);
        $graph = function (string $period) use ($id) {
            FakeAccess::clearAccess(false, [], [1], 'anna');
            Cache::getTransientCache()->flushAll();
            foreach (CoreApi::getInstance()->getReportMetadata(1, $period, self::DAY) as $report) {
                if (($report['parameters']['idSaved'] ?? null) === $id) {
                    return $report['imageGraphUrl'] ?? null;
                }
            }
            $this->fail('no report for the card');
        };

        $this->assertNotEmpty($graph('day'));
        $this->assertNull($graph('week'));
        $this->assertNull($graph('month'));
    }

    public function testProcessedReportFlattensEveryLevel(): void
    {
        $three = $this->save('anna', 'Three', ['fwdeDims' => 'pageTitle,browser,deviceType', 'fwdeMetrics' => 'nb_visits']);
        $pivoted = $this->save('anna', 'Pivoted', ['fwdeDims' => 'pageTitle', 'fwdeMetrics' => 'nb_visits', 'fwdePivot' => 'browser']);
        FakeAccess::clearAccess(false, [], [1], 'anna');

        $flat = function (int $idSaved): array {
            Cache::getTransientCache()->flushAll();
            $report = Request::processRequest('API.getProcessedReport', ['idSite' => 1, 'period' => 'day', 'date' => self::DAY,
                'apiModule' => 'FeinwerkDataExploration', 'apiAction' => 'getSavedReport', 'apiParameters' => ['idSaved' => $idSaved],
                'flat' => 1, 'format_metrics' => 0, 'idSaved' => $idSaved]);
            $rows = [];
            foreach ($report['reportData']->getRows() as $row) {
                $rows[$row->getColumn('label')] = $row->getColumn('nb_visits');
            }
            ksort($rows);
            return $rows;
        };

        $this->assertSame(['Home - Chrome - Desktop' => 1, 'Home - Firefox - Desktop' => 2, 'Shop - Chrome - Desktop' => 1,
            'Shop - Firefox - Desktop' => 2], $flat($three));
        $this->assertSame(['Home - Chrome' => 1, 'Home - Firefox' => 2, 'Shop - Chrome' => 1, 'Shop - Firefox' => 2], $flat($pivoted));
    }

    /**
     * Peter, 2026-11-25: "dimension1 und dimension2 namen sind immer noch gleich". In Matomo's flat view each level of a
     * saved report is a dimension column (FeinwerkDataExploration_<dimension>), which the table must title with that
     * dimension's name.
     */
    public function testFlatViewNamesEachDimensionColumn(): void
    {
        $id = $this->save('anna', 'Pages by browser', ['fwdeDims' => 'pageTitle,browser', 'fwdeMetrics' => 'nb_visits']);
        FakeAccess::clearAccess(false, [], [1], 'anna');
        Cache::getTransientCache()->flushAll();
        $catalog = new DimensionCatalog();
        $names = [$catalog->get(1, 'pageTitle')->name, $catalog->get(1, 'browser')->name];
        $this->assertNotSame($names[0], $names[1]);

        $params = ['idSite' => 1, 'period' => 'day', 'date' => self::DAY, 'idSaved' => $id, 'flat' => 1, 'show_dimensions' => 1];
        $table = Request::processRequest('FeinwerkDataExploration.getSavedReport', $params);
        $this->assertSame(['FeinwerkDataExploration_pageTitle', 'FeinwerkDataExploration_browser'], $table->getMetadata('dimensions'));

        // Matomo's table titles a flat view's dimension columns only for its own registered dimensions (HtmlTable), so
        // the report names its levels itself: the flat label "Page Title - Browser", or one column per dimension
        $_GET = array_merge($_GET, $params, ['module' => 'FeinwerkDataExploration', 'action' => 'getSavedReport']);
        try {
            $view = \Piwik\ViewDataTable\Factory::build('table', 'FeinwerkDataExploration.getSavedReport', 'FeinwerkDataExploration.getSavedReport', true);
        } finally {
            foreach (array_merge(array_keys($params), ['module', 'action']) as $key) {
                unset($_GET[$key]);
            }
        }
        $translations = $view->config->translations;
        $this->assertSame($names, [$translations['FeinwerkDataExploration_pageTitle'] ?? null,
            $translations['FeinwerkDataExploration_browser'] ?? null]);
    }

    public function testProcessedReportFormatsAndFlattens(): void
    {
        $id = $this->save('anna', 'Pages by browser', ['fwdeDims' => 'pageTitle,browser',
            'fwdeMetrics' => 'nb_visits,goal_1_nb_conversions,goal_1_revenue', 'fwdeSort' => 'label', 'fwdeOrder' => 'asc']);
        FakeAccess::clearAccess(false, [], [1], 'anna');

        $flat = Request::processRequest('API.getProcessedReport', ['idSite' => 1, 'period' => 'day', 'date' => self::DAY,
            'apiModule' => 'FeinwerkDataExploration', 'apiAction' => 'getSavedReport', 'apiParameters' => ['idSaved' => $id],
            'flat' => 1, 'format_metrics' => 0, 'idSaved' => $id]);
        $rows = [];
        foreach ($flat['reportData']->getRows() as $row) {
            $rows[$row->getColumn('label')] = [$row->getColumn('nb_visits'), $row->getColumn('goal_1_nb_conversions')];
        }
        ksort($rows);
        $this->assertSame(['Home - Chrome' => [1, 1], 'Home - Firefox' => [2, 1], 'Shop - Chrome' => [1, 1],
            'Shop - Firefox' => [2, 1]], $rows);

        // Matomo's e-mail formats by the column names (Matomo's naming: _rate, revenue, time)
        $rate = $this->save('anna', 'Rates', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'goal_1_conversion_rate,goal_1_revenue',
            'fwdeSort' => 'label', 'fwdeOrder' => 'asc']);
        FakeAccess::clearAccess(false, [], [1], 'anna');
        // a new exploration in the same request: Matomo's report list of this request is cached
        Cache::getTransientCache()->flushAll();
        $mail = Request::processRequest('API.getProcessedReport', ['idSite' => 1, 'period' => 'day', 'date' => self::DAY,
            'apiModule' => 'FeinwerkDataExploration', 'apiAction' => 'getSavedReport', 'apiParameters' => ['idSaved' => $rate]]);
        $firefox = $mail['reportData']->getRowFromLabel('Firefox');
        $this->assertSame(['50%', '$5'], [$firefox->getColumn('goal_1_conversion_rate'), $firefox->getColumn('goal_1_revenue')]);

        // format_metrics=1 formats by the report's types; an HTTP request names the exploration (idSaved)
        $_GET['idSaved'] = $rate;
        $_GET['idSite'] = 1;
        try {
            $formatted = Request::processRequest('API.getProcessedReport', ['idSite' => 1, 'period' => 'day', 'date' => self::DAY,
                'apiModule' => 'FeinwerkDataExploration', 'apiAction' => 'getSavedReport', 'apiParameters' => ['idSaved' => $rate],
                'format_metrics' => 1]);
        } finally {
            unset($_GET['idSaved']);
        }
        $firefox = $formatted['reportData']->getRowFromLabel('Firefox');
        $this->assertStringContainsString('$', (string) $firefox->getColumn('goal_1_revenue'));
        $this->assertStringContainsString('%', (string) $firefox->getColumn('goal_1_conversion_rate'));
    }

    /** review focus 1: an exploration its owner can no longer see leaves the scheduled e-mail */
    public function testScheduledReportFollowsTheOwnersAccess(): void
    {
        $id = $this->save('bert', 'Bert for all', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits'], 1);
        FakeAccess::clearAccess(false, [], [1], 'anna');
        $idReport = ScheduledReportsApi::getInstance()->addReport(1, 'Monday mail', 'week', 0, 'email', 'html',
            ['FeinwerkDataExploration_getSavedReport_idSaved--' . $id], ['displayFormat' => 1, 'emailMe' => false, 'evolutionGraph' => false]);

        $this->assertStringContainsString('Bert for all', $this->mail($idReport));

        FakeAccess::clearAccess(false, [], [1], 'bert', [1]);
        API::getInstance()->updateSavedExploration(1, $id, 'Bert for all', '{"fwdeDims":"browser","fwdeMetrics":"nb_visits"}', '', 0);
        $this->assertStringNotContainsString('Bert for all', $this->mail($idReport));
    }

    public function testMenuEntriesPerSection(): void
    {
        $goals = $this->save('anna', 'In goals', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits', 'fwdeMenu' => 'Goals_Goals']);
        $shop = $this->save('anna', 'In e-commerce', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits', 'fwdeMenu' => 'Goals_Ecommerce']);
        $none = $this->save('anna', 'Not placed', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits']);

        // the menu comes from the request's website, as on Matomo's pages
        $_GET['idSite'] = 1;
        FakeAccess::clearAccess(false, [], [1], 'anna');
        Cache::getTransientCache()->flushAll();
        $pages = [];
        foreach (CoreApi::getInstance()->getReportPagesMetadata(1) as $page) {
            $pages[$page['subcategory']['id']] = [$page['category']['id'], $page['subcategory']['name']];
        }
        $this->assertSame(['Goals_Goals', 'In goals'], $pages['fwdeSaved' . $goals]);
        // a website without e-commerce: the entry falls back to Explorations
        $this->assertSame(['FeinwerkDataExploration_Explorations', 'In e-commerce'], $pages['fwdeSaved' . $shop]);
        $this->assertArrayNotHasKey('fwdeSaved' . $none, $pages);

        FakeAccess::clearAccess(false, [], [1], 'bert');
        Cache::getTransientCache()->flushAll();
        $this->assertSame([], array_filter(array_keys(array_column(array_column(CoreApi::getInstance()->getReportPagesMetadata(1), 'subcategory'), 'name', 'id')),
            fn ($id) => str_starts_with((string) $id, 'fwdeSaved')), 'another user\'s private entries never show');
    }

    private function mail(int $idReport): string
    {
        FakeAccess::clearAccess(true, [], [1], 'root');
        Cache::getTransientCache()->flushAll();
        $this->schedulerRun(true);
        try {
            return (string) ScheduledReportsApi::getInstance()->generateReport($idReport, self::DAY, 'en', ScheduledReportsApi::OUTPUT_RETURN);
        } finally {
            $this->schedulerRun(false);
        }
    }

    private function save(string $login, string $name, array $state, int $shared = 0): int
    {
        FakeAccess::clearAccess(false, [], [1], $login, [1]);
        return (int) API::getInstance()->addSavedExploration(1, $name, json_encode($state), '', $shared)['id'];
    }

    private function reportNames(string $login, bool $superUser = false): array
    {
        FakeAccess::clearAccess($superUser, [], [1], $login);
        Cache::getTransientCache()->flushAll();
        $names = [];
        foreach (CoreApi::getInstance()->getReportMetadata(1) as $report) {
            if ($report['module'] === 'FeinwerkDataExploration' && $report['action'] === 'getSavedReport') {
                $names[] = $report['name'];
            }
        }
        sort($names);
        return $names;
    }

    private function metadataOf(string $login, int $idSaved): array
    {
        FakeAccess::clearAccess(false, [], [1], $login);
        Cache::getTransientCache()->flushAll();
        foreach (CoreApi::getInstance()->getReportMetadata(1) as $report) {
            if (($report['parameters']['idSaved'] ?? null) === $idSaved) {
                return $report;
            }
        }
        $this->fail('no report for exploration ' . $idSaved);
    }

    private function schedulerRun(bool $running): void
    {
        $property = new \ReflectionProperty(Scheduler::class, 'isRunningTask');
        $property->setAccessible(true);
        $property->setValue(StaticContainer::get(Scheduler::class), $running);
    }
}
