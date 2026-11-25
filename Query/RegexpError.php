<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Query;

/**
 * A regular expression the database rejected (the "matches" filter, 2026-11-23): PHP's check before the query accepts
 * PCRE, while MySQL 8 uses ICU, MariaDB PCRE and MySQL 5.7 an older POSIX dialect.
 */
final class RegexpError
{
    /** MariaDB ER_REGEXP_ERROR; MySQL 8 ER_REGEXP_BUFFER_OVERFLOW to ER_REGEXP_INVALID_FLAG */
    private const CODES = [1139, 3685, 3686, 3687, 3688, 3689, 3690, 3691, 3692, 3693, 3694, 3695, 3696, 3697, 3698, 3699];
    private const MESSAGES = ['from regexp', 'regular expression'];

    /**
     * @param object $db a Matomo db adapter (isErrNo())
     */
    public static function is(\Throwable $e, $db): bool
    {
        foreach (self::CODES as $code) {
            if ($db->isErrNo($e, $code)) {
                return true;
            }
        }
        foreach (self::MESSAGES as $message) {
            if (stripos($e->getMessage(), $message) !== false) {
                return true;
            }
        }
        return false;
    }
}
