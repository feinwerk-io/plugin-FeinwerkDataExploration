<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Date;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\FeinwerkDataExploration\Engine\PeriodGuard;

/**
 * @group FeinwerkDataExploration
 */
class PeriodGuardTest extends TestCase
{
    public function testThirtyOneDayRangeAllowed(): void
    {
        $this->check('range', '2026-03-01,2026-03-31', '2026-03-01', '2026-03-31');
        $this->addToAssertionCount(1);
    }

    public function testThirtyTwoDayRangeRejected(): void
    {
        $this->assertRejected('range', '2026-03-01,2026-04-01', '2026-03-01', '2026-04-01', 'FeinwerkDataExploration_ErrorPeriodTooLong', [31]);
    }

    public function testMonthAllowed(): void
    {
        $this->check('month', '2026-03-15', '2026-03-01', '2026-03-31');
        $this->check('week', '2026-03-15', '2026-03-09', '2026-03-15');
        $this->check('day', 'today', '2026-10-19', '2026-10-19');
        $this->addToAssertionCount(1);
    }

    public function testYearRejected(): void
    {
        $this->assertRejected('year', '2026-03-15', '2026-01-01', '2026-12-31', 'FeinwerkDataExploration_ErrorPeriodNotSupported');
    }

    public function testMultiplePeriodRejected(): void
    {
        $this->assertRejected('day', 'last7', '2026-10-13', '2026-10-19', 'FeinwerkDataExploration_ErrorPeriodNotSupported');
        $this->assertRejected('week', '2026-03-01,2026-03-14', '2026-02-23', '2026-03-15', 'FeinwerkDataExploration_ErrorPeriodNotSupported');
    }

    public function testRangeLast7Allowed(): void
    {
        $this->check('range', 'last7', '2026-10-13', '2026-10-19');
        $this->addToAssertionCount(1);
    }

    public function testLimitComesFromLimits(): void
    {
        $this->assertRejected('range', '2026-03-01,2026-03-08', '2026-03-01', '2026-03-08', 'FeinwerkDataExploration_ErrorPeriodTooLong', [7], new Limits(maxDays: 7));
    }

    private function check(string $period, string $date, string $start, string $end, ?Limits $limits = null): void
    {
        PeriodGuard::check($period, $date, Date::factory($start), Date::factory($end), $limits ?? new Limits());
    }

    private function assertRejected(string $period, string $date, string $start, string $end, string $key, ?array $params = null, ?Limits $limits = null): void
    {
        try {
            $this->check($period, $date, $start, $end, $limits);
            $this->fail("expected $key for $period $date");
        } catch (ExplorationException $e) {
            $this->assertSame($key, $e->getTranslationKey());
            if ($params !== null) {
                $this->assertSame($params, $e->getParams());
            }
        }
    }
}
