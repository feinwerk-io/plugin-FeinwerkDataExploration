<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\SavedReport;

use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetConfig;
use Piwik\Widget\WidgetContainerConfig;
use Piwik\Widget\WidgetsList;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Bar;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Pie;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedExploration;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedExplorations;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedReportReader;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchiveSettings;
use Piwik\Plugins\FeinwerkDataExploration\Archive\HistoryQueue;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\LimitsProvider;
use Piwik\Plugins\FeinwerkDataExploration\Saved\KpiCardSettings;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationRules;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedReportTable;

/**
 * One saved exploration as a Matomo report (saved reports, 2026-11-24), never auto-loaded (it lives outside Reports/).
 * The event Report.addReports adds one unconfigured instance, the lister: Matomo's report lists name the website only
 * to configureReportMetadata() (not in the request during a scheduler run or a direct API call), so the lister builds
 * the metadata of every saved exploration the request may see there; its widgets come from the request's website.
 * Matomo creates the class anew to show or format one report (ReportsProvider::factory): then it finds its
 * exploration by the request's idSaved and idSite.
 */
class SavedReport extends Report
{
    /** @var array{idsaved: int, state: array}|null */
    private ?array $saved = null;
    /** @var list<SavedReportDimension> the dimensions of the levels under the first (row dimensions, then the pivot) */
    private array $levels = [];
    /** @var array<string, string> metric id => name */
    private array $metricNames = [];
    /** @var array<string, string> metric id => description */
    private array $metricDocs = [];
    /** @var array<string, string> goal metric id => name (Email Reports Pro: Matomo's goal_<id>_* columns) */
    private array $goalMetrics = [];

    /** set while SavedReports builds instances itself: they must not configure themselves from the request */
    private static bool $building = false;

    /** @var array<int, true> the KPI cards configured in this request (their graphs follow the period, see graphsOnlyForDays()) */
    private static array $cards = [];

    /** An instance configured by SavedReports (or the lister), never from the request. */
    public static function unconfigured(): self
    {
        self::$building = true;
        try {
            return new self();
        } finally {
            self::$building = false;
        }
    }

    protected function init()
    {
        parent::init();
        $this->module = 'FeinwerkDataExploration';
        $this->action = 'getSavedReport';
        $this->categoryId = SavedExplorationRules::MENU_EXPLORATIONS;
        $this->name = Piwik::translate('FeinwerkDataExploration_Explorations');
        $this->order = 1000;

        $idSite = Common::getRequestVar('idSite', 0, 'int');
        $idSaved = Common::getRequestVar('idSaved', 0, 'int');
        if ($idSite > 0 && $idSaved > 0 && !self::$building) {
            try {
                (new SavedReports())->configure($this, $idSite, $idSaved);
            } catch (\Throwable $e) {
                // a report Matomo cannot configure stays the plain one; the API method answers the error
            }
        }
    }

    /**
     * Configures this instance for a saved exploration (SavedReports builds the values).
     *
     * @param array{idsaved: int, state: array} $saved
     * @param list<SavedReportDimension>|null $dimensions one per level: the row dimensions, then the pivot; null when the
     *                                               state no longer resolves (the API method says why)
     * @param array<string, array{name: string, unit: string, description: string, goal: bool}> $metrics
     */
    public function configureFor(array $saved, string $name, string $category, bool $placed, int $order, ?array $dimensions, array $metrics): void
    {
        $this->saved = $saved;
        if (KpiCardSettings::isCard($saved['state'] ?? [])) {
            self::$cards[(int) $saved['idsaved']] = true;
        }
        $this->name = $name;
        $this->categoryId = $category;
        // only a placed exploration has its own menu entry; the others are widgets and e-mail reports only
        $this->subcategoryId = $placed ? 'fwdeSaved' . (int) $saved['idsaved'] : null;
        $this->order = $order;
        $this->parameters = ['idSaved' => (int) $saved['idsaved']];
        $this->dimension = $dimensions[0] ?? null;
        $this->levels = array_slice($dimensions ?? [], 1);
        $this->actionToLoadSubTables = $this->levels !== [] ? 'getSavedReport' : null;
        $this->metrics = array_keys($metrics);
        $this->processedMetrics = [];
        $types = ['count' => 'number', 'number' => 'number', 'money' => 'money', 'rate' => 'percent', 'seconds' => 'duration_s'];
        $this->metricSemanticTypes = [];
        $this->metricNames = [];
        $this->metricDocs = [];
        $this->goalMetrics = [];
        foreach ($metrics as $id => $metric) {
            $this->metricNames[$id] = $metric['name'];
            $this->metricDocs[$id] = $metric['description'];
            $this->metricSemanticTypes[$id] = $types[$metric['unit']] ?? 'number';
            if ($metric['goal']) {
                $this->goalMetrics[$id] = $metric['name'];
            }
        }
    }

