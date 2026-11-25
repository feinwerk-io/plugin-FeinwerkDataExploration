/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  Catalog, ChartType, ChartView, ExplorationState,
} from './types';
import {
  filterGroupsDefinition, filterParam, matchParam, parseFilterParam,
} from './filterGroups';
import { labelColumns } from './labels';
import { parsePivot } from './pivot';

// the filter rules live in filterGroups.ts (filter groups, 2026-11-23); the page imports them here
export {
  isValidFilter, MAX_FILTERS, MAX_FILTER_TEXT, MAX_GROUPS, MAX_PATTERN, NUMBER_OPERATORS,
  TEXT_OPERATORS,
} from './filterGroups';

export const DEFAULT_DIMENSIONS = ['channelType'];
export const DEFAULT_METRICS = ['nb_visits', 'bounce_rate', 'nb_conversions'];
export const LIMIT_OPTIONS = [10, 25, 50, 100, 250, 500, 1000];
/** ExplorationDefinition::MAX_METRICS (more metrics, 2026-11-24) */
export const MAX_METRICS = 10;
/** row dimensions (ExplorationDefinition::MAX_DIMENSIONS; three since rows and pivot) */
export const MAX_DIMENSIONS = 3;
export const VIEWS: ChartView[] = ['table', 'chart', 'both'];
export const CHART_TYPES: ChartType[] = ['bar', 'trend', 'donut'];

export interface Limits {
  defaultRows: number;
  maxRows: number;
}

function knownIds(value: unknown, known: string[], max: number): string[] {
  const ids: string[] = [];
  String(value || '').split(',').forEach((id) => {
    if (known.includes(id) && !ids.includes(id) && ids.length < max) {
      ids.push(id);
    }
  });
  return ids;
}

/** the all-goals metrics an older link narrowed to one goal with fwdeGoal */
const ALL_GOALS_METRICS = ['nb_conversions', 'conversion_rate', 'revenue'];

/**
 * The hash of a link from before more metrics (2026-11-24) with a goal chosen (fwdeGoal=N): the
 * all-goals metrics become goal N's own metrics wherever the hash names metrics (list, sorting,
 * chart, row filter targets), the same numbers. Unchanged when the website no longer has goal N:
 * then the metrics stay "all goals".
 */
function withGoalMetrics(
  hash: Record<string, unknown>,
  metricIds: string[],
): Record<string, unknown> {
  const goal = Number(hash.fwdeGoal);
  if (!Number.isInteger(goal) || goal <= 0 || !metricIds.includes(`goal_${goal}_nb_conversions`)) {
    return hash;
  }
  const map = (id: string) => (ALL_GOALS_METRICS.includes(id) ? `goal_${goal}_${id}` : id);
  const mapped: Record<string, unknown> = { ...hash };
  ['fwdeMetrics', 'fwdeSort', 'fwdeChart'].forEach((key) => {
    if (hash[key]) {
      mapped[key] = String(hash[key]).split(',').map(map).join(',');
    }
  });
  if (hash.fwdeFilters) {
    // groups by "|", an optional "all:"/"any:" prefix, conditions by ";": "target~operator~value"
    mapped.fwdeFilters = String(hash.fwdeFilters).split('|').map((group) => {
      const prefix = (group.match(/^(all|any):/) || [''])[0];
      return prefix + group.substring(prefix.length).split(';').map((condition) => {
        const cut = condition.indexOf('~');
        return cut < 0 ? condition : map(condition.substring(0, cut)) + condition.substring(cut);
      }).join(';');
    }).join('|');
  }
  return mapped;
}

/** The count that fits a dimension's actions, e.g. pageviews for page URLs. */
const KIND_METRICS: Record<string, string> = {
  pageview: 'nb_pageviews',
  event: 'nb_events',
  download: 'nb_downloads',
  outlink: 'nb_outlinks',
  search: 'nb_searches',
};

/**
 * Metrics an exploration starts with when the link names none (e.g. "Open in exploration" of a
 * standard report): the count of the dimension's first action kind and visits; otherwise the
 * general defaults. Metrics the catalog does not have are left out.
 */
export function defaultMetricsFor(dimensionId: string, catalog: Catalog): string[] {
  const dimension = catalog.dimensions.find((d) => d.id === dimensionId);
  const kindMetric = dimension?.kinds.length ? KIND_METRICS[dimension.kinds[0]] : undefined;
  const ids = kindMetric ? [kindMetric, 'nb_visits'] : DEFAULT_METRICS;
  const metricIds = catalog.metrics.map((m) => m.id);
  return ids.filter((id) => metricIds.includes(id));
}

