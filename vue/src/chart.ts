/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { Catalog, ChartView, ExplorationState } from './types';
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
const INTEGER_METRICS = [
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
}

export interface ChartSeries {
  label: string;
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

export function canChart(rowCount: number): boolean {
  return rowCount >= 2;
}

/** Both where a chart makes sense, the table otherwise; a chosen view only while it is possible. */
export function effectiveView(state: ExplorationState, rowCount: number): ChartView {
  if (!canChart(rowCount)) {
    return 'table';
  }
  return state.view || 'both';
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

function valueOf(row: ResultRow, metric: string): number {
  const value = Number(row[metric]);
  return Number.isFinite(value) ? value : 0;
}

/** Position on the time axis: dates as they are, hours and weekdays as Matomo orders them. */
function timeOrder(
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

function compareKeys(a: number|string, b: number|string): number {
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
 * The chart of a result: a line over time when the first dimension is a time dimension (one line
 * per value of the second dimension), otherwise one bar per row. Values are never added up, so
 * every metric works.
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
  const dimension = state.dimensions[0];
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
    rows,
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
