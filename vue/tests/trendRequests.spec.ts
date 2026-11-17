/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  chartTypeFor, mergeTrendLines, needsOwnLine, trendColumns, trendIsCurrent, TrendRow, withOwnLine,
} from '../src/trend';
import { requestColumns } from '../src/comparison';
import { parseState } from '../src/state';
import { ComparisonColumn } from '../src/types';
import { CATALOG, LIMITS } from './fixtures';

function state(hash: Record<string, string>) {
  return parseState(hash, CATALOG, LIMITS);
}

function row(line: number, time: string, visits: number): TrendRow {
  return {
    label: `Line ${line}`, time, fwde_line: line, nb_visits: visits,
  } as TrendRow;
}

const column = (title: string, kind: ComparisonColumn['kind']): ComparisonColumn => ({
  segment: '', period: 'range', date: '2026-03-27,2026-03-30', title, kind,
});
const own = column('All visits', 'own');
const mobile = column('Mobile', 'segment');
const lastMonth = column('Previous period', 'period');

const response = (n: number) => [row(0, '2026-03-27', 9), row(1, '2026-03-27', n)];

describe('trend requests', () => {
  it('asks the rows without comparing, one request per column otherwise', () => {
    expect(trendColumns([own])).toEqual([0]);
    expect(trendColumns([own, mobile, lastMonth])).toEqual([1, 2]);
  });

  it('renumbers the compared line of each response', () => {
    const merged = mergeTrendLines(mergeTrendLines([], response(1), 1), response(2), 2);
    expect(merged.map((r) => [r.fwde_line, r.nb_visits])).toEqual([[0, 9], [1, 1], [2, 2]]);
  });

  it('takes the rows response as it is', () => {
    const rows = [row(0, '2026-03-27', 3), row(1, '2026-03-27', 2)];
    expect(mergeTrendLines([], rows, 0)).toEqual(rows);
  });

  it('asks the own line again when every comparison failed', () => {
    const columns = [own, mobile];
    expect(needsOwnLine([], columns)).toBe(true);
    expect(needsOwnLine(response(1), columns)).toBe(false);
    expect(needsOwnLine([], [own])).toBe(false);
    const own0 = [row(0, '2026-03-27', 9), row(1, '2026-03-27', 9)];
    expect(withOwnLine([], own0).map((r) => r.fwde_line)).toEqual([0]);
    expect(withOwnLine(response(1), own0)).toEqual(response(1));
  });

  it('a trend is outdated once the page asks for another setup', () => {
    const request = { sequence: 3, key: 'A' };
    expect(trendIsCurrent(request, { sequence: 3, definition: 'A', aborted: false })).toBe(true);
    expect(trendIsCurrent(request, { sequence: 3, definition: 'B', aborted: false })).toBe(false);
    expect(trendIsCurrent(request, { sequence: 4, definition: 'A', aborted: false })).toBe(false);
    expect(trendIsCurrent(request, { sequence: 3, definition: 'A', aborted: true })).toBe(false);
  });

  it('chooses the trend for one row and as chosen otherwise', () => {
    expect(chartTypeFor(state({ fwdeDims: 'browser' }), 1)).toBe('trend');
    expect(chartTypeFor(state({ fwdeDims: 'browser' }), 5)).toBe('bar');
    expect(chartTypeFor(state({ fwdeDims: 'browser', fwdeChartType: 'trend' }), 5)).toBe('trend');
    expect(chartTypeFor(state({ fwdeDims: 'date', fwdeChartType: 'trend' }), 5)).toBe('bar');
  });

  it('drops an outdated trend', async () => {
    let lines: TrendRow[] = [];
    let current = true;
    await requestColumns([1, 2], (n) => {
      current = false; // a newer table load started while column 1 loaded
      return Promise.resolve(response(n));
    }, () => current, (n, rows) => {
      lines = mergeTrendLines(lines, rows as TrendRow[], n);
    }, () => undefined);
    expect(lines).toEqual([]);
  });

  it('a failing column keeps the other lines', async () => {
    let lines: TrendRow[] = [];
    const failed: number[] = [];
    await requestColumns([1, 2, 3], (n) => (n === 2
      ? Promise.reject(new Error('Column Mobile: too long'))
      : Promise.resolve(response(n))), () => true, (n, rows) => {
      lines = mergeTrendLines(lines, rows as TrendRow[], n);
    }, (n) => {
      failed.push(n);
    });
    expect([...new Set(lines.map((r) => r.fwde_line))]).toEqual([0, 1, 3]);
    expect(failed).toEqual([2]);
  });
});
