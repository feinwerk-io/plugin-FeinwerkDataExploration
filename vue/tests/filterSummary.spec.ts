/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { filterSummary, filterTargets } from '../src/filterSummary';
import { parseState } from '../src/state';
import { CATALOG, LIMITS } from './fixtures';

const TEXTS: Record<string, string> = {
  FeinwerkDataExploration_OperatorNotEquals: 'is not',
  FeinwerkDataExploration_OperatorContains: 'contains',
  FeinwerkDataExploration_OperatorGt: 'greater than',
  FeinwerkDataExploration_OperatorLt: 'less than',
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
