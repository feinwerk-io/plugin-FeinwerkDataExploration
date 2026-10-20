<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Piwik;

/**
 * Messages that end with what the reader can do: super users read the setting they can change ("…SuperUser" key),
 * everyone else whom to ask.
 */
final class RoleText
{
    public static function key(string $key): string
    {
        try {
            return Piwik::hasUserSuperUserAccess() ? $key . 'SuperUser' : $key;
        } catch (\Throwable $e) {
            return $key;
        }
    }
}
