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
    ];
    /** Five filters of 1,000 characters, URL-encoded by the page at up to 9 bytes each, with targets and operators. */
    private const MAX_STATE_VALUE = 46000;
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
        $state = strlen($json) <= self::MAX_STATE_JSON ? json_decode($json, true) : null;
        if (!is_array($state) || $state === [] || array_is_list($state)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorSavedState');
        }
        foreach ($state as $key => $value) {
            if (!in_array($key, self::STATE_KEYS, true) || !is_string($value) || strlen($value) > self::MAX_STATE_VALUE) {
                throw new ExplorationException('FeinwerkDataExploration_ErrorSavedState');
            }
        }
        return $state;
    }
}
