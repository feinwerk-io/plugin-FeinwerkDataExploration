<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Piwik;
use Piwik\Plugins\CustomDimensions\API as CustomDimensionsApi;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * A failure in another plugin (CustomDimensions, a listener of the campaign case event) costs only its own
 * dimensions, never the whole exploration page.
 *
 * @group FeinwerkDataExploration
 */
class OtherPluginsTest extends IntegrationTestCase
{
    public function provideContainerConfig()
    {
        return ['Piwik\\Access' => new FakeAccess()];
    }

    public function setUp(): void
    {
        parent::setUp();
        FakeAccess::$superUser = true;
        Fixture::createWebsite('2026-01-01 00:00:00');
    }

    public function tearDown(): void
    {
        CustomDimensionsApi::unsetInstance();
        parent::tearDown();
    }

    public function testFailingCustomDimensionsLeaveTheOtherDimensions(): void
    {
        CustomDimensionsApi::setSingletonInstance(new class extends CustomDimensionsApi {
            public function getConfiguredCustomDimensions($idSite)
            {
                throw new \Error('CustomDimensions tables broken');
            }
        });

        $ids = array_column(API::getInstance()->getAvailableDimensions(1), 'id');

        $this->assertContains('browser', $ids);
        $this->assertEmpty(array_filter($ids, fn ($id) => str_starts_with($id, 'customDimension')));
    }

    public function testFailingCampaignListenerKeepsTheCampaignCase(): void
    {
        Piwik::addAction('Plugin.shouldLowerCampaignCase', function (string $plugin, bool &$lower) {
            $lower = true;
        });
        $this->assertTrue(DimensionCatalog::campaignCaseLowered());

        Piwik::addAction('Plugin.shouldLowerCampaignCase', function () {
            throw new \RuntimeException('listener failed');
        });
        $this->assertFalse(DimensionCatalog::campaignCaseLowered());
    }
}
