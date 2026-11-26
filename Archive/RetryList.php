<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Archive;

use Piwik\Option;

/**
 * Days an exploration's archive could not be built (design 2026-11-25: a failed or refused day gets no marker, Matomo
 * would never build it again): the hourly task invalidates them again, at most MAX_TRIES times per day. One option per
 * exploration (`fw_dataexploration.archive_retry.<idSaved>`, JSON day => tries).
 */
final class RetryList
{
    public const MAX_TRIES = 3;

    private const PREFIX = 'fw_dataexploration.archive_retry.';

    public function add(int $idSaved, string $day): void
    {
        $days = $this->days($idSaved);
        $days[$day] ??= 0;
        Option::set(self::PREFIX . $idSaved, (string) json_encode($days));
    }

    /** counts one more try of each day */
    public function tried(int $idSaved, array $days): void
    {
        $all = $this->days($idSaved);
        foreach ($days as $day) {
            $all[$day] = ($all[$day] ?? 0) + 1;
        }
        Option::set(self::PREFIX . $idSaved, (string) json_encode($all));
    }

    /** @return array<int, list<string>> per exploration the days with fewer than MAX_TRIES tries (one option read) */
    public function due(): array
    {
        $due = [];
        foreach (Option::getLike(self::PREFIX . '%') as $name => $value) {
            $days = json_decode((string) $value, true);
            if (!is_array($days)) {
                continue;
            }
            $open = array_keys(array_filter($days, static fn ($tries): bool => (int) $tries < self::MAX_TRIES));
            if ($open !== []) {
                sort($open);
                $due[(int) substr((string) $name, strlen(self::PREFIX))] = array_map('strval', $open);
            }
        }
        ksort($due);
        return $due;
    }

    public function forget(int $idSaved): void
    {
        Option::delete(self::PREFIX . $idSaved);
    }

    /** @return array<string, int> */
    private function days(int $idSaved): array
    {
        $days = json_decode((string) Option::get(self::PREFIX . $idSaved), true);
        return is_array($days) ? $days : [];
    }
}
