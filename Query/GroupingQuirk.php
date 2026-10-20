<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Query;

/**
 * MySQL before 8.0 (Matomo 5 supports it from 5.5) splits the groups of a computed key into several rows when ORDER BY
 * sits next to GROUP BY and COUNT(DISTINCT). There the exploration sorts outside the grouping; MariaDB and MySQL 8 run
 * the plain query.
 */
final class GroupingQuirk
{
    public static function applies(string $serverVersion): bool
    {
        if (stripos($serverVersion, 'mariadb') !== false || !preg_match('/^(\d+\.\d+\.\d+)/', $serverVersion, $m)) {
            return false;
        }
        return version_compare($m[1], '8.0.0', '<');
    }
}
