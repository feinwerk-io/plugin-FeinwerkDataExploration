/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  changeKey, comparisonColumns, isComparing, mergeColumn, remainingColumns, requestColumns, shareKey,
  valueKey, withoutComparisonColumns,
} from '../src/comparison';
import { columnLabel as labelOf } from '../src/comparison';

const segments = [
  { params: { segment: '' }, title: 'All visits' },
  { params: { segment: 'deviceType==smartphone' }, title: 'Mobile' },
];
const periods = [
  { params: { period: 'month', date: '2026-11-01' }, title: 'Nov 2026' },
  { params: { period: 'month', date: '2026-10-01' }, title: 'Oct 2026' },
];

describe('comparison columns from Matomo\'s comparison store', () => {
  it('periods outer, segments inner, with kinds and titles', () => {
    const { columns, cut } = comparisonColumns(segments, periods, 10);
    expect(columns.map((c) => c.kind)).toEqual(['own', 'segment', 'period', 'both']);
    expect(columns.map((c) => c.title)).toEqual([
      'All visits, Nov 2026', 'Mobile', 'Oct 2026', 'Mobile, Oct 2026',
    ]);
    expect(columns[3]).toEqual({
      segment: 'deviceType==smartphone',
      period: 'month',
      date: '2026-10-01',
      title: 'Mobile, Oct 2026',
      segmentTitle: 'Mobile',
      periodTitle: 'Oct 2026',
      kind: 'both',
    });
    expect(cut).toBe(false);
  });

  it('a segment Matomo does not know by name is titled by its definition', () => {
    const unsaved = [segments[0], { params: { segment: 'browserCode==FF' }, title: 'Unknown' }];
    const { columns } = comparisonColumns(unsaved, [periods[0]], 3, 'Unknown');
    expect(columns[1].title).toBe('browserCode==FF');
    expect(comparisonColumns(segments, [periods[0]], 3, 'Unknown').columns[1].title).toBe('Mobile');
  });

  it('cuts at the column limit', () => {
    const { columns, cut } = comparisonColumns(segments, periods, 3);
    expect(columns).toHaveLength(3);
    expect(cut).toBe(true);
  });

  it('without comparisons there is only the own column', () => {
    const own = comparisonColumns([segments[0]], [periods[0]], 3);
    expect(own.columns.map((c) => c.kind)).toEqual(['own']);
    expect(own.columns[0].title).toBe('All visits');
    expect(isComparing(own.columns)).toBe(false);
    expect(isComparing(comparisonColumns(segments, [periods[0]], 3).columns)).toBe(true);
    expect(comparisonColumns([], [], 3).columns).toHaveLength(0);
  });

  it('keys of the response columns', () => {
    expect([valueKey('nb_visits', 0), shareKey('nb_visits', 0)]).toEqual(['nb_visits', 'nb_visits__share']);
    expect([valueKey('nb_visits', 2), shareKey('nb_visits', 2), changeKey('nb_visits', 2)])
      .toEqual(['nb_visits__c2', 'nb_visits__c2__share', 'nb_visits__c2__change']);
  });

  it('merges a column by labels and the total row', () => {
    // typed, so Matomo 5's Jest (which type-checks specs) accepts the merged columns
    const rows: Record<string, unknown>[] = [
      { label: 'Total', fwde_total: 1, nb_visits: 10 },
      { label: 'Chrome', nb_visits: 7 },
      { label: 'Firefox', nb_visits: 3 },
    ];
    const response: Record<string, unknown>[] = [
      { label: 'Total', fwde_total: 1, nb_visits: 10, nb_visits__c1: 4 },
      { label: 'Firefox', nb_visits: 3, nb_visits__c1: 1, nb_visits__c1__share: 25 },
      { label: 'Chrome', nb_visits: 7, nb_visits__c1: 3, nb_visits__c1__share: 75 },
    ];
    const merged = mergeColumn(rows, response, 2);
    expect(merged[0].nb_visits__c2).toBe(4);
    expect(merged[1]).toEqual({
      label: 'Chrome', nb_visits: 7, nb_visits__c2: 3, nb_visits__c2__share: 75,
    });
    expect(merged[2].nb_visits__c2__share).toBe(25);
    expect(rows[1].nb_visits__c2 as unknown).toBeUndefined();
  });

  it('merges by the rows\' keys: labels are not unique', () => {
    const rows: Record<string, unknown>[] = [
      { label: 'Unknown', fwde_key: '[null,null]', nb_visits: 5 },
      { label: 'Unknown', fwde_key: '["",null]', nb_visits: 2 },
    ];
    const response: Record<string, unknown>[] = [
      { label: 'Unknown', fwde_key: '[null,null]', nb_visits__c1: 4 },
      { label: 'Unknown', fwde_key: '["",null]', nb_visits__c1: 1 },
    ];
    expect(mergeColumn(rows, response, 1).map((row) => row.nb_visits__c1)).toEqual([4, 1]);
  });

  it('stops requesting columns once a newer setup started', async () => {
    const asked: number[] = [];
    let current = true;
    await requestColumns(
      [1, 2, 3],
      (n) => {
        asked.push(n);
        current = false; // the user changed something while column 1 loaded
        return Promise.resolve([]);
      },
      () => current,
      () => undefined,
      () => undefined,
    );
    expect(asked).toEqual([1]);
  });

  it('reports each column and each failing column', async () => {
    const done: number[] = [];
    const failed: number[] = [];
    await requestColumns(
      [1, 2],
      (n) => (n === 2 ? Promise.reject(new Error('too long')) : Promise.resolve([])),
      () => true,
      (n) => { done.push(n); },
      (n) => { failed.push(n); },
    );
    expect([done, failed]).toEqual([[1], [2]]);
  });

  it('gives each column its segment and its period for a title on two lines', () => {
    const { columns } = comparisonColumns(segments, periods, 10);
    expect(columns.map((c) => [c.segmentTitle, c.periodTitle])).toEqual([
      ['All visits', 'Nov 2026'], ['Mobile', 'Nov 2026'], ['All visits', 'Oct 2026'], ['Mobile', 'Oct 2026'],
    ]);
    const segmentsOnly = comparisonColumns(segments, [periods[0]], 10).columns;
    expect(segmentsOnly.map((c) => [c.segmentTitle, c.periodTitle])).toEqual([['All visits', ''], ['Mobile', '']]);
  });

  it('names every column the same way in the charts: segment and period while periods are compared', () => {
    expect(comparisonColumns(segments, periods, 10).columns.map(labelOf)).toEqual([
      'All visits, Nov 2026', 'Mobile, Nov 2026', 'All visits, Oct 2026', 'Mobile, Oct 2026',
    ]);
    expect(comparisonColumns(segments, [periods[0]], 10).columns.map(labelOf)).toEqual(['All visits', 'Mobile']);
  });

  it('the first response keeps the own columns only (its column 1 repeats the own view)', () => {
    expect(withoutComparisonColumns([{
      label: 'Chrome', nb_visits: 7, nb_visits__share: 70, nb_visits__c1: 7, nb_visits__c1__share: 70,
    }])).toEqual([{ label: 'Chrome', nb_visits: 7, nb_visits__share: 70 }]);
  });

  it('no column requests without own rows', () => {
    const { columns } = comparisonColumns(segments, periods, 3);
    expect(remainingColumns([{ label: 'Total', fwde_total: 1 }], columns)).toEqual([]);
    expect(remainingColumns([{ label: 'Total', fwde_total: 1 }, { label: 'Chrome' }], columns)).toEqual([1, 2]);
  });
});
