<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Archive;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Archive\HistoryQueue;

/**
 * How far back the history reaches (archive_history_months): the same day, the last day of a shorter month.
 */
class MonthsBeforeTest extends TestCase
{
    public function testSameDayOrTheMonthsLast(): void
    {
        $this->assertSame('2026-02-15', HistoryQueue::monthsBefore('2026-03-15', 1));
        $this->assertSame('2026-02-28', HistoryQueue::monthsBefore('2026-03-31', 1));
        $this->assertSame('2024-02-29', HistoryQueue::monthsBefore('2024-03-31', 1));
        $this->assertSame('2025-11-15', HistoryQueue::monthsBefore('2026-01-15', 2));
        $this->assertSame('2025-03-15', HistoryQueue::monthsBefore('2026-03-15', 12));
    }
}
