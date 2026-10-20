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

    private function widget(): ?WidgetConfig
    {
        Cache::getTransientCache()->flushAll();
        foreach (WidgetsList::get()->getWidgetConfigs() as $config) {
            if ($config->getModule() === 'FeinwerkDataExploration' && $config->getAction() === 'getExploration') {
                return $config;
            }
        }
        return null;
    }
}
