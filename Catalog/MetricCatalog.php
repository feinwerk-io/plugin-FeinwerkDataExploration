<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Catalog;

/**
 * The tested positive list of metrics (spec 2.3), in the order the UI lists them.
 */
final class MetricCatalog
{
    public const SCOPE_VISIT = 'visit';
    public const SCOPE_GOAL = 'goal';
    public const SCOPE_ACTION = 'action';

    /** @var array<string, MetricDefinition>|null */
    private ?array $metrics = null;

    /**
     * @return array<string, MetricDefinition>
     */
    public function all(): array
    {
        if ($this->metrics === null) {
            $this->metrics = [];
            foreach (self::definitions() as $entry) {
                [$id, $scope, $nameKey, $actionScopeKey] = $entry;
                $this->metrics[$id] = new MetricDefinition($id, $scope, $nameKey, $actionScopeKey, $entry[4] ?? null);
            }
        }
        return $this->metrics;
    }

    public function get(string $id): ?MetricDefinition
    {
        return $this->all()[$id] ?? null;
    }

    /** What a metric counts: Matomo's own description where it has one without a placeholder, else ours. */
    private const DESCRIPTIONS = [
        'nb_visits' => 'General_ColumnNbVisitsDocumentation',
        'nb_uniq_visitors' => 'General_ColumnNbUniqVisitorsDocumentation',
        'nb_actions' => 'General_ColumnNbActionsDocumentation',
        'bounce_rate' => 'General_ColumnBounceRateDocumentation',
        'avg_time_on_site' => 'General_ColumnAvgTimeOnSiteDocumentation',
        'nb_actions_per_visit' => 'General_ColumnActionsPerVisitDocumentation',
        'nb_conversions' => 'FeinwerkDataExploration_MetricDescriptionConversions',
        'conversion_rate' => 'General_ColumnConversionRateDocumentation',
        'revenue' => 'FeinwerkDataExploration_MetricDescriptionRevenue',
        'nb_pageviews' => 'General_ColumnPageviewsDocumentation',
        'nb_uniq_pageviews' => 'General_ColumnUniquePageviewsDocumentation',
        'nb_events' => 'Events_TotalEventsDocumentation',
        'sum_event_value' => 'Events_TotalValueDocumentation',
        'avg_event_value' => 'FeinwerkDataExploration_MetricDescriptionAvgEventValue',
        'nb_searches' => 'FeinwerkDataExploration_MetricDescriptionSearches',
        'max_actions' => 'FeinwerkDataExploration_MetricDescriptionMaxActions',
        'nb_downloads' => 'FeinwerkDataExploration_MetricDescriptionDownloads',
        'nb_outlinks' => 'FeinwerkDataExploration_MetricDescriptionOutlinks',
    ];

    /**
     * The description of a metric for its tooltip; unique visitors note that an exploration counts them exactly over
     * the whole period (Matomo's archived reports only for single days).
     *
     * @param callable $translate fn(string $key): string
     */
    public static function description(string $id, callable $translate): string
    {
        $text = isset(self::DESCRIPTIONS[$id]) ? $translate(self::DESCRIPTIONS[$id]) : '';
        if ($id === 'nb_uniq_visitors') {
            $text = trim($text . ' ' . $translate('FeinwerkDataExploration_MetricNoteUniqueVisitors'));
        }
        return $text;
    }

    private static function definitions(): array
    {
        return [
            ['nb_visits', self::SCOPE_VISIT, 'General_ColumnNbVisits', 'FeinwerkDataExploration_VisitsWithThisAction'],
            ['nb_uniq_visitors', self::SCOPE_VISIT, 'General_ColumnNbUniqVisitors', 'FeinwerkDataExploration_UniqueVisitorsWithThisAction'],
            ['nb_actions', self::SCOPE_VISIT, 'General_ColumnNbActions', 'FeinwerkDataExploration_ActionsOfTheseVisits'],
            ['bounce_rate', self::SCOPE_VISIT, 'General_ColumnBounceRate', 'FeinwerkDataExploration_BounceRateOfTheseVisits'],
            ['avg_time_on_site', self::SCOPE_VISIT, 'General_ColumnAvgTimeOnSite', 'FeinwerkDataExploration_AvgTimeOnSiteOfTheseVisits'],
            ['nb_actions_per_visit', self::SCOPE_VISIT, 'General_ColumnActionsPerVisit', 'FeinwerkDataExploration_ActionsPerVisitOfTheseVisits'],
            ['max_actions', self::SCOPE_VISIT, 'General_ColumnMaxActions', 'FeinwerkDataExploration_MaxActionsOfTheseVisits'],
            ['nb_conversions', self::SCOPE_GOAL, 'Goals_ColumnConversions', null],
            ['conversion_rate', self::SCOPE_GOAL, 'General_ColumnConversionRate', null],
            ['revenue', self::SCOPE_GOAL, 'General_ColumnRevenue', null],
            ['nb_pageviews', self::SCOPE_ACTION, 'General_ColumnPageviews', null, 'pageview'],
            ['nb_uniq_pageviews', self::SCOPE_ACTION, 'General_ColumnUniquePageviews', null, 'pageview'],
            ['nb_events', self::SCOPE_ACTION, 'Events_TotalEvents', null, 'event'],
            ['sum_event_value', self::SCOPE_ACTION, 'Events_TotalValue', null, 'event'],
            ['avg_event_value', self::SCOPE_ACTION, 'FeinwerkDataExploration_MetricAvgEventValue', null, 'event'],
            ['nb_downloads', self::SCOPE_ACTION, 'General_Downloads', null, 'download'],
            ['nb_outlinks', self::SCOPE_ACTION, 'General_Outlinks', null, 'outlink'],
            ['nb_searches', self::SCOPE_ACTION, 'Actions_ColumnSearches', null, 'search'],
        ];
    }
}
