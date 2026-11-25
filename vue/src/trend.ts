/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { isInteger } from './metricMeta';
import { LABEL_COLUMNS } from './labels';
import {
  canTrend, ChartModel, ChartOptions, ChartSeries, compareKeys, effectiveChartType,
  niceScale,
  timeOrder,
} from './chart';
import { columnLabel, valueKey } from './comparison';
import { ResultRow, TrendRow } from './api';
import { ChartType, ComparisonColumn, ExplorationState } from './types';

export { canTrend };
export type { TrendRow };

export interface TrendOptions {
  /** Matomo's 24 hour labels (0 to 23) */
  hourLabels: string[];
  /** rows of the table: the lines are its first rows */
  tableRows: number;
  tickCount?: number;
}

function numberOrNull(value: unknown): number|null {
  if (value === null || value === undefined || value === '') {
    return null;
  }
  const number = Number(value);
  return Number.isFinite(number) ? number : null;
}

function timeLabel(time: string, hourLabels: string[]): string {
  return /^\d{1,2}$/.test(time) ? hourLabels[Number(time)] || time : time;
}

function scaleOf(series: ChartSeries[], metric: string, tickCount: number) {
  const values = series
    .flatMap((s) => s.points.map((p) => p.value))
    .filter((value): value is number => value !== null);
  return niceScale(
    Math.min(0, ...values),
    Math.max(0, ...values),
    tickCount,
    isInteger(metric),
  );
}

function isOtherPeriod(kind: unknown): boolean {
  return kind === 'period' || kind === 'both';
}

/**
 * The trend chart (trend design 3): one line per line of getTrend, in its order. The axis has the
 * days or hours of line 0 (the page's period); point i of every line is that line's i-th day or
 * hour, so a compared period lines up by position: a shorter one ends early, a longer one is cut.
 */
export function trendModel(rows: TrendRow[], metric: string, options: TrendOptions): ChartModel {
  const { hourLabels, tableRows, tickCount = 5 } = options;
  const lines = new Map<number, TrendRow[]>();
  rows.forEach((row) => {
    const line = Number(row.fwde_line);
    lines.set(line, [...(lines.get(line) || []), row]);
  });
  const ordered = [...lines.entries()].sort((a, b) => a[0] - b[0]).map(([, lineRows]) => lineRows);
  const first = ordered[0] || [];
  const categories = first.map((row) => timeLabel(String(row.time), hourLabels));
  const series: ChartSeries[] = ordered.map((lineRows) => {
    const head = lineRows[0];
    const label = LABEL_COLUMNS
      .filter((column) => head[column] !== undefined && head[column] !== null)
      .map((column) => String(head[column])).join(' · ');
    return {
      label,
      dashed: isOtherPeriod(head.fwde_kind),
      comparison: head.fwde_kind !== undefined && head.fwde_kind !== null,
      line: Number(head.fwde_line),
      points: categories.map((category, i) => {
        const row = lineRows[i];
        if (!row) {
          return { label: category, value: null };
        }
        const time = String(row.time);
        return {
          label: category,
          value: numberOrNull(row[metric]),
          date: time !== String(first[i].time) ? timeLabel(time, hourLabels) : undefined,
        };
      }),
    };
  });
  const comparing = rows.some((row) => row.fwde_kind !== undefined && row.fwde_kind !== null);
  return {
    type: 'line',
    categories,
    series,
    // no lines yet (loading, an error): nothing is omitted
    omittedRows: comparing || !series.length ? 0 : Math.max(0, tableRows - series.length),
    omittedSeries: 0,
    incomplete: false,
    gaps: false,
    scale: scaleOf(series, metric, tickCount),
  };
}

/**
 * A table with one time dimension while comparing (trend design 3): one line per comparison column
 * from the table's values, over the table's rows in time order; the total row is left out.
 */
export function compareTimeModel(
  rows: ResultRow[],
  columns: ComparisonColumn[],
  state: ExplorationState,
  metric: string,
  options: ChartOptions,
): ChartModel {
  const { weekdayNames, hourLabels = [], tickCount = 5 } = options;
  const order = timeOrder(state.dimensions[0], weekdayNames, hourLabels);
  const data = rows
    .filter((row) => !row.fwde_total)
    .sort((a, b) => compareKeys(order(String(a.label)), order(String(b.label))));
  const categories = data.map((row) => String(row.label));
  const series: ChartSeries[] = columns.map((column, n) => ({
    label: columnLabel(column),
    dashed: isOtherPeriod(column.kind),
    comparison: true,
    points: data.map((row) => ({
      label: String(row.label),
      value: numberOrNull(row[valueKey(metric, n)]),
    })),
  }));
  return {
    type: 'line',
    categories,
    series,
    omittedRows: 0,
    omittedSeries: 0,
    incomplete: false,
    gaps: false,
    scale: scaleOf(series, metric, tickCount),
  };
}

/** The trend for one row (bars of one row say nothing) and where chosen, bars otherwise. */
export function chartTypeFor(state: ExplorationState, rowCount: number): ChartType {
  // never the donut here: the page asks effectiveChartType with the donut's reason
  return effectiveChartType(state, rowCount, 'not-checked');
}

/**
 * The requests of a trend: 0 (the rows) without comparing, else one per comparison column
 * (1 … n), each answered with the own total (line 0) and that column (line 1).
 */
export function trendColumns(columns: ComparisonColumn[]): number[] {
  return columns.length > 1 ? columns.slice(1).map((_, i) => i + 1) : [0];
}

/**
 * The lines with response n merged in: the rows response as it is; for a comparison column its
 * line 1 as line n, and line 0 (the own total) from the first response that brings it.
 */
export function mergeTrendLines(lines: TrendRow[], response: TrendRow[], n: number): TrendRow[] {
  if (n === 0) {
    return response;
  }
  const hasOwn = lines.some((row) => Number(row.fwde_line) === 0);
  const own = hasOwn ? [] : response.filter((row) => Number(row.fwde_line) === 0);
  const column = response
    .filter((row) => Number(row.fwde_line) === 1)
    .map((row) => ({ ...row, fwde_line: n }));
  return [...lines, ...own, ...column];
}

/** While comparing, the own line (0) is missing when every comparison request failed. */
export function needsOwnLine(lines: TrendRow[], columns: ComparisonColumn[]): boolean {
  return columns.length > 1 && !lines.some((row) => Number(row.fwde_line) === 0);
}

/** The lines with the own line (0) of a response added, unless they have it. */
export function withOwnLine(lines: TrendRow[], response: TrendRow[]): TrendRow[] {
  if (lines.some((row) => Number(row.fwde_line) === 0)) {
    return lines;
  }
  return [...response.filter((row) => Number(row.fwde_line) === 0), ...lines];
}

/**
 * A trend request still counts while it is the newest and the page still shows the setup it was
 * asked for (another definition or comparison makes it void, so its next requests are not sent).
 */
export function trendIsCurrent(
  request: { sequence: number, key: string },
  page: { sequence: number, definition: string, aborted: boolean },
): boolean {
  return request.sequence === page.sequence && request.key === page.definition && !page.aborted;
}
