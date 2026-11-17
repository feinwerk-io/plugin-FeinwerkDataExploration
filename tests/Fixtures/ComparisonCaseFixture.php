<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures;

use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;

/**
 * Two spellings of one page title ("Shoes", "shoes"): the database groups them as one value (case-insensitive
 * collation), and the spelling a group shows depends on the query. Firefox visits see "Shoes", the Chrome visit "shoes".
 */
class ComparisonCaseFixture extends Fixture
{
    public const DATE = '2026-03-27';
    private const VISITS = [
        ['08:00:00', 'Mozilla/5.0 (X11; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0', '/a', 'Shoes'],
        ['09:00:00', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', '/b', 'shoes'],
        ['10:00:00', 'Mozilla/5.0 (X11; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0', '/c', 'Shoes'],
    ];

    public $dateTime = '2026-03-27 08:00:00';

    public function setUp(): void
    {
        self::createSuperUser(false);
        IPAnonymizer::deactivate();
        if (!self::siteCreated(1)) {
            self::createWebsite('2026-01-01 00:00:00', 0, 'Case shop', 'https://example.com', 1, null, null, 'UTC');
        }
        foreach (self::VISITS as $i => [$time, $userAgent, $path, $title]) {
            $t = self::getTracker(1, self::DATE . ' ' . $time, true, true);
            $t->setVisitorId(sprintf('c%015x', $i + 1));
            $t->setIp('198.51.100.' . ($i + 1));
            $t->setUserAgent($userAgent);
            $t->setForceVisitDateTime(self::DATE . ' ' . $time);
            $t->setUrl('https://example.com' . $path);
            self::checkResponse($t->doTrackPageView($title));
        }
    }

    public function tearDown(): void
    {
    }
}
