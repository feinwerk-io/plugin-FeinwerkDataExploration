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

export type FilterOperator = 'contains'|'equals'|'notContains'|'gt'|'lt';

export interface RowFilter {
  /** 'label', 'secondary_label' or a selected metric id */
  target: string;
  operator: FilterOperator;
  value: string;
}

/** Result as a table, a chart or both (null: the default for the result). */
export type ChartView = 'table'|'chart'|'both';

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
}