    /**
     * No default sort: the table comes sorted as saved, and Matomo's generic filters would sort it by visits otherwise.
     * A widget asks for the saved sort itself (configureView()), a click on a column for another.
     */
    public function getDefaultSortColumn()
    {
        return '';
    }

    public function isSaved(): bool
    {
        return $this->saved !== null;
    }

    /**
     * The metrics that add up across rows (aggregation "sum"). Matomo's table relates each metric of getMetrics() to the
     * report's and the website's total (HtmlTable::setRowPercentagesRecursively): a rate or an average has no such
     * share, and the website's conversion_rate comes from Goals as a formatted string ("11.85%"), which made the table
     * fail next to revenue (gallery shots, 2026-11-25). The report metadata lists every metric in the saved order
     * (configureReportMetadata()), getAllMetrics() too.
     */
    public function getMetrics()
    {
        return array_filter(
            $this->metricNames,
            static fn (string $id): bool => (MetricCatalog::meta($id)['aggregation'] ?? '') === 'sum',
            ARRAY_FILTER_USE_KEY
        );
    }

    /** Every metric of the saved exploration, in its order (Matomo's charts check their columns against it). */
    public function getAllMetrics()
    {
        return array_keys($this->metricNames);
    }

    protected function getMetricsDocumentation()
    {
        return $this->metricDocs;
    }

    public function getSubtableDimension()
    {
        return $this->levels[0] ?? null;
    }

    /** Matomo 5 names the third level by this (its flattener and metadata stop there). */
    public function getThirdLeveltableDimension()
    {
        return $this->levels[1] ?? null;
    }

    /** Matomo 6 asks the sub table's report by its module and action, which knows no exploration: answered here. */
    public function getNthLevelTableDimension(int $level): ?Dimension
    {
        return $level >= 1 ? ($this->levels[$level - 1] ?? null) : null;
    }

