<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Cache;
use Piwik\NoAccessException;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The visit count of a period, for the page's warning before long explorations on large sites.
 *
 * @group FeinwerkDataExploration
 */
class ApiPeriodVisitsTest extends IntegrationTestCase
{
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
        Fixture::createWebsite('2026-01-01 00:00:00', 0, 'Shop', 'https://example.com', 1, null, null, 'UTC');
        for ($i = 0; $i < 3; $i++) {
            $t = Fixture::getTracker(1, '2026-03-27 1' . $i . ':00:00', true, true);
            $t->setNewVisitorId();
            $t->setIp('198.51.100.' . ($i + 1));
            $t->setUrl('https://example.com/');
            Fixture::checkResponse($t->doTrackPageView('Home'));
        }
    }

    public function testCountsTheVisitsOfThePeriodWithTheLimits(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'viewer');

        $this->assertSame(
            ['visits' => 3, 'maxVisits' => 2000000, 'timeoutSeconds' => 30],
            API::getInstance()->getPeriodVisits(1, 'day', '2026-03-27')
        );
    }

    public function testStopsCountingAboveTheThreshold(): void
    {
        Piwik::addAction('FeinwerkDataExploration.getLimits', function (Limits &$limits) {
            $limits = new Limits(maxVisits: 1);
        });

        $this->assertSame(2, API::getInstance()->getPeriodVisits(1, 'day', '2026-03-27')['visits']);
    }

    public function testRefusesAnonymousUsers(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'anonymous');

        $this->expectException(NoAccessException::class);
        API::getInstance()->getPeriodVisits(1, 'day', '2026-03-27');
    }

    public function testRefusesPeriodsLongerThanAnExploration(): void
    {
        $this->expectException(ExplorationException::class);
        API::getInstance()->getPeriodVisits(1, 'range', '2026-01-01,2026-03-27');
    }
}
