<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Cache;

/**
 * Remembers a computed value in Matomo's cache for a while, e.g. the visit count of a period: users change metrics and
 * dimensions one after the other, and every change would count the same visits again.
 */
class Remember
{
    /** @var callable fn(string $key): mixed, false when there is nothing */
    private $fetch;
    /** @var callable fn(string $key, mixed $value, int $ttl): void */
    private $save;

    public function __construct(?callable $fetch = null, ?callable $save = null)
    {
        $this->fetch = $fetch ?? static fn (string $key) => Cache::getLazyCache()->fetch($key);
        $this->save = $save ?? static function (string $key, $value, int $ttl): void {
            Cache::getLazyCache()->save($key, $value, $ttl);
        };
    }

    /**
     * @param int $ttl seconds; 0 computes without caching
     * @return mixed the cached or computed value (never false: the cache cannot tell it from "nothing")
     */
    public function get(string $key, int $ttl, callable $compute)
    {
        if ($ttl > 0) {
            $value = ($this->fetch)($key);
            if ($value !== false && $value !== null) {
                return $value;
            }
        }
        $value = $compute();
        if ($ttl > 0) {
            ($this->save)($key, $value, $ttl);
        }
        return $value;
    }
}
