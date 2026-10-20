<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\NoAccessException;
use Piwik\Plugins\CustomDimensions\API as CustomDimensionsApi;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group FeinwerkDataExploration
 */
class ApiDimensionsTest extends IntegrationTestCase
{
    private const CORE_POSITIVE_LIST = [
        'channelType', 'referrerName', 'campaignName', 'visitorType', 'country', 'region', 'city', 'language',
        'deviceType', 'operatingSystem', 'browser', 'resolution', 'date', 'weekday', 'hour',
        'entryPageUrl', 'entryPageTitle', 'exitPageUrl', 'exitPageTitle', 'visitConverted',
        'pageUrl', 'pageTitle', 'eventCategory', 'eventAction', 'eventName', 'downloadUrl', 'outlinkUrl', 'siteSearchKeyword',
    ];

    private int $visitDimension;
    private int $actionDimension;
    private int $inactiveDimension;

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
        Fixture::createWebsite('2026-01-01 00:00:00');

        $api = CustomDimensionsApi::getInstance();
        $this->visitDimension = (int) $api->configureNewCustomDimension(1, 'Customer type', 'visit', true);
        $this->actionDimension = (int) $api->configureNewCustomDimension(1, 'Page author', 'action', true);
        $this->inactiveDimension = (int) $api->configureNewCustomDimension(1, 'Old dimension', 'visit', false);
    }

    public function testListsPositiveListAndActiveCustomDimensions(): void
    {
        $dimensions = API::getInstance()->getAvailableDimensions(1);
        $byId = array_column($dimensions, null, 'id');

        $this->assertSame(
            array_merge(self::CORE_POSITIVE_LIST, ['customDimension' . $this->visitDimension, 'customDimension' . $this->actionDimension]),
            array_column($dimensions, 'id')
        );
        $this->assertSame('visit', $byId['customDimension' . $this->visitDimension]['scope']);
        $this->assertSame('action', $byId['customDimension' . $this->actionDimension]['scope']);
        $this->assertSame('Page author', $byId['customDimension' . $this->actionDimension]['name']);
        $this->assertArrayNotHasKey('customDimension' . $this->inactiveDimension, $byId);
    }

    public function testNamesAndGroupsAreSet(): void
    {
        foreach (API::getInstance()->getAvailableDimensions(1) as $dimension) {
            $this->assertNotSame('', $dimension['name'], $dimension['id']);
            $this->assertStringNotContainsString('_', $dimension['name'], $dimension['id']);
            $this->assertContains($dimension['group'], ['acquisition', 'visitor', 'technology', 'time', 'visit', 'behaviour', 'custom']);
        }
    }

    public function testAnonymousIsRejectedEvenWithViewAccess(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'anonymous');

        $this->expectException(NoAccessException::class);
        API::getInstance()->getAvailableDimensions(1);
    }

    public function testRequiresViewAccess(): void
    {
        FakeAccess::$superUser = false;
        FakeAccess::$idSitesView = [];

        $this->expectException(NoAccessException::class);
        API::getInstance()->getAvailableDimensions(1);
    }
}
