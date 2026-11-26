<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration;

use Piwik\Date;
use Piwik\Site;
use Piwik\DataTable;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedExplorations;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchiveSettings;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ComparisonList;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Definition\FunnelDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\FunnelExplorer;
use Piwik\Plugins\FeinwerkDataExploration\Engine\FunnelSwitch;
use Piwik\Plugins\FeinwerkDataExploration\Engine\SiteGoals;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Explorer;
use Piwik\Plugins\FeinwerkDataExploration\Engine\AccessPolicy;
use Piwik\Plugins\FeinwerkDataExploration\Engine\CostGuard;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationWindow;
use Piwik\Plugins\FeinwerkDataExploration\Engine\PeriodSuggestion;
use Piwik\Plugins\FeinwerkDataExploration\Engine\PivotExplorer;
use Piwik\Plugins\FeinwerkDataExploration\Engine\KpiExplorer;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ReportLevels;
use Piwik\Plugins\FeinwerkDataExploration\Query\QueryRunner;
use Piwik\Plugins\FeinwerkDataExploration\Engine\LimitsProvider;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Remember;
use Piwik\Plugins\FeinwerkDataExploration\Engine\TextColumns;
use Piwik\Plugins\FeinwerkDataExploration\Engine\TrendExplorer;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\FilterSuggestions;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedStateDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedReportUnavailable;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedReportTable;
use Piwik\Plugins\FeinwerkDataExploration\Saved\ReportVisibility;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationCopier;
use Piwik\Plugins\FeinwerkDataExploration\Saved\KpiCardSettings;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationRules;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationStore;
use Piwik\Segment;
use Piwik\Container\StaticContainer;
use Piwik\Option;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\FeinwerkDataExploration\Feedback\PluginFeedback;
use Piwik\Translation\Translator;
use Piwik\Version;

