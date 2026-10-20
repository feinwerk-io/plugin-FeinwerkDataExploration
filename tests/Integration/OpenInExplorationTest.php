<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\ViewDataTable\Factory;

/**
 * "Open in exploration" on real report views of Matomo (the ViewDataTable.configure.end event).
 *
 * @group FeinwerkDataExploration
 */
class OpenInExplorationTest extends IntegrationTestCase
{
    public function provideContainerConfig()
    {
        return ['Piwik\\Access' => new FakeAccess()];
    }

    public function setUp(): void
    {
        parent::setUp();
        FakeAccess::$superUser = true;
        Fixture::createWebsite('2026-01-01 00:00:00', 0, 'Shop', 'https://example.com', 1, null, null, 'UTC');
        $_GET = ['module' => 'CoreHome', 'action' => 'index', 'idSite' => '1', 'period' => 'day', 'date' => '2026-03-27'];
    }

    public function tearDown(): void
    {
        $_GET = [];
        parent::tearDown();
    }

    public function testBrowsersReportHasTheAction(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'viewer');

        $this->assertContains('fwde-explore fwde-explore--browser', $this->actionIds('DevicesDetection.getBrowsers'));
    }

    public function testNotForAnonymousUsers(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'anonymous');

        $this->assertSame([], $this->actionIds('DevicesDetection.getBrowsers'));
    }

    public function testNotOnSubtables(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'viewer');
        $_GET['idSubtable'] = '2';

        $this->assertSame([], $this->actionIds('Actions.getPageUrls'));
    }

    public function testNotOnUnmappedReports(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'viewer');

        $this->assertSame([], $this->actionIds('DevicesDetection.getBrowserEngines'));
        // mapped since 0.3.0 / 1.0.0-beta.4
        $this->assertSame(['fwde-explore fwde-explore--referrerName fwde-scope--referrerType-website'], $this->actionIds('Referrers.getWebsites'));
    }

    private function actionIds(string $apiMethod): array
    {
        $view = Factory::build(null, $apiMethod, $apiMethod, true);
        $ids = array_column($view->config->datatable_actions, 'id');
        return array_values(array_filter($ids, fn ($id) => strpos($id, 'fwde-explore') === 0));
    }
}
