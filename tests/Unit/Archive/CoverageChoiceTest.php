<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Archive;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedReportReader;

/**
 * Which archive answers a long period (sandbox check 2026-11-26): Matomo 5 rebuilds a period that contains today for
 * all plugins without a plugin's newer partial day archives, so the current year lost the explorations' records; a
 * range of the same days is built per plugin and holds them. The one that covers more answers.
 */
class CoverageChoiceTest extends TestCase
{
    public function testTheRangeWhenItCoversMore(): void
    {
        $year = ['year', '2026-11-26', 0, 280];
        $range = ['range', '2026-01-01,2026-11-26', 280, 280];
        $this->assertSame($range, ArchivedReportReader::better($year, $range));
    }

    public function testThePeriodWhenTheRangeIsNoBetter(): void
    {
        $year = ['year', '2026-11-26', 100, 280];
        $this->assertSame($year, ArchivedReportReader::better($year, ['range', '2026-01-01,2026-11-26', 100, 280]));
        $this->assertSame($year, ArchivedReportReader::better($year, ['range', '2026-01-01,2026-11-26', 90, 280]));
        // nothing needed in the range (no visits): the period stays
        $this->assertSame($year, ArchivedReportReader::better($year, ['range', '2026-01-01,2026-11-26', 0, 0]));
    }
}