/**
 * The exploration state from the hash parameters; invalid parts fall back to valid values.
 */
export function parseState(
  rawHash: Record<string, unknown>,
  catalog: Catalog,
  limits: Limits,
): ExplorationState {
  const dimensionIds = catalog.dimensions.map((d) => d.id);
  const metricIds = catalog.metrics.map((m) => m.id);
  const hash = withGoalMetrics(rawHash, metricIds);

  let dimensions = knownIds(hash.fwdeDims, dimensionIds, MAX_DIMENSIONS);
  if (!dimensions.length) {
    dimensions = knownIds(DEFAULT_DIMENSIONS.join(','), dimensionIds, 2);
    if (!dimensions.length && dimensionIds.length) {
      dimensions = [dimensionIds[0]];
    }
  }
  let metrics = knownIds(hash.fwdeMetrics, metricIds, MAX_METRICS);
  if (!metrics.length) {
    metrics = defaultMetricsFor(dimensions[0] || '', catalog);
  }

  const view = String(hash.fwdeView) as ChartView;
  const limit = Number(hash.fwdeLimit);
  const sortColumns = [...metrics, ...labelColumns(dimensions.length)];
  const sort = String(hash.fwdeSort || '');

  return {
    dimensions,
    metrics,
    sort: sortColumns.includes(sort) ? sort : metrics[0],
    sortOrder: hash.fwdeOrder === 'asc' ? 'asc' : 'desc',
    limit: LIMIT_OPTIONS.includes(limit) && limit <= limits.maxRows ? limit : limits.defaultRows,
    ...parseFilterParam(hash.fwdeFilters, hash.fwdeMatch, dimensions.length, metrics),
    view: VIEWS.includes(view) ? view : null,
    chartMetric: metrics.includes(String(hash.fwdeChart)) ? String(hash.fwdeChart) : null,
    chartType: CHART_TYPES.includes(hash.fwdeChartType as ChartType)
      ? hash.fwdeChartType as ChartType
      : null,
    ...parsePivot(hash.fwdePivot, hash.fwdePivotLimit, dimensions, metrics.length, catalog),
  };
}

/**
 * Every key toHashParams writes: the page reloads when one of them changes (a key missing here
 * would change the link and nothing else).
 */
export const STATE_HASH_KEYS = [
  'fwdeDims', 'fwdeMetrics', 'fwdeGoal', 'fwdeSort', 'fwdeOrder', 'fwdeLimit', 'fwdeFilters',
  'fwdeView', 'fwdeChart', 'fwdeChartType', 'fwdeMatch', 'fwdePivot', 'fwdePivotLimit',
];

/**
 * All parameters, empty ones too: the hash is merged, so an omitted parameter would keep its old
 * value.
 */
export function toHashParams(state: ExplorationState): Record<string, string> {
  return {
    fwdeDims: state.dimensions.join(','),
    fwdeMetrics: state.metrics.join(','),
    // the older goal choice: always cleared, the goal's own metrics replace it
    fwdeGoal: '',
    fwdeSort: state.sort,
    fwdeOrder: state.sortOrder,
    fwdeLimit: String(state.limit),
    fwdeFilters: filterParam(state.filters, state.groupMatch),
    fwdeView: state.view || '',
    fwdeChart: state.chartMetric || '',
    fwdeChartType: state.chartType || '',
    fwdeMatch: matchParam(state.filters, state.match),
    fwdePivot: state.pivot || '',
    fwdePivotLimit: state.pivot ? String(state.pivotLimit) : '',
  };
}

/**
 * The definition for FeinwerkDataExploration.getExploration. While Matomo compares segments or
 * periods the pivot stays in the state but is not sent (pivot and comparison are exclusive, the
 * comparison wins).
 */
export function toDefinition(state: ExplorationState, comparing = false): Record<string, unknown> {
  const definition: Record<string, unknown> = {
    dimensions: state.dimensions,
    metrics: state.metrics,
    sort: state.sort,
    sortOrder: state.sortOrder,
    limit: state.limit,
  };
  const filterGroups = filterGroupsDefinition(state);
  if (filterGroups) {
    definition.filterGroups = filterGroups;
  }
  if (state.pivot && !comparing) {
    definition.pivot = { dimension: state.pivot, limit: state.pivotLimit };
  }
  return definition;
}
