<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Config;

/**
 * Funnels are off until `[FeinwerkDataExploration] funnel = 1` turns them on (Peter, 2026-11-21: "Funnel erstmal raus.
 * das ist noch nicht ausreichend, um es zu veröffentlichen"; hidden behind a switch, not deleted). While off there is
 * no funnel page and no menu entry, getFunnel is refused, saved funnels stay stored but out of the saved list, and no
 * funnel is saved, changed into or copied.
 */
final class FunnelSwitch
{
    private const PLUGIN = 'FeinwerkDataExploration';

    public static function enabled(): bool
    {
        try {
            $section = Config::getInstance()->{self::PLUGIN};
        } catch (\Throwable $e) {
            return false;
        }
        return self::enabledBy(is_array($section) ? $section : []);
    }

    /**
     * Only an explicit on counts: 1, on, yes and true in any case (Matomo's INI reader may hand over PHP true or 1).
     *
     * @param array<string, mixed> $section the plugin's config.ini section
     */
    public static function enabledBy(array $section): bool
    {
        $value = $section['funnel'] ?? null;
        if ($value === true || $value === 1) {
            return true;
        }
        return is_string($value) && in_array(strtolower(trim($value)), ['1', 'on', 'yes', 'true'], true);
    }

    /**
     * @throws ExplorationException while funnels are off
     */
    public static function check(): void
    {
        if (!self::enabled()) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorFunnelOff');
        }
    }
}
