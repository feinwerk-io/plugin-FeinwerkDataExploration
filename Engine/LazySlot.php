<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Concurrency\Lock;

/**
 * One concurrency slot for a whole request, taken at its first query that is not cached: a table with comparison
 * columns runs them one after the other in that slot, so the number of explorations at the same time stays as set.
 * Each further query renews the slot, so it lasts as long as the request (a slot is sized for one query).
 */
final class LazySlot
{
    private ?Lock $lock = null;

    public function __construct(private readonly Limits $limits, private readonly ?ConcurrencyGate $gate = null)
    {
    }

    /** Before each query that runs: takes the slot, or renews it for one more query. */
    public function acquire(): void
    {
        if ($this->lock === null) {
            $this->lock = ($this->gate ?? new ConcurrencyGate())->acquire($this->limits);
            return;
        }
        $this->lock->expireLock(ConcurrencyGate::ttl($this->limits));
    }

    public function release(): void
    {
        if ($this->lock !== null) {
            $this->lock->unlock();
            $this->lock = null;
        }
    }
}