/**
 * Data Exploration: ad hoc tables straight from the raw log data of one site.
 *
 * @method static API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * An ad hoc table straight from the raw data of one site: one to three dimensions and one to ten metrics.
     *
     * The definition is JSON, e.g. {"dimensions": ["browser", "pageUrl"], "metrics": ["nb_visits", "nb_pageviews"],
     * "idGoal": 2, "sort": "nb_pageviews", "sortOrder": "desc", "limit": 100}; see getAvailableDimensions() and
     * getAvailableMetrics() for the ids. idGoal is the older form of a goal's metrics: it narrows only nb_conversions,
     * conversion_rate and revenue to that goal; the per-goal metrics (goal_<idGoal>_nb_conversions and so on) do the same
     * side by side and are not affected by it.
     *
     * Row filters (optional) choose the table's rows: "filterGroups": {"match": "all"|"any", "groups": [{"match":
     * "all"|"any", "filters": [{"target": ..., "operator": ..., "value": ...}]}]}. The outer match joins the groups,
     * each group's match its conditions; both default to "all". Example (Chrome on desktops, or more than 100 visits):
     * {"match": "any", "groups": [{"filters": [{"target": "label", "operator": "contains", "value": "chrome"},
     * {"target": "secondary_label", "operator": "equals", "value": "Desktop"}]}, {"filters": [{"target": "nb_visits",
     * "operator": "gt", "value": 100}]}]}. 1 to 5 groups, at most 10 conditions in all, every group with at least one.
     * - A text condition: target "label" (first dimension), "secondary_label" (second dimension) or "tertiary_label"
     *   (third dimension), each only when the definition has that dimension;
     *   operator contains, equals, notContains, notEquals, startsWith, endsWith, notStartsWith, notEndsWith, matches,
     *   empty or notEmpty; empty and notEmpty take no value (one sent is ignored): empty is what the table shows as
     *   "... not defined" or "Unknown" (no value, an empty value, or a code labelled like them), notEmpty all other rows;
     *   value a text of 1 to 1,000 characters (trimmed), compared case-insensitively with the row's value as shown. The
     *   negated operators (notContains, notEquals, notStartsWith, notEndsWith) keep rows without a value, the others drop
     *   them. matches takes a regular expression of at most 200 characters in the dialect of the database (MySQL 8:
     *   ICU, MariaDB: PCRE, MySQL 5.7: POSIX; the common syntax works everywhere), checked with PHP's PCRE first; on
     *   page titles, event names, keywords and custom dimensions, which Matomo stores HTML-escaped, the characters & ' "
     *   < > outside a character class match their plain and their escaped form.
     * - A number condition: target one of the definition's metric ids; operator gt, gte, lt or lte; value a number in
     *   the metric's raw unit (rates such as bounce_rate and conversion_rate as fractions: 0.25 for 25 %, durations in
     *   seconds). Number conditions decide which rows are shown. Totals, shares, "Others" and trend lines cover what
     *   the text conditions let through before the grouping: a text condition joined by "any" with a number condition
     *   (in its group, or between groups) is tested on the grouped rows and does not narrow them.
     * - The form before filter groups is still accepted: "filters": [conditions] with "match": "all"|"any" is one group
     *   with that match. Sending both forms is refused.
     * Errors answer HTTP 400 and name the field, e.g. "filterGroups.groups[1].filters[0]", with the allowed values.
     *
     * A pivot (optional) turns the top values of one more dimension into column groups: "pivot": {"dimension": <id>,
     * "limit": 3|5|10} (limit defaults to 5). The pivot dimension may not be a row dimension, Date or Hour; Weekday
     * shows its seven days (Monday first) whatever the limit. The limit times the number of metrics may be at most 60.
     * Not together with comparisons. With a pivot the first row is the total row (metadata fwde_total, and fwde_pivot:
     * {"dimension": id, "values": [{"label", "value" (raw), "segment" (Matomo segment or null)}], "others": bool}).
     * Every row then has, per pivot value n (from 1, the first metric's largest first over what the text conditions
     * let through), {metric}__p{n}; when values were cut, {metric}__p_others for the rest of the values where it is
     * exact (sums of what one visit counts once: not visits or visitors under a page pivot, not rates or averages);
     * {metric} stays the row's total over all values. A cell without data is 0 for counts and sums, null otherwise.
     * Sorting, the row limit and number conditions apply to the rows by their totals.
     *
     * With readableColumns=1 the columns are named by the dimension and metric names in the user's language (exports);
     * with a pivot "<value>: <metric>", "Others: <metric>" and "Total: <metric>".
     *
     * @param int $idSite one website; roll-up sites have no raw data and are refused
     * @param 'day'|'week'|'month'|'range' $period one period, at most 31 days (see the plugin settings)
     * @param string $date a day (YYYY-MM-DD, today, yesterday) or for period=range "YYYY-MM-DD,YYYY-MM-DD", lastN or previousN
     * @param string $definition JSON: dimensions (1 to 3), metrics (1 to 10), optional idGoal, sort, sortOrder, limit
     *                           (1 to 1,000), filterGroups (row filters) and pivot (both described above)
     * @param string $segment a Matomo segment definition
     * @param int $readableColumns 1 names the columns in the user's language
     * @param string $comparisons JSON, optional: comparison columns after the own one (Matomo's comparison of segments and
     *                            periods), e.g. [{"segment": "browserCode==FF", "period": "day", "date": "2026-03-27"}];
     *                            up to 3 (4 columns per metric with the own one). With comparisons the first row is the
     *                            total row (metadata fwde_total), and each row gets {metric}__share and per comparison n
     *                            {metric}__c{n} with {metric}__c{n}__share (segments) or {metric}__c{n}__change (periods).
     *                            Refused together with a pivot (HTTP 400, naming pivot and comparisons)
     * @return DataTable label, secondary_label (two or three dimensions), tertiary_label (three dimensions) and the
     *                   metric columns; rows carry a "segment" metadata for the visits behind them where the dimensions
     *                   have one. To repeat "filter to this value" a client takes per label column the stored value
     *                   fwde_value, fwde_value2, fwde_value3 (sent where it differs from the label) with "equals", or
     *                   the label; a row whose dimension has no value carries fwde_no_value true and fwde_empty_labels
     *                   (the label columns without a value), which take the condition "empty" instead
     * @unsanitized the definition and the comparisons are JSON and segments are parsed by Matomo's Segment class
     */
    public function getExploration(int $idSite, string $period, string $date, string $definition, string $segment = '', int $readableColumns = 0, string $comparisons = ''): DataTable
    {
        self::checkAccess($idSite);

        $dimensions = new DimensionCatalog();
        $metrics = new MetricCatalog();
        $limits = (new LimitsProvider())->get();
        $parsed = ExplorationDefinition::fromJson($definition, $idSite, $dimensions, $metrics, $limits);
        $compared = ComparisonList::fromJson($comparisons, $limits);

        if ($parsed->pivot !== null) {
            if ($compared !== []) {
                throw new ExplorationException('FeinwerkDataExploration_ErrorPivotWithComparison');
            }
            return TextColumns::skipInTotals((new PivotExplorer($dimensions, $metrics, $limits))
                ->explore($idSite, trim($period), trim($date), $parsed, trim($segment), $readableColumns === 1));
        }
        return TextColumns::skipInTotals((new Explorer($dimensions, $metrics, $limits))
            ->explore($idSite, trim($period), trim($date), $parsed, trim($segment), $readableColumns === 1, $compared));
    }

    /**
     * The exact rest of a cut table ("Others"), computed on demand: every metric of the definition over everything the
     * table covers but its shown rows. `others` is null when nothing was cut. Refused (HTTP 400) for a table whose rows
     * can add up to more than the total, since its rest would not be exact.
     *
     * @param int $idSite one website
     * @param string $period as for getExploration()
     * @param string $date as for getExploration()
     * @param string $definition JSON as for getExploration(), row filters (filterGroups) included: the rest covers what
     *                           the text conditions let through before the grouping (see getExploration()), rows hidden
     *                           by a number condition included; a pivot is ignored (the rest of the rows, no columns)
     * @param string $segment a Matomo segment definition: only its visits count
     * @return array{others: array<string, int|float|null>|null} by metric id
     * @unsanitized the definition is JSON and the segment is parsed by Matomo's Segment class
     */
    public function getOthers(int $idSite, string $period, string $date, string $definition, string $segment = ''): array
    {
        self::checkAccess($idSite);

        $dimensions = new DimensionCatalog();
        $metrics = new MetricCatalog();
        $limits = (new LimitsProvider())->get();
        $parsed = ExplorationDefinition::fromJson($definition, $idSite, $dimensions, $metrics, $limits);

        return ['others' => (new Explorer($dimensions, $metrics, $limits))->others($idSite, trim($period), trim($date), $parsed, trim($segment))];
    }

    /**
     * The trend of an exploration: the first rows of the table (as getExploration() returns them for the same
     * parameters) per day of the period, or per hour for a period of one day; with comparisons the total of the own
     * view (line 0) and of each comparison (line 1, 2, ...) per day or hour of its own period. Days and hours are
     * counted in the site's timezone as the dimensions "Date" and "Hour" count them; every day or hour of a line's
     * period is in the result.
     *
     * @param int $idSite one website; roll-up sites have no raw data and are refused
     * @param 'day'|'week'|'month'|'range' $period one period, at most 31 days (see the plugin settings)
     * @param string $date as for getExploration()
     * @param string $definition JSON as for getExploration(), row filters (filterGroups) included: they choose the
     *                           rows, number conditions do not remove days; a pivot is ignored (lines follow the rows)
     * @param string $segment a Matomo segment definition: only its visits count
     * @param string $comparisons JSON as for getExploration()
     * @return DataTable one row per line and day or hour: label (secondary_label, tertiary_label per dimension) of the row or
     *                   the title of the comparison, time (YYYY-MM-DD or the hour 0 to 23) and the definition's
     *                   metrics; metadata fwde_line, fwde_key (rows) or fwde_kind (comparisons)
     * @unsanitized the definition and the comparisons are JSON and the segment is parsed by Matomo's Segment class
     */
    public function getTrend(int $idSite, string $period, string $date, string $definition, string $segment = '', string $comparisons = ''): DataTable
    {
        self::checkAccess($idSite);

        $dimensions = new DimensionCatalog();
        $metrics = new MetricCatalog();
        $limits = (new LimitsProvider())->get();
        $parsed = ExplorationDefinition::fromJson($definition, $idSite, $dimensions, $metrics, $limits);
        $compared = ComparisonList::fromJson($comparisons, $limits);

        return TextColumns::skipInTotals((new TrendExplorer($dimensions, $metrics, $limits))
            ->trend($idSite, trim($period), trim($date), $parsed, trim($segment), $compared));
    }

    /**
     * A funnel straight from the raw data of one site: how many visits reached each step, in this order (other actions
     *
     * Off by default: the method answers "Funnels are not available in this version." (HTTP 400) unless
     * `[FeinwerkDataExploration] funnel = 1` is set in config.ini.php.
     * in between are allowed, one action counts for one step only, a goal step may be met by the conversion of the
     * previous step's action, entry only through step 1).
     *
     * The definition is JSON, e.g. {"steps": [{"type": "pageUrl", "operator": "contains", "value": "/shop"},
     * {"type": "eventAction", "operator": "equals", "value": "Add to cart"}, {"type": "goal", "idGoal": 2}]}.
     * Types: pageUrl, pageTitle (contains, equals, startsWith), eventCategory, eventAction, eventName, siteSearch,
     * download, outlink (contains, equals), goal (idGoal) and anyGoal. Texts are compared without case.
     *
     * With readableColumns=1 the columns are named in the user's language (exports).
     *
     * @param int $idSite one website; roll-up sites have no raw data and are refused
     * @param 'day'|'week'|'month'|'range' $period one period, at most 31 days (see the plugin settings)
     * @param string $date as for getExploration()
     * @param string $definition JSON: 2 to 3 steps (another plugin may allow more through FeinwerkDataExploration.getLimits)
     * @param string $segment a Matomo segment definition: only its visits count
     * @param int $readableColumns 1 names the columns in the user's language
     * @return DataTable one row per step: step, label, nb_visits, share_of_start and share_of_previous (percent),
     *                   nb_dropoffs
     * @unsanitized the definition is JSON and the segment is parsed by Matomo's Segment class
     */
    public function getFunnel(int $idSite, string $period, string $date, string $definition, string $segment = '', int $readableColumns = 0): DataTable
    {
        self::checkAccess($idSite);
        FunnelSwitch::check();

        $limits = (new LimitsProvider())->get();
        $parsed = FunnelDefinition::fromJson($definition, SiteGoals::names($idSite), $limits);

        $table = (new FunnelExplorer($limits))->explore($idSite, trim($period), trim($date), $parsed, trim($segment), $readableColumns === 1);
        return TextColumns::skipInTotals($table);
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
        $window = ExplorationWindow::for($idSite, trim($period), trim($date), $limits);
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
        return (new PeriodSuggestion(new CostGuard(new QueryRunner($limits)), $limits))->suggest($idSite, trim($period), trim($date));
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
     * Suggestions for a filter's value (2026-11-25): the values of one dimension as the exploration's table shows them,
     * from Matomo's archived report of that dimension for the period (never from the raw data), most visited first; the
     * dimensions whose every value the plugin knows (weekday, hour, visitorType, visitConverted, channelType) from that
     * list in its order. A dimension without a Matomo report, or a report that is not available, gives an empty list.
     *
     * @param int $idSite one website
     * @param string $period day, week, month, year or range, as the exploration
     * @param string $date a date, or for a range two dates "YYYY-MM-DD,YYYY-MM-DD", as the exploration
     * @param string $dimension a dimension id of getAvailableDimensions, for example browser, pageUrl or campaignName
     * @param string $text the typed text, matched literally and case-insensitively anywhere in the value (at most 100
     *                     characters are used); empty for the most visited values
     * @return list<string> at most 15 values (a fixed list all of its values, hour 24)
     * @throws ExplorationException 400 for a period or date an exploration does not take (as getExploration: the
     *                              message names it) and for a dimension the website does not offer (the message names
     *                              it)
     */
    public function getFilterSuggestions(int $idSite, string $period, string $date, string $dimension, string $text = ''): array
    {
        self::checkAccess($idSite);
        // the exploration's own period rules: Matomo's reports are asked for at most its longest period
        ExplorationWindow::for($idSite, trim($period), trim($date), (new LimitsProvider())->get());
        $definition = (new DimensionCatalog())->get($idSite, trim($dimension));
        if ($definition === null) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorUnknownDimension', [trim($dimension)]);
        }
        $text = mb_substr(trim($text), 0, FilterSuggestions::MAX_TEXT);
        // a pause in typing asks again for the same text: Matomo's report is read once in five minutes
        $key = 'fwde_suggest_' . sha1((string) json_encode([PluginManager::getInstance()->getLoadedPlugin('FeinwerkDataExploration')
            ->getVersion(), Piwik::getCurrentUserLogin(), $idSite, trim($period), trim($date), $definition->id, mb_strtolower($text)]));
        return (new Remember())->get($key, 300, fn (): array => (new FilterSuggestions())
            ->suggest($definition, $idSite, trim($period), trim($date), $text));
    }

    /**
     * The metrics an exploration of this website can use, in display order: the fixed metrics, then three per goal of
     * the website (goal_<idGoal>_nb_conversions, goal_<idGoal>_conversion_rate, goal_<idGoal>_revenue, in Matomo's goal
     * order), then on an e-commerce website the e-commerce metrics (goal_ecommerceOrder_nb_conversions, _revenue, _items,
     * _avg_order_revenue, _conversion_rate, goal_ecommerceAbandonedCart_nb_conversions, _revenue).
     *
     * @param int $idSite one website
     * @return array<int, array{id: string, scope: string, group: string, name: string, nameInActionScope: string|null,
     *         kind: string|null, unit: string, aggregation: string, idGoal: int|null, description: string}>
     *         scope "visit", "goal" or "action"; group "visits", "goals", "ecommerce" or "actions" (the list's headings);
     *         name in the user's language ("<goal name>: <column>" for a goal's metrics); nameInActionScope the name next to
     *         an action dimension (visit metrics then count the visits with that action), else null; kind the kind of
     *         action an action metric counts (pageview, event, download, outlink), else null; unit "count", "money" (the
     *         website's currency), "rate" (a fraction from 0 to 1), "seconds" or "number"; aggregation how rows combine:
     *         "sum" (the rows add up to the total), "distinct" (one visitor or visit can count in several rows), "ratio",
     *         "average" or "max" (never added up); idGoal the goal of a per-goal metric, else null; description what the
     *         metric counts. Goal and e-commerce metrics count the conversions of the visits behind each row.
     */
    public function getAvailableMetrics(int $idSite): array
    {
        self::checkAccess($idSite);

        $translate = [Piwik::class, 'translate'];
        $metrics = [];
        foreach ((new MetricCatalog())->all($idSite) as $metric) {
            $metrics[] = [
                'id' => $metric->id,
                'scope' => $metric->scope,
                'group' => $metric->group,
                'name' => $metric->label($translate),
                'nameInActionScope' => $metric->nameInActionScopeKey === null ? null : $metric->label($translate, true),
                'kind' => $metric->kind,
                'unit' => $metric->unit,
                'aggregation' => $metric->aggregation,
                'idGoal' => $metric->goalKey !== null && ctype_digit($metric->goalKey) ? (int) $metric->goalKey : null,
                'description' => MetricCatalog::description($metric->id, $translate, $metric->goalName),
            ];
        }
        return $metrics;
    }

    /**
     * The saved explorations the current user may open on the site: their own and those shared by others.
     *
     * @param int $idSite the website whose saved explorations are listed (view access)
     * @return array list of explorations with id, name, shared, owner, canEdit, canDelete (also site admins for shared
     *               ones), menu (the section of its own menu entry: one of General_Visitors, General_Actions,
     *               Referrers_Referrers, Goals_Ecommerce, Goals_Goals, FeinwerkDataExploration_Explorations, or null
     *               when it is not in the menu), canPlace (whether the user may change the menu place: as canDelete,
     *               always false for KPI cards and funnels), kind (table, funnel or kpi: a KPI card), updated (UTC),
     *               state (the page's parameters), segment and archived (true: Matomo archives it, so its report
     *               covers any period; false: its report covers at most 31 days)
     */
    public function getSavedExplorations(int $idSite): array
    {
        self::checkAccess($idSite);

        $login = Piwik::getCurrentUserLogin();
        $rows = (new SavedExplorationStore())->listVisible($idSite, $login);
        if (!FunnelSwitch::enabled()) {
            // funnels off: saved funnels stay stored and come back with the switch
            $rows = array_values(array_filter($rows, static fn (array $row): bool => !self::isFunnel($row['state'])));
        }
        // archived saved explorations (2026-11-25): their reports cover any period
        try {
            $archived = array_flip(array_map(
                static fn ($exploration): int => $exploration->idSaved,
                (new ArchivedExplorations(ArchiveSettings::fromConfig()))->forSite($idSite)
            ));
        } catch (\Throwable $e) {
            $archived = [];
        }
        return array_map(fn (array $row) => [
            'id' => $row['idsaved'],
            'name' => $row['name'],
            'shared' => $row['shared'],
            'owner' => $row['login'],
            'canEdit' => self::canEdit($row),
            // site admins may delete the website's shared explorations (design 2026-11-20)
            'canDelete' => self::canDelete($row, $idSite),
            // its menu place as a report (saved reports, 2026-11-24) and whether the user may change it
            'menu' => self::placeable($row['state']) ? SavedExplorationRules::menuOf($row['state']) : null,
            'canPlace' => self::placeable($row['state']) && self::canDelete($row, $idSite),
            'kind' => self::isFunnel($row['state']) ? 'funnel' : (KpiCardSettings::isCard($row['state']) ? 'kpi' : 'table'),
            'updated' => $row['ts_updated'],
            'state' => $row['state'],
            'segment' => $row['segment'],
            'archived' => isset($archived[(int) $row['idsaved']]),
        ], $rows);
    }

    /**
     * Only tables get a menu entry; KPI cards and funnels never do (UI review 2026-11-24).
     *
     * @param array<string, string> $state
     */
    private static function placeable(array $state): bool
    {
        return !self::isFunnel($state) && !KpiCardSettings::isCard($state);
    }

    /** @param array<string, string> $state */
    private static function isFunnel(array $state): bool
    {
        return ($state['fwdeKind'] ?? '') === SavedExplorationRules::KIND_FUNNEL;
    }

    /**
     * Saves the exploration as a new one of the current user.
     *
     * @param int $idSite the website the exploration belongs to (view access; sharing needs write access)
     * @param string $name 1 to 100 characters
     * @param string $state JSON of the page's parameters (fwdeDims, fwdeMetrics, ...). A KPI card (2026-11-24) adds
     *                      "fwdeKind": "kpi" and its settings: fwdeKpiMetric (one of fwdeMetrics, default the first),
     *                      fwdeKpiCompare (none, previous, weekday or average4; default average4), fwdeKpiLine (0, 7 or
     *                      30 days; default 30), fwdeKpiChange (percent or absolute; default percent); a value outside
     *                      its choices answers HTTP 400 naming the key and the choices. A card has no menu place
     *                      (fwdeMenu is ignored); getKpiCard returns its numbers
     * @param string $segmentDefinition Matomo segment the exploration opens with (not "segment": Matomo fills that
     *                                  parameter with the page's segment)
     * @param int $shared 1 shows it to everyone who may use explorations on the site
     * @return array the new exploration's id
     * @unsanitized name and state are kept as typed (shown with text bindings only), the state is JSON
     */
    public function addSavedExploration(int $idSite, string $name, string $state, string $segmentDefinition = '', int $shared = 0): array
    {
        self::checkAccess($idSite);

        self::checkShare($idSite, $shared);
        $name = SavedExplorationRules::name($name);
        $parsed = SavedExplorationRules::state($state);
        if (self::isFunnel($parsed)) {
            FunnelSwitch::check();
        }
        $segmentDefinition = trim($segmentDefinition);
        self::checkSegment($segmentDefinition, $idSite);

        $store = new SavedExplorationStore();
        $login = Piwik::getCurrentUserLogin();
        if ($store->countFor($idSite, $login) >= SavedExplorationRules::MAX_PER_USER_AND_SITE) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorSavedLimit', [SavedExplorationRules::MAX_PER_USER_AND_SITE]);
        }
        return ['id' => $store->add($idSite, $login, $name, $parsed, $segmentDefinition, $shared === 1)];
    }

    /**
     * Changes a saved exploration (its owner or a super user).
     *
     * @param int $idSite
     * @param int $idSaved
     * @param string $name 1 to 100 characters
     * @param string $state JSON of the page's parameters
     * @param string $segmentDefinition Matomo segment the exploration opens with
     * @param int $shared 1 shows it to everyone who may use explorations on the site
     * @unsanitized name and state are kept as typed (shown with text bindings only), the state is JSON
     */
    public function updateSavedExploration(int $idSite, int $idSaved, string $name, string $state, string $segmentDefinition = '', int $shared = 0): void
    {
        self::checkAccess($idSite);
        self::editable($idSite, $idSaved);
        self::checkShare($idSite, $shared);

        $name = SavedExplorationRules::name($name);
        $parsed = SavedExplorationRules::state($state);
        if (self::isFunnel($parsed)) {
            FunnelSwitch::check();
        }
        $segmentDefinition = trim($segmentDefinition);
        self::checkSegment($segmentDefinition, $idSite);
        (new SavedExplorationStore())->update($idSaved, $name, $parsed, $segmentDefinition, $shared === 1);
    }

    /**
     * A saved exploration as a Matomo report (saved reports, 2026-11-24): its table for one period, with the
     * exploration's dimensions, metrics, filters, sorting, row limit and segment. This is the report behind the
     * exploration's dashboard widget, scheduled e-mail reports and menu entry (API.getReportMetadata lists it as module
     * FeinwerkDataExploration, action getSavedReport, parameter idSaved).
     *
     * Archived explorations (getSavedExplorations: archived = true; archived reports, 2026-11-25) answer any single
     * period: complete periods and periods longer than the plugin's days come from Matomo's archive (table metadata
     * fwde_archived = true; when the archive does not hold every day with visits yet, fwde_archive_coverage =
     * [days held, days with visits]); an incomplete period of up to the plugin's days is computed live from the raw
     * data. Unique visitors cannot be added up across days: they come for days and live periods only and are left out of
     * longer ones. Explorations that are not archived (archiving off, over the per-website cap, a KPI card) are computed
     * live and cover at most the plugin's days.
     *
     * One dimension without a pivot: one row per value. Otherwise one level per dimension (up to three) and the pivot
     * dimension as the last level (up to four levels): the first level has one row per value of the first dimension
     * with its totals over what the text filters let through, each with a sub table (idSubtable, from the row) holding
     * its rows of the next dimension, and so on. Live, the id comes from the value path, so it stays when the order
     * changes; from the archive it is Matomo's id of the stored sub table, valid for that period's archive only.
     * The pivot level holds the exploration's top pivot values under every row (0 for counts without data), then an
     * "Others" row when values were cut and every metric adds up across them. flat=1 gives one row per path. A metric
     * of a goal the website no longer has is left out. Visible to the owner (private) or to everyone who may use
     * explorations (shared); Email Reports Pro names the owner with fwReportOwner.
     *
     * @param int $idSite the exploration's website
     * @param 'day'|'week'|'month'|'year'|'range' $period one period; archived explorations any length, the others at
     *                                                  most the plugin's days (31 by default); more than one period
     *                                                  (date=last7 with period=day) is refused except for KPI cards
     * @param string $date a day (YYYY-MM-DD, today, yesterday) or for period=range "YYYY-MM-DD,YYYY-MM-DD"
     * @param int $idSaved the saved exploration (getSavedExplorations: id)
     * @param string $segment a Matomo segment: it narrows the exploration's own segment further (both apply)
     * @param int $idSubtable 0 for the report; a row's sub table id (from the row of the level above) for its rows of
     *                        the next level
     * @param string $fwReportOwner the login of the e-mail's owner (Email Reports Pro): honoured for super user callers
     *                              only and only narrowing, the owner's private explorations plus shared ones
     * @return DataTable\DataTableInterface columns label and the metric ids (raw values: counts, money in the website's
     *                   currency, rates as fractions, seconds). A KPI card (kind "kpi") is a report without a dimension:
     *                   one row with its metric for the period, or for period day with a date range or lastN (at most
     *                   the plugin's days) one such table per day, keyed by the day
     * @throws SavedReportUnavailable 400 when the period is refused (ArchiveNotArchived: a period longer than the
     *                                plugin's days for an exploration that is not archived) or the exploration no longer
     *                                resolves on the website (a dimension gone, no metric left); the message is translated
     * @throws ExplorationException 404 when the exploration does not exist or the user may not see it, 503 when the
     *                              database is busy (after waiting for a free slot)
     */
    public function getSavedReport(int $idSite, string $period, string $date, int $idSaved, string $segment = '', int $idSubtable = 0, string $fwReportOwner = ''): DataTable\DataTableInterface
    {
        self::checkAccess($idSite);
        $owner = trim($fwReportOwner);
        $visibility = new ReportVisibility(null, static fn (): array => ['owner' => $owner] + ReportVisibility::currentContext());
        $row = (new SavedExplorationStore())->get($idSaved);
        if ($row === null || (int) $row['idsite'] !== $idSite || !$visibility->canFetch($row, $idSite)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorSavedNotFound', [], 404);
        }

        $dimensions = new DimensionCatalog();
        $metrics = new MetricCatalog();
        $context = ReportVisibility::currentContext();
        // a dashboard loads its widgets in parallel, Matomo's e-mail drops a report that fails: wait for a slot
        $limits = (new LimitsProvider())->get();
        $limits = $limits->withSlotWait(SavedReportTable::slotWait($limits, $context['runningTask']));
        $definition = ExplorationDefinition::fromJson(
            (string) json_encode(SavedStateDefinition::toDefinition($row['state'], $idSite, $dimensions, $metrics, $limits)),
            $idSite,
            $dimensions,
            $metrics,
            $limits
        );
        try {
            $card = KpiCardSettings::isCard($row['state']) ? KpiCardSettings::fromState($row['state'], $definition->metricIds) : null;
            // archived saved explorations (2026-11-25) answer any period; the others at most maxDays days
            $archived = $card === null ? ArchivedExplorations::cachedFind($idSite, $idSaved, ArchiveSettings::fromConfig()) : null;
            if ($card === null && $archived === null && !Period::isMultiplePeriod(trim($date), trim($period))
                && SavedReportTable::periodDays($idSite, trim($period), trim($date)) > $limits->maxDays) {
                throw new SavedReportUnavailable('FeinwerkDataExploration_ArchiveNotArchived', [$limits->maxDays]);
            }
            return (new SavedReportTable(new Explorer($dimensions, $metrics, $limits), new ReportLevels($dimensions, $metrics, $limits), $dimensions, $metrics, $limits))
                ->build($idSite, trim($period), trim($date), $definition, (string) $row['segment'], $segment, $idSubtable, $card, $archived);
        } catch (ExplorationException $e) {
            if (in_array($e->getTranslationKey(), ['FeinwerkDataExploration_ErrorPeriodNotSupported', 'FeinwerkDataExploration_ErrorPeriodTooLong'], true)) {
                throw new SavedReportUnavailable($e->getTranslationKey(), $e->getParams());
            }
            throw $e;
        }
    }

    /**
     * A KPI card's numbers (2026-11-24): one metric of a saved exploration of kind "kpi" for one period, its
     * comparison and its line, as the card on the dashboard shows them. Raw values: counts, money in the website's
     * currency, rates as fractions, seconds.
     *
     * The number is the metric's total over the exploration's segment and text filters (its table's total row) for the
     * period; with a period that includes today it covers the time so far (partial, until). The comparison follows the
     * card's setting fwdeKpiCompare: previous (the day, week or month before), weekday (a day: 7 days before; a week or
     * month: the one before) or average4 (a day: the same weekday 7, 14, 21 and 28 days before; a week or month: the
     * four before), each cut at the same point when the number is partial; days before the website's first complete day
     * of raw data and values without data (a rate without visits) are left out (used out of wanted). A range has no
     * comparison. The line has the metric per day over the card's last 7 or 30 days, the comparison days marked.
     *
     * @param int $idSite the card's website
     * @param 'day'|'week'|'month'|'range' $period one period of at most the plugin's limit (31 days by default)
     * @param string $date a day (YYYY-MM-DD, today, yesterday) or for period=range "YYYY-MM-DD,YYYY-MM-DD"
     * @param int $idSaved the card (getSavedExplorations: id of a row with kind "kpi")
     * @param string $segment a Matomo segment: it narrows the card's own segment further (both apply)
     * @return array{title: string, metric: string, metricName: string, unit: string, value: int|float|null, changeAs: string, partial: bool,
     *     until: string|null, firstDay: string, lastDay: string, comparison: array|null, comparisonNote: string|null,
     *     line: list<array{day: string, value: int|float|null, baseline: bool}>|null, lineNote: string|null,
     *     lowerIsBetter: bool} comparison: kind, period, baseline, used, wanted, spans (firstDay, lastDay, value), change
     *     (a fraction, null when the baseline is 0), absolute; changeAs the card's setting (percent or absolute); the
     *     notes say why a part is missing
     * @throws ExplorationException 404 when the card does not exist or the user may not see it, 400 when idSaved is
     *                              not a KPI card, 503 when the database is busy
     * @throws SavedReportUnavailable 400 when the period is refused or the exploration no longer resolves (a dimension
     *                                gone, no metric left)
     */
    public function getKpiCard(int $idSite, string $period, string $date, int $idSaved, string $segment = ''): array
    {
        self::checkAccess($idSite);
        $row = (new SavedExplorationStore())->get($idSaved);
        if ($row === null || (int) $row['idsite'] !== $idSite || !(new ReportVisibility())->canFetch($row, $idSite)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorSavedNotFound', [], 404);
        }
        if (!KpiCardSettings::isCard($row['state'])) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorNotKpiCard', ['idSaved']);
        }

        $dimensions = new DimensionCatalog();
        $metrics = new MetricCatalog();
        $limits = (new LimitsProvider())->get();
        // a dashboard loads its cards in parallel: wait for a slot as saved reports do
        $limits = $limits->withSlotWait(SavedReportTable::slotWait($limits, ReportVisibility::currentContext()['runningTask']));
        $definition = ExplorationDefinition::fromJson(
            (string) json_encode(SavedStateDefinition::toDefinition($row['state'], $idSite, $dimensions, $metrics, $limits)),
            $idSite,
            $dimensions,
            $metrics,
            $limits
        );
        $settings = KpiCardSettings::fromState($row['state'], $definition->metricIds);
        // the website's local time (Date::factory with a timezone converts UTC to it), cut to five minutes: the card's
        // queries share their cache keys while today's results are kept (five minutes)
        $local = Date::factory('now', Site::getTimezoneFor($idSite))->getTimestamp();
        $now = gmdate('Y-m-d H:i:s', $local - $local % 300);
        try {
            return ['title' => (string) $row['name']] + (new KpiExplorer($dimensions, $metrics, $limits))
                ->card($idSite, trim($period), trim($date), $definition, SavedReportTable::segment((string) $row['segment'], $segment), $settings, $now);
        } catch (ExplorationException $e) {
            if (in_array($e->getTranslationKey(), ['FeinwerkDataExploration_ErrorPeriodNotSupported', 'FeinwerkDataExploration_ErrorPeriodTooLong'], true)) {
                throw new SavedReportUnavailable($e->getTranslationKey(), $e->getParams());
            }
            throw $e;
        }
    }

    /**
     * Changes the menu place of a saved exploration as a Matomo report: its owner, a super user, or an admin of the
     * website for a shared exploration (Peter, 2026-11-24); the content stays the owner's.
     *
     * @param int $idSite the exploration's website
     * @param int $idSaved the exploration (getSavedExplorations: id); only a table (kind "table", canPlace true) has a
     *                     menu place, never a KPI card or a funnel
     * @param string $menu the section of its own menu entry: General_Visitors, General_Actions, Referrers_Referrers,
     *                     Goals_Ecommerce, Goals_Goals or FeinwerkDataExploration_Explorations; empty takes it out of
     *                     the menu (it stays a report for widgets and e-mails)
     * @return void
     * @throws ExplorationException 400 for an unknown section (the message names "menu" and the allowed values) or for
     *                              a KPI card or a funnel (the message names "idSaved"), 403
     *                              when the user may not change it, 404 when the user cannot see it
     */
    public function setSavedExplorationMenu(int $idSite, int $idSaved, string $menu): void
    {
        self::checkAccess($idSite);
        $row = self::visible($idSite, $idSaved);
        if (!self::canDelete($row, $idSite)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorSavedNotYours', [], 403);
        }
        if (!self::placeable($row['state'])) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorNotPlaceable',
                ['idSaved', self::isFunnel($row['state']) ? SavedExplorationRules::KIND_FUNNEL : SavedExplorationRules::KIND_KPI]);
        }
        $menu = trim($menu);
        SavedExplorationRules::checkMenu($menu, 'menu');
        $state = $row['state'];
        unset($state['fwdeMenu']);
        if ($menu !== '') {
            $state['fwdeMenu'] = $menu;
        }
        (new SavedExplorationStore())->update($idSaved, $row['name'], $state, $row['segment'], (bool) $row['shared']);
    }

    /**
     * Deletes a saved exploration (its owner or a super user).
     *
     * @param int $idSite
     * @param int $idSaved
     */
    public function deleteSavedExploration(int $idSite, int $idSaved): void
    {
        self::checkAccess($idSite);
        $row = self::visible($idSite, $idSaved);
        if (!self::canDelete($row, $idSite)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorSavedNotYours', [], 403);
        }
        (new SavedExplorationStore())->delete($idSaved);
    }

    /**
     * A feedback vote ("Is this exploration helpful?"), sent from this server to Feinwerk: plugin, versions, question,
     * vote, browser family and UI language; nothing about the user, the website or its visitors. At most one vote per
     * user and question every 10 seconds and 20 a minute per server; switched off with [FeinwerkDataExploration]
     * feedback = 0, and never sent from a test run.
     *
     * @param int $idSite
     * @param string $topic one of PluginFeedback::TOPICS
     * @param string $vote up|down
     * @return array{sent: bool}
     */
    public function sendFeedback(int $idSite, string $topic, string $vote): array
    {
        self::checkAccess($idSite);
        $feedback = StaticContainer::get(PluginFeedback::class);
        if (!$feedback->isEnabled()) {
            return ['sent' => false];
        }
        try {
            $payload = PluginFeedback::payload(
                trim($topic),
                trim($vote),
                (string) PluginManager::getInstance()->getLoadedPlugin(PluginFeedback::PLUGIN)->getVersion(),
                Version::VERSION,
                (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''),
                (string) StaticContainer::get(Translator::class)->getCurrentLanguage()
            );
        } catch (\InvalidArgumentException $e) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorFeedbackValue', [$e->getMessage()]);
        }
        $key = PluginFeedback::throttleOption(Piwik::getCurrentUserLogin(), $payload['topic']);
        if (time() - (int) Option::get($key) < 10) {
            return ['sent' => false];
        }
        // at most SERVER_CAP votes a minute leave this Matomo; both counters are stored only when the vote is sent
        $server = PluginFeedback::countVote((string) Option::get(PluginFeedback::SERVER_OPTION), time());
        if ($server === null) {
            return ['sent' => false];
        }
        Option::set($key, (string) time());
        Option::set(PluginFeedback::SERVER_OPTION, $server);
        return ['sent' => $feedback->send($payload)];
    }

    /**
     * Copies a saved exploration to other websites (Matomo's "copy to other websites" dialog, Matomo 5.14 and newer).
     * The copy belongs to the current user, is shared only where the user may share, and maps a goal by its name;
     * websites the user may not use, without a goal of that name or at the user's limit are skipped and named in the
     * message.
     *
     * @param int $idSite the website of the exploration
     * @param int $idSaved the saved exploration (own or shared)
     * @param array $idDestinationSites website ids
     * @return array{success: bool, message: string, additionalData: array}
     */
    public function copySavedExploration(int $idSite, int $idSaved, array $idDestinationSites): array
    {
        self::checkAccess($idSite);
        $row = self::visible($idSite, $idSaved);
        if (self::isFunnel($row['state'])) {
            FunnelSwitch::check();
        }
        return (new SavedExplorationCopier())->copy($row, $idDestinationSites, Piwik::getCurrentUserLogin());
    }

    /** The row when the current user may see it (own, shared, or super user); another site's counts as missing. */
    private static function visible(int $idSite, int $idSaved): array
    {
        $row = (new SavedExplorationStore())->get($idSaved);
        if ($row === null || $row['idsite'] !== $idSite || (!$row['shared'] && !self::canEdit($row))) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorSavedNotFound', [], 404);
        }
        return $row;
    }

    /**
     * The row when the current user may change it; an exploration of another site counts as missing.
     */
    private static function editable(int $idSite, int $idSaved): array
    {
        $row = self::visible($idSite, $idSaved);
        if (!self::canEdit($row)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorSavedNotYours', [], 403);
        }
        return $row;
    }

    private static function canEdit(array $row): bool
    {
        return Piwik::hasUserSuperUserAccess() || $row['login'] === Piwik::getCurrentUserLogin();
    }

    /** The owner and super users, and admins of the website for a shared exploration (design 2026-11-20). */
    private static function canDelete(array $row, int $idSite): bool
    {
        return self::canEdit($row) || ($row['shared'] && Piwik::isUserHasAdminAccess($idSite));
    }

    private static function checkShare(int $idSite, int $shared): void
    {
        if ($shared === 1 && !AccessPolicy::canShare($idSite)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorSavedShareNeedsWrite', [], 403);
        }
    }

    /** Matomo's own check of the segment definition (unknown segments, access to them). */
    private static function checkSegment(string $segment, int $idSite): void
    {
        if ($segment !== '') {
            new Segment($segment, [$idSite]);
        }
    }

    /**
     * Never anonymous users (Peter, 2026-10-19), and at least the role chosen in the settings (see AccessPolicy).
     */
    private static function checkAccess(int $idSite): void
    {
        AccessPolicy::check($idSite);
    }
}
