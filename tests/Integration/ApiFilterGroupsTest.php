<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\API\Request;
use Piwik\Cache;
use Piwik\Common;
use Piwik\Db;
use Piwik\DataTable;
use Piwik\NoAccessException;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group FeinwerkDataExploration
 */
class ApiFilterGroupsTest extends IntegrationTestCase
{

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
        Cache::getLazyCache()->flushAll(); // results are cached across tests of one PHP process
        Fixture::createWebsite('2026-01-01 00:00:00', 0, 'Shop', 'https://example.com', 1, null, null, 'UTC');

        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
        ];
        foreach ($userAgents as $i => $userAgent) {
            $t = Fixture::getTracker(1, '2026-03-27 1' . $i . ':00:00', true, true);
            $t->setNewVisitorId();
            $t->setIp('198.51.100.' . ($i + 1));
            $t->setUserAgent($userAgent);
            $t->setUrl('https://example.com/page' . ($i % 2));
            Fixture::checkResponse($t->doTrackPageView('Page'));
            $t->setForceVisitDateTime('2026-03-27 1' . $i . ':01:00');
            $t->setUrl('https://example.com/other');
            Fixture::checkResponse($t->doTrackPageView('Other'));
        }
    }

    /** (A and B) or C: Firefox by its text and number, Safari by its name (filter groups, 2026-11-23) */
    public function testAAndBOrC(): void
    {
        $this->assertSame(['Firefox', 'Safari'], $this->labels('{"match": "any", "groups": ['
            . '{"filters": [{"target": "label", "operator": "contains", "value": "fire"}, {"target": "nb_visits", "operator": "gte", "value": 1}]},'
            . '{"filters": [{"target": "label", "operator": "equals", "value": "Safari"}]}]}'));
    }

    /** (A or B) and C: Chrome and Firefox by their names, only Chrome has more than one visit */
    public function testAOrBAndC(): void
    {
        $this->assertSame(['Chrome'], $this->labels('{"groups": ['
            . '{"match": "any", "filters": [{"target": "label", "operator": "contains", "value": "chr"}, {"target": "label", "operator": "contains", "value": "fire"}]},'
            . '{"filters": [{"target": "nb_visits", "operator": "gt", "value": 1}]}]}'));
    }

    /** "Others" covers what the text clauses let through before the grouping: Firefox, not Safari */
    public function testTotalsAndOthersWithGroups(): void
    {
        $definition = '{"dimensions": ["browser"], "metrics": ["nb_visits"], "limit": 1, "filterGroups": {"groups": ['
            . '{"match": "any", "filters": [{"target": "label", "operator": "contains", "value": "chr"}, {"target": "label", "operator": "contains", "value": "fire"}]}]}}';

        $this->assertSame(['Chrome'], API::getInstance()->getExploration(1, 'day', '2026-03-27', $definition)->getColumn('label'));
        $this->assertSame(1, API::getInstance()->getOthers(1, 'day', '2026-03-27', $definition)['others']['nb_visits']);
    }

    /** "matches" on a stored text (page URLs, the database's REGEXP) and on a coded dimension (browsers, in PHP) */
    public function testMatchesOnPageUrlAndOnABrowser(): void
    {
        $pages = API::getInstance()->getExploration(1, 'day', '2026-03-27', '{"dimensions": ["pageUrl"], "metrics": ["nb_pageviews"], "sort": "label", "sortOrder": "asc",'
            . ' "filterGroups": {"groups": [{"filters": [{"target": "label", "operator": "matches", "value": "PAGE[01]$"}]}]}}');
        $this->assertSame(['example.com/page0', 'example.com/page1'], $pages->getColumn('label'));

        $this->assertSame(['Chrome', 'Safari'], $this->labels('{"groups": [{"filters": [{"target": "label", "operator": "matches", "value": "^(chr|saf)"}]}]}'));
    }

    /** a rate as a fraction inside an "any" group with a text condition: the key is tested on the grouped rows */
    public function testRateGteInsideAnyGroupWithText(): void
    {
        $this->assertSame(['Safari'], $this->labels('{"groups": [{"match": "any", "filters": ['
            . '{"target": "label", "operator": "equals", "value": "Safari"}, {"target": "bounce_rate", "operator": "gte", "value": 0.25}]}]}', 'nb_visits", "bounce_rate'));
        $this->assertSame(['Chrome', 'Firefox', 'Safari'], $this->labels('{"groups": [{"match": "any", "filters": ['
            . '{"target": "label", "operator": "equals", "value": "Safari"}, {"target": "bounce_rate", "operator": "lte", "value": 0}]}]}', 'nb_visits", "bounce_rate'));
    }

    /** every visit came directly: "does not start with" keeps the row without a referrer name, "starts with" not */
    public function testNegativeOperatorsKeepRowsWithoutValue(): void
    {
        $explore = fn (string $operator) => API::getInstance()->getExploration(1, 'day', '2026-03-27', '{"dimensions": ["referrerName"], "metrics": ["nb_visits"],'
            . ' "filterGroups": {"groups": [{"filters": [{"target": "label", "operator": "' . $operator . '", "value": "goo"}]}]}}')->getRowsCount();

        $this->assertSame(1, $explore('notStartsWith'));
        $this->assertSame(1, $explore('notEndsWith'));
        $this->assertSame(0, $explore('startsWith'));
    }

    /** A title with an apostrophe (stored as &#039;) and a page URL pattern on the grouped rows (final review, 2026-11-24) */
    public function testMatchesOnAnEscapedTitleAndAUrlPatternWithANumber(): void
    {
        $t = Fixture::getTracker(1, '2026-03-27 20:00:00', true, true);
        $t->setNewVisitorId();
        $t->setIp('198.51.100.9');
        $t->setUrl('https://example.com/shop');
        Fixture::checkResponse($t->doTrackPageView("Men's & Women's"));

        $titles = API::getInstance()->getExploration(1, 'day', '2026-03-27', '{"dimensions": ["pageTitle"], "metrics": ["nb_pageviews"],'
            . ' "filterGroups": {"groups": [{"filters": [{"target": "label", "operator": "matches", "value": "^Men\'s & W"}]}]}}');
        $this->assertSame(1, $titles->getRowsCount());

        // "any" with a number condition: the page URL pattern is tested on MAX() of the grouped key (MySQL 5.7 and 8.0)
        $pages = API::getInstance()->getExploration(1, 'day', '2026-03-27', '{"dimensions": ["pageUrl"], "metrics": ["nb_pageviews"], "sort": "label", "sortOrder": "asc",'
            . ' "filterGroups": {"groups": [{"match": "any", "filters": [{"target": "label", "operator": "matches", "value": "shop$"},'
            . ' {"target": "nb_pageviews", "operator": "gte", "value": 4}]}]}}');
        $this->assertSame(['example.com/other', 'example.com/shop'], $pages->getColumn('label'));
    }

    public function testLegacyDefinitionStillWorks(): void
    {
        $table = API::getInstance()->getExploration(1, 'day', '2026-03-27', '{"dimensions": ["browser"], "metrics": ["nb_visits"], "sort": "label", "sortOrder": "asc",'
            . ' "match": "any", "filters": [{"target": "label", "operator": "contains", "value": "chr"}, {"target": "label", "operator": "contains", "value": "fire"}]}');

        $this->assertSame(['Chrome', 'Firefox'], $table->getColumn('label'));
    }

    public function testInvalidPatternAnswers400(): void
    {
        try {
            $this->labels('{"groups": [{"filters": [{"target": "label", "operator": "matches", "value": "(ab"}]}]}');
            $this->fail('expected an ExplorationException');
        } catch (ExplorationException $e) {
            $this->assertSame('FeinwerkDataExploration_ErrorFilterPattern', $e->getTranslationKey());
            $this->assertSame(400, $e->getCode());
            $this->assertStringContainsString('filterGroups.groups[0].filters[0]', $e->getMessage());
        }
    }

    /** @return string[] the browsers of the day, sorted by name */
    private function labels(string $filterGroups, string $metrics = 'nb_visits'): array
    {
        return API::getInstance()->getExploration(1, 'day', '2026-03-27', '{"dimensions": ["browser"], "metrics": ["' . $metrics . '"], "sort": "label", "sortOrder": "asc",'
            . ' "filterGroups": ' . $filterGroups . '}')->getColumn('label');
    }
}
