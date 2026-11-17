<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\FeinwerkDataExploration\Engine\TrendExplorer;

/**
 * Lines and time units of the trend (trend design 3 and 5).
 *
 * @group FeinwerkDataExploration
 */
class TrendLinesTest extends TestCase
{
    public function testLinesAreClampedToOneToTen(): void
    {
        $this->assertSame(5, TrendExplorer::maxLines(new Limits()));
        $this->assertSame(1, TrendExplorer::maxLines(new Limits(maxTrendLines: 0)));
        $this->assertSame(10, TrendExplorer::maxLines(new Limits(maxTrendLines: 50)));
    }

    /** Days and hours after now have no data yet: no value, not 0 (a line must not drop to zero). */
    public function testUnitsAfterNowAreInTheFuture(): void
    {
        $now = '2026-11-15 14';
        $this->assertFalse(TrendExplorer::isFuture('day', '2026-11-15', '2026-11-01', $now));
        $this->assertTrue(TrendExplorer::isFuture('day', '2026-11-16', '2026-11-01', $now));
        $this->assertFalse(TrendExplorer::isFuture('hour', '14', '2026-11-15', $now));
        $this->assertTrue(TrendExplorer::isFuture('hour', '15', '2026-11-15', $now));
        $this->assertFalse(TrendExplorer::isFuture('hour', '23', '2026-11-14', $now), 'yesterday is over');
        $this->assertTrue(TrendExplorer::isFuture('hour', '0', '2026-11-16', $now));
    }

    public function testUnitsCoverEveryDayOrHour(): void
    {
        $this->assertSame(['2026-03-28', '2026-03-29', '2026-03-30'], TrendExplorer::units('day', '2026-03-28', '2026-03-30'));
        $this->assertSame(['2026-02-28', '2026-03-01'], TrendExplorer::units('day', '2026-02-28', '2026-03-01'));
        $this->assertSame(array_map('strval', range(0, 23)), TrendExplorer::units('hour', '2026-03-29', '2026-03-29'));
    }
}