    /** the archived exploration of this report for the request's website, null when it is not archived */
    private function archived(): ?ArchivedExploration
    {
        $idSite = Common::getRequestVar('idSite', 0, 'int');
        if ($this->saved === null || $idSite <= 0) {
            return null;
        }
        try {
            return ArchivedExplorations::cachedFind($idSite, (int) $this->saved['idsaved'], ArchiveSettings::fromConfig());
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** whether the request asks a period longer than an exploration covers (answered from the archive only) */
    private function isLong(ArchivedExploration $archived): bool
    {
        $period = Common::getRequestVar('period', '', 'string');
        $date = Common::getRequestVar('date', '', 'string');
        if ($period === '' || $date === '' || Period::isMultiplePeriod($date, $period)) {
            return false;
        }
        try {
            return SavedReportTable::periodDays($archived->idSite, $period, $date) > (new LimitsProvider())->get()->maxDays;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** while the exploration's history is queued (design 2026-11-25) */
    private function pendingNote(ArchivedExploration $archived): string
    {
        try {
            return (new HistoryQueue())->isPending($archived->idSaved) ? Piwik::translate('FeinwerkDataExploration_ArchiveHistoryPending') : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Under a long period the archive answers alone; when it does not hold every day with visits yet the report says how
     * many (design 2026-11-25). Empty otherwise.
     */
    private function coverageNote(ArchivedExploration $archived): string
    {
        $period = Common::getRequestVar('period', '', 'string');
        $date = Common::getRequestVar('date', '', 'string');
        if ($period === '' || $date === '' || Period::isMultiplePeriod($date, $period)) {
            return '';
        }
        try {
            if (SavedReportTable::periodDays($archived->idSite, $period, $date) <= (new LimitsProvider())->get()->maxDays) {
                return '';
            }
            [, , $covered, $needed] = (new ArchivedReportReader())->source($archived, $period, $date, Common::getRequestVar('segment', '', 'string'));
            return $covered < $needed ? Piwik::translate('FeinwerkDataExploration_ArchiveCoverage', [$covered, $needed]) : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function configureReportMetadata(&$availableReports, $infos)
    {
        if ($this->saved === null) {
            // the lister: every saved exploration the request may see on the named website
            $idSite = (int) ($infos['idSite'] ?? 0);
            if ($idSite > 0) {
                foreach ((new SavedReports())->reportsFor($idSite) as $report) {
                    $report->configureReportMetadata($availableReports, $infos);
                }
            }
            return;
        }
        $metadata = $this->buildReportMetadata();
        // every metric, also rates and averages, which getMetrics() leaves out for the table's shares
        $metadata['metrics'] = $this->metricNames;
        if ($this->goalMetrics !== []) {
            $metadata['metricsGoal'] = $this->goalMetrics;
        }
        $availableReports[] = $metadata;
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        if ($this->saved !== null) {
            parent::configureWidgets($widgetsList, $factory);
            return;
        }
        // the lister: the widgets of the request's website (the dashboard and the menu pages name it)
        $idSite = SavedReports::requestedSite();
        if ($idSite < 1) {
            return;
        }
        foreach ((new SavedReports())->reportsFor($idSite) as $report) {
            $widgetsList->addWidgetConfig($report->isCard() ? $report->cardWidget() : $report->container());
        }
    }

    public function isCard(): bool
    {
        return $this->saved !== null && KpiCardSettings::isCard($this->saved['state']);
    }

    /** A KPI card's widget (2026-11-24): the card with its footer, in the dashboard's widget list only. */
    public function cardWidget(): WidgetConfig
    {
        $idSaved = (int) $this->saved['idsaved'];
        $widget = new WidgetConfig();
        $widget->setModule('FeinwerkDataExploration');
        $widget->setAction('kpiCard');
        $widget->setParameters(['idSaved' => $idSaved]);
        $widget->setCategoryId($this->categoryId);
        $widget->setName($this->name);
        $widget->setOrder($this->order);
        $widget->setIsWidgetizable();
        return $widget;
    }

    /**
     * Matomo's e-mail draws a graph of a report without a dimension over runs of the e-mail's period; a card answers runs
     * of days only (2026-11-24), so its graph goes for weeks, months and years (API.getReportMetadata.end, after
     * ImageGraph). A day or a range keeps its graph of days.
     */
    public static function graphsOnlyForDays(array &$reports, array $info): void
    {
        if (!in_array((string) ($info['period'] ?? 'day'), ['week', 'month', 'year'], true)) {
            return;
        }
        foreach ($reports as &$report) {
            if (($report['module'] ?? '') === 'FeinwerkDataExploration' && ($report['action'] ?? '') === 'getSavedReport'
                && isset(self::$cards[(int) ($report['parameters']['idSaved'] ?? 0)])) {
                unset($report['imageGraphUrl'], $report['imageGraphEvolutionUrl']);
            }
        }
    }

    /**
     * The exploration's widget: the report and under it the footer ("Open in exploration", the feedback line) in one
     * container, on its menu page and in the dashboard's widget list.
     */
    public function container(): WidgetContainerConfig
    {
        $idSaved = (int) $this->saved['idsaved'];
        $report = (new ReportWidgetFactory($this))->createWidget();
        $report->setIsNotWidgetizable();
        $footer = new WidgetConfig();
        $footer->setModule('FeinwerkDataExploration');
        $footer->setAction('reportFooter');
        $footer->setParameters(['idSaved' => $idSaved]);
        $footer->setName($this->name);
        $footer->setIsNotWidgetizable();

        $container = new WidgetContainerConfig();
        $container->setId('fwdeSavedContainer' . $idSaved);
        $container->setCategoryId($this->categoryId);
        $container->setSubcategoryId((string) $this->subcategoryId);
        $container->setName($this->name);
        $container->setOrder($this->order);
        $container->setParameters(['idSaved' => $idSaved]);
        $container->addWidgetConfig($report);
        $container->addWidgetConfig($footer);
        $container->setIsWidgetizable();
        return $container;
    }

    /** The saved view: a table, bars as a bar chart, the donut as a pie (trend and "both" open as a table). */
    public function getDefaultTypeViewDataTable()
    {
        $state = $this->saved['state'] ?? [];
        if (($state['fwdeView'] ?? '') === 'chart') {
            $chartType = $state['fwdeChartType'] ?? '';
            if ($chartType === 'donut') {
                return Pie::ID;
            }
            if ($chartType === '' || $chartType === 'bar') {
                return Bar::ID;
            }
        }
        return HtmlTable::ID;
    }

    public function configureView(ViewDataTable $view)
    {
        if ($this->saved === null) {
            return;
        }
        $idSaved = (int) $this->saved['idsaved'];
        $view->requestConfig->request_parameters_to_modify['idSaved'] = $idSaved;
        $view->requestConfig->request_parameters_to_modify['reportUniqueId'] = 'FeinwerkDataExploration_getSavedReport_idSaved--' . $idSaved;
        // row evolution asks several periods at once, which a saved report does not answer (archived or not; final
        // review 2026-11-26); the goals view would ask Matomo's goal archives, which an exploration has not
        $archived = $this->archived();
        $view->config->disable_row_evolution = true;
        $view->config->show_goals = false;
        $note = $archived !== null ? trim($this->pendingNote($archived) . ' ' . $this->coverageNote($archived)) : '';
        if ($note !== '') {
            $view->config->show_footer_message = $note;
        }
        // a dashboard widget (widget=1) has a header with the name: no second title (Peter, 2026-11-25: the widget did
        // not look good yet); the report's own menu page keeps its title and Matomo's card, and so does Matomo 6's
        // Widgetize embed of the container, whose child draws its own header (own_report_header=1, the only place
        // for the name there)
        if (Common::getRequestVar('widget', 0, 'int') === 1 && Common::getRequestVar('own_report_header', 0, 'int') !== 1) {
            $view->config->show_title = false;
        }
        $view->config->show_table_all_columns = false;
        if ($this->dimension !== null) {
            $view->config->addTranslation('label', $this->dimension->getName());
        }
        // Matomo's flat view puts each level in a column named by its dimension (FeinwerkDataExploration_<id>) and titles
        // only Matomo's own registered dimensions; without these, the levels' titles stayed empty or alike (Peter,
        // 2026-11-25: "dimension1 und dimension2 namen sind immer noch gleich")
        foreach (array_filter(array_merge([$this->dimension], $this->levels)) as $dimension) {
            $view->config->addTranslation(str_replace('.', '_', $dimension->getId()), $dimension->getName());
        }
        foreach ($this->metricNames as $id => $name) {
            $view->config->addTranslation($id, $name);
        }
        $view->config->columns_to_display = array_merge(['label'], array_keys($this->metricNames));
        // a long period from the archive has no distinct metrics (unique visitors): no empty column for them
        if ($archived !== null && $archived->distinctMetrics() !== [] && $this->isLong($archived)) {
            $view->config->columns_to_display = array_values(array_diff($view->config->columns_to_display, $archived->distinctMetrics()));
        }
        $state = $this->saved['state'];
        $sort = (string) ($state['fwdeSort'] ?? '');
        if ($sort !== '') {
            $view->requestConfig->filter_sort_column = in_array($sort, ExplorationDefinition::LABEL_COLUMNS, true) ? 'label' : $sort;
            $view->requestConfig->filter_sort_order = ($state['fwdeOrder'] ?? '') === 'asc' ? 'asc' : 'desc';
        }
    }
}
