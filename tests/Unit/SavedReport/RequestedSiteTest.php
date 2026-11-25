<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\SavedReport;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\SavedReport\SavedReports;

/**
 * The website of a report list request (saved reports): idSite, or a single idSites. Matomo 6 no longer trims request
 * values (release check 2026-11-25).
 *
 * @group FeinwerkDataExploration
 */
class RequestedSiteTest extends TestCase
{
    public function tearDown(): void
    {
        unset($_GET['idSite'], $_GET['idSites']);
        parent::tearDown();
    }

    public function testIdSiteFirst(): void
    {
        $_GET['idSite'] = '4';
        $_GET['idSites'] = '7';
        $this->assertSame(4, SavedReports::requestedSite());
    }

    public function testASingleIdSitesAlsoWithSpaces(): void
    {
        foreach (['3' => 3, ' 3 ' => 3, '3,4' => 0, '' => 0, 'all' => 0] as $value => $expected) {
            $_GET['idSites'] = (string) $value;
            $this->assertSame($expected, SavedReports::requestedSite(), var_export($value, true));
        }
        $_GET['idSites'] = ['5'];
        $this->assertSame(5, SavedReports::requestedSite());
    }
}
