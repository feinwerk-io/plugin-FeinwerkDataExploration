<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Cache;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The most recent days of a period that an exploration may read, offered instead of a refusal.
 *
 * @group FeinwerkDataExploration
 */
class ApiSuggestedPeriodTest extends IntegrationTestCase
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
        // three visits on the 25th, two on the 26th, one on the 27th
        foreach (['2026-03-25' => 3, '2026-03-26' => 2, '2026-03-27' => 1] as $day => $visits) {
            for ($i = 0; $i < $visits; $i++) {
                $t = Fixture::getTracker(1, $day . ' 1' . $i . ':00:00', true, true);
                $t->setNewVisitorId();
                $t->setIp('198.51.100.' . ($i + 1));
                $t->setUrl('https://example.com/');
                Fixture::checkResponse($t->doTrackPageView('Home'));
            }
        }
    }

    public function testOffersTheMostRecentDaysThatFit(): void
    {
        $this->limitVisits(3);

        $this->assertSame(['date' => '2026-03-26,2026-03-27', 'days' => 2], API::getInstance()->getSuggestedPeriod(1, 'range', '2026-03-25,2026-03-27'));
    }

    public function testOffersNothingWhenOneDayIsTooMuch(): void
    {
        $this->limitVisits(0);

        $this->assertSame([], API::getInstance()->getSuggestedPeriod(1, 'range', '2026-03-25,2026-03-27'));
    }

    public function testShortensAPeriodLongerThanAnExploration(): void
    {
        $this->assertSame(['date' => '2026-02-25,2026-03-27', 'days' => 31], API::getInstance()->getSuggestedPeriod(1, 'range', '2026-01-01,2026-03-27'));
    }

    public function testOffersTheLastDaysOfAYear(): void
    {
        // explorations refuse period=year (more than 31 days); the offer is the year's last days that fit
        $this->limitVisits(3);

        $this->assertSame(['date' => '2026-03-26,2026-03-27', 'days' => 2], API::getInstance()->getSuggestedPeriod(1, 'range', '2026-03-25,2026-03-27'));
        $this->assertSame(['date' => '2025-12-01,2025-12-31', 'days' => 31], API::getInstance()->getSuggestedPeriod(1, 'year', '2025-06-01'));
    }

    private function limitVisits(int $maxVisits): void
    {
        Piwik::addAction('FeinwerkDataExploration.getLimits', function (Limits &$limits) use ($maxVisits) {
            $limits = new Limits(maxVisits: $maxVisits);
        });
    }
}
