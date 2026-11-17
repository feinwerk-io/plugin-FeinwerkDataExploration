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
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Definition\FunnelDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\FunnelExplorer;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\FeinwerkDataExploration\Engine\SiteGoals;
use Piwik\Plugins\FeinwerkDataExploration\Query\QueryRunner;
use Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures\FunnelFixture;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The funnel against visits with known action sequences (spec 3, 4 and 8). Counts are visits per step.
 *
 * @group FeinwerkDataExploration
 */
class ApiFunnelTest extends IntegrationTestCase
{
    /** @var FunnelFixture */
    public static $fixture = null;

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
        FakeAccess::$superUser = true;
        Cache::getLazyCache()->flushAll(); // results and resolved steps are cached across tests of one PHP process
    }

    public function testCountsVisitsPerStepInOrder(): void
    {
        $rows = $this->rows([self::page('contains', '/products'), self::page('contains', '/cart')]);

        $this->assertSame([9, 3], array_column($rows, 'nb_visits'));
        $this->assertSame([1, 2], array_column($rows, 'step'));
        $this->assertSame([100.0, 33.3], array_column($rows, 'share_of_start'));
        $this->assertSame([100.0, 33.3], array_column($rows, 'share_of_previous'));
        $this->assertSame([0, 6], array_column($rows, 'nb_dropoffs'));
        $this->assertSame(['Page URL contains "/products"', 'Page URL contains "/cart"'], array_column($rows, 'label'));
    }

    public function testALaterStepBeforeStepOneDoesNotCount(): void
    {
        $this->assertSame([4, 1], $this->counts([self::page('contains', '/cart'), self::page('contains', '/products')]));
    }

    public function testActionsInTheSameSecondCountInTheirOrder(): void
    {
        $steps = [self::page('contains', '/products'), ['type' => 'eventAction', 'operator' => 'equals', 'value' => 'Add to cart']];
        $this->assertSame([9, 1], $this->counts($steps));
    }

    public function testOneActionNeverCountsForTwoSteps(): void
    {
        $this->assertSame([9, 1], $this->counts([self::page('contains', '/products'), self::page('contains', '/products/shoes')]));
    }

    public function testGoalStepCountsTheConversionOfThePreviousAction(): void
    {
        $rows = $this->rows([self::page('contains', '/thanks'), self::goal(FunnelFixture::GOAL_ORDER)]);
        $this->assertSame([2, 2], array_column($rows, 'nb_visits'));
        $this->assertSame(['Page URL contains "/thanks"', 'Goal "Order"'], array_column($rows, 'label'));

        $steps = [self::page('contains', '/products'), self::page('contains', '/thanks'), self::goal(FunnelFixture::GOAL_ORDER)];
        $this->assertSame([9, 1, 1], $this->counts($steps));
    }

    /** One conversion never counts for two goal steps (spec 3: one action, one step). */
    public function testGoalAfterGoalNeedsAnotherConversion(): void
    {
        $newsletter = self::goal(FunnelFixture::GOAL_NEWSLETTER);
        $this->assertSame([2, 1], $this->counts([$newsletter, $newsletter]), 'V8 converted twice, V7 once');
        $this->assertSame([4, 0], $this->counts([['type' => 'anyGoal'], self::goal(FunnelFixture::GOAL_ORDER)]));
        $this->assertSame([2, 0], $this->counts([self::goal(FunnelFixture::GOAL_ORDER), ['type' => 'anyGoal']]));
        $this->assertSame([2, 0], $this->counts([$newsletter, self::page('contains', '/products')]), 'the pages came before');
    }

    public function testRepeatedActionsCountTheVisitOnce(): void
    {
        $rows = $this->rows([self::page('contains', '/products'), self::page('contains', '/cart'), ['type' => 'anyGoal']]);
        $this->assertSame([9, 3, 2], array_column($rows, 'nb_visits'));
        $this->assertSame('Any goal', $rows[2]['label']);
    }

    public function testSearchDownloadAndOutlinkSteps(): void
    {
        $steps = [
            ['type' => 'siteSearch', 'operator' => 'equals', 'value' => 'boots'],
            ['type' => 'download', 'operator' => 'contains', 'value' => 'catalog.pdf'],
            ['type' => 'outlink', 'operator' => 'contains', 'value' => 'partner.example.org'],
        ];
        $this->assertSame([1, 1, 1], $this->counts($steps));
    }

    public function testSegmentLimitsTheVisits(): void
    {
        $this->assertSame([1, 1], $this->counts([self::page('contains', '/products'), self::page('contains', '/cart')], 'browserCode==FF'));
    }

    public function testEmptyResult(): void
    {
        $rows = $this->rows([self::page('contains', '/nothing'), self::page('contains', '/cart')]);
        $this->assertSame([0, 0], array_column($rows, 'nb_visits'));
        $this->assertSame([0.0, 0.0], array_column($rows, 'share_of_start'));
        $this->assertSame([0.0, 0.0], array_column($rows, 'share_of_previous'));
    }

    public function testTextIsMatchedWithoutCase(): void
    {
        $this->assertSame([9, 3], $this->counts([self::page('contains', '/PRODUCTS'), self::page('contains', '/CART')]));
    }

    public function testUrlWithSchemeMatches(): void
    {
        $steps = [self::page('startsWith', 'https://www.example.com/products'), self::page('equals', 'example.com/cart')];
        $this->assertSame([9, 3], $this->counts($steps));
    }

    public function testSpecialCharactersMatchLiterally(): void
    {
        $event = ['type' => 'eventName', 'operator' => 'contains', 'value' => 'Tom & Jerry 50%_off'];
        $this->assertSame([1, 0], $this->counts([$event, ['type' => 'anyGoal']]));
        $wildcards = ['type' => 'eventName', 'operator' => 'contains', 'value' => 'Jerry 5_%'];
        $this->assertSame([0, 0], $this->counts([$wildcards, ['type' => 'anyGoal']]));
    }

    public function testBroadStepCountsLikeAResolvedOne(): void
    {
        $definition = FunnelDefinition::fromJson(
            json_encode(['steps' => [self::page('contains', '/products'), self::page('contains', '/cart')]]),
            SiteGoals::names(1),
            new Limits()
        );
        // maxIds 0: every text step with matches is compared through log_action, on level 1 and in the LEFT JOIN of level 2
        $table = (new FunnelExplorer(new Limits(), null, 0))->explore(1, 'day', FunnelFixture::DATE, $definition, '');
        $this->assertSame([9, 3], array_column(self::columns($table), 'nb_visits'));
    }

    public function testTooManyStepsAreRefused(): void
    {
        $step = self::page('contains', '/products');
        $this->assertRefused('FeinwerkDataExploration_ErrorFunnelStepCount', 400, fn () => $this->counts([$step, $step, $step, $step]));
    }

    public function testMoreStepsThroughTheLimitsEvent(): void
    {
        Piwik::addAction('FeinwerkDataExploration.getLimits', function (Limits &$limits) {
            $limits = new Limits(maxFunnelSteps: 4);
        });
        $step = self::page('contains', '/products');
        $this->assertSame([9, 2, 0, 0], $this->counts([$step, $step, $step, $step]));
    }

    public function testUnknownGoalIsRefused(): void
    {
        $this->assertRefused('FeinwerkDataExploration_ErrorFunnelGoal', 400, fn () => $this->counts([self::page('contains', '/a'), self::goal(99)]));
    }

    public function testNoAccessIsRefusedLikeTheTable(): void
    {
        FakeAccess::clearAccess(false, [], [2], 'viewer');
        $refusal = function (callable $call): array {
            try {
                $call();
            } catch (\Exception $e) {
                return [get_class($e), $e->getCode()];
            }
            $this->fail('no refusal');
        };
        $table = $refusal(fn () => API::getInstance()->getExploration(1, 'day', FunnelFixture::DATE, '{"dimensions":["browser"],"metrics":["nb_visits"]}'));
        $funnel = $refusal(fn () => $this->counts([self::page('contains', '/a'), ['type' => 'anyGoal']]));
        $this->assertSame($table, $funnel);
    }

    public function testParametersAreTrimmedThroughProcessRequest(): void
    {
        $definition = json_encode(['steps' => [self::page('contains', '/products'), self::page('contains', '/cart')]]);
        $table = Request::processRequest('FeinwerkDataExploration.getFunnel', [
            'idSite' => 1, 'period' => ' day ', 'date' => ' ' . FunnelFixture::DATE . ' ', 'segment' => ' browserCode==FF ',
            'definition' => $definition,
        ]);
        $this->assertSame([1, 1], array_column(self::columns($table), 'nb_visits'));
    }

    /** The parameters are typed: an array where the API expects a string is refused, never read as an empty value. */
    public function testAWronglyTypedParameterIsRefused(): void
    {
        try {
            Request::processRequest('FeinwerkDataExploration.getFunnel', [
                'idSite' => 1, 'period' => 'day', 'date' => FunnelFixture::DATE, 'definition' => ['steps' => []],
            ]);
        } catch (\Throwable $e) {
            $this->assertNotInstanceOf(ExplorationException::class, $e);
            $this->assertStringContainsString('definition', $e->getMessage());
            return;
        }
        $this->fail('an array was accepted as the definition');
    }

    public function testReadableColumnsUseTranslatedNames(): void
    {
        $definition = json_encode(['steps' => [self::page('contains', '/products'), self::page('contains', '/cart')]]);
        $table = API::getInstance()->getFunnel(1, 'day', FunnelFixture::DATE, $definition, '', 1);
        $this->assertSame(
            ['Step', 'Condition', 'Visits', 'Share of step 1', 'Share of previous step', 'Drop-offs'],
            array_keys($table->getFirstRow()->getColumns())
        );
    }

    public function testTimeoutIsReported(): void
    {
        $runner = new class (new Limits()) extends QueryRunner {
            public function fetchAll(array $query): array
            {
                throw new ExplorationException('FeinwerkDataExploration_ErrorTimeout', [30]);
            }
        };
        $definition = FunnelDefinition::fromJson(
            json_encode(['steps' => [self::page('contains', '/products'), self::page('contains', '/cart')]]),
            SiteGoals::names(1),
            new Limits()
        );
        $this->assertRefused(
            'FeinwerkDataExploration_ErrorTimeout',
            400,
            fn () => (new FunnelExplorer(new Limits(), $runner))->explore(1, 'day', FunnelFixture::DATE, $definition, '')
        );
    }

    public function testSecondCallIsServedFromCache(): void
    {
        $steps = [self::page('contains', '/products'), self::page('contains', '/cart')];
        $first = $this->counts($steps);
        Db::query('DELETE FROM ' . Common::prefixTable('log_link_visit_action') . ' WHERE idsite = 1');

        $this->assertSame([9, 3], $first);
        $this->assertSame($first, $this->counts($steps));
    }

    private static function page(string $operator, string $value): array
    {
        return ['type' => 'pageUrl', 'operator' => $operator, 'value' => $value];
    }

    private static function goal(int $idGoal): array
    {
        return ['type' => 'goal', 'idGoal' => $idGoal];
    }

    private function counts(array $steps, string $segment = ''): array
    {
        return array_column($this->rows($steps, $segment), 'nb_visits');
    }

    private function rows(array $steps, string $segment = ''): array
    {
        return self::columns(API::getInstance()->getFunnel(1, 'day', FunnelFixture::DATE, json_encode(['steps' => $steps]), $segment));
    }

    private static function columns(DataTable $table): array
    {
        return array_map(static fn ($row) => $row->getColumns(), $table->getRows());
    }

    private function assertRefused(string $key, int $code, callable $call): void
    {
        try {
            $call();
            $this->fail('expected ' . $key);
        } catch (ExplorationException $e) {
            $this->assertSame($key, $e->getTranslationKey());
            $this->assertSame($code, $e->getCode(), $key);
        }
    }
}

ApiFunnelTest::$fixture = new FunnelFixture();
