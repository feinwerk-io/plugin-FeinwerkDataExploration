/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  canFilterTo, combinedSegment, filtersForRow, rowFilterState,
} from '../src/rows';
import { MAX_FILTER_TEXT, parseState } from '../src/state';
import { CATALOG, LIMITS } from './fixtures';
import { ResultRow } from '../src/api';

describe('rowFilterState', () => {
  const filters = 'label~contains~fire;nb_visits~gt~2';
  const firefox = { label: 'Firefox', nb_visits: 3 };
  const two = { label: 'Firefox', secondary_label: 'Germany', nb_visits: 3 };
  const rowGroup = (group: number) => [
    { target: 'label', operator: 'equals', value: 'Firefox', group },
    { target: 'secondary_label', operator: 'equals', value: 'Germany', group },
  ];

  it('joins one "all" list as before, in place of conditions on the same columns', () => {
    const state = parseState({ fwdeDims: 'browser', fwdeMetrics: 'nb_visits', fwdeFilters: filters }, CATALOG, LIMITS);
    expect(rowFilterState(state, firefox)).toEqual({
      filters: filtersForRow(state, firefox), groupMatch: ['all'], match: 'all',
    });
  });

  it('starts from the row alone without filters', () => {
    const state = parseState({ fwdeDims: 'browser,country', fwdeMetrics: 'nb_visits' }, CATALOG, LIMITS);
    expect(rowFilterState(state, two)).toEqual({ filters: rowGroup(0), groupMatch: ['all'], match: 'all' });
  });

  it('adds the row as a new group to an "any" group: (A or B) and the row (filter groups, 2026-11-23)', () => {
    const state = parseState({
      fwdeDims: 'browser,country', fwdeMetrics: 'nb_visits', fwdeFilters: filters, fwdeMatch: 'any',
    }, CATALOG, LIMITS);
    expect(rowFilterState(state, two)).toEqual({
      filters: [...state.filters, ...rowGroup(1)], groupMatch: ['any', 'all'], match: 'all',
    });
  });

  it('adds the row as a new group when the groups are joined by "all"', () => {
    const state = parseState({
      fwdeDims: 'browser,country', fwdeMetrics: 'nb_visits', fwdeFilters: 'label~contains~fire|nb_visits~gt~2',
    }, CATALOG, LIMITS);
    expect(rowFilterState(state, two)).toEqual({
      filters: [...state.filters, ...rowGroup(2)], groupMatch: ['all', 'all', 'all'], match: 'all',
    });
  });

  it('starts from the row alone when the groups are joined by "any", where another group would widen the table', () => {
    const state = parseState({
      fwdeDims: 'browser,country', fwdeMetrics: 'nb_visits', fwdeFilters: 'label~contains~fire|nb_visits~gt~2', fwdeMatch: 'any',
    }, CATALOG, LIMITS);
    expect(rowFilterState(state, two)).toEqual({ filters: rowGroup(0), groupMatch: ['all'], match: 'all' });
  });

  it('starts from the row alone when another group would pass the limits', () => {
    const five = parseState({
      fwdeDims: 'browser,country', fwdeMetrics: 'nb_visits', fwdeFilters: Array(5).fill('nb_visits~gt~2').join('|'),
    }, CATALOG, LIMITS);
    expect(rowFilterState(five, two)).toEqual({ filters: rowGroup(0), groupMatch: ['all'], match: 'all' });
    const nine = parseState({
      fwdeDims: 'browser,country', fwdeMetrics: 'nb_visits', fwdeFilters: `any:${Array(9).fill('nb_visits~gt~2').join(';')}`,
    }, CATALOG, LIMITS);
    expect(rowFilterState(nine, two)).toEqual({ filters: rowGroup(0), groupMatch: ['all'], match: 'all' });
  });

  it('keeps a single condition with a left-over "any" (the switch shows from two on) and joins it', () => {
    const state = parseState({
      fwdeDims: 'browser', fwdeMetrics: 'nb_visits', fwdeFilters: 'nb_visits~gt~2', fwdeMatch: 'any',
    }, CATALOG, LIMITS);
    expect(rowFilterState(state, firefox)).toEqual({
      filters: [
        { target: 'nb_visits', operator: 'gt', value: '2', group: 0 },
        { target: 'label', operator: 'equals', value: 'Firefox', group: 0 },
      ],
      groupMatch: ['all'],
      match: 'all',
    });
  });
});

describe('three dimensions (rows and pivot, 2026-11-24)', () => {
  it('filters to all three values of the row, the third by its stored value', () => {
    const state = parseState({ fwdeDims: 'browser,country,pageTitle', fwdeMetrics: 'nb_visits' }, CATALOG, LIMITS);
    const row = {
      label: 'Firefox', secondary_label: 'Germany', tertiary_label: 'Tom & Jerry', fwde_value3: 'Tom &amp; Jerry',
    };
    expect(filtersForRow(state, row)).toEqual([
      { target: 'label', operator: 'equals', value: 'Firefox', group: 0 },
      { target: 'secondary_label', operator: 'equals', value: 'Germany', group: 0 },
      { target: 'tertiary_label', operator: 'equals', value: 'Tom &amp; Jerry', group: 0 },
    ]);
    expect(canFilterTo({ ...row, fwde_value3: 'x'.repeat(MAX_FILTER_TEXT + 1) })).toBe(false);
  });
});

