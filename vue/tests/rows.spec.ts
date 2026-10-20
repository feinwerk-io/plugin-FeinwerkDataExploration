/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { canFilterTo, combinedSegment, filtersForRow } from '../src/rows';
import { MAX_FILTER_TEXT, parseState } from '../src/state';
import { CATALOG, LIMITS } from './fixtures';
import { ResultRow } from '../src/api';

describe('filtersForRow', () => {
  it('filters the dimension (and the second one) to the values of the row', () => {
    const one = parseState({ fwdeDims: 'browser' }, CATALOG, LIMITS);
    expect(filtersForRow(one, { label: 'Firefox', nb_visits: 3 })).toEqual([
      { target: 'label', operator: 'equals', value: 'Firefox' },
    ]);
    const two = parseState({ fwdeDims: 'browser,country' }, CATALOG, LIMITS);
    expect(filtersForRow(two, { label: 'Firefox', secondary_label: 'Germany', nb_visits: 3 })).toEqual([
      { target: 'label', operator: 'equals', value: 'Firefox' },
      { target: 'secondary_label', operator: 'equals', value: 'Germany' },
    ]);
  });

  it('replaces a filter on the same column and keeps the others', () => {
    const state = parseState({
      fwdeDims: 'browser', fwdeMetrics: 'nb_visits',
      fwdeFilters: 'label~contains~fire;nb_visits~gt~2',
    }, CATALOG, LIMITS);
    expect(filtersForRow(state, { label: 'Firefox', nb_visits: 3 })).toEqual([
      { target: 'nb_visits', operator: 'gt', value: '2' },
      { target: 'label', operator: 'equals', value: 'Firefox' },
    ]);
  });

  it('keeps at most five filters', () => {
    const state = parseState({
      fwdeDims: 'browser,country', fwdeMetrics: 'nb_visits,nb_actions,bounce_rate,avg_time_on_site',
      fwdeFilters: 'nb_visits~gt~1;nb_actions~gt~1;bounce_rate~lt~90;avg_time_on_site~gt~5',
    }, CATALOG, LIMITS);
    expect(filtersForRow(state, { label: 'A', secondary_label: 'B', nb_visits: 1 })).toHaveLength(5);
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
      { target: 'label', operator: 'equals', value: 'example.com/%C3%BCber' },
      { target: 'secondary_label', operator: 'equals', value: 'Tom &amp; Jerry' },
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

  it('cannot filter to a row without a stored value ("… not defined")', () => {
    expect(canFilterTo({ label: 'Firefox', nb_visits: 3 })).toBe(true);
    // the API sends the marker as JSON true
    const noValue = { label: 'Referrer name not defined', fwde_no_value: true, nb_visits: 3 };
    expect(canFilterTo(noValue as unknown as ResultRow)).toBe(false);
  });
});
