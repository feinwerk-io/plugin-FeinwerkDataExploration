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
use Piwik\Plugins\FeinwerkDataExploration\Engine\LazySlot;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;

/**
 * One slot per request: taken at the first query, renewed before every further one (a request with comparison columns
 * runs several queries of up to the time limit each, longer than one slot lasts).
 *
 * @group FeinwerkDataExploration
 */
class LazySlotTest extends TestCase
{
    public function testTheSlotIsRenewedBeforeEveryFurtherQuery(): void
    {
        $backend = $this->backend();
        $slot = new LazySlot(new Limits(timeoutSeconds: 30), new ConcurrencyGate($backend));

        $slot->acquire();
        $slot->acquire();
        $slot->acquire();

        $this->assertSame([90], $backend->ttls, 'taken once');
        $this->assertSame([90, 90], $backend->renewals, 'renewed before the second and third query');
        $slot->release();
        $this->assertSame([], $backend->store);
    }

    private function backend(): LockBackend
    {
        return new class implements LockBackend {
            public array $ttls = [];
            public array $renewals = [];
            public array $store = [];

            public function getKeysMatchingPattern($pattern)
            {
                return array_keys($this->store);
            }

            public function setIfNotExists($lockKey, $lockValue, $ttlInSeconds)
            {
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
                $this->renewals[] = $ttlInSeconds;
                return true;
            }
        };
    }
}
