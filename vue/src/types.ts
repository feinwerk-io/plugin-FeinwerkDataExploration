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
  /** the first day a changed archived exploration's history reaches again, null: nothing lost */
  archiveHistoryFrom: string|null;
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
  /** the metric list's heading (more metrics, 2026-11-24); from scope when missing */
  group?: 'visits'|'goals'|'ecommerce'|'actions';
  /** the goal of a per-goal metric */
  idGoal?: number|null;
  unit?: string;
  aggregation?: string;
}

export interface Catalog {
  dimensions: DimensionInfo[];
  metrics: MetricInfo[];
}

export type FilterOperator = 'contains'|'equals'|'notContains'|'notEquals'|'startsWith'|'endsWith'
  |'notStartsWith'|'notEndsWith'|'matches'|'empty'|'notEmpty'|'gt'|'gte'|'lt'|'lte';

export interface RowFilter {
  /** 'label', 'secondary_label', 'tertiary_label' or a selected metric id */
  target: string;
  operator: FilterOperator;
  value: string;
  /** the index of its group (filter groups, 2026-11-23) */
  group: number;
}

/** Result as a table, a chart or both (null: the default for the result). */
export type ChartView = 'table'|'chart'|'both';

/** Bars of the rows, or the trend of the first rows over the days or hours. */
export type ChartType = 'bar'|'trend'|'donut';

/** The exact rest of a cut table ("Others", on demand): not asked, running, failed, calculated. */
export type OthersState = 'idle'|'loading'|'failed'|'done';

/** How conditions or groups combine: "all" with AND, "any" with OR (2026-11-20). */
export type FilterMatch = 'all'|'any';

export interface ExplorationState {
  dimensions: string[];
  metrics: string[];
  sort: string;
  sortOrder: 'asc'|'desc';
  limit: number;
  filters: RowFilter[];
  view: ChartView|null;
  /** the metric of the chart, null: the default (chartMetricFor) */
  chartMetric: string|null;
  /** bars or the trend (trend design 3), null: bars */
  chartType: ChartType|null;
  /** how the filter groups combine (2026-11-23; before: how the filters combine) */
  match: FilterMatch;
  /** how the conditions of each group combine, by group index */
  groupMatch: FilterMatch[];
  /** the pivot dimension (rows and pivot, 2026-11-24), null: none */
  pivot: string|null;
  /** how many of the pivot's values become columns: 3, 5 or 10 */
  pivotLimit: number;
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
