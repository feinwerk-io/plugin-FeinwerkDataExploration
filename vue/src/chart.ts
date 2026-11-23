/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  Catalog, ChartType, ChartView, ComparisonColumn, ExplorationState, OthersState,
} from './types';
import {
  changeKey, columnLabel, SHARE_METRICS, valueKey,
} from './comparison';
import { addsUp } from './additivity';
import { ResultRow } from './api';
import { NUMBER_OPERATORS } from './state';

/** First dimensions shown as a line over time instead of bars. */
export const TIME_DIMENSIONS = ['date', 'hour', 'weekday'];

/** Metrics where a missing row is not zero (a rate or an average of no visits): lines get a gap. */
const NO_ZERO_METRICS = [
  'bounce_rate', 'conversion_rate', 'avg_time_on_site', 'nb_actions_per_visit', 'avg_event_value',
  'max_actions',
];

/** Metrics counted in whole numbers (and seconds): their axis steps by whole numbers. */
export const INTEGER_METRICS = [
  'nb_visits', 'nb_uniq_visitors', 'nb_actions', 'nb_conversions', 'nb_pageviews',
  'nb_uniq_pageviews', 'nb_events', 'nb_downloads', 'nb_outlinks', 'avg_time_on_site',
  'nb_searches', 'max_actions',
];

const NICE_STEPS = [1, 2, 2.5, 5, 10];

export interface ChartOptions {
  /** Matomo's weekday names, Monday first, as the API writes them */
  weekdayNames: string[];
  /** Matomo's 24 hour labels (0 to 23) as the API writes them */
  hourLabels?: string[];
  /** every day of the period (Y-m-d), null when unknown */
  periodDays?: string[]|null;
  /** false when the result was cut at the row limit */
  complete?: boolean;
  tickCount?: number;
  maxBars?: number;
  maxSeries?: number;
}

export interface Scale {
  min: number;
  max: number;
  ticks: number[];
}

export interface ChartPoint {
  label: string;
  /** null: no value (a gap in a line) */
  value: number|null;
  /** the real date or hour of a compared period lined up by position (tooltip) */
  date?: string;
  /** a compared period's change against the page's period, in percent (grouped bars' tooltip) */
  change?: number|null;
}

export interface ChartSeries {
  label: string;
  /** a compared period other than the page's (trend) */
  dashed?: boolean;
  /** a comparison column: coloured as Matomo colours its comparison series */
  comparison?: boolean;
  /** the trend's line (fwde_line): comparison line n is column n, also after a missing line */
  line?: number;
  /** one point per category, in the order of the categories */
  points: ChartPoint[];
}

export interface ChartModel {
  type: 'bar'|'line';
  categories: string[];
  series: ChartSeries[];
  /** rows (bars) or values of the second dimension (lines) not shown */
  omittedRows: number;
  omittedSeries: number;
  /** a line chart of a result cut at the row limit: points outside the result are gaps */
  incomplete: boolean;
  scale: Scale;
}

/** The title of the result: the names of its dimensions, e.g. "Channel type × Browser". */
export function resultTitle(state: ExplorationState, catalog: Catalog): string {
  return state.dimensions
    .map((id) => catalog.dimensions.find((d) => d.id === id)?.name || id)
    .join(' × ');
}

/** Readable steps between axis labels: days, weeks, fortnights; hours in parts of a day. */
const LABEL_STEPS: Record<'day'|'hour', number[]> = {
  day: [1, 2, 7, 14, 28],
  hour: [1, 2, 3, 4, 6, 12],
};

/**
 * Every how many categories the time axis is labelled: the smallest readable step whose labels
 * do not overlap (days weekly as in Matomo's graphs, always on the first day's weekday). When even
 * the largest step does not fit, the first and the last category, or only the first.
 *
 * @param plotWidth the width from the first to the last category, in pixels
 * @param labelWidth the width one label needs, gap included
 */
export function axisLabelStep(
  count: number,
  plotWidth: number,
  labelWidth: number,
  kind: 'day'|'hour'|'other',
): number {
  if (count <= 1) {
    return 1;
  }
  const spacing = plotWidth / (count - 1);
  const steps = kind === 'other'
    ? Array.from({ length: count - 1 }, (_, i) => i + 1)
    : LABEL_STEPS[kind].filter((step) => step < count);
  const fitting = steps.find((step) => step * spacing >= labelWidth);
  if (fitting !== undefined) {
    return fitting;
  }
  return plotWidth >= labelWidth ? count - 1 : count;
}

