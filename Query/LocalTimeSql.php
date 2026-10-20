<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Query;

/**
 * Turns a UTC datetime column into the site's local time in SQL, exact across DST switches and without MySQL's
 * timezone tables: the offsets valid inside the query window are written into the expression.
 */
final class LocalTimeSql
{
    /**
     * @param string $timezone a Matomo site timezone: an IANA name, "UTC", or a manual offset such as "UTC+5.5"
     */
    public static function expression(string $utcColumn, string $timezone, string $windowStartUtc, string $windowEndUtc): string
    {
        if ($timezone === 'UTC') {
            return $utcColumn;
        }

        if (preg_match('/^UTC([+-]\d+(?:\.\d+)?)$/', $timezone, $m)) {
            return self::addSeconds($utcColumn, (string) (int) round((float) $m[1] * 3600));
        }

        $utc = new \DateTimeZone('UTC');
        $start = (new \DateTimeImmutable($windowStartUtc, $utc))->getTimestamp();
        $end = (new \DateTimeImmutable($windowEndUtc, $utc))->getTimestamp();
        // the first entry is the state at $start, every further entry a switch inside the window
        $transitions = (new \DateTimeZone($timezone))->getTransitions($start, $end);

        if (count($transitions) <= 1) {
            return self::addSeconds($utcColumn, (string) $transitions[0]['offset']);
        }

        $case = 'CASE';
        for ($i = 1; $i < count($transitions); $i++) {
            $switchAt = gmdate('Y-m-d H:i:s', $transitions[$i]['ts']);
            $case .= ' WHEN ' . $utcColumn . " < '" . $switchAt . "' THEN " . $transitions[$i - 1]['offset'];
        }
        $case .= ' ELSE ' . $transitions[count($transitions) - 1]['offset'] . ' END';

        return self::addSeconds($utcColumn, $case);
    }

    private static function addSeconds(string $column, string $seconds): string
    {
        return 'DATE_ADD(' . $column . ', INTERVAL ' . $seconds . ' SECOND)';
    }
}
