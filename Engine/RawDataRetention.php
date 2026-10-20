<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Date;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Site;

/**
 * The earliest day whose raw data is still complete when "delete old raw data" is active (spec 3.7).
 */
final class RawDataRetention
{
    /**
     * @return string|null Y-m-d, or null when raw data is kept
     */
    public static function earliestCompleteDay(int $idSite): ?string
    {
        $settings = PrivacyManager::getPurgeDataSettings();
        if (empty($settings['delete_logs_enable']) || (int) ($settings['delete_logs_older_than'] ?? 0) < 1) {
            return null;
        }

        // Matomo deletes visits older than today (UTC) minus the given days; that day itself may be partly gone
        $earliest = Date::factory('today')->subDay((int) $settings['delete_logs_older_than'] - 1);
        $created = Date::factory(Site::getCreationDateFor($idSite))->setTime('00:00:00');

        return ($created->isLater($earliest) ? $created : $earliest)->toString();
    }
}
