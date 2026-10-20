<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\CustomDimensions\API as CustomDimensionsApi;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tracker\Cache as TrackerCache;

/**
 * Sixteen visits of ten visitors on four days around the DST switch of Europe/Berlin (2026-03-29), covering every
 * dimension of the positive list. No visit crosses midnight site time. Two visits of J lie close to midnight on both
 * sides of the switch (23:30 CET on 03-28, 00:30 CEST on 03-30), so a wrong UTC offset moves them to another day.
 * Visit I views one title and one page author on two URLs. Visit K has a title and a URL with characters Matomo stores
 * differently (titles HTML-escaped, page URLs as they are, also the page URL of an event).
 */
class ExplorationFixture extends Fixture
{
    public const DATES = ['2026-03-27', '2026-03-28', '2026-03-29', '2026-03-30'];
    public const RANGE = '2026-03-27,2026-03-30';
    public const GOAL_CONTACT = 1;
    public const GOAL_BROCHURE = 2;
    public const BASE_URL = 'https://example.com';

    private const UA = [
        'chromeWindows' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'firefoxLinux' => 'Mozilla/5.0 (X11; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0',
        'safariIphone' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1',
        'chromeTablet' => 'Mozilla/5.0 (Linux; Android 13; SM-X700) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'safariMac' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
    ];

    /** visitor => [visitor id, IP, user agent, Accept-Language, resolution, [country, region, city], customer type] */
    private const VISITORS = [
        'A' => ['a000000000000001', '198.51.100.1', 'chromeWindows', 'de-DE,de;q=0.9', [1920, 1080], ['de', 'BY', 'Munich'], 'B2B'],
        'B' => ['a000000000000002', '198.51.100.2', 'firefoxLinux', 'fr-FR', [1366, 768], ['fr', 'IDF', 'Paris'], 'B2C'],
        'C' => ['a000000000000003', '198.51.100.3', 'safariIphone', 'en-US,en;q=0.8', [390, 844], ['us', 'CA', 'San Francisco'], 'B2C'],
        'D' => ['a000000000000004', '198.51.100.4', 'chromeTablet', 'de-DE,de;q=0.9', [1280, 800], ['de', 'BE', 'Berlin'], 'B2B'],
        'E' => ['a000000000000005', '198.51.100.5', 'safariMac', 'en-US,en;q=0.8', [2560, 1440], ['us', 'CA', 'San Francisco'], 'B2B'],
        'F' => ['a000000000000006', '198.51.100.6', 'chromeWindows', 'de-DE,de;q=0.9', [1920, 1080], ['de', 'BY', 'Munich'], 'B2C'],
        'G' => ['a000000000000007', '198.51.100.7', 'chromeTablet', 'fr-FR', [1280, 800], ['fr', 'IDF', 'Paris'], 'B2C'],
        'H' => ['a000000000000008', '198.51.100.8', 'firefoxLinux', 'de-DE,de;q=0.9', [1366, 768], ['de', 'BY', 'Munich'], 'B2B'],
        'I' => ['a000000000000009', '198.51.100.9', 'safariMac', 'en-US,en;q=0.8', [2560, 1440], ['us', 'CA', 'San Francisco'], 'B2C'],
        'J' => ['a00000000000000a', '198.51.100.10', 'chromeWindows', 'de-DE,de;q=0.9', [1920, 1080], ['de', 'BY', 'Munich'], 'B2B'],
        'K' => ['a00000000000000b', '198.51.100.11', 'firefoxLinux', 'en-US,en;q=0.8', [1366, 768], ['us', 'CA', 'San Francisco'], 'B2C'],
    ];

    private const GOOGLE = 'https://www.google.com/search?q=matomo';
    private const WEBSITE = 'https://partner-blog.example.org/post';

    /**
     * [visitor, start (UTC), referrer URL or null, campaign or null, actions]. Actions are one minute apart:
     * ['page', path, title], ['event', category, action, name, value], ['download', url], ['outlink', url],
     * ['search', keyword, result count], ['goal', idgoal, revenue].
     */
    private const VISITS = [
        ['A', '2026-03-27 08:00:00', null, null, [
            ['page', '/', 'Home'], ['page', '/blog/a', 'Blog A'], ['event', 'Video', 'Play', 'Intro', 1.5],
            ['page', '/shop', 'Shop'], ['goal', self::GOAL_CONTACT, 10.0],
        ]],
        ['B', '2026-03-27 09:30:00', self::GOOGLE, null, [['page', '/blog/a', 'Blog A']]],
        ['C', '2026-03-27 11:00:00', self::WEBSITE, null, [
            ['page', '/', 'Home'], ['page', '/blog/b', 'Blog B'], ['download', 'https://example.com/files/brochure.pdf'],
            ['outlink', 'https://partner.example.org/'],
        ]],
        ['D', '2026-03-27 15:00:00', null, 'Spring', [
            ['page', '/shop', 'Shop'], ['event', 'Video', 'Pause', 'Intro', 2.25], ['search', 'matomo', 3],
        ]],
        ['A', '2026-03-28 08:15:00', null, null, [
            ['page', '/blog/b', 'Blog B'], ['page', '/shop', 'Shop'], ['goal', self::GOAL_CONTACT, 10.0],
        ]],
        ['E', '2026-03-28 12:00:00', self::WEBSITE, null, [['page', '/', 'Home'], ['search', 'Preis', 0]]],
        ['B', '2026-03-28 16:45:00', self::GOOGLE, null, [
            ['page', '/blog/a', 'Blog A'], ['page', '/blog/b', 'Blog B'], ['event', 'Video', 'Play', 'Outro', 1.5],
        ]],
        ['J', '2026-03-28 22:30:00', null, null, [['page', '/', 'Home']]],
        ['F', '2026-03-29 08:00:00', null, 'Spring', [
            ['page', '/', 'Home'], ['page', '/shop', 'Shop'], ['event', 'Video', 'Play', 'Intro', 2.25],
            ['download', 'https://example.com/files/brochure.pdf'], ['goal', self::GOAL_CONTACT, 10.0],
        ]],
        ['C', '2026-03-29 10:20:00', null, null, [['page', '/blog/a', 'Blog A'], ['event', 'Video', 'Play', 'Intro', 1.5]]],
        ['G', '2026-03-29 17:00:00', self::GOOGLE, null, [['page', '/shop', 'Shop']]],
        ['J', '2026-03-29 22:30:00', null, null, [['page', '/blog/a', 'Blog A']]],
        ['D', '2026-03-30 07:30:00', null, null, [
            ['page', '/', 'Home'], ['page', '/blog/a', 'Blog A'], ['page', '/blog/b', 'Blog B'],
            ['outlink', 'https://partner.example.org/'],
        ]],
        ['H', '2026-03-30 11:10:00', self::WEBSITE, null, [['page', '/blog/b', 'Blog B']]],
        ['E', '2026-03-30 14:40:00', null, 'Spring', [
            ['page', '/shop', 'Shop'], ['search', 'matomo', 3], ['goal', self::GOAL_CONTACT, 10.0],
        ]],
        ['I', '2026-03-30 16:00:00', null, null, [['page', '/product?id=1', 'Product'], ['page', '/product?id=2', 'Product']]],
        ['K', '2026-03-30 17:30:00', null, null, [
            ['page', '/tips?a=1&b=2', "Tom & Jerry's tips"], ['event', 'Video', 'Play', 'Tips', 1.0],
        ]],
    ];