/** A trend is possible without a time dimension (with one, the chart has a time axis already). */
export function canTrend(state: ExplorationState): boolean {
  return !state.dimensions.some((id) => TIME_DIMENSIONS.includes(id));
}

/** Two rows or more; one row when its trend can be shown (with the state). */
export function canChart(rowCount: number, state?: ExplorationState): boolean {
  return rowCount >= 2
    || (rowCount === 1 && state !== undefined && canTrend(state));
}

/**
 * Both where a chart makes sense, the table otherwise; a chosen view only while it is possible.
 * One row (its trend) shows the table unless a chart view was chosen.
 */
export function effectiveView(state: ExplorationState, rowCount: number): ChartView {
  if (rowCount >= 2) {
    return state.view || 'both';
  }
  return canChart(rowCount, state) ? state.view || 'table' : 'table';
}

/** The chosen chart metric, else the sorted metric, else the first metric. */
export function chartMetricFor(state: ExplorationState): string {
  if (state.chartMetric && state.metrics.includes(state.chartMetric)) {
    return state.chartMetric;
  }
  return state.metrics.includes(state.sort) ? state.sort : state.metrics[0];
}

function finite(value: number): number {
  return Number.isFinite(value) ? value : 0;
}

/** Rounds to the precision of the step, so ticks print as 0.3 and not 0.30000000000000004. */
function round(value: number, step: number): number {
  const decimals = Math.min(20, Math.max(0, -Math.floor(Math.log10(step)) + 2));
  return Number(value.toFixed(decimals));
}

const UNIT_SCALE: Scale = { min: 0, max: 1, ticks: [0, 0.2, 0.4, 0.6, 0.8, 1] };

/**
 * An axis from zero (or the smallest negative value) to a round maximum with about `tickCount`
 * steps; whole-number steps for counts.
 */
export function niceScale(
  minValue: number,
  maxValue: number,
  tickCount = 5,
  integer = false,
): Scale {
  const low = Math.min(finite(minValue), 0);
  const high = Math.max(finite(maxValue), 0);
  const span = high - low;
  if (span < 1e-9) {
    return UNIT_SCALE;
  }
  if (!Number.isFinite(span) || Math.max(-low, high) > 1e300) {
    // too large for round steps (e.g. an event value near the largest number)
    return { min: low, max: high, ticks: [low, high] };
  }
  const raw = span / Math.max(1, tickCount);
  const magnitude = 10 ** Math.floor(Math.log10(raw));
  const candidates = NICE_STEPS
    .map((s) => s * magnitude)
    .filter((s) => !integer || Number.isInteger(s));
  const fallback = integer ? Math.max(1, Math.ceil(raw)) : 10 * magnitude;
  const step = candidates.find((s) => s >= raw) || fallback;
  const min = round(Math.floor(low / step) * step, step);
  const max = round(Math.ceil(high / step) * step, step);
  const count = Math.min(50, Math.round((max - min) / step));
  const ticks = Array.from({ length: count + 1 }, (_, i) => round(min + i * step, step));
  return { min, max, ticks };
}

/** a cell's number, or null for a missing or empty one (never NaN) */
function numberOrNull(value: unknown): number|null {
  if (value === null || value === undefined || value === '') {
    return null;
  }
  const number = Number(value);
  return Number.isFinite(number) ? number : null;
}

function valueOf(row: ResultRow, metric: string): number {
  const value = Number(row[metric]);
  return Number.isFinite(value) ? value : 0;
}

/** Position on the time axis: dates as they are, hours and weekdays as Matomo orders them. */
export function timeOrder(
  dimension: string,
  weekdayNames: string[],
  hourLabels: string[],
): (label: string) => number|string {
  if (dimension === 'hour') {
    return (label) => {
      const index = hourLabels.indexOf(label);
      return index < 0 ? parseInt(label, 10) : index;
    };
  }
  if (dimension === 'weekday') {
    return (label) => {
      const index = weekdayNames.indexOf(label);
      return index < 0 ? weekdayNames.length : index;
    };
  }
  return (label) => label;
}

export function compareKeys(a: number|string, b: number|string): number {
  if (typeof a === 'number' && typeof b === 'number') {
    return (Number.isNaN(a) ? Infinity : a) - (Number.isNaN(b) ? Infinity : b);
  }
  return String(a).localeCompare(String(b));
}

