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

    public function __construct(private readonly ?LockBackend $backend = null)
    {
    }

    /**
     * @return Lock the slot; the caller unlocks it when done
     * @throws ExplorationException when all slots are taken
     */
    public function acquire(Limits $limits): Lock
    {
        $backend = $this->backend ?? StaticContainer::get(LockBackend::class);
        for ($slot = 1; $slot <= max(1, $limits->maxConcurrent); $slot++) {
            $lock = new Lock($backend, self::LOCK_NAMESPACE);
            if ($lock->acquireLock('slot' . $slot, self::ttl($limits))) {
                return $lock;
            }
        }
        throw new ExplorationException('FeinwerkDataExploration_ErrorBusy');
    }

    private static function ttl(Limits $limits): int
    {
        return $limits->timeoutSeconds > 0 ? 2 * $limits->timeoutSeconds + 30 : 900;
    }
}