    /** Page author (action-scope custom dimension) per path; set on pageviews only. */
    private const AUTHORS = ['/blog/a' => 'Anna', '/blog/b' => 'Ben', '/product?id=1' => 'Anna', '/product?id=2' => 'Anna'];

    public $dateTime = '2026-03-27 08:00:00';
    public int $idVisitDimension = 0;
    public int $idActionDimension = 0;
    public int $idInactiveDimension = 0;

    public function setUp(): void
    {
        // tracking with a custom time and location needs a valid super user token
        self::createSuperUser(false);
        IPAnonymizer::deactivate();

        if (!self::siteCreated(1)) {
            self::createWebsite('2026-03-01 00:00:00', 0, 'Exploration shop', self::BASE_URL, 1, null, null, 'Europe/Berlin');
        }

        $goals = GoalsApi::getInstance();
        $goals->addGoal(1, 'Contact', 'manually', '', 'contains', false, 10);
        $goals->addGoal(1, 'Download brochure', 'file', 'brochure.pdf', 'contains', false, 0);

        $customDimensions = CustomDimensionsApi::getInstance();
        $this->idVisitDimension = (int) $customDimensions->configureNewCustomDimension(1, 'Customer type', 'visit', true);
        $this->idActionDimension = (int) $customDimensions->configureNewCustomDimension(1, 'Page author', 'action', true);
        $this->idInactiveDimension = (int) $customDimensions->configureNewCustomDimension(1, 'Old dimension', 'visit', false);
        TrackerCache::deleteCacheWebsiteAttributes(1);
        TrackerCache::clearCacheGeneral();

        foreach (self::VISITS as [$visitor, $startUtc, $referrer, $campaign, $actions]) {
            $this->trackVisit($visitor, $startUtc, $referrer, $campaign, $actions);
        }
    }

    public function tearDown(): void
    {
    }

    private function trackVisit(string $visitor, string $startUtc, ?string $referrer, ?string $campaign, array $actions): void
    {
        [$visitorId, $ip, $userAgent, $language, [$width, $height], [$country, $region, $city], $customerType] = self::VISITORS[$visitor];

        $t = self::getTracker(1, $startUtc, true, true);
        $t->setForceNewVisit();
        $t->setVisitorId($visitorId);
        $t->setIp($ip);
        $t->setUserAgent(self::UA[$userAgent]);
        $t->setBrowserLanguage($language);
        $t->setResolution($width, $height);
        $t->setCountry($country);
        $t->setRegion($region);
        $t->setCity($city);
        if ($referrer !== null) {
            $t->setUrlReferrer($referrer);
        }

        $minute = 0;
        foreach ($actions as $i => $action) {
            $t->setForceVisitDateTime(Date::factory($startUtc)->addPeriod($minute++, 'minute')->getDatetime());
            $t->clearCustomDimensions();
            $t->setCustomDimension($this->idVisitDimension, $customerType);

            switch ($action[0]) {
                case 'page':
                    $url = self::BASE_URL . $action[1];
                    if ($i === 0 && $campaign !== null) {
                        $url .= '?mtm_campaign=' . $campaign;
                    }
                    if (isset(self::AUTHORS[$action[1]])) {
                        $t->setCustomDimension($this->idActionDimension, self::AUTHORS[$action[1]]);
                    }
                    $t->setUrl($url);
                    self::checkResponse($t->doTrackPageView($action[2]));
                    break;
                case 'event':
                    self::checkResponse($t->doTrackEvent($action[1], $action[2], $action[3], $action[4]));
                    break;
                case 'download':
                    self::checkResponse($t->doTrackAction($action[1], 'download'));
                    break;
                case 'outlink':
                    self::checkResponse($t->doTrackAction($action[1], 'link'));
                    break;
                case 'search':
                    self::checkResponse($t->doTrackSiteSearch($action[1], '', $action[2]));
                    break;
                case 'goal':
                    self::checkResponse($t->doTrackGoal($action[1], $action[2]));
                    break;
            }
        }
    }
}
