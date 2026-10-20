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

        $this->assertCount(15, $metrics);
        $this->assertSame([
            'id' => 'nb_visits', 'scope' => 'visit', 'name' => 'Visits', 'nameInActionScope' => 'Visits with this action', 'kind' => null,
            'description' => Piwik::translate('General_ColumnNbVisitsDocumentation'),
        ], $metrics[0]);
        $this->assertSame('nb_outlinks', $metrics[14]['id']);
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
