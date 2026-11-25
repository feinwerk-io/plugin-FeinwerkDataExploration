<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Cache;
use Piwik\Date;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\KpiExplorer;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\FeinwerkDataExploration\Saved\KpiCardSettings;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedReportUnavailable;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache as TrackerCache;

/**
 * The KPI card's number, comparison and line (design 2026-11-24). Website 1 runs in New York time, created
 * 2026-09-01; website 2 was created 2026-11-01. Visits (local times, pages: one page bounces):
 * Wednesdays 10-21: 2 (one bounce), 10-28: 4 (two bounces), 11-04: none, 11-11: 3 (08:00 and 12:00), 11-18: 5 (two of
 * them after 10:00); Tuesday 11-17: 1; Thursday 11-12: 2.
 *
 * @group FeinwerkDataExploration
 */
class ApiKpiCardTest extends IntegrationTestCase
{
    private const LATER = '2026-11-24 12:00:00';
    private const VISITS = [
        // local time, pages
        ['2026-10-21 08:00', 1], ['2026-10-21 14:00', 2],
        ['2026-10-28 08:00', 1], ['2026-10-28 09:00', 2], ['2026-10-28 13:00', 1], ['2026-10-28 15:00', 3],
        ['2026-11-11 08:00', 2], ['2026-11-11 12:00', 1], ['2026-11-11 16:00', 2],
        ['2026-11-12 09:00', 1], ['2026-11-12 18:00', 2],
        ['2026-11-17 11:00', 2],
        ['2026-11-18 07:00', 1], ['2026-11-18 08:30', 2], ['2026-11-18 09:45', 2], ['2026-11-18 11:00', 1], ['2026-11-18 20:00', 3],
    ];

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
        Fixture::createWebsite('2026-09-01 00:00:00', 0, 'Shop', 'https://example.com', 1, null, null, 'America/New_York');
        Fixture::createWebsite('2026-11-01 00:00:00', 0, 'New shop', 'https://new.example.com', 1, null, null, 'America/New_York');
        GoalsApi::getInstance()->addGoal(1, 'Newsletter', 'manually', '', 'contains');
        TrackerCache::deleteCacheWebsiteAttributes(1);
        TrackerCache::deleteCacheWebsiteAttributes(2);
        TrackerCache::clearCacheGeneral();
        foreach (self::VISITS as $n => [$local, $pages]) {
            $this->track(1, $n, $local, $pages);
        }
        foreach (['2026-11-04 10:00', '2026-11-11 10:00', '2026-11-11 11:00', '2026-11-18 10:00'] as $n => $local) {
            $this->track(2, 100 + $n, $local, 1);
        }
    }

    public function testDayCardEqualsTheTotalRow(): void
    {
        $card = $this->card(1, 'day', '2026-11-18', ['nb_visits']);
        $total = API::getInstance()->getExploration(1, 'day', '2026-11-18', '{"dimensions": ["browser"], "metrics": ["nb_visits"]}', '', 0,
            '[{"segment": "", "period": "day", "date": "2026-11-18"}]')->getFirstRow()->getColumn('nb_visits');

        $this->assertSame(5, $total);
        $this->assertSame($total, $card['value']);
        $this->assertFalse($card['partial']);
    }

    public function testAverageOfTheFourWednesdays(): void
    {
        $card = $this->card(1, 'day', '2026-11-18', ['nb_visits']);

        // 11-11: 3, 11-04: 0 (the website had no visits), 10-28: 4, 10-21: 2
        $this->assertSame(4, $card['comparison']['used']);
        $this->assertEqualsWithDelta(2.25, $card['comparison']['baseline'], 0.0001);
        $this->assertEqualsWithDelta((5 - 2.25) / 2.25, $card['comparison']['change'], 0.0001);
        $this->assertSame(['2026-11-11', '2026-11-04', '2026-10-28', '2026-10-21'], array_column($card['comparison']['spans'], 'firstDay'));
    }

    /** review focus 3: a rate has no value on a day without visits; the average takes the other days' own rates */
    public function testAverageLeavesOutDaysWithoutAValue(): void
    {
        $card = $this->card(1, 'day', '2026-11-18', ['bounce_rate']);

        $this->assertSame(3, $card['comparison']['used']);
        // 11-11: 1 of 3, 10-28: 2 of 4, 10-21: 1 of 2
        $this->assertEqualsWithDelta((1 / 3 + 2 / 4 + 1 / 2) / 3, $card['comparison']['baseline'], 0.01);
        $this->assertNull($card['comparison']['spans'][1]['value'], '11-04 has no bounce rate');
    }

    /** review focus 4: days before the website existed are missing, not 0 */
    public function testDaysBeforeTheWebsiteAreMissing(): void
    {
        $card = $this->card(2, 'day', '2026-11-18', ['nb_visits']);

        $this->assertSame(2, $card['comparison']['used']);
        $this->assertEqualsWithDelta((2 + 1) / 2, $card['comparison']['baseline'], 0.0001);
    }

    /** review focus 1: "so far" ends at the same local time on the day and on every comparison day */
    public function testTodaySoFarInTheSitesTimezone(): void
    {
        $card = $this->card(1, 'day', '2026-11-18', ['nb_visits'], 'average4', 30, 'percent', '2026-11-18 10:00:00');

        $this->assertTrue($card['partial']);
        $this->assertSame('10:00', $card['until']);
        $this->assertSame(3, $card['value'], '07:00, 08:30 and 09:45');
        // until 10:00: 11-11: 1, 11-04: 0, 10-28: 2, 10-21: 1
        $this->assertEqualsWithDelta(1.0, $card['comparison']['baseline'], 0.0001);
    }

    public function testWeekAndMonthComparisons(): void
    {
        $week = $this->card(1, 'week', '2026-11-18', ['nb_visits'], 'previous');
        $this->assertSame(6, $week['value'], 'week 11-16 to 11-22');
        $this->assertSame(5, $week['comparison']['baseline'], 'week 11-09 to 11-15');

        $month = $this->card(1, 'month', '2026-11-18', ['nb_visits'], 'average4', 30, 'percent', '2026-12-02 12:00:00');
        $this->assertSame(11, $month['value']);
        $this->assertFalse($month['partial']);
        // October 6, September 0, August and July before the website: missing
        $this->assertSame(2, $month['comparison']['used']);
        $this->assertEqualsWithDelta(3.0, $month['comparison']['baseline'], 0.0001);

        // on November 24 the month is so far: October up to its 24th (2), September 0
        $soFar = $this->card(1, 'month', '2026-11-18', ['nb_visits'], 'average4');
        $this->assertTrue($soFar['partial']);
        $this->assertEqualsWithDelta(1.0, $soFar['comparison']['baseline'], 0.0001);
    }

    public function testRangeHasNoComparison(): void
    {
        $card = $this->card(1, 'range', '2026-11-11,2026-11-18', ['nb_visits']);

        $this->assertSame(11, $card['value']);
        $this->assertNull($card['comparison']);
        $this->assertNotEmpty($card['comparisonNote']);
    }

    public function testAbsoluteChange(): void
    {
        $card = $this->card(1, 'day', '2026-11-18', ['nb_visits'], 'weekday', 30, 'absolute');

        $this->assertSame(3, $card['comparison']['baseline']);
        $this->assertSame(2, $card['comparison']['absolute']);
    }

    public function testLowerIsBetterForBounceRate(): void
    {
        $this->assertTrue($this->card(1, 'day', '2026-11-18', ['bounce_rate'])['lowerIsBetter']);
        $this->assertFalse($this->card(1, 'day', '2026-11-18', ['nb_visits'])['lowerIsBetter']);
    }

    public function testLineHasTheDaysAndMarksTheBaseline(): void
    {
        $line = $this->card(1, 'day', '2026-11-18', ['nb_visits'])['line'];

        $this->assertCount(30, $line);
        $this->assertSame(['2026-10-20', '2026-11-18'], [$line[0]['day'], $line[29]['day']]);
        $this->assertSame(5, $line[29]['value']);
        $marked = array_values(array_map(fn (array $point) => $point['day'], array_filter($line, fn (array $point) => $point['baseline'])));
        $this->assertSame(['2026-10-21', '2026-10-28', '2026-11-04', '2026-11-11'], $marked);
        $this->assertCount(7, $this->card(1, 'day', '2026-11-18', ['nb_visits'], 'average4', 7)['line']);
        $this->assertNull($this->card(1, 'day', '2026-11-18', ['nb_visits'], 'average4', 0)['line']);
    }

    public function testLineRefusedLeavesTheNumber(): void
    {
        $card = $this->card(1, 'day', '2026-11-18', ['nb_visits'], 'average4', 30, 'percent', self::LATER, new Limits(maxDays: 7));

        $this->assertSame(5, $card['value']);
        $this->assertNull($card['line']);
        $this->assertNotEmpty($card['lineNote']);
        $this->assertSame(4, $card['comparison']['used']);
    }

    /** review focus 5: with every metric's goal deleted the card is unavailable, as a saved report is */
    public function testCardWithoutMetricIsUnavailable(): void
    {
        $id = $this->save(['fwdeKind' => 'kpi', 'fwdeDims' => 'browser', 'fwdeMetrics' => 'goal_1_nb_conversions']);
        GoalsApi::getInstance()->deleteGoal(1, 1);
        Cache::getTransientCache()->flushAll();

        $this->expectException(SavedReportUnavailable::class);
        API::getInstance()->getKpiCard(1, 'day', '2026-11-18', $id);
    }

    public function testNotACardIsRefused(): void
    {
        $id = $this->save(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits']);
        try {
            API::getInstance()->getKpiCard(1, 'day', '2026-11-18', $id);
            $this->fail('a saved table is no card');
        } catch (ExplorationException $e) {
            $this->assertSame(['FeinwerkDataExploration_ErrorNotKpiCard', 400], [$e->getTranslationKey(), $e->getCode()]);
            $this->assertStringContainsString('idSaved', $e->getMessage());
        }
    }

    public function testApiReturnsTheCardWithItsTitle(): void
    {
        $id = $this->save(['fwdeKind' => 'kpi', 'fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits,bounce_rate', 'fwdeKpiMetric' => 'bounce_rate',
            'fwdeKpiCompare' => 'previous', 'fwdeKpiLine' => '7']);
        $card = API::getInstance()->getKpiCard(1, 'day', '2026-11-18', $id);

        $this->assertSame('Card', $card['title']);
        $this->assertSame(['bounce_rate', 'rate', 'previous'], [$card['metric'], $card['unit'], $card['comparison']['kind']]);
        $this->assertCount(7, $card['line']);
    }

    /** final review: "now" is the website's local time (New York 10:07 when UTC is 15:07), cut to five minutes */
    public function testApiCardUsesTheWebsitesTimeNow(): void
    {
        $id = $this->save(['fwdeKind' => 'kpi', 'fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits']);
        Date::$now = strtotime('2026-11-18 15:07:31 UTC');
        try {
            $card = API::getInstance()->getKpiCard(1, 'day', '2026-11-18', $id);
        } finally {
            Date::$now = null;
        }

        $this->assertTrue($card['partial']);
        $this->assertSame('10:05', $card['until']);
        $this->assertSame(3, $card['value'], '07:00, 08:30 and 09:45');
    }

    /** final review, the same mistake in the trend: today's hourly line ends at the website's current hour */
    public function testTrendStopsAtTheWebsitesHourNow(): void
    {
        Date::$now = strtotime('2026-11-18 15:30:00 UTC');
        try {
            $trend = API::getInstance()->getTrend(1, 'day', '2026-11-18', '{"dimensions": ["browser"], "metrics": ["nb_visits"]}');
        } finally {
            Date::$now = null;
        }

        $byHour = [];
        foreach ($trend->getRows() as $row) {
            if ($row->getMetadata('fwde_line') === 0) {
                $byHour[(int) $row->getColumn('time')] = $row->getColumns()['nb_visits'];
            }
        }
        $this->assertNotNull($byHour[10], 'New York 10:30: hour 10 is past');
        $this->assertNull($byHour[11], 'hour 11 is still to come');
    }

    /** final review: a comparison the guards refuse is missing, the card stays */
    public function testRefusedComparisonIsMissing(): void
    {
        // with at most 30 days October (31 days) is refused; November and September have 30
        $limits = new Limits(maxDays: 30);
        $card = $this->card(1, 'month', '2026-11-18', ['nb_visits'], 'average4', 0, 'percent', '2026-12-02 12:00:00', $limits);
        $this->assertSame(11, $card['value']);
        $this->assertNull($card['comparison']['spans'][0]['value'], 'October refused');
        $this->assertSame(1, $card['comparison']['used'], 'September (0 visits); August and July before the website');

        $only = $this->card(1, 'month', '2026-11-18', ['nb_visits'], 'previous', 0, 'percent', '2026-12-02 12:00:00', $limits);
        $this->assertNull($only['comparison']);
        $this->assertStringContainsString('30', (string) $only['comparisonNote']);
    }

    private function card(int $idSite, string $period, string $date, array $metrics, string $compare = 'average4', int $line = 30, string $change = 'percent', string $now = self::LATER, ?Limits $limits = null): array
    {
        $dimensions = new DimensionCatalog();
        $catalog = new MetricCatalog();
        $limits ??= new Limits();
        $definition = ExplorationDefinition::fromJson(json_encode(['dimensions' => ['browser'], 'metrics' => $metrics]), $idSite, $dimensions, $catalog, $limits);
        $settings = KpiCardSettings::fromState(['fwdeKpiCompare' => $compare, 'fwdeKpiLine' => (string) $line, 'fwdeKpiChange' => $change], $metrics);
        return (new KpiExplorer($dimensions, $catalog, $limits))->card($idSite, $period, $date, $definition, '', $settings, $now);
    }

    private function save(array $state): int
    {
        FakeAccess::clearAccess(true, [], [], 'root');
        return (int) API::getInstance()->addSavedExploration(1, 'Card', json_encode($state))['id'];
    }

    private function track(int $idSite, int $n, string $local, int $pages): void
    {
        $utc = (new \DateTimeImmutable($local, new \DateTimeZone('America/New_York')))->setTimezone(new \DateTimeZone('UTC'));
        $t = Fixture::getTracker($idSite, $utc->format('Y-m-d H:i:s'), true, true);
        $t->setVisitorId(sprintf('e%015x', $n + 1));
        $t->setIp('198.51.100.' . ($n % 250 + 1));
        for ($p = 0; $p < $pages; $p++) {
            $t->setForceVisitDateTime($utc->modify('+' . $p . ' minute')->format('Y-m-d H:i:s'));
            $t->setUrl('https://example.com/page' . $p);
            Fixture::checkResponse($t->doTrackPageView('Page ' . $p));
        }
    }
}
