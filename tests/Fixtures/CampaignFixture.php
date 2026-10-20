<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;

/**
 * Campaign visits for MarketingCampaignsReporting (MCR): the same campaign in two spellings, a campaign without
 * medium, a visit with only a source (MCR still makes it a campaign visit) and two visits without a campaign.
 * Only usable when MCR is in the Matomo under test (see mcrInstalled()).
 */
class CampaignFixture extends Fixture
{
    public const DATE = '2026-03-27';
    public const BASE_URL = 'https://example.com';

    /** [utm query or null, referrer URL or null] */
    private const VISITS = [
        ['utm_campaign=Spring&utm_source=Newsletter&utm_medium=Email', null],
        ['utm_campaign=spring&utm_source=newsletter&utm_medium=email', null],
        ['utm_campaign=Spring&utm_source=Newsletter&utm_medium=Email', null],
        ['utm_campaign=Autumn&utm_source=Google&utm_medium=cpc', null],
        ['utm_campaign=Autumn&utm_source=Partner', null],
        ['utm_source=facebook', 'https://www.facebook.com/'],
        [null, null],
        [null, 'https://www.google.com/search?q=matomo'],
    ];

    public $dateTime = '2026-03-27 08:00:00';
    public $extraPluginsToLoad = ['MarketingCampaignsReporting'];

    public static function mcrInstalled(): bool
    {
        return is_file(PIWIK_INCLUDE_PATH . '/plugins/MarketingCampaignsReporting/plugin.json');
    }

    public function setUp(): void
    {
        self::createSuperUser(false);
        if (!self::siteCreated(1)) {
            self::createWebsite('2026-03-01 00:00:00', 0, 'Campaign shop', self::BASE_URL);
        }

        foreach (self::VISITS as $i => [$query, $referrer]) {
            $start = Date::factory(self::DATE . ' 08:00:00')->addPeriod($i * 10, 'minute');
            $t = self::getTracker(1, $start->getDatetime(), true, true);
            $t->setForceNewVisit();
            $t->setVisitorId(sprintf('b%015d', $i + 1));
            $t->setIp('198.51.100.' . ($i + 20));
            if ($referrer !== null) {
                $t->setUrlReferrer($referrer);
            }
            $t->setUrl(self::BASE_URL . '/landing' . ($query === null ? '' : '?' . $query));
            self::checkResponse($t->doTrackPageView('Landing'));
        }
    }

    public function tearDown(): void
    {
    }
}
