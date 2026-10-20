<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Cache;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;

/**
 * Query results in Matomo's cache (spec 3.5). Raw rows are cached, labels are formatted per request (language).
 */
final class ResultCache
{
    public function key(int $idSite, string $startUtc, string $endUtc, string $segment, ExplorationDefinition $definition, string $pluginVersion): string
    {
        return 'fwde_' . sha1(json_encode([$pluginVersion, $idSite, $startUtc, $endUtc, $segment, $definition->normalized()]));
    }

    /**
     * One hour for closed periods, five minutes while today's data still changes.
     */
    public function ttl(bool $includesToday): int
    {
        return $includesToday ? 300 : 3600;
    }

    public function fetch(string $key): ?array
    {
        $rows = Cache::getLazyCache()->fetch($key);
        return is_array($rows) ? $rows : null;
    }

    public function save(string $key, array $rows, int $ttl): void
    {
        Cache::getLazyCache()->save($key, $rows, $ttl);
    }
}
