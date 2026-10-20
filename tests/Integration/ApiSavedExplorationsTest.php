<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\API\Request;
use Piwik\NoAccessException;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationStore;
use Piwik\Plugins\SitesManager\API as SitesManagerApi;
use Piwik\Plugins\UsersManager\API as UsersManagerApi;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Saved explorations through the API: who sees, changes and deletes what (spec section 13).
 *
 * @group FeinwerkDataExploration
 */
class ApiSavedExplorationsTest extends IntegrationTestCase
{
    private const STATE = '{"fwdeDims":"browser","fwdeMetrics":"nb_visits,bounce_rate"}';

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
    }

    public function testOwnAndSharedAreListedWithTheirRights(): void
    {
        $this->as('anna', [1]);
        $private = $this->api()->addSavedExploration(1, '  Mine  ', self::STATE)['id'];
        $this->api()->addSavedExploration(1, 'Team view', self::STATE, 'browserCode==FF', 1);
        $this->as('bert', [1]);
        $this->api()->addSavedExploration(1, 'Bert alone', self::STATE);
        $bertShared = $this->api()->addSavedExploration(1, 'Bert for all', self::STATE, '', 1)['id'];

        $this->as('anna', [1]);
        $list = $this->api()->getSavedExplorations(1);

        $this->assertSame(['Bert for all', 'Mine', 'Team view'], array_column($list, 'name'));
        $byName = array_column($list, null, 'name');
        $this->assertSame($private, $byName['Mine']['id']);
        $this->assertFalse($byName['Mine']['shared']);
        $this->assertTrue($byName['Mine']['canEdit']);
        $this->assertSame(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits,bounce_rate'], $byName['Mine']['state']);
        $this->assertSame('browserCode==FF', $byName['Team view']['segment']);
        $this->assertSame($bertShared, $byName['Bert for all']['id']);
        $this->assertSame('bert', $byName['Bert for all']['owner']);
        $this->assertFalse($byName['Bert for all']['canEdit']);
    }

    public function testOthersCannotChangeOrDeleteButSuperUsersCan(): void
    {
        $this->as('bert', [1]);
        $id = $this->api()->addSavedExploration(1, 'Bert for all', self::STATE, '', 1)['id'];

        $this->as('anna', [1]);
        $this->assertRefused('FeinwerkDataExploration_ErrorSavedNotYours', fn () => $this->api()->updateSavedExploration(1, $id, 'Taken', self::STATE));
        $this->assertRefused('FeinwerkDataExploration_ErrorSavedNotYours', fn () => $this->api()->deleteSavedExploration(1, $id));

        FakeAccess::$superUser = true;
        FakeAccess::$identity = 'admin';
        $this->api()->updateSavedExploration(1, $id, 'Renamed by admin', self::STATE, '', 1);
        $this->assertSame('Renamed by admin', (new SavedExplorationStore())->get($id)['name']);
        $this->api()->deleteSavedExploration(1, $id);
        $this->assertNull((new SavedExplorationStore())->get($id));
    }

    public function testAnotherSitesExplorationIsNotFound(): void
    {
        $this->as('anna', [1, 2]);
        $id = $this->api()->addSavedExploration(2, 'Site two', self::STATE, '', 1)['id'];

        $this->assertRefused('FeinwerkDataExploration_ErrorSavedNotFound', fn () => $this->api()->updateSavedExploration(1, $id, 'X', self::STATE));
        $this->assertRefused('FeinwerkDataExploration_ErrorSavedNotFound', fn () => $this->api()->deleteSavedExploration(1, $id));
        $this->assertSame([], $this->api()->getSavedExplorations(1));
    }

    public function testAnonymousAndUsersWithoutAccessAreRefused(): void
    {
        $this->as('anonymous', [1]);
        $this->expectException(NoAccessException::class);
        $this->api()->getSavedExplorations(1);
    }

    public function testNameStateSegmentAndLimitAreChecked(): void
    {
        $this->as('anna', [1]);
        $this->assertRefused('FeinwerkDataExploration_ErrorSavedName', fn () => $this->api()->addSavedExploration(1, '   ', self::STATE));
        $this->assertRefused('FeinwerkDataExploration_ErrorSavedName', fn () => $this->api()->addSavedExploration(1, str_repeat('n', 101), self::STATE));
        $this->assertRefused('FeinwerkDataExploration_ErrorSavedState', fn () => $this->api()->addSavedExploration(1, 'Bad', '{"fwdeDims":["browser"]}'));
        $this->assertRefused('FeinwerkDataExploration_ErrorSavedState', fn () => $this->api()->addSavedExploration(1, 'Bad', '{"other":"x"}'));
        $this->assertRefused('FeinwerkDataExploration_ErrorSavedState', fn () => $this->api()->addSavedExploration(1, 'Bad', 'not json'));

        try {
            $this->api()->addSavedExploration(1, 'Bad segment', self::STATE, 'nosuchsegment==1');
            $this->fail('an invalid segment was saved');
        } catch (\Exception $e) {
            $this->assertStringNotContainsString('FeinwerkDataExploration_ErrorSaved', $e->getMessage());
        }

        $store = new SavedExplorationStore();
        for ($i = 0; $i < 100; $i++) {
            $store->add(1, 'anna', 'N' . $i, [], '', false);
        }
        $this->assertRefused('FeinwerkDataExploration_ErrorSavedLimit', fn () => $this->api()->addSavedExploration(1, 'One too many', self::STATE));
    }

    /** Matomo escapes HTML in every request parameter; the saved name, state and segment are kept as typed. */
    public function testSavingThroughAnApiRequestKeepsQuotesAndMarkup(): void
    {
        $this->as('anna', [1]);
        $name = 'A <b>"quoted"</b> & \'name\'';
        $result = Request::processRequest('FeinwerkDataExploration.addSavedExploration', [
            'idSite' => 1,
            'name' => $name,
            'state' => '{"fwdeDims":"browser","fwdeFilters":"label~contains~a%26b"}',
            'segmentDefinition' => 'browserCode==FF;pageUrl=@a%26b',
        ]);

        $saved = (new SavedExplorationStore())->get((int) $result['id']);
        $this->assertSame($name, $saved['name']);
        $this->assertSame(['fwdeDims' => 'browser', 'fwdeFilters' => 'label~contains~a%26b'], $saved['state']);
        $this->assertSame('browserCode==FF;pageUrl=@a%26b', $saved['segment']);

        Request::processRequest('FeinwerkDataExploration.updateSavedExploration', [
            'idSite' => 1,
            'idSaved' => $result['id'],
            'name' => '<i>Renamed</i>',
            'state' => '{"fwdeDims":"country"}',
        ]);
        $saved = (new SavedExplorationStore())->get((int) $result['id']);
        $this->assertSame('<i>Renamed</i>', $saved['name']);
        $this->assertSame(['fwdeDims' => 'country'], $saved['state']);
    }

    public function testDeletingAUserOrASiteRemovesTheirExplorations(): void
    {
        $store = new SavedExplorationStore();
        $store->add(1, 'carla', 'Of a deleted user', [], '', true);
        $store->add(2, 'dora', 'Of a deleted site', [], '', true);
        // Matomo deletes a user only while a super user is left
        Fixture::createSuperUser(false);
        UsersManagerApi::getInstance()->addUser('carla', 'secret-password-1', 'carla@example.org');

        UsersManagerApi::getInstance()->deleteUser('carla');
        SitesManagerApi::getInstance()->deleteSite(2);

        $this->assertSame([], $store->listVisible(1, 'admin'));
        $this->assertSame([], $store->listVisible(2, 'dora'));
    }

    private function as(string $login, array $viewSites): void
    {
        FakeAccess::clearAccess(false, [], $viewSites, $login);
    }

    private function api(): API
    {
        return API::getInstance();
    }

    private function assertRefused(string $key, callable $call): void
    {
        try {
            $call();
            $this->fail('expected ' . $key);
        } catch (ExplorationException $e) {
            $this->assertSame($key, $e->getTranslationKey());
        }
    }
}
