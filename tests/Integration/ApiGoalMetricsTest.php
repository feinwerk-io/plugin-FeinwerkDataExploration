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
use Piwik\DataTable;
use Piwik\Db;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache as TrackerCache;

/**
 * More metrics (2026-11-24): each goal's metrics side by side, e-commerce orders, items and abandoned carts, the visit
 * metrics, in visit and in action scope, with totals, a comparison, "Others" and readable names. One e-commerce website,
 * one day; Chrome has V1, V2, V5, Firefox V3, V4.
 *
 * @group FeinwerkDataExploration
 */
class ApiGoalMetricsTest extends IntegrationTestCase
{
    private const DAY = '2026-03-27';
    private const CHROME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    private const FIREFOX = 'Mozilla/5.0 (X11; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0';

    /** visit => [user agent, [time, what, ...]]: page path, goal id and revenue, order id with items [sku, price, quantity], cart value with items */
    private const VISITS = [
        'V1' => [self::CHROME, [['08:00:00', 'page', '/home'], ['08:01:00', 'page', '/shop'], ['08:02:00', 'goal', 1, 5],
            ['08:03:00', 'goal', 2, 20]]],
        'V2' => [self::CHROME, [['09:00:00', 'page', '/shop'], ['09:01:00', 'order', 'A1', 50, [['SKU1', 25, 2]]]]],
        'V3' => [self::FIREFOX, [['10:00:00', 'page', '/shop'], ['10:01:00', 'goal', 1, 5], ['10:02:00', 'goal', 1, 5],
            ['10:03:00', 'cart', 30, [['SKU2', 30, 1]]]]],
        'V4' => [self::FIREFOX, [['11:00:00', 'page', '/home'], ['11:00:40', 'page', '/home']]],
        'V5' => [self::CHROME, [['12:00:00', 'page', '/home'], ['12:01:00', 'order', 'A2', 70, [['SKU1', 25, 1], ['SKU3', 22.5, 2]]],
            ['12:02:00', 'goal', 2, 20]]],
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
        Fixture::createWebsite('2026-01-01 00:00:00', 1, 'Shop', 'https://example.com', 1, null, null, 'UTC');
        // goal 1 may convert several times per visit; goal 2's name as Matomo stores what a form sends (HTML-escaped)
        GoalsApi::getInstance()->addGoal(1, 'Newsletter', 'manually', '', 'contains', false, 0, true);
        GoalsApi::getInstance()->addGoal(1, 'Q&amp;A: Leads', 'manually', '', 'contains');
        // a second goal with goal 1's name (Matomo allows it), never converted
        GoalsApi::getInstance()->addGoal(1, 'Newsletter', 'manually', '', 'contains');
        TrackerCache::deleteCacheWebsiteAttributes(1);
        TrackerCache::clearCacheGeneral();

        $number = 0;
        foreach (self::VISITS as [$userAgent, $actions]) {
            $number++;
            $t = Fixture::getTracker(1, self::DAY . ' ' . $actions[0][0], true, true);
            $t->setVisitorId(sprintf('a%015x', $number));
            $t->setIp('198.51.100.' . $number);
            $t->setUserAgent($userAgent);
            foreach ($actions as $action) {
                $t->setForceVisitDateTime(self::DAY . ' ' . $action[0]);
                if ($action[1] === 'page') {
                    $t->setUrl('https://example.com' . $action[2]);
                    Fixture::checkResponse($t->doTrackPageView('Page ' . $action[2]));
                } elseif ($action[1] === 'goal') {
                    Fixture::checkResponse($t->doTrackGoal($action[2], $action[3]));
                } else {
                    foreach (end($action) as [$sku, $price, $quantity]) {
                        $t->addEcommerceItem($sku, 'Product ' . $sku, 'Shoes', $price, $quantity);
                    }
                    Fixture::checkResponse($action[1] === 'order'
                        ? $t->doTrackEcommerceOrder($action[2], $action[3])
                        : $t->doTrackEcommerceCartUpdate($action[2]));
                }
            }
        }
    }

    public function testEachGoalSideBySide(): void
    {
        $rows = $this->rows('["browser"]', '["nb_visits", "goal_1_nb_conversions", "goal_1_conversion_rate", "goal_1_revenue",'
            . ' "goal_2_nb_conversions", "goal_2_conversion_rate", "goal_2_revenue", "nb_conversions", "revenue", "nb_visits_converted"]');

        $this->assertSame([3, 1, 0.3333, 5.0, 2, 0.6667, 40.0, 5, 165.0, 3], array_values($rows['Chrome']));
        $this->assertSame([2, 2, 0.5, 10.0, 0, 0.0, 0.0, 2, 10.0, 1], array_values($rows['Firefox']));
    }

