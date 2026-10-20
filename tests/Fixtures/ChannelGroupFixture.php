<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures;

use Piwik\Common;
use Piwik\Db;
use Piwik\Tests\Framework\Fixture;

/**
 * Four visits with the channel FeinwerkChannelGrouping would have stored (the last one has none). A system test
 * fixture, because that plugin's foreign keys stop the table truncation of integration tests.
 */
class ChannelGroupFixture extends Fixture
{
    public const DATE = '2026-03-27';

    public $dateTime = '2026-03-27 08:00:00';
    public $extraPluginsToLoad = ['FeinwerkChannelGrouping'];

    public static function fcgInstalled(): bool
    {
        return is_file(PIWIK_INCLUDE_PATH . '/plugins/FeinwerkChannelGrouping/plugin.json');
    }

    public function setUp(): void
    {
        self::createSuperUser(false);
        if (!self::siteCreated(1)) {
            self::createWebsite('2026-03-01 00:00:00', 0, 'Shop', 'https://example.com');
        }

        for ($i = 0; $i < 4; $i++) {
            $t = self::getTracker(1, self::DATE . ' 1' . $i . ':00:00', true, true);
            $t->setForceNewVisit();
            $t->setVisitorId(sprintf('c%015d', $i + 1));
            $t->setIp('198.51.100.' . ($i + 1));
            $t->setUrl('https://example.com/page' . $i);
            self::checkResponse($t->doTrackPageView('Page'));
        }
        foreach (['Direct', 'Direct', 'Paid Search', null] as $i => $channel) {
            Db::query('UPDATE ' . Common::prefixTable('log_visit') . ' SET channel_grouping = ? WHERE idvisit = ?', [$channel, $i + 1]);
        }
    }

    public function tearDown(): void
    {
    }
}
