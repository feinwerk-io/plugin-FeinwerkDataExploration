<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Catalog;

use Piwik\Plugins\FeinwerkDataExploration\Engine\SiteGoals;
use Piwik\Common;
use Piwik\Site;

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

    /** @var callable fn(int $idSite): array<int, string> idgoal => name */
    private $goalsOf;

    /** @var callable fn(int $idSite): bool */
    private $ecommerceOf;

    /** @var array<int, array<string, MetricDefinition>> by website */
    private array $bySite = [];

    /** unit and aggregation of the fixed metrics (more metrics, 2026-11-24) */
    private const META = [
        'nb_visits' => ['count', 'sum'], 'nb_uniq_visitors' => ['count', 'distinct'], 'nb_actions' => ['count', 'sum'],
        'bounce_rate' => ['rate', 'ratio'], 'avg_time_on_site' => ['seconds', 'average'],
        'nb_actions_per_visit' => ['number', 'average'], 'max_actions' => ['count', 'max'],
        'sum_visit_length' => ['seconds', 'sum'], 'nb_conversions' => ['count', 'sum'], 'conversion_rate' => ['rate', 'ratio'],
        'revenue' => ['money', 'sum'], 'nb_visits_converted' => ['count', 'sum'], 'revenue_per_visit' => ['money', 'average'],
        'nb_pageviews' => ['count', 'sum'], 'nb_uniq_pageviews' => ['count', 'distinct'], 'nb_events' => ['count', 'sum'],
        'sum_event_value' => ['number', 'sum'], 'avg_event_value' => ['number', 'average'], 'nb_downloads' => ['count', 'sum'],
        'nb_outlinks' => ['count', 'sum'], 'nb_searches' => ['count', 'sum'],
    ];

    /** per-goal and e-commerce metrics in Matomo's naming: goal key => metric => [unit, aggregation, name key] */
    private const GOAL_METRICS = [
        'goal' => [
            'nb_conversions' => ['count', 'sum', 'Goals_ColumnConversions'],
            'conversion_rate' => ['rate', 'ratio', 'General_ColumnConversionRate'],
            'revenue' => ['money', 'sum', 'General_ColumnRevenue'],
        ],
        'ecommerceOrder' => [
            'nb_conversions' => ['count', 'sum', 'Ecommerce_Orders'],
            'revenue' => ['money', 'sum', 'FeinwerkDataExploration_MetricOrderRevenue'],
            'items' => ['count', 'sum', 'General_PurchasedProducts'],
            'avg_order_revenue' => ['money', 'average', 'General_AverageOrderValue'],
            'conversion_rate' => ['rate', 'ratio', 'FeinwerkDataExploration_MetricEcommerceConversionRate'],
        ],
        'ecommerceAbandonedCart' => [
            'nb_conversions' => ['count', 'sum', 'General_AbandonedCarts'],
            'revenue' => ['money', 'sum', 'FeinwerkDataExploration_MetricAbandonedCartRevenue'],
        ],
    ];

    private const GOAL_ID = '/^goal_(\d+|ecommerceOrder|ecommerceAbandonedCart)_([a-z_]+)$/';

    /**
     * @param callable|null $goalsOf fn(int $idSite): array<int, string> idgoal => name, in Matomo's order
     * @param callable|null $ecommerceOf fn(int $idSite): bool
     */
    public function __construct(?callable $goalsOf = null, ?callable $ecommerceOf = null)
    {
        $this->goalsOf = $goalsOf ?? [SiteGoals::class, 'names'];
        $this->ecommerceOf = $ecommerceOf ?? static fn (int $idSite): bool => Site::isEcommerceEnabledFor($idSite);
    }

    /**
     * The metrics in the order the UI lists them: the fixed ones, and with a website its goals' metrics (in Matomo's
     * goal order) and, on an e-commerce website, the e-commerce metrics.
     *
     * @return array<string, MetricDefinition>
     */
    public function all(?int $idSite = null): array
    {
        if ($this->metrics === null) {
            $this->metrics = [];
            foreach (self::definitions() as $entry) {
                [$id, $scope, $nameKey, $actionScopeKey] = $entry;
                [$unit, $aggregation] = self::META[$id];
                $group = ['visit' => 'visits', 'goal' => 'goals', 'action' => 'actions'][$scope];
                $this->metrics[$id] = new MetricDefinition($id, $scope, $nameKey, $actionScopeKey, $entry[4] ?? null, $unit, $aggregation, $group);
            }
        }
        if ($idSite === null) {
            return $this->metrics;
        }
        if (!isset($this->bySite[$idSite])) {
            $all = $this->metrics;
            // Matomo stores goal names HTML-escaped
            $names = array_map(static fn ($name): string => Common::unsanitizeInputValue((string) $name), ($this->goalsOf)($idSite));
            $count = array_count_values($names);
            foreach ($names as $idGoal => $name) {
                // two goals with one name (Matomo allows it) stay apart in export columns and list headings
                $name = $count[$name] > 1 ? $name . ' (' . $idGoal . ')' : $name;
                foreach (array_keys(self::GOAL_METRICS['goal']) as $metric) {
                    $definition = self::goalMetric((string) $idGoal, $metric, $name);
                    $all[$definition->id] = $definition;
                }
            }
            if (($this->ecommerceOf)($idSite)) {
                foreach (['ecommerceOrder', 'ecommerceAbandonedCart'] as $goalKey) {
                    foreach (array_keys(self::GOAL_METRICS[$goalKey]) as $metric) {
                        $definition = self::goalMetric($goalKey, $metric, null);
                        $all[$definition->id] = $definition;
                    }
                }
            }
            $this->bySite[$idSite] = $all;
        }
        return $this->bySite[$idSite];
    }

    /**
     * A metric by its id. With a website only the metrics that website has (a deleted goal's metric is unknown); without
     * one a per-goal or e-commerce id is resolved by its pattern, enough for scope, unit and aggregation.
     */
    public function get(string $id, ?int $idSite = null): ?MetricDefinition
    {
        // a fixed metric needs no look at the website's goals
        if (isset($this->all()[$id])) {
            return $this->all()[$id];
        }
        // only an id in Matomo's goal or e-commerce naming needs the website's goals
        if (preg_match(self::GOAL_ID, $id, $m) !== 1 || self::meta($id) === null) {
            return null;
        }
        if ($idSite !== null) {
            return $this->all($idSite)[$id] ?? null;
        }
        return self::goalMetric($m[1], $m[2], null);
    }

    /**
     * A metric by its id alone, without a website: the fixed metrics and Matomo's goal and e-commerce patterns, enough
     * for the rules derived from unit and aggregation (shares, whole numbers, zero for a missing row).
     */
    /**
     * Whether less of the metric is better (KPI card colours, 2026-11-24): the bounce rate and abandoned carts with their
     * value; more is better for every other metric.
     */
    public static function lowerIsBetter(string $id): bool
    {
        return $id === 'bounce_rate' || str_starts_with($id, 'goal_ecommerceAbandonedCart_');
    }

    public static function byId(string $id): ?MetricDefinition
    {
        static $catalog = null;
        $catalog ??= new self(static fn (int $idSite): array => [], static fn (int $idSite): bool => false);
        return $catalog->get($id);
    }

    /**
     * Unit and aggregation of a metric by its id alone (fixed ids and Matomo's goal and e-commerce patterns), null for an
     * unknown id. The page derives the same from the same table (vue/src/metricMeta.ts).
     *
     * @return array{unit: string, aggregation: string}|null
     */
    public static function meta(string $id): ?array
    {
        if (isset(self::META[$id])) {
            return ['unit' => self::META[$id][0], 'aggregation' => self::META[$id][1]];
        }
        if (preg_match(self::GOAL_ID, $id, $m) !== 1) {
            return null;
        }
        $entry = self::GOAL_METRICS[ctype_digit($m[1]) ? 'goal' : $m[1]][$m[2]] ?? null;
        return $entry === null ? null : ['unit' => $entry[0], 'aggregation' => $entry[1]];
    }

    private static function goalMetric(string $goalKey, string $metric, ?string $goalName): MetricDefinition
    {
        $type = ctype_digit($goalKey) ? 'goal' : $goalKey;
        [$unit, $aggregation, $nameKey] = self::GOAL_METRICS[$type][$metric];
        return new MetricDefinition(
            'goal_' . $goalKey . '_' . $metric,
            self::SCOPE_GOAL,
            $nameKey,
            null,
            null,
            $unit,
            $aggregation,
            $type === 'goal' ? 'goals' : 'ecommerce',
            $goalKey,
            $goalName
        );
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
        'sum_visit_length' => 'FeinwerkDataExploration_MetricDescriptionSumVisitLength',
        'nb_visits_converted' => 'FeinwerkDataExploration_MetricDescriptionVisitsConverted',
        'revenue_per_visit' => 'FeinwerkDataExploration_MetricDescriptionRevenuePerVisit',
        'goal_ecommerceOrder_nb_conversions' => 'FeinwerkDataExploration_MetricDescriptionOrders',
        'goal_ecommerceOrder_revenue' => 'FeinwerkDataExploration_MetricDescriptionOrderRevenue',
        'goal_ecommerceOrder_items' => 'Goals_ColumnPurchasedProductsDocumentation',
        'goal_ecommerceOrder_avg_order_revenue' => 'Goals_ColumnAverageOrderRevenueDocumentation',
        'goal_ecommerceOrder_conversion_rate' => 'FeinwerkDataExploration_MetricDescriptionEcommerceConversionRate',
        'goal_ecommerceAbandonedCart_nb_conversions' => 'FeinwerkDataExploration_MetricDescriptionAbandonedCarts',
        'goal_ecommerceAbandonedCart_revenue' => 'FeinwerkDataExploration_MetricDescriptionAbandonedCartRevenue',
    ];

    /** a goal's metrics: the description names the goal */
    private const GOAL_DESCRIPTIONS = [
        'nb_conversions' => 'FeinwerkDataExploration_MetricDescriptionGoalConversions',
        'conversion_rate' => 'FeinwerkDataExploration_MetricDescriptionGoalConversionRate',
        'revenue' => 'FeinwerkDataExploration_MetricDescriptionGoalRevenue',
    ];

    /**
     * The description of a metric for its tooltip; unique visitors note that an exploration counts them exactly over
     * the whole period (Matomo's archived reports only for single days).
     *
     * @param callable $translate fn(string $key, array $params = []): string
     * @param string|null $goalName the goal of a per-goal metric, named in its description
     */
    public static function description(string $id, callable $translate, ?string $goalName = null): string
    {
        if (preg_match('/^goal_\d+_([a-z_]+)$/', $id, $m) === 1 && isset(self::GOAL_DESCRIPTIONS[$m[1]])) {
            return $translate(self::GOAL_DESCRIPTIONS[$m[1]], [$goalName ?? '']);
        }
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
            ['sum_visit_length', self::SCOPE_VISIT, 'FeinwerkDataExploration_MetricSumVisitLength', 'FeinwerkDataExploration_SumVisitLengthOfTheseVisits'],
            ['nb_conversions', self::SCOPE_GOAL, 'Goals_ColumnConversions', null],
            ['conversion_rate', self::SCOPE_GOAL, 'General_ColumnConversionRate', null],
            ['revenue', self::SCOPE_GOAL, 'General_ColumnRevenue', null],
            ['nb_visits_converted', self::SCOPE_GOAL, 'General_ColumnVisitsWithConversions', null],
            ['revenue_per_visit', self::SCOPE_GOAL, 'General_ColumnValuePerVisit', null],
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
