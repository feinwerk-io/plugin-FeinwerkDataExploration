<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Remember;

/**
 * @group FeinwerkDataExploration
 */
class RememberTest extends TestCase
{
    public function testComputesOnceForOneKey(): void
    {
        $remember = self::inMemory();
        $calls = 0;
        $compute = function () use (&$calls) {
            $calls++;
            return 42;
        };

        $this->assertSame(42, $remember->get('a', 60, $compute));
        $this->assertSame(42, $remember->get('a', 60, $compute));
        $this->assertSame(1, $calls);
    }

    public function testComputesAgainForAnotherKeyAndWithoutTtl(): void
    {
        $remember = self::inMemory();
        $calls = 0;
        $compute = function () use (&$calls) {
            return ++$calls;
        };

        $this->assertSame(1, $remember->get('a', 60, $compute));
        $this->assertSame(2, $remember->get('b', 60, $compute));
        // a TTL of 0 means: do not cache
        $this->assertSame(3, $remember->get('c', 0, $compute));
        $this->assertSame(4, $remember->get('c', 0, $compute));
    }

    public static function inMemory(): Remember
    {
        $store = [];
        return new Remember(
            static function (string $key) use (&$store) {
                return $store[$key] ?? false;
            },
            static function (string $key, $value, int $ttl) use (&$store): void {
                $store[$key] = $value;
            }
        );
    }
}
