<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;

/**
 * Three visits on one day whose values contain characters a segment has to carry: a literal "+", percent sequences,
 * commas and semicolons (page URLs, titles, events, downloads, outlinks, site search), and a title Matomo stores
 * HTML-escaped. Matomo stores page URLs with their percent sequences; a "q" parameter would make the page view a site
 * search.
 */
class SpecialValuesFixture extends Fixture
{
    public const DATE = '2026-03-27';
    public const BASE_URL = 'https://example.com';

    /** [start (UTC), actions]: ['page', path, title], ['event', category, action, name], ['download', url], ['outlink', url], ['search', keyword] */
    private const VISITS = [
        ['2026-03-27 08:00:00', [
            ['page', '/list?tag=a+b%2Bc', 'Size 10+ deals'], ['event', 'C++', 'Play', 'a+b%20c'],
            ['download', 'https://example.com/files/a+b.pdf'], ['outlink', 'https://partner.example.org/?q=x%2By'],
            ['search', 'c++'],
        ]],
        ['2026-03-27 09:00:00', [
            ['page', '/caf%C3%A9?x=1%2C2;y=3', '100% sure, really'], ['event', 'Video', 'Play', 'Intro'],
        ]],
        ['2026-03-27 10:00:00', [['page', '/plain', 'Plain'], ['page', '/tips?a=1&b=2', "Tom & Jerry's tips"]]],
    ];

    public function setUp(): void
    {
        self::createSuperUser(false);
        IPAnonymizer::deactivate();
        if (!self::siteCreated(1)) {
            self::createWebsite('2026-03-01 00:00:00', 0, 'Special values', self::BASE_URL);
        }
        foreach (self::VISITS as $i => [$startUtc, $actions]) {
            $t = self::getTracker(1, $startUtc, true, true);
            $t->setForceNewVisit();
            $t->setVisitorId(sprintf('b00000000000000%d', $i + 1));
            $t->setIp('198.51.100.' . ($i + 20));
            $minute = 0;
            foreach ($actions as $action) {
                $t->setForceVisitDateTime(Date::factory($startUtc)->addPeriod($minute++, 'minute')->getDatetime());
                switch ($action[0]) {
                    case 'page':
                        $t->setUrl(self::BASE_URL . $action[1]);
                        self::checkResponse($t->doTrackPageView($action[2]));
                        break;
                    case 'event':
                        self::checkResponse($t->doTrackEvent($action[1], $action[2], $action[3]));
                        break;
                    case 'download':
                        self::checkResponse($t->doTrackAction($action[1], 'download'));
                        break;
                    case 'outlink':
                        self::checkResponse($t->doTrackAction($action[1], 'link'));
                        break;
                    case 'search':
                        self::checkResponse($t->doTrackSiteSearch($action[1]));
                        break;
                }
            }
        }
    }

    public function tearDown(): void
    {
    }
}