describe('filtersForRow', () => {
  it('filters the dimension (and the second one) to the values of the row', () => {
    const one = parseState({ fwdeDims: 'browser' }, CATALOG, LIMITS);
    expect(filtersForRow(one, { label: 'Firefox', nb_visits: 3 })).toEqual([
      { target: 'label', operator: 'equals', value: 'Firefox', group: 0 },
    ]);
    const two = parseState({ fwdeDims: 'browser,country' }, CATALOG, LIMITS);
    expect(filtersForRow(two, { label: 'Firefox', secondary_label: 'Germany', nb_visits: 3 })).toEqual([
      { target: 'label', operator: 'equals', value: 'Firefox', group: 0 },
      { target: 'secondary_label', operator: 'equals', value: 'Germany', group: 0 },
    ]);
  });

  it('replaces a filter on the same column and keeps the others', () => {
    const state = parseState({
      fwdeDims: 'browser', fwdeMetrics: 'nb_visits',
      fwdeFilters: 'label~contains~fire;nb_visits~gt~2',
    }, CATALOG, LIMITS);
    expect(filtersForRow(state, { label: 'Firefox', nb_visits: 3 })).toEqual([
      { target: 'nb_visits', operator: 'gt', value: '2', group: 0 },
      { target: 'label', operator: 'equals', value: 'Firefox', group: 0 },
    ]);
  });

  it('keeps at most ten conditions', () => {
    const state = parseState({
      fwdeDims: 'browser,country', fwdeMetrics: 'nb_visits',
      fwdeFilters: Array(9).fill('nb_visits~gt~1').join(';'),
    }, CATALOG, LIMITS);
    expect(filtersForRow(state, { label: 'A', secondary_label: 'B', nb_visits: 1 })).toHaveLength(10);
  });
});

describe('combinedSegment', () => {
  it('joins the page segment and the row segment with AND', () => {
    expect(combinedSegment('deviceType==smartphone', 'browserCode==FF')).toBe('deviceType==smartphone;browserCode==FF');
    expect(combinedSegment('', 'browserCode==FF')).toBe('browserCode==FF');
    expect(combinedSegment('a==1', null)).toBeNull();
  });
});

describe('filtersForRow with stored values', () => {
  it('filters to the stored value the API sends, not the decoded label', () => {
    const two = parseState({ fwdeDims: 'browser,country' }, CATALOG, LIMITS);
    const row = {
      label: 'example.com/über', fwde_value: 'example.com/%C3%BCber', secondary_label: 'Tom & Jerry', fwde_value2: 'Tom &amp; Jerry', nb_visits: 1,
    };
    expect(filtersForRow(two, row)).toEqual([
      { target: 'label', operator: 'equals', value: 'example.com/%C3%BCber', group: 0 },
      { target: 'secondary_label', operator: 'equals', value: 'Tom &amp; Jerry', group: 0 },
    ]);
  });
});

describe('canFilterTo', () => {
  it('cannot filter to a value longer than a filter takes', () => {
    expect(canFilterTo({ label: 'x', fwde_value: 'a'.repeat(MAX_FILTER_TEXT), nb_visits: 1 })).toBe(true);
    expect(canFilterTo({ label: 'x', fwde_value: 'a'.repeat(MAX_FILTER_TEXT + 1), nb_visits: 1 })).toBe(false);
    expect(canFilterTo({ label: 'a'.repeat(MAX_FILTER_TEXT + 1), nb_visits: 1 })).toBe(false);
    expect(canFilterTo({ label: 'x', secondary_label: 'b'.repeat(MAX_FILTER_TEXT + 1), nb_visits: 1 })).toBe(false);
    expect(MAX_FILTER_TEXT).toBe(1000);
  });

  // UI review 2026-11-25 (F4): "is empty" selects a row without a value ("… not defined")
  it('filters to a row without a value with "is empty" on its empty columns', () => {
    expect(canFilterTo({ label: 'Firefox', nb_visits: 3 })).toBe(true);
    // the API sends the marker as JSON true and the empty label columns as a list
    const noValue = {
      label: 'Unknown', secondary_label: 'Germany', fwde_no_value: true,
      fwde_empty_labels: ['label'], nb_visits: 3,
    };
    expect(canFilterTo(noValue as unknown as ResultRow)).toBe(true);
    const state = parseState({ fwdeDims: 'browser,country', fwdeMetrics: 'nb_visits' }, CATALOG, LIMITS);
    expect(filtersForRow(state, noValue as unknown as ResultRow)).toEqual([
      {
        target: 'label', operator: 'empty', value: '', group: 0,
      },
      {
        target: 'secondary_label', operator: 'equals', value: 'Germany', group: 0,
      },
    ]);
  });
});
