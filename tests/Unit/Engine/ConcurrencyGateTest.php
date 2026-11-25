<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Concurrency\LockBackend;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ConcurrencyGate;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;

/**
 * @group FeinwerkDataExploration
 */
class ConcurrencyGateTest extends TestCase
{
    public function testTtlIsTwiceTheTimeoutPlusMargin(): void
    {
        // the cost check and the exploration each may take up to the time limit
        $backend = $this->backend();
        (new ConcurrencyGate($backend))->acquire(new Limits(timeoutSeconds: 30));

        $this->assertSame([90], $backend->ttls);
    }

    public function testTtlWithoutTimeout(): void
    {
        $backend = $this->backend();
        (new ConcurrencyGate($backend))->acquire(new Limits(timeoutSeconds: 0));

        $this->assertSame([900], $backend->ttls);
    }

    /** saved reports (2026-11-24): a report waits for a slot the page would answer "busy" for */
    public function testGateWaitsForAFreedSlot(): void
    {
        $backend = $this->backend(2);
        $sleeps = 0;
        $gate = new ConcurrencyGate($backend, function () use (&$sleeps, $backend): void {
            $sleeps++;
            if ($sleeps === 3) {
                $backend->free();
            }
        });
        $this->assertNotNull($gate->acquire((new Limits(maxConcurrent: 2))->withSlotWait(20)));
        $this->assertSame(3, $sleeps);
    }

    public function testGateGivesUpAfterTheWait(): void
    {
        $sleeps = 0;
        $gate = new ConcurrencyGate($this->backend(2), function () use (&$sleeps): void {
            $sleeps++;
        });
        try {
            $gate->acquire((new Limits(maxConcurrent: 2))->withSlotWait(2));
            $this->fail('no slot, yet acquired');
        } catch (ExplorationException $e) {
            $this->assertSame([503, 8], [$e->getCode(), $sleeps], 'four tries a second');
        }
    }

    public function testPageDoesNotWait(): void
    {
        $sleeps = 0;
        $gate = new ConcurrencyGate($this->backend(2), function () use (&$sleeps): void {
            $sleeps++;
        });
        try {
            $gate->acquire(new Limits(maxConcurrent: 2));
            $this->fail('no slot, yet acquired');
        } catch (ExplorationException $e) {
            $this->assertSame([503, 0], [$e->getCode(), $sleeps]);
        }
        $this->assertSame(0, (new Limits())->slotWait);
        $this->assertSame([7, 20], [(new Limits(maxRows: 7))->withSlotWait(20)->maxRows, (new Limits())->withSlotWait(20)->slotWait]);
    }

    private function backend(int $taken = 0): LockBackend
    {
        // $taken: that many slots are held by others until free()
        return new class ($taken) implements LockBackend {
            public array $ttls = [];
            private array $store = [];

            public function __construct(private int $taken)
            {
            }

            public function free(): void
            {
                $this->taken = 0;
            }

            public function getKeysMatchingPattern($pattern)
            {
                return array_keys($this->store);
            }

            public function setIfNotExists($lockKey, $lockValue, $ttlInSeconds)
            {
                if ($this->taken > 0) {
                    return false;
                }
                $this->ttls[] = $ttlInSeconds;
                $this->store[$lockKey] = $lockValue;
                return true;
            }

            public function get($lockKey)
            {
                return $this->store[$lockKey] ?? null;
            }

            public function deleteIfKeyHasValue($lockKey, $lockValue)
            {
                unset($this->store[$lockKey]);
                return true;
            }

            public function expireIfKeyHasValue($lockKey, $lockValue, $ttlInSeconds)
            {
                return true;
            }
        };
    }
}