function rowLabel(row: ResultRow): string {
  return row.secondary_label === undefined
    ? String(row.label)
    : `${row.label} · ${row.secondary_label}`;
}

function barModel(
  rows: ResultRow[],
  metric: string,
  maxBars: number,
  tickCount: number,
): ChartModel {
  const top = [...rows]
    .sort((a, b) => valueOf(b, metric) - valueOf(a, metric))
    .slice(0, maxBars);
  const values = top.map((row) => valueOf(row, metric));
  return {
    type: 'bar',
    categories: top.map(rowLabel),
    series: [{
      label: metric,
      points: top.map((row) => ({ label: rowLabel(row), value: valueOf(row, metric) })),
    }],
    omittedRows: Math.max(0, rows.length - maxBars),
    omittedSeries: 0,
    incomplete: false,
    scale: niceScale(
      Math.min(...values),
      Math.max(...values),
      tickCount,
      INTEGER_METRICS.includes(metric),
    ),
  };
}

function lineModel(
  rows: ResultRow[],
  metric: string,
  order: (label: string) => number|string,
  /** every value of the time axis when missing values are real zeros, else null */
  allCategories: string[]|null,
  zeroForMissing: boolean,
  complete: boolean,
  maxSeries: number,
  tickCount: number,
): ChartModel {
  const present = rows.map((row) => String(row.label));
  const categories = [...new Set([...(allCategories || []), ...present])]
    .sort((a, b) => compareKeys(order(a), order(b)));
  const hasSecond = rows.some((row) => row.secondary_label !== undefined);
  const groups = new Map<string, ResultRow[]>();
  rows.forEach((row) => {
    const key = hasSecond ? String(row.secondary_label) : metric;
    groups.set(key, [...(groups.get(key) || []), row]);
  });
  // the largest values of the second dimension by their total, ties by name
  const ranked = [...groups.entries()]
    .map(([label, groupRows]) => ({
      label,
      groupRows,
      total: groupRows.reduce((sum, row) => sum + valueOf(row, metric), 0),
    }))
    .sort((a, b) => b.total - a.total || a.label.localeCompare(b.label));
  const kept = ranked.slice(0, maxSeries);
  const missing = zeroForMissing ? 0 : null;
  const series = kept.map(({ label, groupRows }) => ({
    label,
    points: categories.map((category) => {
      const row = groupRows.find((r) => String(r.label) === category);
      return { label: category, value: row ? valueOf(row, metric) : missing };
    }),
  }));
  const values = series
    .flatMap((s) => s.points.map((p) => p.value))
    .filter((value): value is number => value !== null);
  return {
    type: 'line',
    categories,
    series,
    omittedRows: 0,
    omittedSeries: ranked.length - kept.length,
    incomplete: !complete,
    scale: niceScale(
      Math.min(0, ...values),
      Math.max(0, ...values),
      tickCount,
      INTEGER_METRICS.includes(metric),
    ),
  };
}

/**
 * The bar chart while comparing: the largest rows of the own view by the metric, one series per
 * comparison column (own view first), coloured as Matomo's comparison series; a compared period
 * carries its change for the tooltip.
 */
export function compareBarModel(
  rows: ResultRow[],
  columns: ComparisonColumn[],
  metric: string,
  options: { maxBars?: number, tickCount?: number },
): ChartModel {
  const { maxBars = 10, tickCount = 5 } = options;
  const data = rows.filter((row) => !row.fwde_total);
  const top = [...data]
    .sort((a, b) => (numberOrNull(b[metric]) ?? 0) - (numberOrNull(a[metric]) ?? 0))
    .slice(0, maxBars);
  const series: ChartSeries[] = columns.map((column, n) => ({
    label: columnLabel(column),
    comparison: true,
    points: top.map((row) => {
      const point: ChartPoint = {
        label: rowLabel(row),
        value: numberOrNull(row[valueKey(metric, n)]),
      };
      if (column.kind === 'period' || column.kind === 'both') {
        point.change = numberOrNull(row[changeKey(metric, n)]);
      }
      return point;
    }),
  }));
  const values = series.flatMap((s) => s.points.map((p) => p.value))
    .filter((value): value is number => value !== null);
  return {
    type: 'bar',
    categories: top.map(rowLabel),
    series,
    omittedRows: Math.max(0, data.length - maxBars),
    omittedSeries: 0,
    incomplete: false,
    scale: niceScale(
      Math.min(0, ...values),
      Math.max(0, ...values),
      tickCount,
      INTEGER_METRICS.includes(metric),
    ),
  };
}

