/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { filterSummary, filterSummaryLines, filterTargets } from '../src/filterSummary';
import { parseState } from '../src/state';
import { CATALOG, LIMITS } from './fixtures';

const TEXTS: Record<string, string> = {
  FeinwerkDataExploration_OperatorNotEquals: 'is not',
  FeinwerkDataExploration_OperatorContains: 'contains',
  FeinwerkDataExploration_OperatorGt: 'greater than',
  FeinwerkDataExploration_OperatorLt: 'less than',
  FeinwerkDataExploration_OperatorGte: 'at least',
  FeinwerkDataExploration_OperatorEquals: 'is',
  FeinwerkDataExploration_FilterJoinAnd: 'and',
  FeinwerkDataExploration_FilterJoinOr: 'or',
  FeinwerkDataExploration_FilterMatchAny: 'Match any',
};
const translate = (key: string) => TEXTS[key] || key;

describe('filter summary in the left column (UI item 4)', () => {
  it('a text filter: column, condition and the value in quotes', () => {
    const state = parseState({
      fwdeDims: 'browser,pageUrl', fwdeMetrics: 'nb_visits', fwdeFilters: 'label~notEquals~Opera;secondary_label~contains~shop',
    }, CATALOG, LIMITS);
    expect(filterSummary(state, CATALOG, false, translate)).toEqual([
      'Browser is not "Opera"',
      'Page URL contains "shop"',
    ]);
  });

  it('"is empty" and "is not empty" read without a value (2026-11-25)', () => {
    const state = parseState({
      fwdeDims: 'browser', fwdeMetrics: 'nb_visits', fwdeFilters: 'label~notEmpty~;label~empty~',
    }, CATALOG, LIMITS);
    expect(filterSummary(state, CATALOG, false, translate)).toEqual([
      'Browser FeinwerkDataExploration_OperatorNotEmpty',
      'Browser FeinwerkDataExploration_OperatorEmpty',
    ]);
  });

  it('a number filter with its unit, by the metric name the table shows', () => {
    const state = parseState({
      fwdeDims: 'browser', fwdeMetrics: 'nb_visits,bounce_rate,avg_time_on_site',
      fwdeFilters: 'bounce_rate~gt~50;avg_time_on_site~lt~30;nb_visits~gt~10',
    }, CATALOG, LIMITS);
    expect(filterSummary(state, CATALOG, false, translate)).toEqual([
      'Bounce rate greater than 50 %',
      'Avg. time less than 30 s',
      'Visits greater than 10',
    ]);
    expect(filterSummary(state, CATALOG, true, translate)[2]).toBe('Visits with this action greater than 10');
  });

  it('nothing without filters', () => {
    const state = parseState({ fwdeDims: 'browser', fwdeMetrics: 'nb_visits' }, CATALOG, LIMITS);
    expect(filterSummary(state, CATALOG, false, translate)).toEqual([]);
  });

  it('the columns a filter can test: the dimensions, then the metrics', () => {
    const state = parseState({ fwdeDims: 'browser,pageUrl', fwdeMetrics: 'nb_visits' }, CATALOG, LIMITS);
    expect(filterTargets(state, CATALOG, true)).toEqual([
      { key: 'label', label: 'Browser' },
      { key: 'secondary_label', label: 'Page URL' },
      { key: 'nb_visits', label: 'Visits with this action' },
    ]);
  });
});

describe('filter summary with groups (2026-11-23)', () => {
  it('one line per group, the groups joined by a line of their own', () => {
    const state = parseState({
      fwdeDims: 'browser,pageUrl',
      fwdeMetrics: 'nb_visits',
      fwdeFilters: 'label~contains~chr;secondary_label~equals~%2F|any:nb_visits~gte~10;label~notEquals~Opera',
      fwdeMatch: 'any',
    }, CATALOG, LIMITS);
    expect(filterSummaryLines(state, CATALOG, false, translate)).toEqual([
      { text: 'Browser contains "chr" and Page URL is "/"', join: false },
      { text: 'or', join: true },
      { text: 'Visits at least 10 or Browser is not "Opera"', join: false },
    ]);
  });

  it('one group reads as before: a line per condition, "Match any" below from two on', () => {
    const any = parseState({
      fwdeDims: 'browser', fwdeMetrics: 'nb_visits', fwdeFilters: 'any:label~contains~a;label~contains~e',
    }, CATALOG, LIMITS);
    expect(filterSummaryLines(any, CATALOG, false, translate)).toEqual([
      { text: 'Browser contains "a"', join: false },
      { text: 'Browser contains "e"', join: false },
      { text: 'Match any', join: true },
    ]);
    const none = parseState({ fwdeDims: 'browser', fwdeMetrics: 'nb_visits' }, CATALOG, LIMITS);
    expect(filterSummaryLines(none, CATALOG, false, translate)).toEqual([]);
  });
});

