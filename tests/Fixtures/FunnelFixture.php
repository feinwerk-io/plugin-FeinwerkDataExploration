<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures;

use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tracker\Cache as TrackerCache;

/**
 * Visits with known action sequences for the funnel (spec 8): one site in UTC, one day, one visitor per visit. Times
 * are exact; two actions with the same time are tracked in the listed order (so the second has the higher idlink_va).
 */
class FunnelFixture extends Fixture
{
    public const DATE = '2026-03-27';
    public const GOAL_ORDER = 1;
    public const GOAL_NEWSLETTER = 2;
    private const BASE_URL = 'https://example.com';
    private const CHROME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    private const FIREFOX = 'Mozilla/5.0 (X11; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0';

    /** visit => [user agent, [time, action, ...]]; actions: page path, event category/action/name, search, download, outlink, goal id */
    private const VISITS = [
        'V1' => [self::CHROME, [['08:00:00', 'page', '/products'], ['08:01:00', 'page', '/cart'], ['08:02:00', 'page', '/thanks']]],
        'V2' => [self::CHROME, [['09:00:00', 'page', '/cart'], ['09:01:00', 'page', '/products']]],
        'V3' => [self::CHROME, [['10:00:00', 'page', '/products'], ['10:00:00', 'event', 'Shop', 'Add to cart', 'Shoes']]],
        'V4' => [self::CHROME, [['11:00:00', 'event', 'Shop', 'Add to cart', 'Shoes'], ['11:00:00', 'page', '/products']]],
        'V5' => [self::CHROME, [['12:00:00', 'page', '/products'], ['12:00:30', 'page', '/products/shoes']]],
        'V6' => [self::CHROME, [
            ['13:00:00', 'search', 'boots'], ['13:01:00', 'download', 'https://example.com/files/catalog.pdf'],
            ['13:02:00', 'outlink', 'https://partner.example.org/'],
        ]],
        'V7' => [self::CHROME, [['14:00:00', 'page', '/products'], ['14:01:00', 'goal', self::GOAL_NEWSLETTER]]],
        'V8' => [self::CHROME, [
            ['15:00:00', 'page', '/products'], ['15:01:00', 'page', '/products'], ['15:02:00', 'page', '/cart'],
            ['15:03:00', 'goal', self::GOAL_NEWSLETTER], ['15:04:00', 'goal', self::GOAL_NEWSLETTER],
        ]],
        'V9' => [self::FIREFOX, [['16:00:00', 'page', '/products'], ['16:01:00', 'page', '/cart']]],
        'V10' => [self::CHROME, [['17:00:00', 'event', 'Promo', 'View', 'Tom & Jerry 50%_off']]],
        'V11' => [self::CHROME, [['18:00:00', 'page', '/thanks']]],
        'V12' => [self::CHROME, [['19:00:00', 'page', '/products/shoes']]],
    ];

    public $dateTime = '2026-03-27 08:00:00';

    public function setUp(): void
    {
        // tracking with a custom time needs a valid super user token
        self::createSuperUser(false);
        IPAnonymizer::deactivate();

        if (!self::siteCreated(1)) {
            self::createWebsite('2026-01-01 00:00:00', 0, 'Funnel shop', self::BASE_URL, 1, null, null, 'UTC');
        }
        $goals = GoalsApi::getInstance();
        $goals->addGoal(1, 'Order', 'url', 'thanks', 'contains', false, 0);
        $goals->addGoal(1, 'Newsletter', 'manually', '', 'contains', false, 0, true);
        TrackerCache::deleteCacheWebsiteAttributes(1);
        TrackerCache::clearCacheGeneral();

        $number = 0;
        foreach (self::VISITS as [$userAgent, $actions]) {
            $number++;
            $t = self::getTracker(1, self::DATE . ' ' . $actions[0][0], true, true);
            $t->setVisitorId(sprintf('f%015x', $number));
            $t->setIp('198.51.100.' . $number);
            $t->setUserAgent($userAgent);
            foreach ($actions as $action) {
                $t->setForceVisitDateTime(self::DATE . ' ' . $action[0]);
                switch ($action[1]) {
                    case 'page':
                        $t->setUrl(self::BASE_URL . $action[2]);
                        self::checkResponse($t->doTrackPageView('Page ' . $action[2]));
                        break;
                    case 'event':
                        self::checkResponse($t->doTrackEvent($action[2], $action[3], $action[4]));
                        break;
                    case 'search':
                        self::checkResponse($t->doTrackSiteSearch($action[2], '', 3));
                        break;
                    case 'download':
                        self::checkResponse($t->doTrackAction($action[2], 'download'));
                        break;
                    case 'outlink':
                        self::checkResponse($t->doTrackAction($action[2], 'link'));
                        break;
                    case 'goal':
                        self::checkResponse($t->doTrackGoal($action[2]));
                        break;
                }
            }
        }
    }

    public function tearDown(): void
    {
    }
}
