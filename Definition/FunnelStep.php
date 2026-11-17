<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Definition;

use Piwik\Piwik;

/**
 * One step of a funnel (spec 3): a text condition on one kind of action, a goal of the website, or any goal. Goal steps
 * have no operator and no value; "anyGoal" has no goal id.
 */
final class FunnelStep
{
    public const TYPES = [
        'pageUrl', 'pageTitle', 'eventCategory', 'eventAction', 'eventName', 'siteSearch', 'download', 'outlink', 'goal', 'anyGoal',
    ];
    /** Matomo stores these URLs without scheme and "www." (log_action.url_prefix) */
    public const URL_TYPES = ['pageUrl', 'download', 'outlink'];
    public const GOAL_TYPES = ['goal', 'anyGoal'];
    public const OPERATORS = [
        'pageUrl' => ['contains', 'equals', 'startsWith'],
        'pageTitle' => ['contains', 'equals', 'startsWith'],
        'eventCategory' => ['contains', 'equals'],
        'eventAction' => ['contains', 'equals'],
        'eventName' => ['contains', 'equals'],
        'siteSearch' => ['contains', 'equals'],
        'download' => ['contains', 'equals'],
        'outlink' => ['contains', 'equals'],
    ];

    /** translation keys of the text types and operators, written out so the translations test finds them */
    private const TYPE_KEYS = [
        'pageUrl' => 'FeinwerkDataExploration_FunnelTypePageUrl',
        'pageTitle' => 'FeinwerkDataExploration_FunnelTypePageTitle',
        'eventCategory' => 'FeinwerkDataExploration_FunnelTypeEventCategory',
        'eventAction' => 'FeinwerkDataExploration_FunnelTypeEventAction',
        'eventName' => 'FeinwerkDataExploration_FunnelTypeEventName',
        'siteSearch' => 'FeinwerkDataExploration_FunnelTypeSiteSearch',
        'download' => 'FeinwerkDataExploration_FunnelTypeDownload',
        'outlink' => 'FeinwerkDataExploration_FunnelTypeOutlink',
    ];
    private const OPERATOR_KEYS = [
        'contains' => 'FeinwerkDataExploration_OperatorContains',
        'equals' => 'FeinwerkDataExploration_OperatorEquals',
        'startsWith' => 'FeinwerkDataExploration_OperatorStartsWith',
    ];

    public function __construct(
        public readonly string $type,
        public readonly string $operator,
        public readonly string $value,
        public readonly ?int $idGoal,
        public readonly string $goalName = ''
    ) {
    }

    public function isGoal(): bool
    {
        return in_array($this->type, self::GOAL_TYPES, true);
    }

    /** What the user sees for the step, e.g. 'Page URL contains "/shop"' or 'Goal "Order"'. */
    public function label(): string
    {
        if ($this->type === 'anyGoal') {
            return Piwik::translate('FeinwerkDataExploration_FunnelTypeAnyGoal');
        }
        if ($this->type === 'goal') {
            return Piwik::translate('FeinwerkDataExploration_FunnelGoalLabel', [$this->goalName]);
        }
        return Piwik::translate('FeinwerkDataExploration_FunnelStepLabel', [
            Piwik::translate(self::TYPE_KEYS[$this->type]),
            Piwik::translate(self::OPERATOR_KEYS[$this->operator]),
            $this->value,
        ]);
    }
}
