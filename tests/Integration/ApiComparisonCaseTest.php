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
 * A comparison column finds its row although the database spells the grouped value differently there ("Shoes" in the
 * own view, "shoes" in the Chrome segment): rows are matched by the database, not by the spelling.
 *
 * @group FeinwerkDataExploration
 */
class ApiComparisonCaseTest extends IntegrationTestCase
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

    public function testCaseVariantsOfOneValueAreCompared(): void
    {
        $definition = json_encode(['dimensions' => ['pageTitle'], 'metrics' => ['nb_visits']]);
        $comparisons = json_encode([['segment' => 'browserCode==CH', 'period' => 'day', 'date' => ComparisonCaseFixture::DATE]]);
        $table = API::getInstance()->getExploration(1, 'day', ComparisonCaseFixture::DATE, $definition, '', 0, $comparisons);

        $rows = array_values(array_filter(
            array_map(static fn ($row) => $row->getColumns(), $table->getRows()),
            static fn (array $row) => strcasecmp((string) $row['label'], 'shoes') === 0
        ));
        $this->assertCount(1, $rows, 'one group for both spellings');
        $this->assertSame(3, $rows[0]['nb_visits']);
        $this->assertSame(1, $rows[0]['nb_visits__c1'], 'the Chrome visit, whichever spelling each query shows');
    }
}

ApiComparisonCaseTest::$fixture = new ComparisonCaseFixture();
