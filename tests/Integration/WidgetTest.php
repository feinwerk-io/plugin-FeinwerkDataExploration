<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Cache;
use Piwik\Plugins\FeinwerkDataExploration\SystemSettings;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Widget\WidgetConfig;
use Piwik\Widget\WidgetsList;

/**
 * The "Explorationen" page: one widget in its own category, hidden for users the access policy refuses.
 *
 * @group FeinwerkDataExploration
 */
class WidgetTest extends IntegrationTestCase
{
    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        // the currency symbol comes from Matomo's translations
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
        Fixture::createWebsite('2026-01-01 00:00:00', 0, 'Shop', 'https://example.com', 1, null, null, 'UTC');
        $_GET['idSite'] = '1';
    }

    public function tearDown(): void
    {
        unset($_GET['idSite']);
        parent::tearDown();
    }

    public function testWidgetIsListedWithClientComponent(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'viewer');

        $widget = $this->widget();

        $this->assertNotNull($widget);
        $this->assertSame('FeinwerkDataExploration_Explorations', $widget->getCategoryId());
        $this->assertSame('FeinwerkDataExploration_FreeFormTable', $widget->getSubcategoryId());
        $this->assertSame(['plugin' => 'FeinwerkDataExploration', 'name' => 'ExplorationPage'], $widget->getClientSideComponent());
        $props = $widget->getClientSideProps();
        $this->assertSame(1, $props['idSite']);
        $this->assertSame(1000, $props['maxRows']);
        // free limit 4 (Peter, 2026-11-17: "raise limit to 4"): one segment and one period fit together
        $this->assertSame(4, $props['maxCompareColumns']);
        $this->assertSame(5, $props['maxTrendLines']);
        $this->assertSame(100, $props['defaultRows']);
        $this->assertSame(31, $props['maxDays']);
        $this->assertNull($props['rawDataFrom']);
        $this->assertSame('$', $props['currencySymbol']);
        // the chart orders hours by the labels the API writes (12- or 24-hour clock)
        $this->assertCount(24, $props['hourLabels']);
        $this->assertSame(\Piwik\Plugins\VisitTime\getTimeLabel(13), $props['hourLabels'][13]);
        $this->assertTrue($widget->isWide());
    }

    public function testWidgetHiddenForRefusedUsers(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'anonymous');
        $this->assertNull($this->widget(), 'anonymous users never see explorations');

        FakeAccess::clearAccess(false, [], [], 'superUserLogin');
        FakeAccess::$superUser = true;
        $settings = new SystemSettings();
        $settings->minimumRole->setValue('write');
        $settings->save();

        FakeAccess::clearAccess(false, [], [1], 'viewer');
        $this->assertNull($this->widget(), 'the role setting asks for write access');

        FakeAccess::clearAccess(false, [], [1], 'writer', [1]);
        $this->assertNotNull($this->widget());
    }

    public function testFunnelWidgetIsListedWithClientComponent(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'viewer');

        $widget = $this->widget('getFunnel');

        $this->assertNotNull($widget);
        $this->assertSame('FeinwerkDataExploration_Explorations', $widget->getCategoryId());
        $this->assertSame('FeinwerkDataExploration_Funnel', $widget->getSubcategoryId());
        $this->assertSame(['plugin' => 'FeinwerkDataExploration', 'name' => 'FunnelPage'], $widget->getClientSideComponent());
        $props = $widget->getClientSideProps();
        $this->assertSame(
            ['idSite', 'maxDays', 'rawDataFrom', 'canShare', 'canCopy', 'feedback', 'promo', 'maxSteps'],
            array_keys($props)
        );
        $this->assertSame(1, $props['idSite']);
        $this->assertSame(3, $props['maxSteps']);
        $this->assertFalse($props['canShare']);
        $this->assertTrue($widget->isWide());
        $this->assertFalse($widget->isWidgetizeable());

        $subcategories = array_filter(
            \Piwik\Category\CategoryList::get()->getCategory('FeinwerkDataExploration_Explorations')->getSubcategories(),
            static fn ($s) => $s->getId() === 'FeinwerkDataExploration_Funnel'
        );
        $this->assertCount(1, $subcategories);
        $this->assertSame(2, array_values($subcategories)[0]->getOrder());
    }

    public function testFunnelWidgetHiddenForRefusedUsers(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'anonymous');
        $this->assertNull($this->widget('getFunnel'), 'anonymous users never see explorations');

        FakeAccess::clearAccess(false, [], [], 'superUserLogin');
        FakeAccess::$superUser = true;
        $settings = new SystemSettings();
        $settings->minimumRole->setValue('write');
        $settings->save();

        FakeAccess::clearAccess(false, [], [1], 'viewer');
        $this->assertNull($this->widget('getFunnel'), 'the role setting asks for write access');

        FakeAccess::clearAccess(false, [], [1], 'writer', [1]);
        $this->assertNotNull($this->widget('getFunnel'));
    }

    private function widget(string $action = 'getExploration'): ?WidgetConfig
    {
        Cache::getTransientCache()->flushAll();
        foreach (WidgetsList::get()->getWidgetConfigs() as $config) {
            if ($config->getModule() === 'FeinwerkDataExploration' && $config->getAction() === $action) {
                return $config;
            }
        }
        return null;
    }
}
