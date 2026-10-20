<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Query;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Query\LocalTimeSql;

/**
 * @group FeinwerkDataExploration
 */
class LocalTimeSqlTest extends TestCase
{
    private const COLUMN = 'log_visit.visit_last_action_time';

    public function testUtcTimezoneReturnsColumn(): void
    {
        $sql = LocalTimeSql::expression(self::COLUMN, 'UTC', '2026-07-01 00:00:00', '2026-07-31 23:59:59');
        $this->assertSame(self::COLUMN, $sql);
    }

    public function testFixedOffsetZone(): void
    {
        $sql = LocalTimeSql::expression(self::COLUMN, 'UTC+5.5', '2026-07-01 00:00:00', '2026-07-31 23:59:59');
        $this->assertSame('DATE_ADD(' . self::COLUMN . ', INTERVAL 19800 SECOND)', $sql);
    }

    public function testNegativeFixedOffsetZone(): void
    {
        $sql = LocalTimeSql::expression(self::COLUMN, 'UTC-3', '2026-07-01 00:00:00', '2026-07-31 23:59:59');
        $this->assertSame('DATE_ADD(' . self::COLUMN . ', INTERVAL -10800 SECOND)', $sql);
    }

    public function testNoTransitionGivesConstantOffset(): void
    {
        $sql = LocalTimeSql::expression(self::COLUMN, 'Europe/Berlin', '2026-06-30 22:00:00', '2026-07-31 21:59:59');
        $this->assertSame('DATE_ADD(' . self::COLUMN . ', INTERVAL 7200 SECOND)', $sql);
    }

    public function testDstSwitchInWindowGivesCase(): void
    {
        $sql = LocalTimeSql::expression(self::COLUMN, 'Europe/Berlin', '2026-03-27 23:00:00', '2026-03-30 21:59:59');
        $this->assertSame(
            'DATE_ADD(' . self::COLUMN . ', INTERVAL CASE WHEN ' . self::COLUMN . " < '2026-03-29 01:00:00' THEN 3600"
            . ' ELSE 7200 END SECOND)',
            $sql
        );
    }

    public function testTwoSwitchesInWindowGiveTwoBranches(): void
    {
        // Sydney: DST ends 2026-04-05 (UTC+11 -> +10) and starts 2026-10-04 (+10 -> +11); a window spanning both.
        $sql = LocalTimeSql::expression(self::COLUMN, 'Australia/Sydney', '2026-04-01 00:00:00', '2026-10-31 00:00:00');
        $this->assertSame(
            'DATE_ADD(' . self::COLUMN . ', INTERVAL CASE WHEN ' . self::COLUMN . " < '2026-04-04 16:00:00' THEN 39600"
            . ' WHEN ' . self::COLUMN . " < '2026-10-03 16:00:00' THEN 36000 ELSE 39600 END SECOND)",
            $sql
        );
    }
}