    public function testEcommerceAndVisitMetrics(): void
    {
        $rows = $this->rows('["browser"]', '["goal_ecommerceOrder_nb_conversions", "goal_ecommerceOrder_revenue",'
            . ' "goal_ecommerceOrder_items", "goal_ecommerceOrder_avg_order_revenue", "goal_ecommerceOrder_conversion_rate",'
            . ' "goal_ecommerceAbandonedCart_nb_conversions", "goal_ecommerceAbandonedCart_revenue", "revenue_per_visit",'
            . ' "sum_visit_length", "nb_conversions"]');

        // the abandoned cart widens the subquery to idgoal -1, the all-goals conversions still leave it out
        $this->assertSame([2, 120.0, 5, 60.0, 0.6667, 0, 0.0, 55.0, $this->visitTime('CH'), 5], array_values($rows['Chrome']));
        $this->assertSame([0, 0.0, 0, 0.0, 0.0, 1, 30.0, 5.0, $this->visitTime('FF'), 2], array_values($rows['Firefox']));
    }

    /** review focus 3: next to an action dimension, the conversions of the visits behind the row, never per action */
    public function testActionScopeCountsTheVisitsBehindTheRow(): void
    {
        $rows = $this->rows('["pageUrl"]', '["nb_visits", "goal_2_nb_conversions", "goal_ecommerceOrder_revenue",'
            . ' "nb_visits_converted", "sum_visit_length"]');
        $byPath = [];
        foreach ($rows as $label => $columns) {
            $byPath[str_contains($label, 'home') ? 'home' : 'shop'] = array_values($columns);
        }

        $this->assertSame([3, 2, 70.0, 2, $this->visitTime(null, [1, 4, 5])], $byPath['home']);
        $this->assertSame([3, 1, 50.0, 3, $this->visitTime(null, [1, 2, 3])], $byPath['shop']);
    }

    public function testTotalsAndComparisonWithGoalMetrics(): void
    {
        $table = API::getInstance()->getExploration(1, 'day', self::DAY, $this->definition('["browser"]',
            '["goal_1_nb_conversions", "goal_ecommerceOrder_revenue", "goal_1_conversion_rate"]'), '', 0,
            '[{"segment": "browserCode==FF", "period": "day", "date": "' . self::DAY . '"}]');
        $total = $table->getFirstRow();
        $chrome = $table->getRowFromLabel('Chrome');

        $this->assertTrue((bool) $total->getMetadata('fwde_total'));
        $this->assertSame([3, 120.0, 0.4, 2, 0.0, 0.5], [$total->getColumn('goal_1_nb_conversions'),
            $total->getColumn('goal_ecommerceOrder_revenue'), $total->getColumn('goal_1_conversion_rate'),
            $total->getColumn('goal_1_nb_conversions__c1'), $total->getColumn('goal_ecommerceOrder_revenue__c1'),
            $total->getColumn('goal_1_conversion_rate__c1')]);
        // a row missing in the comparison: 0 for counts and sums, nothing for rates; shares only for counts and sums
        // (Row::getColumn() reads a null column as false: the columns array tells null from missing)
        $columns = $chrome->getColumns();
        $this->assertSame(0, $columns['goal_1_nb_conversions__c1']);
        $this->assertArrayHasKey('goal_1_conversion_rate__c1', $columns);
        $this->assertNull($columns['goal_1_conversion_rate__c1']);
        $this->assertArrayHasKey('goal_1_nb_conversions__share', $columns);
        $this->assertArrayNotHasKey('goal_1_conversion_rate__share', $columns);
    }

    public function testOthersAddUpGoalAndEcommerceMetrics(): void
    {
        $others = API::getInstance()->getOthers(1, 'day', self::DAY, '{"dimensions": ["browser"], "metrics": ["goal_1_nb_conversions",'
            . ' "goal_ecommerceOrder_revenue", "goal_ecommerceOrder_items"], "sort": "goal_1_nb_conversions", "sortOrder": "desc", "limit": 1}')['others'];

        $this->assertSame(1, (int) $others['goal_1_nb_conversions']);
        $this->assertEquals(120.0, $others['goal_ecommerceOrder_revenue']);
        $this->assertSame(5, (int) $others['goal_ecommerceOrder_items']);
    }

    /** review focus 2: the goal's name as people read it, also in export columns */
    public function testReadableColumnsNameTheGoal(): void
    {
        $table = API::getInstance()->getExploration(1, 'day', self::DAY, $this->definition('["browser"]',
            '["goal_1_nb_conversions", "goal_2_revenue", "goal_ecommerceOrder_nb_conversions"]'), '', 1);

        // two goals named "Newsletter": the id tells them apart, so no export column overwrites another
        $this->assertSame(['Browser', 'Newsletter (1): Conversions', 'Q&A: Leads: Revenue', 'Orders'],
            array_keys($table->getFirstRow()->getColumns()));
        $both = API::getInstance()->getExploration(1, 'day', self::DAY, $this->definition('["browser"]',
            '["goal_1_nb_conversions", "goal_3_nb_conversions"]'), '', 1);
        $this->assertSame(['Browser', 'Newsletter (1): Conversions', 'Newsletter (3): Conversions'],
            array_keys($both->getFirstRow()->getColumns()));
    }