/**
 * The hint above a compared chart (translation key, '' for none): grouped bars show every column
 * themselves; the lines of a time dimension and the trend show column totals; a time dimension in
 * second place shows the own view.
 */
export function compareChartHint(
  state: ExplorationState,
  chartType: ChartType,
  columnCount: number,
): string {
  const [first, second] = state.dimensions;
  const singleTime = TIME_DIMENSIONS.includes(first) && second === undefined;
  if (chartType === 'trend' || (singleTime && columnCount > 1)) {
    return 'FeinwerkDataExploration_CompareTrendHint';
  }
  if (state.dimensions.some((id) => TIME_DIMENSIONS.includes(id))) {
    return 'FeinwerkDataExploration_CompareChartHint';
  }
  return columnCount > 1 ? '' : 'FeinwerkDataExploration_CompareChartHint';
}

/**
 * The chart of a result: a line over time when a dimension is a time dimension (the first one when
 * both are; one line per value of the other dimension), otherwise one bar per row. Values are never
 * added up, so every metric works.
 *
 * A missing point is zero only when the result is complete (not cut at the row limit), no metric
 * filter removed rows and the metric is a count or a sum; then every hour, weekday and day of the
 * period is on the axis. Otherwise it is a gap.
 */
export function chartModel(
  rows: ResultRow[],
  state: ExplorationState,
  metric: string,
  options: ChartOptions,
): ChartModel {
  const {
    weekdayNames, hourLabels = [], periodDays = null, complete = true,
    tickCount = 5, maxBars = 20, maxSeries = 5,
  } = options;
  let dimension = state.dimensions[0];
  let data = rows;
  if (!TIME_DIMENSIONS.includes(dimension) && TIME_DIMENSIONS.includes(state.dimensions[1])) {
    // "Browser x Date": the date is the axis, one line per browser (trend design 3)
    [, dimension] = state.dimensions;
    data = rows.map((row) => ({ ...row, label: row.secondary_label, secondary_label: row.label }));
  }
  if (!TIME_DIMENSIONS.includes(dimension)) {
    return barModel(rows, metric, maxBars, tickCount);
  }
  const metricFilter = state.filters.some((f) => NUMBER_OPERATORS.includes(f.operator));
  const zeroForMissing = complete && !metricFilter && !NO_ZERO_METRICS.includes(metric);
  let allCategories: string[]|null = null;
  if (zeroForMissing) {
    if (dimension === 'hour' && hourLabels.length === 24) {
      allCategories = hourLabels;
    } else if (dimension === 'weekday' && weekdayNames.length === 7) {
      allCategories = weekdayNames;
    } else if (dimension === 'date') {
      allCategories = periodDays;
    }
  }
  return lineModel(
    data,
    metric,
    timeOrder(dimension, weekdayNames, hourLabels),
    allCategories,
    zeroForMissing,
    complete,
    maxSeries,
    tickCount,
  );
}

function coordinate(value: number): string {
  return String(Math.round(value * 10) / 10);
}

/** SVG path through the values; a null value ends the line, the next value starts a new one. */
export function linePath(
  values: (number|null)[],
  x: (index: number) => number,
  y: (value: number) => number,
): string {
  const parts: string[] = [];
  let drawing = false;
  values.forEach((value, index) => {
    if (value === null) {
      drawing = false;
      return;
    }
    parts.push(`${drawing ? 'L' : 'M'}${coordinate(x(index))},${coordinate(y(value))}`);
    drawing = true;
  });
  return parts.join(' ');
}

/**
 * A label cut to a number of characters with an ellipsis in the middle, so labels that start alike
 * (URLs) keep their different ends.
 */
export function truncateLabel(label: string, maxChars: number): string {
  if (label.length <= maxChars) {
    return label;
  }
  const keep = Math.max(0, maxChars - 1);
  const tail = Math.floor(keep / 2);
  return `${label.slice(0, keep - tail)}…${tail > 0 ? label.slice(-tail) : ''}`;
}

/** A slice of the donut (design 2026-11-20); "Others" carries Matomo's key as its label. */
export interface DonutSlice {
  label: string;
  value: number;
  share: number;
  others?: boolean;
}

export interface DonutModel {
  slices: DonutSlice[];
  total: number;
}

