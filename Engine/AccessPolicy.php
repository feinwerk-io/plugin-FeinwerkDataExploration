<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Piwik;
use Piwik\Site;
use Piwik\Plugins\FeinwerkDataExploration\SystemSettings;

/**
 * Who may run explorations: never anonymous users (spec 7.5), and only users with at least the role chosen in the
 * settings (default: view) on the site.
 */
final class AccessPolicy
{
    public static function canUse(int $idSite): bool
    {
        try {
            self::check($idSite);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Who may share an exploration with the website's users: at least write access (Peter, 2026-10-20). Users with
     * view access save explorations for themselves only.
     */
    public static function canShare(int $idSite): bool
    {
        try {
            return Piwik::isUserHasWriteAccess($idSite);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function check(int $idSite): void
    {
        Piwik::checkUserIsNotAnonymous();

        switch (self::minimumRole()) {
            case 'superuser':
                Piwik::checkUserHasSuperUserAccess();
                break;
            case 'admin':
                Piwik::checkUserHasAdminAccess($idSite);
                break;
            case 'write':
                Piwik::checkUserHasWriteAccess($idSite);
                break;
            default:
                Piwik::checkUserHasViewAccess($idSite);
        }

        // a roll-up site (RollUpReporting) has no raw data of its own
        if (self::isRollUp($idSite)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorRollUp');
        }
    }

    private static function isRollUp(int $idSite): bool
    {
        try {
            return Site::getTypeFor($idSite) === 'rollup';
        } catch (\Throwable $e) {
            return false;
        }
    }

    private static function minimumRole(): string
    {
        try {
            $role = (string) (new SystemSettings())->minimumRole->getValue();
        } catch (\Throwable $e) {
            $role = 'view';
        }
        return in_array($role, SystemSettings::ROLES, true) ? $role : 'view';
    }
}
