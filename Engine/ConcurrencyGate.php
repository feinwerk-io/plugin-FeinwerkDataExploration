<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend;
use Piwik\Container\StaticContainer;

/**
 * At most Limits::$maxConcurrent explorations run at the same time on an instance (spec 3.6): one Matomo lock per
 * slot. A slot expires after twice the query time limit (cost check and exploration) plus a margin, so a crashed
 * request frees it.
 */
final class ConcurrencyGate
{
    public const LOCK_NAMESPACE = 'fwDataExploration';

    private const RETRY_MICROSECONDS = 250000;

    /** @var callable fn(): void the pause between two rounds of tries */
    private $sleep;

    public function __construct(private readonly ?LockBackend $backend = null, ?callable $sleep = null)
    {
        $this->sleep = $sleep ?? static function (): void {
            usleep(self::RETRY_MICROSECONDS);
        };
    }

    /**
     * @return Lock the slot; the caller unlocks it when done
     * @throws ExplorationException when all slots are taken
     */
    public function acquire(Limits $limits): Lock
    {
        $backend = $this->backend ?? StaticContainer::get(LockBackend::class);
        // with a wait (saved reports) four rounds a second until it passes, the page tries once
        $rounds = 1 + $limits->slotWait * (int) (1000000 / self::RETRY_MICROSECONDS);
        for ($round = 1; $round <= $rounds; $round++) {
            for ($slot = 1; $slot <= max(1, $limits->maxConcurrent); $slot++) {
                $lock = new Lock($backend, self::LOCK_NAMESPACE);
                if ($lock->acquireLock('slot' . $slot, self::ttl($limits))) {
                    return $lock;
                }
            }
            if ($round < $rounds) {
                ($this->sleep)();
            }
        }
        // every slot taken: a server condition, the client may retry later (503, not 400)
        throw new ExplorationException('FeinwerkDataExploration_ErrorBusy', [], 503);
    }

    /** Seconds a slot lasts: long enough for one query at the time limit, renewed before each further one (LazySlot). */
    public static function ttl(Limits $limits): int
    {
        return $limits->timeoutSeconds > 0 ? 2 * $limits->timeoutSeconds + 30 : 900;
    }
}
