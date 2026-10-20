/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  Catalog, ChartView, ExplorationState, FilterOperator, RowFilter,
} from './types';

export const DEFAULT_DIMENSIONS = ['channelType'];
export const DEFAULT_METRICS = ['nb_visits', 'bounce_rate', 'nb_conversions'];
export const LIMIT_OPTIONS = [10, 25, 50, 100, 250, 500, 1000];
export const MAX_METRICS = 5;
export const MAX_FILTERS = 5;
/** Long enough for a stored URL ("filter to this value" in the row menu). */
export const MAX_FILTER_TEXT = 1000;
export const TEXT_OPERATORS: FilterOperator[] = ['contains', 'equals', 'notContains'];
export const NUMBER_OPERATORS: FilterOperator[] = ['gt', 'lt'];
/** Shown in percent, sent to the API as fractions. */
export const RATE_METRICS = ['bounce_rate', 'conversion_rate'];
export const VIEWS: ChartView[] = ['table', 'chart', 'both'];

interface Limits {
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

function labelTargets(dimensionCount: number): string[] {
  return dimensionCount === 2 ? ['label', 'secondary_label'] : ['label'];
}

export function isValidFilter(
  filter: RowFilter,
  dimensionCount: number,
  metrics: string[],
): boolean {
  if (TEXT_OPERATORS.includes(filter.operator)) {
    const text = filter.value.trim();
    return labelTargets(dimensionCount).includes(filter.target)
      && text !== '' && text.length <= MAX_FILTER_TEXT;
  }
  if (NUMBER_OPERATORS.includes(filter.operator)) {
    return metrics.includes(filter.target)
      && filter.value.trim() !== '' && Number.isFinite(Number(filter.value));
  }
  return false;
}

/** "target~operator~value;…" with the value URI-encoded; only the first two "~" separate. */
function parseFilters(value: unknown, dimensionCount: number, metrics: string[]): RowFilter[] {
  const filters: RowFilter[] = [];
  String(value || '').split(';').forEach((entry) => {
    const first = entry.indexOf('~');
    const second = first < 0 ? -1 : entry.indexOf('~', first + 1);
    if (second < 0 || filters.length >= MAX_FILTERS) {
      return;
    }
    let text: string;
    try {
      text = decodeURIComponent(entry.substring(second + 1));
    } catch (e) {
      return;
    }
    const filter = {
      target: entry.substring(0, first),
      operator: entry.substring(first + 1, second) as FilterOperator,
      value: text,
    };
    if (isValidFilter(filter, dimensionCount, metrics)) {
      filters.push({ ...filter, value: filter.value.trim() });
    }
  });
  return filters;
}

/** The count that fits a dimension's actions, e.g. pageviews for page URLs. */
const KIND_METRICS: Record<string, string> = {
  pageview: 'nb_pageviews',
  event: 'nb_events',
  download: 'nb_downloads',
  outlink: 'nb_outlinks',
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
  hash: Record<string, unknown>,
  catalog: Catalog,
  limits: Limits,
): ExplorationState {
  const dimensionIds = catalog.dimensions.map((d) => d.id);
  const metricIds = catalog.metrics.map((m) => m.id);

  let dimensions = knownIds(hash.fwdeDims, dimensionIds, 2);
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
  const goal = Number(hash.fwdeGoal);
  const limit = Number(hash.fwdeLimit);
  const sortColumns = [...metrics, ...labelTargets(dimensions.length)];
  const sort = String(hash.fwdeSort || '');

  return {
    dimensions,
    metrics,
    idGoal: Number.isInteger(goal) && goal > 0 ? goal : null,
    sort: sortColumns.includes(sort) ? sort : metrics[0],
    sortOrder: hash.fwdeOrder === 'asc' ? 'asc' : 'desc',
    limit: LIMIT_OPTIONS.includes(limit) && limit <= limits.maxRows ? limit : limits.defaultRows,
    filters: parseFilters(hash.fwdeFilters, dimensions.length, metrics),
    view: VIEWS.includes(view) ? view : null,
    chartMetric: metrics.includes(String(hash.fwdeChart)) ? String(hash.fwdeChart) : null,
  };
}

/**
 * All parameters, empty ones too: the hash is merged, so an omitted parameter would keep its old
 * value.
 */
export function toHashParams(state: ExplorationState): Record<string, string> {
  return {
    fwdeDims: state.dimensions.join(','),
    fwdeMetrics: state.metrics.join(','),
    fwdeGoal: state.idGoal === null ? '' : String(state.idGoal),
    fwdeSort: state.sort,
    fwdeOrder: state.sortOrder,
    fwdeLimit: String(state.limit),
    fwdeFilters: state.filters
      .map((f) => `${f.target}~${f.operator}~${encodeURIComponent(f.value)}`)
      .join(';'),
    fwdeView: state.view || '',
    fwdeChart: state.chartMetric || '',
  };
}

/**
 * The definition for FeinwerkDataExploration.getExploration.
 */
export function toDefinition(state: ExplorationState): Record<string, unknown> {
  const definition: Record<string, unknown> = {
    dimensions: state.dimensions,
    metrics: state.metrics,
    sort: state.sort,
    sortOrder: state.sortOrder,
    limit: state.limit,
    filters: state.filters.map((f) => {
      let { value }: { value: string|number } = f;
      if (NUMBER_OPERATORS.includes(f.operator)) {
        value = RATE_METRICS.includes(f.target) ? Number(f.value) / 100 : Number(f.value);
      }
      return { target: f.target, operator: f.operator, value };
    }),
  };
  if (state.idGoal !== null) {
    definition.idGoal = state.idGoal;
  }
  return definition;
}
