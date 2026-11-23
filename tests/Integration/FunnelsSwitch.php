<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Config;

/**
 * Turns funnels on or off in the test's config ([FeinwerkDataExploration] funnel = 1; off by default since
 * 2026-11-21). Call funnels(false) in tearDown, so no other test inherits the switch.
 */
trait FunnelsSwitch
{
    private static function funnels(bool $on): void
    {
        $config = Config::getInstance();
        $section = $config->FeinwerkDataExploration;
        $section = is_array($section) ? $section : [];
        if ($on) {
            $section['funnel'] = '1';
        } else {
            unset($section['funnel']);
        }
        $config->FeinwerkDataExploration = $section;
    }
}
