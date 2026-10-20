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

    private function backend(): LockBackend
    {
        return new class implements LockBackend {
            public array $ttls = [];
            private array $store = [];

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
                return true;
            }
        };
    }
}
