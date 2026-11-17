<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Plugins\Goals\API as GoalsApi;

/**
 * The goals of a website by id, with their names (goal steps of funnels, goals mapped when copying).
 */
final class SiteGoals
{
    /** @return array<int, string> idgoal => name */
    public static function names(int $idSite): array
    {
        $goals = [];
        foreach (GoalsApi::getInstance()->getGoals($idSite) as $goal) {
            $goals[(int) $goal['idgoal']] = (string) $goal['name'];
        }
        return $goals;
    }
}
