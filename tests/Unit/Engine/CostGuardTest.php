<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Engine\CostGuard;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\FeinwerkDataExploration\Query\QueryRunner;

/**
 * @group FeinwerkDataExploration
 */
class CostGuardTest extends TestCase
{
    public function testCountsTheVisitsOfAWindowOnce(): void
    {
        $runner = self::runner(5);
        $guard = new CostGuard($runner, RememberTest::inMemory(), static fn (string $table) => $table);
        $limits = new Limits(maxVisits: 10);

        $guard->check(1, '2026-03-27 00:00:00', '2026-03-27 23:59:59', $limits, 300);
        $guard->check(1, '2026-03-27 00:00:00', '2026-03-27 23:59:59', $limits, 300);
        $this->assertSame(5, $guard->visits(1, '2026-03-27 00:00:00', '2026-03-27 23:59:59', $limits, 300));

        $this->assertSame(1, $runner->calls);
    }

    public function testRejectsMoreVisitsThanTheThreshold(): void
    {
        $guard = new CostGuard(self::runner(11), RememberTest::inMemory(), static fn (string $table) => $table);

        $this->expectException(ExplorationException::class);
        $guard->check(1, '2026-03-27 00:00:00', '2026-03-27 23:59:59', new Limits(maxVisits: 10), 300);
    }

    public function testAnotherThresholdCountsAgain(): void
    {
        $runner = self::runner(5);
        $guard = new CostGuard($runner, RememberTest::inMemory(), static fn (string $table) => $table);

        $guard->visits(1, '2026-03-27 00:00:00', '2026-03-27 23:59:59', new Limits(maxVisits: 10), 300);
        $guard->visits(1, '2026-03-27 00:00:00', '2026-03-27 23:59:59', new Limits(maxVisits: 20), 300);

        $this->assertSame(2, $runner->calls, 'the count stops at the threshold, so it belongs to it');
    }

    private static function runner(int $visits): QueryRunner
    {
        return new class ($visits) extends QueryRunner {
            public int $calls = 0;

            public function __construct(private readonly int $visits)
            {
            }

            public function fetchAll(array $query): array
            {
                $this->calls++;
                return [['visits' => $this->visits]];
            }
        };
    }
}
