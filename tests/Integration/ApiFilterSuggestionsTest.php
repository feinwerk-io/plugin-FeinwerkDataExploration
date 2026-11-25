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
 * Suggestions for a filter's value (Peter, 2026-11-25: "felder wo sinnvoll mit autovervollständigen", source "Matomo
 * reports"): Matomo's archived report of the dimension, the typed text literal and case-insensitive, at most 15, as
 * the table shows the value; the fixed lists (weekday, hour, ...) from the plugin itself.
 *
 * @group FeinwerkDataExploration
 */
class ApiFilterSuggestionsTest extends IntegrationTestCase
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

        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
        ];
        foreach ($userAgents as $i => $userAgent) {
            $t = Fixture::getTracker(1, self::DAY . ' 1' . $i . ':00:00', true, true);
            $t->setNewVisitorId();
            $t->setIp('198.51.100.' . ($i + 1));
            $t->setUserAgent($userAgent);
            $t->setUrl('https://example.com/page' . ($i % 2));
            Fixture::checkResponse($t->doTrackPageView('Page ' . ($i % 2)));
            $t->setForceVisitDateTime(self::DAY . ' 1' . $i . ':01:00');
            $t->setUrl('https://example.com/other');
            Fixture::checkResponse($t->doTrackPageView('Other'));
        }
        // an Ubuntu visit with a download (Matomo stores download URLs as they are)
        $t = Fixture::getTracker(1, self::DAY . ' 18:00:00', true, true);
        $t->setNewVisitorId();
        $t->setIp('198.51.100.20');
        $t->setUserAgent('Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0');
        $t->setUrl('https://example.com/other');
        Fixture::checkResponse($t->doTrackPageView('Other'));
        $t->setForceVisitDateTime(self::DAY . ' 18:01:00');
        Fixture::checkResponse($t->doTrackAction('https://www.example.com/files/guide.pdf', 'download'));
        // a search engine without a keyword and a website as referrers
        foreach (['https://www.google.com/', 'https://blog.example.org/post'] as $i => $referrer) {
            $t = Fixture::getTracker(1, self::DAY . ' 2' . $i . ':00:00', true, true);
            $t->setNewVisitorId();
            $t->setIp('198.51.100.' . (10 + $i));
            $t->setUserAgent($userAgents[0]);
            $t->setUrlReferrer($referrer);
            $t->setUrl('https://example.com/other');
            Fixture::checkResponse($t->doTrackPageView('Other'));
        }
    }

    public function testBrowsersFromMatomosReportMostVisitedFirst(): void
    {
        $this->assertSame(['Chrome', 'Firefox', 'Safari'], array_slice($this->suggest('browser', ''), 0, 3));
        $this->assertSame(['Firefox'], $this->suggest('browser', 'fIr'));
    }

    /** page URLs as the table shows them (without the scheme), so "is" matches the row */
    public function testPageUrlsAsTheTableShowsThem(): void
    {
        $this->assertEqualsCanonicalizing(['example.com/page0', 'example.com/page1'], $this->suggest('pageUrl', 'page'));
        $this->assertSame(['Other'], $this->suggest('pageTitle', 'oth'));
    }

    /** the typed text is literal: a regular expression character matches itself only */
    public function testTheTextIsLiteral(): void
    {
        $this->assertSame([], $this->suggest('browser', 'f.r'));
    }

    /** Matomo lists every device type, also those without visits; only values with visits are suggested */
    public function testOnlyValuesWithVisits(): void
    {
        $this->assertSame(['Desktop'], $this->suggest('deviceType', ''));
    }

    /**
     * Referrer names come from the reports of search engines, social networks, websites and campaigns (Matomo's
     * Referrers.getAll lists keywords for search engines); a "not defined" or "Unknown" row is no value to type ("is
     * empty" is the condition for it).
     */
    public function testReferrerNamesWithoutNotDefined(): void
    {
        $this->assertEqualsCanonicalizing(['Google', 'blog.example.org'], $this->suggest('referrerName', ''));
        $this->assertSame([], $this->suggest('referrerKeyword', ''));
    }

    public function testFixedListsFromThePluginItself(): void
    {
        $this->assertSame(['Friday'], $this->suggest('weekday', 'fri'));
        $this->assertCount(7, $this->suggest('weekday', ''));
        // every value of a fixed list, also all 24 hours (release review 2026-11-25)
        $this->assertCount(24, $this->suggest('hour', ''));
    }

    /**
     * Release review 2026-11-25: a download keeps its stored form (scheme and "www."), a page URL is found by the form
     * the table shows, operating systems by the names the table shows (not Matomo's OS families).
     */
    public function testValuesAsTheTableShowsThem(): void
    {
        $this->assertSame(['https://www.example.com/files/guide.pdf'], $this->suggest('downloadUrl', 'guide'));
        $this->assertSame(['https://www.example.com/files/guide.pdf'], $this->labels('downloadUrl'));
        $this->assertEqualsCanonicalizing(['example.com/page0', 'example.com/page1'], $this->suggest('pageUrl', 'example.com/pa'));

        $systems = $this->suggest('operatingSystem', '');
        $this->assertContains('Ubuntu', $systems);
        $this->assertSame([], array_diff($systems, $this->labels('operatingSystem')));
        $this->assertSame(['Ubuntu'], $this->suggest('operatingSystem', 'ubu'));
    }

    /** the period is checked as for the exploration (release review 2026-11-25) */
    public function testThePeriodIsChecked(): void
    {
        try {
            API::getInstance()->getFilterSuggestions(1, 'decade', self::DAY, 'browser', '');
            $this->fail('expected a refusal');
        } catch (ExplorationException $e) {
            $this->assertSame(400, $e->getCode());
        }
    }

    /** @return string[] the labels of the exploration's table of one dimension */
    private function labels(string $dimension): array
    {
        return API::getInstance()->getExploration(1, 'day', self::DAY, '{"dimensions": ["' . $dimension . '"], "metrics": ["nb_visits"]}')
            ->getColumn('label');
    }

    public function testAnUnknownDimensionIsRefusedNamingIt(): void
    {
        try {
            $this->suggest('noSuchDimension', '');
            $this->fail('expected a refusal');
        } catch (ExplorationException $e) {
            $this->assertSame('FeinwerkDataExploration_ErrorUnknownDimension', $e->getTranslationKey());
            $this->assertSame(400, $e->getCode());
        }
    }

    /** @return string[] */
    private function suggest(string $dimension, string $text): array
    {
        return API::getInstance()->getFilterSuggestions(1, 'day', self::DAY, $dimension, $text);
    }
}
