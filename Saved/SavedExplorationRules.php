<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Saved;

use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;

/**
 * What a saved exploration may hold (spec section 13): a name of 1 to 100 characters and the page's state parameters
 * as strings. The state is checked like a link when the exploration is opened (parseState), so here only its shape.
 */
final class SavedExplorationRules
{
    public const MAX_PER_USER_AND_SITE = 100;
    public const MAX_NAME = 100;
    public const STATE_KEYS = [
        'fwdeDims', 'fwdeMetrics', 'fwdeGoal', 'fwdeSort', 'fwdeOrder', 'fwdeLimit', 'fwdeFilters', 'fwdeView', 'fwdeChart',
        // the chart type (trend design 3)
        'fwdeChartType',
        // how the filter groups combine, "all" or "any" (2026-11-20; between the groups since 2026-11-23)
        'fwdeMatch',
        // a funnel (spec 3): its kind and steps; a state without fwdeKind is a free-form table
        'fwdeKind', 'fwdeSteps',
        // the menu place of a saved exploration as a report (saved reports, 2026-11-24); never part of the page's link
        'fwdeMenu',
        // the pivot dimension and how many of its values become columns (rows and pivot, 2026-11-24)
        'fwdePivot', 'fwdePivotLimit',
        // a KPI card (fwdeKind kpi, 2026-11-24): its metric, comparison, line and change (KpiCardSettings)
        'fwdeKpiMetric', 'fwdeKpiCompare', 'fwdeKpiLine', 'fwdeKpiChange',
    ];
    /**
     * Matomo's reporting sections a saved exploration can have its own menu entry in (Peter, 2026-11-24, "C1"):
     * Visitors, Behaviour, Acquisition, Ecommerce (only on e-commerce websites), Goals, Explorations. No key or an
     * empty value: not in the menu.
     */
    public const MENU_SECTIONS = [
        'General_Visitors', 'General_Actions', 'Referrers_Referrers', 'Goals_Ecommerce', 'Goals_Goals',
        'FeinwerkDataExploration_Explorations',
    ];
    public const MENU_EXPLORATIONS = 'FeinwerkDataExploration_Explorations';
    public const MENU_ECOMMERCE = 'Goals_Ecommerce';
    public const KIND_FUNNEL = 'funnel';
    /** a KPI card (2026-11-24): a copy of an exploration's state plus the card's settings */
    public const KIND_KPI = 'kpi';
    /**
     * As much of the TEXT column as one value may take: ten conditions of 1,000 Latin characters fit easily; the same
     * in a non-Latin script (URL-encoded by the page at up to 9 bytes per character) may not, and is refused as too
     * large (filter groups, 2026-11-23; no schema change).
     */
    private const MAX_STATE_VALUE = 60000;
    /** The whole state fits the TEXT column (65,535 bytes). */
    private const MAX_STATE_JSON = 65000;

    public static function name(string $name): string
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > self::MAX_NAME) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorSavedName', [self::MAX_NAME]);
        }
        return $name;
    }

    /**
     * @return array<string, string>
     */
    public static function state(string $json): array
    {
        if (strlen($json) > self::MAX_STATE_JSON) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorSavedTooLarge');
        }
        $state = json_decode($json, true);
        if (!is_array($state) || $state === [] || array_is_list($state)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorSavedState');
        }
        foreach ($state as $key => $value) {
            if (!in_array($key, self::STATE_KEYS, true) || !is_string($value)) {
                throw new ExplorationException('FeinwerkDataExploration_ErrorSavedState');
            }
            if (strlen($value) > self::MAX_STATE_VALUE) {
                throw new ExplorationException('FeinwerkDataExploration_ErrorSavedTooLarge');
            }
            if ($key === 'fwdeKind' && $value !== self::KIND_FUNNEL && $value !== self::KIND_KPI) {
                throw new ExplorationException('FeinwerkDataExploration_ErrorSavedState');
            }
            $allowed = KpiCardSettings::allowed($key);
            if ($allowed !== null && !in_array($value, $allowed, true)) {
                throw new ExplorationException('FeinwerkDataExploration_ErrorKpiSetting', [$key, implode(', ', $allowed)]);
            }
            if ($key === 'fwdeMenu') {
                self::checkMenu($value, 'fwdeMenu');
            }
        }
        return $state;
    }

    /** A menu place as sent: empty (not in the menu) or one of MENU_SECTIONS; the error names the field. */
    public static function checkMenu(string $menu, string $field): void
    {
        if ($menu !== '' && !in_array($menu, self::MENU_SECTIONS, true)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorSavedMenu', [$field, implode(', ', self::MENU_SECTIONS)]);
        }
    }

    /**
     * The menu section of a saved state, null when it is not in the menu (no key, empty, or a value no longer offered).
     *
     * @param array<string, string> $state
     */
    public static function menuOf(array $state): ?string
    {
        $menu = $state['fwdeMenu'] ?? '';
        return in_array($menu, self::MENU_SECTIONS, true) ? $menu : null;
    }
}
