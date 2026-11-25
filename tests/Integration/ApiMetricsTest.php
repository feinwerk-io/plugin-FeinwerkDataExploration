<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\NoAccessException;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Piwik;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group FeinwerkDataExploration
 */
class ApiMetricsTest extends IntegrationTestCase
{
    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        // IntegrationTestCase disables real translations; these tests assert English names.
        $fixture->extraTestEnvVars['loadRealTranslations'] = true;
    }

    public function provideContainerConfig()
    {
        // Without FakeAccess the real Access stays super user and the access test could not fail.
        return ['Piwik\\Access' => new FakeAccess()];
    }

    public function setUp(): void
    {
        parent::setUp();
        Fixture::loadAllTranslations();
        FakeAccess::$superUser = true;
        Fixture::createWebsite('2026-01-01 00:00:00');
    }

    public function testReturnsAllMetricsWithNames(): void
    {
        $metrics = API::getInstance()->getAvailableMetrics(1);

        $this->assertCount(21, $metrics);
        $this->assertSame([
            'id' => 'nb_visits', 'scope' => 'visit', 'group' => 'visits', 'name' => 'Visits',
            'nameInActionScope' => 'Visits with this action', 'kind' => null, 'unit' => 'count', 'aggregation' => 'sum',
            'idGoal' => null, 'description' => Piwik::translate('General_ColumnNbVisitsDocumentation'),
        ], $metrics[0]);
        $this->assertSame('nb_searches', $metrics[20]['id']);
        $names = array_column($metrics, 'name', 'id');
        $this->assertSame('Maximum actions in one visit', $names['max_actions']);
        $this->assertSame('Average event value', $names['avg_event_value']);
        $this->assertSame('Searches', $names['nb_searches']);
        foreach ($metrics as $metric) {
            $this->assertNotSame('', $metric['name']);
            $this->assertStringNotContainsString('FeinwerkDataExploration_', $metric['name']);
        }
    }

    public function testMetricsCarryMatomosDescriptions(): void
    {
        $metrics = array_column(API::getInstance()->getAvailableMetrics(1), null, 'id');

        $this->assertSame(Piwik::translate('General_ColumnNbVisitsDocumentation'), $metrics['nb_visits']['description']);
        $this->assertStringContainsString(Piwik::translate('FeinwerkDataExploration_MetricNoteUniqueVisitors'), $metrics['nb_uniq_visitors']['description']);
        $this->assertSame(Piwik::translate('FeinwerkDataExploration_MetricDescriptionDownloads'), $metrics['nb_downloads']['description']);
        foreach ($metrics as $id => $metric) {
            $this->assertNotSame('', $metric['description'], $id);
        }
    }

    /** more metrics (2026-11-24): a website's goals and e-commerce, each metric with unit, aggregation and goal */
    public function testGoalsAndEcommerceMetricsOfTheWebsite(): void
    {
        Fixture::createWebsite('2026-01-01 00:00:00', 1);
        \Piwik\Plugins\Goals\API::getInstance()->addGoal(2, 'Newsletter', 'url', 'newsletter', 'contains');
        \Piwik\Plugins\Goals\API::getInstance()->addGoal(2, 'Q&A', 'url', 'faq', 'contains');

        $metrics = API::getInstance()->getAvailableMetrics(2);
        $ids = array_column($metrics, 'id');
        $byId = array_column($metrics, null, 'id');

        $this->assertSame(['goal_1_nb_conversions', 'goal_1_conversion_rate', 'goal_1_revenue', 'goal_2_nb_conversions',
            'goal_2_conversion_rate', 'goal_2_revenue', 'goal_ecommerceOrder_nb_conversions', 'goal_ecommerceOrder_revenue',
            'goal_ecommerceOrder_items', 'goal_ecommerceOrder_avg_order_revenue', 'goal_ecommerceOrder_conversion_rate',
            'goal_ecommerceAbandonedCart_nb_conversions', 'goal_ecommerceAbandonedCart_revenue'], array_slice($ids, 21));
        $this->assertSame(['id' => 'goal_1_nb_conversions', 'scope' => 'goal', 'group' => 'goals', 'name' => 'Newsletter: Conversions',
            'nameInActionScope' => null, 'kind' => null, 'unit' => 'count', 'aggregation' => 'sum', 'idGoal' => 1,
            'description' => 'The number of conversions of the goal "Newsletter" in these visits.'], $byId['goal_1_nb_conversions']);
        $this->assertSame('Q&A: Revenue', $byId['goal_2_revenue']['name']);
        $this->assertSame(['ecommerce', 'money', 'average', null], [$byId['goal_ecommerceOrder_avg_order_revenue']['group'],
            $byId['goal_ecommerceOrder_avg_order_revenue']['unit'], $byId['goal_ecommerceOrder_avg_order_revenue']['aggregation'],
            $byId['goal_ecommerceOrder_avg_order_revenue']['idGoal']]);
        $this->assertSame('Abandoned Carts', $byId['goal_ecommerceAbandonedCart_nb_conversions']['name']);
        $this->assertSame(['rate', 'ratio'], [$byId['conversion_rate']['unit'], $byId['conversion_rate']['aggregation']]);
        foreach ($metrics as $metric) {
            $this->assertNotSame('', $metric['description'], $metric['id']);
            $this->assertStringNotContainsString('_', $metric['name'], $metric['id']);
        }

        // a website without e-commerce: no e-commerce metrics
        $this->assertSame([], array_filter(array_column(API::getInstance()->getAvailableMetrics(1), 'id'),
            fn ($id) => str_starts_with($id, 'goal_ecommerce')));
    }

    public function testAnonymousIsRejectedEvenWithViewAccess(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'anonymous');

        $this->expectException(NoAccessException::class);
        API::getInstance()->getAvailableMetrics(1);
    }

    public function testRequiresViewAccess(): void
    {
        FakeAccess::$superUser = false;
        FakeAccess::$idSitesView = [];

        $this->expectException(NoAccessException::class);
        API::getInstance()->getAvailableMetrics(1);
    }
}