export const DONUT_OTHERS_LABEL = 'General_Others';

/**
 * The donut of one count or sum: the largest rows (at most maxSlices) and "Others" for the rest
 * of the shown rows plus the exact rest of a cut table (others, null when not calculated).
 */
export function donutModel(
  rows: ResultRow[],
  metric: string,
  others: number|null,
  maxSlices = 9,
): DonutModel {
  const values = rows
    .map((row) => ({ label: rowLabel(row), value: Number(row[metric]) || 0 }))
    .filter((entry) => entry.value > 0)
    .sort((a, b) => b.value - a.value);
  const top = values.slice(0, maxSlices);
  const rest = values.slice(maxSlices).reduce((sum, entry) => sum + entry.value, 0)
    + (others !== null && others > 0 ? others : 0);
  const parts = rest > 0
    ? [...top, { label: DONUT_OTHERS_LABEL, value: rest, others: true }]
    : top;
  const total = parts.reduce((sum, entry) => sum + entry.value, 0);
  return {
    slices: parts.map((entry) => ({ ...entry, share: total > 0 ? entry.value / total : 0 })),
    total,
  };
}

/**
 * Why the donut cannot show the chart metric (a lang key), null when it can: only counts and
 * sums, only rows that add up to the total, not while comparing.
 */
export function donutBlocker(
  state: ExplorationState,
  catalog: Catalog,
  metric: string,
  comparing: boolean,
): string|null {
  if (comparing) {
    return 'FeinwerkDataExploration_DonutNotWhileComparing';
  }
  if (!SHARE_METRICS.includes(metric)) {
    return 'FeinwerkDataExploration_DonutNeedsCount';
  }
  if (!addsUp(state, catalog)) {
    return 'FeinwerkDataExploration_DonutNeedsSum';
  }
  return null;
}

/**
 * The chart that shows: the chosen donut while it qualifies (donutReason null) and there are two
 * rows or more, else the trend or the bars as chartTypeFor decides.
 */
export function effectiveChartType(
  state: ExplorationState,
  rowCount: number,
  donutReason: string|null,
): ChartType {
  // a time dimension draws a line over time and offers no switch, so no donut there either
  if (state.chartType === 'donut' && rowCount >= 2 && donutReason === null && canTrend(state)) {
    return 'donut';
  }
  if (!canTrend(state)) {
    return 'bar';
  }
  return rowCount === 1 || state.chartType === 'trend' ? 'trend' : 'bar';
}

/**
 * The SVG path of one ring segment from startShare to startShare + share of the circle (0 at the
 * top, clockwise); a share of the whole circle as two half rings.
 */
export function donutArc(
  startShare: number,
  share: number,
  outer: number,
  inner: number,
  centre: number,
): string {
  if (share >= 0.9999) {
    return `${donutArc(0, 0.5, outer, inner, centre)} ${donutArc(0.5, 0.5, outer, inner, centre)}`;
  }
  const point = (radius: number, at: number) => {
    const angle = at * 2 * Math.PI - Math.PI / 2;
    const x = centre + radius * Math.cos(angle);
    const y = centre + radius * Math.sin(angle);
    return `${Math.round(x * 100) / 100} ${Math.round(y * 100) / 100}`;
  };
  const end = startShare + share;
  const large = share > 0.5 ? 1 : 0;
  return `M ${point(outer, startShare)} A ${outer} ${outer} 0 ${large} 1 ${point(outer, end)} `
    + `L ${point(inner, end)} A ${inner} ${inner} 0 ${large} 0 ${point(inner, startShare)} Z`;
}

/**
 * Whether the page asks for the exact rest because of the donut (design 2026-11-20, on demand):
 * only while a donut of a cut table is on screen (not in the table view) and nothing runs.
 */
export function donutNeedsOthers(page: {
  chartType: ChartType,
  view: ChartView,
  cut: boolean,
  othersState: OthersState,
  loading: boolean,
}): boolean {
  return page.chartType === 'donut' && page.view !== 'table' && page.cut
    && page.othersState === 'idle' && !page.loading;
}

/**
 * The page's rest after the server answered: final either way. "No rest" (the server found the
 * table not cut) is an answer too, so the donut never asks again for the same table.
 */
export function othersAnswered(
  answer: Record<string, number|null>|null,
): { others: Record<string, number|null>|null, othersState: OthersState } {
  return { others: answer, othersState: 'done' };
}
