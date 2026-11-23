/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/** Static props of the exploration page (Widgets/GetExploration.php). */
export interface PageConfig {
  idSite: number;
  maxRows: number;
  defaultRows: number;
  maxDays: number;
  rawDataFrom: string|null;
  currencySymbol: string;
  /** the 24 hour labels as the API writes them */
  hourLabels: string[];
}

export interface DimensionInfo {
  id: string;
  name: string;
  group: string;
  scope: 'visit'|'action';
  /** Kinds of action rows that can have a value (pageview, event, download, outlink, search). */
  kinds: string[];
}

export interface MetricInfo {
  id: string;
  scope: 'visit'|'goal'|'action';
  name: string;
  nameInActionScope: string|null;
  /** Kind of action rows an action metric counts, null for visit and goal metrics. */
  kind: string|null;
  /** what the metric counts (Matomo's description), for the tooltip */
  description?: string;
}

export interface Catalog {
  dimensions: DimensionInfo[];
  metrics: MetricInfo[];
}

export type FilterOperator = 'contains'|'equals'|'notContains'|'notEquals'|'startsWith'|'endsWith'|'gt'|'lt';

export interface RowFilter {
  /** 'label', 'secondary_label' or a selected metric id */
  target: string;
  operator: FilterOperator;
  value: string;
}

/** Result as a table, a chart or both (null: the default for the result). */
export type ChartView = 'table'|'chart'|'both';

/** Bars of the rows, or the trend of the first rows over the days or hours. */
export type ChartType = 'bar'|'trend'|'donut';

/** The exact rest of a cut table ("Others", on demand): not asked, running, failed, calculated. */
export type OthersState = 'idle'|'loading'|'failed'|'done';

/** How the filters combine: "all" with AND, "any" with OR (2026-11-20). */
export type FilterMatch = 'all'|'any';

export interface ExplorationState {
  dimensions: string[];
  metrics: string[];
  idGoal: number|null;
  sort: string;
  sortOrder: 'asc'|'desc';
  limit: number;
  filters: RowFilter[];
  view: ChartView|null;
  /** the metric of the chart, null: the default (chartMetricFor) */
  chartMetric: string|null;
  /** bars or the trend (trend design 3), null: bars */
  chartType: ChartType|null;
  /** "all" joins the filters with AND, "any" with OR (2026-11-20) */
  match: FilterMatch;
}

/** Kinds of funnel steps (FunnelStep::TYPES in PHP). */
export type StepType = 'pageUrl'|'pageTitle'|'eventCategory'|'eventAction'|'eventName'|'siteSearch'
  |'download'|'outlink'|'goal'|'anyGoal';

/** '' for goal steps */
export type StepOperator = 'contains'|'equals'|'startsWith'|'';

export interface FunnelStepState {
  type: StepType;
  operator: StepOperator;
  /** the text as typed (trimmed only for the request) */
  value: string;
  /** goal steps: the goal, null until one is chosen */
  idGoal: number|null;
}

/** One row of FeinwerkDataExploration.getFunnel. */
export interface FunnelRow {
  label: string;
  step: number;
  nb_visits: number;
  /** percent */
  share_of_start: number;
  /** percent */
  share_of_previous: number;
  nb_dropoffs: number;
}

/** A column of the comparison table: the own view or one of Matomo's comparisons. */
export interface ComparisonColumn {
  segment: string;
  period: string;
  date: string;
  title: string;
  /** the segment's title, the first line of the column title */
  segmentTitle?: string;
  /** the period's title, the second line ('' when only segments are compared) */
  periodTitle?: string;
  /** own: the page's segment and period; period/both: changes against the page's period */
  kind: 'own'|'segment'|'period'|'both';
}