    /** review focus 5 on the database (HAVING): a goal's rate as a fraction, its revenue in the website's currency */
    public function testNumberFiltersOnGoalMetrics(): void
    {
        $filtered = function (string $groups): array {
            $definition = '{"dimensions": ["browser"], "metrics": ["goal_1_conversion_rate", "goal_2_revenue", "revenue"],'
                . ' "limit": 100, "filterGroups": ' . $groups . '}';
            $labels = array_map(fn ($row) => $row->getColumn('label'),
                API::getInstance()->getExploration(1, 'day', self::DAY, $definition)->getRows());
            sort($labels);
            return $labels;
        };

        $this->assertSame(['Firefox'], $filtered('{"groups": [{"filters": [{"target": "goal_1_conversion_rate", "operator": "gte", "value": 0.4}]}]}'));
        $this->assertSame(['Chrome'], $filtered('{"groups": [{"filters": [{"target": "goal_2_revenue", "operator": "gt", "value": 10}]}]}'));
        $this->assertSame(['Chrome'], $filtered('{"groups": [{"filters": [{"target": "revenue", "operator": "gt", "value": 100}]}]}'));
        $this->assertSame(['Chrome', 'Firefox'], $filtered('{"groups": [{"match": "any", "filters": ['
            . '{"target": "goal_1_conversion_rate", "operator": "gte", "value": 0.4}, {"target": "goal_2_revenue", "operator": "gt", "value": 10}]}]}'));
        // a text condition joined by "any" with a goal metric's number condition
        $this->assertSame(['Chrome', 'Firefox'], $filtered('{"groups": [{"match": "any", "filters": ['
            . '{"target": "label", "operator": "contains", "value": "fire"}, {"target": "goal_2_revenue", "operator": "gte", "value": 40}]}]}'));
    }

    /** review focus 4 on the database: idGoal narrows the all-goals metrics only */
    public function testIdGoalNarrowsOnlyTheAllGoalsMetrics(): void
    {
        $table = API::getInstance()->getExploration(1, 'day', self::DAY, '{"dimensions": ["browser"], "metrics": ["nb_conversions",'
            . ' "revenue", "goal_2_nb_conversions", "nb_visits_converted", "goal_ecommerceOrder_revenue"], "idGoal": 1, "limit": 100}');

        $this->assertSame([1, 5.0, 2, 3, 120.0], array_values(array_slice($table->getRowFromLabel('Chrome')->getColumns(), 1)));
        $this->assertSame([2, 10.0, 0, 1, 0.0], array_values(array_slice($table->getRowFromLabel('Firefox')->getColumns(), 1)));
    }

    public function testTrendWithAGoalMetric(): void
    {
        $trend = API::getInstance()->getTrend(1, 'day', self::DAY, '{"dimensions": ["browser"], "metrics": ["goal_2_nb_conversions",'
            . ' "goal_ecommerceOrder_revenue"], "sort": "goal_2_nb_conversions", "limit": 100}');
        $sums = [];
        foreach ($trend->getRows() as $row) {
            $label = (string) $row->getColumn('label');
            $sums[$label][0] = ($sums[$label][0] ?? 0) + (int) $row->getColumn('goal_2_nb_conversions');
            $sums[$label][1] = ($sums[$label][1] ?? 0) + (float) $row->getColumn('goal_ecommerceOrder_revenue');
        }

        // the hours of the day add up to the day's table
        $this->assertSame([2, 120.0], $sums['Chrome']);
        $this->assertSame([0, 0.0], $sums['Firefox']);
    }

    public function testMetricOfAGoalTheWebsiteLacksIsRefused(): void
    {
        try {
            API::getInstance()->getExploration(1, 'day', self::DAY, $this->definition('["browser"]', '["goal_9_revenue"]'));
            $this->fail('a metric of a goal the website lacks was accepted');
        } catch (ExplorationException $e) {
            $this->assertStringContainsString('goal_9_revenue', $e->getMessage());
        }
    }

    private function definition(string $dimensions, string $metrics): string
    {
        return '{"dimensions": ' . $dimensions . ', "metrics": ' . $metrics . ', "limit": 100}';
    }

    /** @return array<string, array<string, mixed>> label => metric columns in the definition's order */
    private function rows(string $dimensions, string $metrics): array
    {
        $table = API::getInstance()->getExploration(1, 'day', self::DAY, $this->definition($dimensions, $metrics));
        $this->assertInstanceOf(DataTable::class, $table);
        $rows = [];
        foreach ($table->getRows() as $row) {
            $columns = $row->getColumns();
            unset($columns['label']);
            $rows[(string) $row->getColumn('label')] = $columns;
        }
        return $rows;
    }

    /**
     * The visits' time on site from log_visit: by browser code, or by the visits' order of tracking (1 for V1).
     *
     * @param int[] $numbers
     */
    private function visitTime(?string $browser, array $numbers = []): int
    {
        $visits = Db::fetchAll('SELECT config_browser_name, visit_total_time FROM ' . Common::prefixTable('log_visit') . ' ORDER BY idvisit');
        $sum = 0;
        foreach ($visits as $i => $visit) {
            if ($browser !== null ? $visit['config_browser_name'] === $browser : in_array($i + 1, $numbers, true)) {
                $sum += (int) $visit['visit_total_time'];
            }
        }
        return $sum;
    }
}
