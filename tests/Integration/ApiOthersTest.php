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
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The exact rest of a cut table, on demand (design 2026-11-20, approach A): every metric over everything the table
 * covers but its shown rows.
 *
 * @group FeinwerkDataExploration
 */
class ApiOthersTest extends IntegrationTestCase
{
    private const DAY = '2026-03-27';

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
        Fixture::createWebsite('2026-01-01 00:00:00', 0, 'Shop', 'https://example.com', 1, null, null, 'UTC');

        // titles in two spellings ("Shoes", "shoes": one group under the database's collation), visits with and
        // without a referrer (direct visits have no referrer name: a NULL key), one bounce among them
        $visits = [
            ['Shoes', 'https://www.google.com/search?q=a', 3],
            ['shoes', null, 2],
            ['Boots', 'https://www.bing.com/search?q=b', 1],
            ['Hats', null, 2],
            ['Socks', 'https://www.google.com/search?q=c', 1],
            ['Gloves', 'https://duckduckgo.com/?q=d', 4],
        ];
        foreach ($visits as $i => [$title, $referrer, $pages]) {
            $t = Fixture::getTracker(1, self::DAY . ' 1' . $i . ':00:00', true, true);
            $t->setNewVisitorId();
            $t->setIp('198.51.100.' . ($i + 1));
            if ($referrer !== null) {
                $t->setUrlReferrer($referrer);
            }
            for ($p = 0; $p < $pages; $p++) {
                $t->setForceVisitDateTime(self::DAY . ' 1' . $i . ':0' . $p . ':00');
                $t->setUrl('https://example.com/' . strtolower($title) . '/' . $p);
                Fixture::checkResponse($t->doTrackPageView($title));
            }
        }
    }

    public function test_rows_plus_others_equal_the_uncut_table(): void
    {
        $cut = $this->explore('{"dimensions": ["pageTitle"], "metrics": ["nb_pageviews"], "limit": 2}');
        $full = $this->explore('{"dimensions": ["pageTitle"], "metrics": ["nb_pageviews"], "limit": 1000}');
        $others = API::getInstance()->getOthers(1, 'day', self::DAY, '{"dimensions": ["pageTitle"], "metrics": ["nb_pageviews"], "limit": 2}')['others'];

        $this->assertCount(2, $cut);
        $this->assertCount(5, $full, '"Shoes" and "shoes" are one row');
        $this->assertSame(array_sum(array_column($full, 'nb_pageviews')), array_sum(array_column($cut, 'nb_pageviews')) + $others['nb_pageviews']);
    }

    public function test_others_rates(): void
    {
        $definition = '{"dimensions": ["channelType"], "metrics": ["nb_visits", "bounce_rate"], "sort": "nb_visits", "limit": 1}';
        $full = $this->explore(str_replace('"limit": 1', '"limit": 1000', $definition));
        $shown = $this->explore($definition)[0]['label'];
        $rest = array_values(array_filter($full, fn (array $row) => $row['label'] !== $shown));
        $visits = array_sum(array_column($rest, 'nb_visits'));
        $bounces = array_sum(array_map(fn (array $row) => $row['bounce_rate'] * $row['nb_visits'], $rest));

        $others = API::getInstance()->getOthers(1, 'day', self::DAY, $definition)['others'];
        $this->assertSame($visits, $others['nb_visits']);
        $this->assertEqualsWithDelta($bounces / $visits, $others['bounce_rate'], 0.001);
    }

    public function test_null_key_row_stays_out(): void
    {
        // direct visits (no referrer name) are the biggest row, so the NULL key is among the shown ones
        $definition = '{"dimensions": ["referrerName"], "metrics": ["nb_visits"], "sort": "nb_visits", "limit": 1}';
        $cut = $this->explore($definition);
        $full = $this->explore(str_replace('"limit": 1', '"limit": 1000', $definition));
        $others = API::getInstance()->getOthers(1, 'day', self::DAY, $definition)['others'];

        $this->assertSame(2, $cut[0]['nb_visits'], 'the direct visits are shown');
        $this->assertSame(array_sum(array_column($full, 'nb_visits')) - 2, $others['nb_visits']);
    }

    public function test_null_key_row_below_the_cut_counts_in_others(): void
    {
        // sorted by name descending, the direct visits (no referrer name: a NULL key) fall below the cut
        $definition = '{"dimensions": ["referrerName"], "metrics": ["nb_visits"], "sort": "label", "sortOrder": "desc", "limit": 2}';
        $cut = API::getInstance()->getExploration(1, 'day', self::DAY, $definition)->getRows();
        $full = $this->explore(str_replace('"limit": 2', '"limit": 1000', $definition));
        $others = API::getInstance()->getOthers(1, 'day', self::DAY, $definition)['others'];

        $this->assertCount(2, $cut);
        foreach ($cut as $row) {
            $this->assertFalse((bool) $row->getMetadata('fwde_no_value'), 'the direct visits are not shown');
        }
        $shown = array_sum(array_map(fn ($row) => $row->getColumn('nb_visits'), $cut));
        $this->assertSame(array_sum(array_column($full, 'nb_visits')), $shown + $others['nb_visits']);
    }

    public function test_number_filter_removing_every_row_leaves_everything_in_others(): void
    {
        $definition = '{"dimensions": ["referrerName"], "metrics": ["nb_visits"], "filters": [{"target": "nb_visits", "operator": "gt", "value": 100}]}';
        $this->assertSame([], $this->explore($definition));

        $this->assertSame(6, API::getInstance()->getOthers(1, 'day', self::DAY, $definition)['others']['nb_visits']);
    }

    public function test_not_adding_up_is_refused(): void
    {
        try {
            API::getInstance()->getOthers(1, 'day', self::DAY, '{"dimensions": ["pageTitle"], "metrics": ["nb_visits"], "limit": 1}');
            $this->fail('a table whose rows overcount has no exact rest');
        } catch (ExplorationException $e) {
            $this->assertSame('FeinwerkDataExploration_ErrorOthersNotAddingUp', $e->getTranslationKey());
            $this->assertSame(400, $e->getCode());
        }
    }

    public function test_nothing_cut_returns_null(): void
    {
        $this->assertSame(['others' => null], API::getInstance()->getOthers(1, 'day', self::DAY, '{"dimensions": ["pageTitle"], "metrics": ["nb_pageviews"], "limit": 1000}'));
    }

    /** @return list<array<string, mixed>> */
    private function explore(string $definition): array
    {
        return array_map(
            fn ($row) => $row->getColumns(),
            API::getInstance()->getExploration(1, 'day', self::DAY, $definition)->getRows()
        );
    }
}
