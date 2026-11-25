<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Saved;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedReportTable;

/**
 * @group FeinwerkDataExploration
 */
class SavedReportTableTest extends TestCase
{
    /** final review: a report waits as long as two statements at the time limit may hold the slots */
    public function testSlotWaitFollowsTheTimeLimit(): void
    {
        $this->assertSame(65, SavedReportTable::slotWait(new Limits(timeoutSeconds: 30), false));
        $this->assertSame(125, SavedReportTable::slotWait(new Limits(timeoutSeconds: 30), true));
        $this->assertSame(20, SavedReportTable::slotWait(new Limits(timeoutSeconds: 5), false));
        $this->assertSame(605, SavedReportTable::slotWait(new Limits(timeoutSeconds: 300), false));
        // no time limit: the plain waits
        $this->assertSame([20, 120], [SavedReportTable::slotWait(new Limits(timeoutSeconds: 0), false),
            SavedReportTable::slotWait(new Limits(timeoutSeconds: 0), true)]);
    }

    public function testSegmentsJoinBothWays(): void
    {
        $this->assertSame('a==1,a==2;b==1', SavedReportTable::segment('a==1,a==2', 'b==1'));
        $this->assertSame('b==1', SavedReportTable::segment('', 'b==1'));
        $this->assertSame('a==1', SavedReportTable::segment('a==1', 'a==1'));
    }
}
