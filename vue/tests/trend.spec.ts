/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  canTrend, compareTimeModel, TrendRow, trendModel,
} from '../src/trend';
import { parseState } from '../src/state';
import { ComparisonColumn } from '../src/types';
import { ResultRow } from '../src/api';
import { CATALOG, LIMITS } from './fixtures';

function state(hash: Record<string, string>) {
  return parseState(hash, CATALOG, LIMITS);
}

function row(line: number, time: string, visits: number|null, extra: Record<string, unknown> = {}): TrendRow {
  return {
    label: `Line ${line}`, time, fwde_line: line, nb_visits: visits, bounce_rate: null, ...extra,
  } as TrendRow;
}

const column = (title: string, kind: ComparisonColumn['kind']): ComparisonColumn => ({
  segment: '', period: 'range', date: '2026-03-27,2026-03-30', title, kind,
});

describe('trendModel', () => {
  it('one series per line in the order of the table', () => {
    const model = trendModel([
      row(0, '2026-03-27', 3), row(0, '2026-03-28', 0), row(1, '2026-03-27', 1), row(1, '2026-03-28', 2),
    ], 'nb_visits', { hourLabels: [], tableRows: 12 });
    expect(model.type).toBe('line');
    expect(model.series.map((s) => s.label)).toEqual(['Line 0', 'Line 1']);
    expect(model.categories).toEqual(['2026-03-27', '2026-03-28']);
    expect(model.series[0].points.map((p) => p.value)).toEqual([3, 0]);
    expect(model.omittedRows).toBe(10);
  });

  it('names a line of two dimensions by both labels', () => {
    const model = trendModel([row(0, '2026-03-27', 3, { secondary_label: 'Desktop' })], 'nb_visits', {
      hourLabels: [], tableRows: 1,
    });
    expect(model.series[0].label).toBe('Line 0 · Desktop');
  });

  it('names no omitted rows before the lines are there', () => {
    expect(trendModel([], 'nb_visits', { hourLabels: [], tableRows: 12 }).omittedRows).toBe(0);
  });

  it('keeps gaps for rates', () => {
    const model = trendModel([row(0, '2026-03-27', 3, { bounce_rate: 50 }), row(0, '2026-03-28', 0)], 'bounce_rate', {
      hourLabels: [], tableRows: 1,
    });
    expect(model.series[0].points.map((p) => p.value)).toEqual([50, null]);
  });

  it('lines a compared period up by position, dashed, with its real date', () => {
    const model = trendModel([
      row(0, '2026-03-29', 4, { fwde_kind: 'own' }), row(0, '2026-03-30', 5, { fwde_kind: 'own' }),
      row(1, '2026-03-27', 1, { fwde_kind: 'period' }),
    ], 'nb_visits', { hourLabels: [], tableRows: 1 });
    expect(model.series[0].dashed).toBe(false);
    expect(model.series[1].dashed).toBe(true);
    expect(model.series[1].points.map((p) => [p.value, p.date])).toEqual([[1, '2026-03-27'], [null, undefined]]);
    expect(model.omittedRows).toBe(0);
  });

  it('cuts a longer compared period at the page\'s length', () => {
    const model = trendModel([
      row(0, '2026-03-30', 1, { fwde_kind: 'own' }),
      row(1, '2026-03-27', 2, { fwde_kind: 'period' }), row(1, '2026-03-28', 3, { fwde_kind: 'period' }),
    ], 'nb_visits', { hourLabels: [], tableRows: 1 });
    expect(model.series[1].points).toHaveLength(1);
  });

  it('shows hours through Matomo\'s hour labels', () => {
    const labels = Array.from({ length: 24 }, (_, h) => `${h}h`);
    const model = trendModel(Array.from({ length: 24 }, (_, h) => row(0, String(h), h)), 'nb_visits', {
      hourLabels: labels, tableRows: 1,
    });
    expect(model.categories[0]).toBe('0h');
    expect(model.categories).toHaveLength(24);
  });
});

describe('canTrend', () => {
  it('offers the trend only without a time dimension', () => {
    expect(canTrend(state({ fwdeDims: 'browser' }))).toBe(true);
    expect(canTrend(state({ fwdeDims: 'browser,date' }))).toBe(false);
    expect(canTrend(state({ fwdeDims: 'hour' }))).toBe(false);
  });
});

describe('compareTimeModel', () => {
  it('one line per comparison column from the table, without the total row', () => {
    const rows: ResultRow[] = [
      {
        label: 'Total', fwde_total: 1, nb_visits: 9, nb_visits__c1: 5, nb_visits__c2: 4,
      },
      {
        label: '2026-03-28', nb_visits: 2, nb_visits__c1: 1, nb_visits__c2: 3,
      },
      {
        label: '2026-03-27', nb_visits: 7, nb_visits__c1: 4, nb_visits__c2: 1,
      },
    ];
    const columns = [column('All visits', 'own'), column('Mobile', 'segment'), column('Previous', 'period')];
    const model = compareTimeModel(rows, columns, state({ fwdeDims: 'date' }), 'nb_visits', { weekdayNames: [] });
    expect(model.categories).toEqual(['2026-03-27', '2026-03-28']);
    expect(model.series.map((s) => [s.label, s.dashed])).toEqual([
      ['All visits', false], ['Mobile', false], ['Previous', true],
    ]);
    expect(model.series[2].points.map((p) => p.value)).toEqual([1, 3]);
  });
});
