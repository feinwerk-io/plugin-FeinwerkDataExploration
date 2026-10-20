<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Cache;
use Piwik\Common;
use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\FeinwerkDataExploration\Query\QueryRunner;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The protections of spec section 3: cost check, concurrency, result cache, time limit, periods.
 *
 * @group FeinwerkDataExploration
 */
class GuardsTest extends IntegrationTestCase
{
    private const DEFINITION = '{"dimensions": ["browser"], "metrics": ["nb_visits"]}';

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

        for ($i = 0; $i < 3; $i++) {
            $t = Fixture::getTracker(1, '2026-03-27 1' . $i . ':00:00', true, true);
            $t->setNewVisitorId();
            $t->setIp('198.51.100.' . ($i + 1));
            $t->setUrl('https://example.com/');
            Fixture::checkResponse($t->doTrackPageView('Home'));
        }
    }

    public function testCostGuardRejectsAboveThreshold(): void
    {
        Piwik::addAction('FeinwerkDataExploration.getLimits', function (Limits &$limits) {
            $limits = new Limits(maxVisits: 2);
        });

        // super users read what they can change, everyone else whom to ask
        $this->assertExplorationError('FeinwerkDataExploration_ErrorTooManyVisitsSuperUser', fn () => $this->explore('day', '2026-03-27'));
        FakeAccess::clearAccess(false, [], [1], 'viewer');
        Cache::getLazyCache()->flushAll();
        $this->assertExplorationError('FeinwerkDataExploration_ErrorTooManyVisits', fn () => $this->explore('day', '2026-03-27'));
    }

    public function testCostGuardAllowsAtThreshold(): void
    {
        Piwik::addAction('FeinwerkDataExploration.getLimits', function (Limits &$limits) {
            $limits = new Limits(maxVisits: 3);
        });

        $this->assertSame(1, $this->explore('day', '2026-03-27')->getRowsCount());
    }

    public function testBusyWhenAllSlotsTaken(): void
    {
        $backend = StaticContainer::get(LockBackend::class);
        $slots = [new Lock($backend, 'fwDataExploration'), new Lock($backend, 'fwDataExploration')];
        $this->assertTrue($slots[0]->acquireLock('slot1', 60));
        $this->assertTrue($slots[1]->acquireLock('slot2', 60));

        try {
            $this->assertExplorationError('FeinwerkDataExploration_ErrorBusy', fn () => $this->explore('day', '2026-03-27'));
        } finally {
            $slots[0]->unlock();
            $slots[1]->unlock();
        }

        $this->assertSame(1, $this->explore('day', '2026-03-27')->getRowsCount(), 'slots are free again');
    }

    public function testSlotIsReleasedAfterEachExploration(): void
    {
        for ($i = 0; $i < 3; $i++) {
            Cache::getLazyCache()->flushAll();
            $this->explore('day', '2026-03-27');
        }

        $backend = StaticContainer::get(LockBackend::class);
        $this->assertSame([], $backend->getKeysMatchingPattern('fwDataExploration%'));
    }

    public function testSecondCallIsServedFromCache(): void
    {
        $first = $this->explore('day', '2026-03-27')->getRows()[0]->getColumns();
        Db::query('DELETE FROM ' . Common::prefixTable('log_visit') . ' WHERE idsite = 1');

        $second = $this->explore('day', '2026-03-27')->getRows()[0]->getColumns();

        $this->assertSame(['label' => 'Firefox', 'nb_visits' => 3], $first);
        $this->assertSame($first, $second);
    }

    public function testTimeoutIsReported(): void
    {
        $runner = new QueryRunner(new Limits(timeoutSeconds: 1));

        $slow = [
            'sql' => 'SELECT COUNT(*) AS c FROM information_schema.columns a, information_schema.columns b, information_schema.columns c',
            'bind' => [],
        ];
        $this->assertExplorationError('FeinwerkDataExploration_ErrorTimeoutSuperUser', fn () => $runner->fetchAll($slow));
        FakeAccess::clearAccess(false, [], [1], 'viewer');
        $this->assertExplorationError('FeinwerkDataExploration_ErrorTimeout', fn () => $runner->fetchAll($slow));
    }

    public function testDateTodayIsAccepted(): void
    {
        $this->assertSame(0, $this->explore('day', 'today')->getRowsCount());
    }

    public function testTooLongRangeIsRejected(): void
    {
        $this->assertExplorationError('FeinwerkDataExploration_ErrorPeriodTooLong', fn () => $this->explore('range', '2026-03-01,2026-04-01'));
        $this->assertExplorationError('FeinwerkDataExploration_ErrorPeriodNotSupported', fn () => $this->explore('day', 'last7'));
    }

    private function explore(string $period, string $date)
    {
        return API::getInstance()->getExploration(1, $period, $date, self::DEFINITION);
    }

    private function assertExplorationError(string $key, callable $call): void
    {
        try {
            $call();
            $this->fail('expected ' . $key);
        } catch (ExplorationException $e) {
            $this->assertSame($key, $e->getTranslationKey());
            $this->assertStringNotContainsString('FeinwerkDataExploration_', $e->getMessage(), 'message is translated');
        }
    }
}
