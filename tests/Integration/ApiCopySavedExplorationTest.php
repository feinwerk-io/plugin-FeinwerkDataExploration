<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\API\Request;
use Piwik\Plugins\CustomDimensions\API as CustomDimensionsApi;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Engine\AccessPolicy;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationCopier;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationStore;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * "Copy to other websites" for saved explorations (with Matomo's entity duplicator): a copy keeps name,
 * state and segment, is shared only where the user may share, maps the goal by its name, and skips websites the user
 * may not use. The response follows the duplicator's contract (success, message, additionalData).
 *
 * @group FeinwerkDataExploration
 */
class ApiCopySavedExplorationTest extends IntegrationTestCase
{
    use FunnelsSwitch;

    private const STATE = '{"fwdeDims":"browser","fwdeMetrics":"nb_visits"}';

    public function provideContainerConfig()
    {
        return ['Piwik\\Access' => new FakeAccess()];
    }

    public function setUp(): void
    {
        parent::setUp();
        FakeAccess::$superUser = true;
        foreach ([1, 2, 3] as $ignored) {
            Fixture::createWebsite('2026-01-01 00:00:00');
        }
        $goals = GoalsApi::getInstance();
        $goals->addGoal(1, 'Order', 'url', 'thanks', 'contains');      // id 1 on site 1
        $goals->addGoal(2, 'Newsletter', 'url', 'subscribed', 'contains'); // id 1 on site 2
        $goals->addGoal(2, 'Order', 'url', 'thanks', 'contains');      // id 2 on site 2
    }

    public function tearDown(): void
    {
        self::funnels(false);
        parent::tearDown();
    }

    public function testCopiesToEachDestination(): void
    {
        $this->as('anna', [1, 2, 3]);
        $id = $this->api()->addSavedExploration(1, 'Browsers', self::STATE, 'browserCode==FF')['id'];

        $result = $this->api()->copySavedExploration(1, $id, [2, 3]);

        $this->assertTrue($result['success']);
        $this->assertSame([2, 3], $result['additionalData']['copied']);
        foreach ([2, 3] as $idSite) {
            $copy = $this->onlySaved($idSite);
            $this->assertSame(['Browsers', 'anna', 'browserCode==FF', false], [$copy['name'], $copy['login'], $copy['segment'], $copy['shared']]);
            $this->assertSame(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits'], $copy['state']);
        }
    }

    public function testSharedOnlyWithWriteAccess(): void
    {
        $this->as('anna', [1, 2, 3], [1, 2]);
        $id = $this->api()->addSavedExploration(1, 'Team', self::STATE, '', 1)['id'];

        $this->api()->copySavedExploration(1, $id, [2, 3]);

        $this->assertTrue($this->onlySaved(2)['shared']);
        $this->assertFalse($this->onlySaved(3)['shared'], 'view access only: a private copy');
    }

    public function testGoalIsMappedByNameOrTheSiteIsSkipped(): void
    {
        $this->as('anna', [1, 2, 3]);
        $id = $this->api()->addSavedExploration(1, 'Orders', '{"fwdeDims":"browser","fwdeMetrics":"nb_conversions","fwdeGoal":"1"}')['id'];

        $result = $this->api()->copySavedExploration(1, $id, [2, 3]);

        $this->assertFalse($result['success']);
        $this->assertSame([2], $result['additionalData']['copied']);
        $this->assertSame([['idSite' => 3, 'reason' => 'goal']], $result['additionalData']['skipped']);
        $this->assertSame('2', $this->onlySaved(2)['state']['fwdeGoal'], 'the goal of the same name');
        $this->assertNull($this->onlySaved(3), 'nothing half-written');
        $this->assertNotSame('', $result['message']);
    }

    public function testSitesWithoutAccessAreRefused(): void
    {
        $this->as('anna', [1, 2]);
        $id = $this->api()->addSavedExploration(1, 'Mine', self::STATE)['id'];

        $result = $this->api()->copySavedExploration(1, $id, [2, 3]);

        $this->assertSame([2], $result['additionalData']['copied']);
        $this->assertSame([['idSite' => 3, 'reason' => 'access']], $result['additionalData']['skipped']);
        $this->assertNull($this->onlySaved(3));
    }

    public function testAnExplorationTheUserCannotSeeIsNotFound(): void
    {
        $this->as('bert', [1, 2]);
        $id = $this->api()->addSavedExploration(1, 'Private of Bert', self::STATE)['id'];
        $this->as('anna', [1, 2]);

        try {
            $this->api()->copySavedExploration(1, $id, [2]);
            $this->fail('a private exploration of another user was copied');
        } catch (ExplorationException $e) {
            $this->assertSame('FeinwerkDataExploration_ErrorSavedNotFound', $e->getTranslationKey());
            $this->assertSame(404, $e->getCode());
        }
    }

    /** The dialog preselects the current website: copying there says to choose another one. */
    public function testTheSourceWebsiteAloneAsksForAnotherOne(): void
    {
        $this->as('anna', [1, 2]);
        $id = $this->api()->addSavedExploration(1, 'Mine', self::STATE)['id'];

        $result = $this->api()->copySavedExploration(1, $id, [1]);

        $this->assertFalse($result['success']);
        $this->assertSame([], $result['additionalData']['copied']);
        $this->assertStringContainsString('FeinwerkDataExploration_CopyChooseOtherSite', $result['message'] . $this->keyOf($result['message']));
    }

    private function keyOf(string $message): string
    {
        return $message === \Piwik\Piwik::translate('FeinwerkDataExploration_CopyChooseOtherSite')
            ? 'FeinwerkDataExploration_CopyChooseOtherSite' : '';
    }

    public function testDestinationsAreTrimmedThroughProcessRequest(): void
    {
        $this->as('anna', [1, 2]);
        $id = $this->api()->addSavedExploration(1, 'Mine', self::STATE)['id'];

        $result = Request::processRequest('FeinwerkDataExploration.copySavedExploration', [
            'idSite' => 1, 'idSaved' => $id, 'idDestinationSites' => [' 2 '], 'format' => 'original',
        ]);

        $this->assertSame([2], $result['additionalData']['copied']);
    }

    /** Custom dimension ids are numbered per website: the copy uses the one of the same name and scope. */
    public function testCustomDimensionIsMappedByNameOrTheSiteIsSkipped(): void
    {
        $dimensions = CustomDimensionsApi::getInstance();
        $dimensions->configureNewCustomDimension(1, 'Customer type', 'visit', true);   // id 1 on site 1
        $dimensions->configureNewCustomDimension(1, 'Page author', 'action', true);    // id 2 on site 1
        $dimensions->configureNewCustomDimension(2, 'Page author', 'action', true);    // id 1 on site 2
        $this->as('anna', [1, 2, 3]);
        $id = $this->api()->addSavedExploration(1, 'Authors', '{"fwdeDims":"customDimension2,browser","fwdeMetrics":"nb_visits"}')['id'];

        $result = $this->api()->copySavedExploration(1, $id, [2, 3]);

        $this->assertSame([2], $result['additionalData']['copied']);
        $this->assertSame([['idSite' => 3, 'reason' => 'dimension']], $result['additionalData']['skipped']);
        $this->assertSame('customDimension1,browser', $this->onlySaved(2)['state']['fwdeDims']);
        $this->assertNull($this->onlySaved(3));
    }

    public function testASegmentThatDoesNotWorkOnTheTargetSkipsTheSite(): void
    {
        CustomDimensionsApi::getInstance()->configureNewCustomDimension(1, 'Customer type', 'visit', true);
        $this->as('anna', [1, 2]);
        $id = $this->api()->addSavedExploration(1, 'B2B', self::STATE, 'dimension1==b2b')['id'];

        $result = $this->api()->copySavedExploration(1, $id, [2]);

        $this->assertSame([], $result['additionalData']['copied']);
        $this->assertSame([['idSite' => 2, 'reason' => 'segment']], $result['additionalData']['skipped']);
        $this->assertNull($this->onlySaved(2));
    }

    /**
     * Custom dimension ids are numbered per website: "dimension1" of the segment means "Customer type" here and "Page
     * author" on site 2. The copy filters on the target's dimension of the same name and scope, or the site is skipped;
     * a segment that is valid on the target is not enough (pre-release review, 2026-11-17).
     */
    public function testSegmentCustomDimensionsAreMappedByNameOrTheSiteIsSkipped(): void
    {
        $dimensions = CustomDimensionsApi::getInstance();
        $dimensions->configureNewCustomDimension(1, 'Customer type', 'visit', true);   // id 1 on site 1
        $dimensions->configureNewCustomDimension(2, 'Page author', 'visit', true);     // id 1 on site 2
        $dimensions->configureNewCustomDimension(2, 'Customer type', 'visit', true);   // id 2 on site 2
        $dimensions->configureNewCustomDimension(3, 'Page author', 'visit', true);     // id 1 on site 3, no customer type
        $this->as('anna', [1, 2, 3]);
        $id = $this->api()->addSavedExploration(1, 'B2B', self::STATE, 'dimension1==b2b;referrerType==search')['id'];

        $result = $this->api()->copySavedExploration(1, $id, [2, 3]);

        $this->assertSame([2], $result['additionalData']['copied']);
        $this->assertSame([['idSite' => 3, 'reason' => 'segment']], $result['additionalData']['skipped']);
        $this->assertSame('dimension2==b2b;referrerType==search', $this->onlySaved(2)['segment']);
        $this->assertNull($this->onlySaved(3));
    }

    /** Goal ids are numbered per website too: a segment on a converted goal filters on the target's goal of that name. */
    public function testSegmentGoalIdsAreMappedByNameOrTheSiteIsSkipped(): void
    {
        $this->as('anna', [1, 2, 3]);
        $id = $this->api()->addSavedExploration(1, 'Buyers', self::STATE, 'visitConvertedGoalId==1,browserCode==FF')['id'];

        $result = $this->api()->copySavedExploration(1, $id, [2, 3]);

        $this->assertSame([2], $result['additionalData']['copied']);
        $this->assertSame([['idSite' => 3, 'reason' => 'segment']], $result['additionalData']['skipped']);
        $this->assertSame('visitConvertedGoalId==2,browserCode==FF', $this->onlySaved(2)['segment']);
    }

    /** House rule: no query per mapping; the target's goals are read once although the state and the segment need them. */
    public function testTheTargetGoalsAreReadOncePerWebsite(): void
    {
        $this->as('anna', [1, 2]);
        $state = '{"fwdeDims":"browser","fwdeMetrics":"nb_conversions","fwdeGoal":"1"}';
        $id = $this->api()->addSavedExploration(1, 'Orders', $state, 'visitConvertedGoalId==1')['id'];
        $calls = [];
        $copier = new SavedExplorationCopier(new SavedExplorationStore(), null, null, function (int $idSite) use (&$calls): array {
            $calls[] = $idSite;
            return SavedExplorationCopier::goalsOf($idSite);
        });

        $copier->copy((new SavedExplorationStore())->get($id), [2], 'anna');

        $this->assertSame([1, 2], $calls);
        $this->assertSame('visitConvertedGoalId==2', $this->onlySaved(2)['segment']);
    }

    /** Super users pass every access check: a website id that does not exist must not get a copy. */
    public function testAWebsiteThatDoesNotExistIsSkipped(): void
    {
        FakeAccess::clearAccess(true, [], [], 'root');
        $id = $this->api()->addSavedExploration(1, 'Mine', self::STATE)['id'];

        $result = $this->api()->copySavedExploration(1, $id, [99]);

        $this->assertSame([], $result['additionalData']['copied']);
        $this->assertSame([['idSite' => 99, 'reason' => 'access']], $result['additionalData']['skipped']);
        $this->assertSame([], (new SavedExplorationStore())->listVisible(99, 'root'));
    }

    /** Spec 9a: goal steps of a saved funnel are mapped by the goal's name like the table's goal. */
    public function testFunnelGoalStepsAreMappedByName(): void
    {
        self::funnels(true);
        $this->as('anna', [1, 2, 3]);
        $state = '{"fwdeKind":"funnel","fwdeSteps":"pageUrl~contains~%2Fa;goal~~1;anyGoal~~"}';
        $id = $this->api()->addSavedExploration(1, 'Orders funnel', $state)['id'];

        $result = $this->api()->copySavedExploration(1, $id, [2, 3]);

        $this->assertSame([2], $result['additionalData']['copied']);
        $this->assertSame([['idSite' => 3, 'reason' => 'goal']], $result['additionalData']['skipped']);
        $this->assertSame(['fwdeKind' => 'funnel', 'fwdeSteps' => 'pageUrl~contains~%2Fa;goal~~2;anyGoal~~'], $this->onlySaved(2)['state']);
        $this->assertNull($this->onlySaved(3));
    }

    /** House rule: no query per target for what belongs to the source website. */
    public function testTheSourceGoalsAreReadOnce(): void
    {
        $this->as('anna', [1, 2, 3]);
        $id = $this->api()->addSavedExploration(1, 'Orders', '{"fwdeDims":"browser","fwdeMetrics":"nb_conversions","fwdeGoal":"1"}')['id'];
        $calls = [];
        $copier = new SavedExplorationCopier(new SavedExplorationStore(), null, null, function (int $idSite) use (&$calls): array {
            $calls[] = $idSite;
            return SavedExplorationCopier::goalsOf($idSite);
        });

        $copier->copy((new SavedExplorationStore())->get($id), [2, 3], 'anna');

        $this->assertSame([1, 2, 3], $calls);
    }

    /** Matomo's copy dialog lists only websites with write access: without any, the page shows no copy action. */
    public function testCopyNeedsWriteAccessOnSomeWebsite(): void
    {
        $this->as('anna', [1, 2]);
        $this->assertFalse(AccessPolicy::canCopy());
        $this->as('anna', [1, 2], [2]);
        $this->assertTrue(AccessPolicy::canCopy());
    }

    private function onlySaved(int $idSite): ?array
    {
        $rows = (new SavedExplorationStore())->listVisible($idSite, 'anna');
        $this->assertLessThanOrEqual(1, count($rows));
        return $rows[0] ?? null;
    }

    private function as(string $login, array $viewSites, array $writeSites = []): void
    {
        FakeAccess::clearAccess(false, [], $viewSites, $login, $writeSites);
    }

    private function api(): API
    {
        return API::getInstance();
    }
}
