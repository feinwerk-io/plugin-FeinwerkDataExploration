<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration;

use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Explorer;
use Piwik\Plugins\FeinwerkDataExploration\Engine\AccessPolicy;
use Piwik\Plugins\FeinwerkDataExploration\Engine\CostGuard;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationWindow;
use Piwik\Plugins\FeinwerkDataExploration\Engine\PeriodSuggestion;
use Piwik\Plugins\FeinwerkDataExploration\Query\QueryRunner;
use Piwik\Plugins\FeinwerkDataExploration\Engine\LimitsProvider;

/**
 * Data Exploration: ad hoc tables straight from the raw log data of one site.
 *
 * @method static API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * An ad hoc table straight from the raw data of one site: one or two dimensions and one to five metrics.
     *
     * The definition is JSON, e.g. {"dimensions": ["browser", "pageUrl"], "metrics": ["nb_visits", "nb_pageviews"],
     * "idGoal": 2, "sort": "nb_pageviews", "sortOrder": "desc", "limit": 100}; see getAvailableDimensions() and
     * getAvailableMetrics() for the ids.
     *
     * With readableColumns=1 the columns are named by the dimension and metric names in the user's language (exports).
     *
     * @param int $idSite one website; roll-up sites have no raw data and are refused
     * @param 'day'|'week'|'month'|'range' $period one period, at most 31 days (see the plugin settings)
     * @param string $date a day (YYYY-MM-DD, today, yesterday) or for period=range "YYYY-MM-DD,YYYY-MM-DD", lastN or previousN
     * @param string $definition JSON: dimensions (1 or 2), metrics (1 to 5), optional idGoal, sort, sortOrder, limit
     *                           (1 to 1,000) and filters (up to 5: label/secondary_label contains, equals, notContains;
     *                           a selected metric gt, lt)
     * @param string $segment a Matomo segment definition
     * @param int $readableColumns 1 names the columns in the user's language
     * @return DataTable label, secondary_label (two dimensions) and the metric columns; rows carry a "segment"
     *                   metadata for the visits behind them where the dimensions have one
     * @unsanitized the definition is JSON and the segment is parsed by Matomo's Segment class
     */
    public function getExploration(int $idSite, string $period, string $date, string $definition, string $segment = '', int $readableColumns = 0): DataTable
    {
        self::checkAccess($idSite);

        $dimensions = new DimensionCatalog();
        $metrics = new MetricCatalog();
        $limits = (new LimitsProvider())->get();
        $parsed = ExplorationDefinition::fromJson($definition, $idSite, $dimensions, $metrics, $limits);

        return (new Explorer($dimensions, $metrics, $limits))->explore($idSite, $period, $date, $parsed, $segment, $readableColumns === 1);
    }

    /**
     * The visits of the site in a period (counted only up to one above the threshold), with the limits that apply: the
     * page warns before long explorations on large sites.
     *
     * @param int $idSite one website
     * @param 'day'|'week'|'month'|'range' $period one period, at most 31 days
     * @param string $date as for getExploration()
     * @return array{visits: int, maxVisits: int, timeoutSeconds: int}
     * @internal for the exploration page
     */
    public function getPeriodVisits(int $idSite, string $period, string $date): array
    {
        self::checkAccess($idSite);

        $limits = (new LimitsProvider())->get();
        $window = ExplorationWindow::for($idSite, $period, $date, $limits);
        $visits = (new CostGuard(new QueryRunner($limits)))->visits($idSite, $window->startUtc, $window->endUtc, $limits, $window->cacheTtl());

        return ['visits' => $visits, 'maxVisits' => $limits->maxVisits, 'timeoutSeconds' => $limits->timeoutSeconds];
    }

    /**
     * The most recent days of a period that an exploration may read (at most the longest period of the settings and
     * the visit threshold), offered when a period is too large.
     *
     * @param int $idSite one website
     * @param 'day'|'week'|'month'|'range' $period one period, also longer than an exploration covers
     * @param string $date as for getExploration()
     * @return array{date: string, days: int}|array{} "YYYY-MM-DD,YYYY-MM-DD" and the number of days, or nothing when
     *         even the last day has too many visits
     * @internal for the exploration page
     */
    public function getSuggestedPeriod(int $idSite, string $period, string $date): array
    {
        self::checkAccess($idSite);

        $limits = (new LimitsProvider())->get();
        return (new PeriodSuggestion(new CostGuard(new QueryRunner($limits)), $limits))->suggest($idSite, $period, $date);
    }

    /**
     * The dimensions an exploration can use for this site, in display order and grouped.
     *
     * @param int $idSite one website
     * @return array<int, array{id: string, name: string, group: string, scope: string, kinds: string[]}> scope "visit" or
     *         "action"; kinds are the action rows an action dimension has values for (pageview, event, download, outlink,
     *         search)
     */
    public function getAvailableDimensions(int $idSite): array
    {
        self::checkAccess($idSite);

        $dimensions = [];
        foreach ((new DimensionCatalog())->availableFor($idSite) as $dimension) {
            $dimensions[] = [
                'id' => $dimension->id,
                'name' => $dimension->name,
                'group' => $dimension->group,
                'scope' => $dimension->scope,
                'kinds' => $dimension->actionKinds,
            ];
        }
        return $dimensions;
    }

    /**
     * The metrics an exploration can use, in display order.
     *
     * @param int $idSite one website
     * @return array<int, array{id: string, scope: string, name: string, nameInActionScope: string|null, kind: string|null,
     *         description: string}> scope "visit", "goal" or "action"; nameInActionScope is the name next to an action
     *         dimension (visit metrics then count the visits with that action)
     */
    public function getAvailableMetrics(int $idSite): array
    {
        self::checkAccess($idSite);

        $metrics = [];
        foreach ((new MetricCatalog())->all() as $metric) {
            $metrics[] = [
                'id' => $metric->id,
                'scope' => $metric->scope,
                'name' => Piwik::translate($metric->nameKey),
                'nameInActionScope' => $metric->nameInActionScopeKey === null ? null : Piwik::translate($metric->nameInActionScopeKey),
                'kind' => $metric->kind,
                'description' => MetricCatalog::description($metric->id, [Piwik::class, 'translate']),
            ];
        }
        return $metrics;
    }

    /**
     * Never anonymous users (Peter, 2026-10-19), and at least the role chosen in the settings (see AccessPolicy).
     */
    private static function checkAccess(int $idSite): void
    {
        AccessPolicy::check($idSite);
    }
}
