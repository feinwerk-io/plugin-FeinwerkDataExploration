<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Cache;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures\ComparisonCaseFixture;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The trend lines belong to the rows as the database groups them (trend design 5): "Shoes" and "shoes" are one row.
 *
 * @group FeinwerkDataExploration
 */
class ApiTrendCaseTest extends IntegrationTestCase
{
    /** @var ComparisonCaseFixture */
    public static $fixture = null;

    public function provideContainerConfig()
    {
        return ['Piwik\\Access' => new FakeAccess()];
    }

    public function setUp(): void
    {
        parent::setUp();
        Fixture::loadAllTranslations();
        FakeAccess::$superUser = true;
        Cache::getLazyCache()->flushAll();
    }

    public function testCaseVariantsLandInTheirRow(): void
    {
        $definition = json_encode(['dimensions' => ['pageTitle'], 'metrics' => ['nb_visits']]);
        $table = API::getInstance()->getTrend(1, 'day', ComparisonCaseFixture::DATE, $definition);
        $lines = [];
        foreach ($table->getRows() as $row) {
            $lines[$row->getMetadata('fwde_line')][$row->getColumn('time')] = $row->getColumn('nb_visits');
        }
        $this->assertCount(1, $lines, 'the database groups both spellings as one row');
        $this->assertSame([1, 1, 1], [$lines[0]['8'], $lines[0]['9'], $lines[0]['10']]);
        $this->assertSame(3, array_sum($lines[0]));
    }
}

ApiTrendCaseTest::$fixture = new ComparisonCaseFixture();
