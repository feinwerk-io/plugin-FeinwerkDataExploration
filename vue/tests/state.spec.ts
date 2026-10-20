/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  parseState, toHashParams, toDefinition, defaultMetricsFor, DEFAULT_DIMENSIONS, DEFAULT_METRICS,
} from '../src/state';
import { CATALOG, LIMITS } from './fixtures';

describe('parseState', () => {
  it('falls back to the defaults for an empty hash', () => {
    const state = parseState({}, CATALOG, LIMITS);
    expect(state.dimensions).toEqual(DEFAULT_DIMENSIONS);
    expect(state.metrics).toEqual(['nb_visits', 'bounce_rate', 'nb_conversions']);
    expect(DEFAULT_METRICS).toEqual(state.metrics);
    expect(state.idGoal).toBeNull();
    expect(state.sort).toBe('nb_visits');
    expect(state.sortOrder).toBe('desc');
    expect(state.limit).toBe(100);
    expect(state.filters).toEqual([]);
  });

  it('round-trips through the hash parameters', () => {
    const state = {
      dimensions: ['browser', 'pageUrl'],
      metrics: ['nb_visits', 'nb_pageviews'],
      idGoal: 2,
      sort: 'nb_pageviews',
      sortOrder: 'asc' as const,
      limit: 250,
      filters: [
        { target: 'secondary_label', operator: 'contains' as const, value: 'a;b~c %_<b>' },
        { target: 'nb_visits', operator: 'gt' as const, value: '5' },
      ],
      view: 'chart' as const,
      chartMetric: 'nb_pageviews',
    };
    expect(parseState(toHashParams(state), CATALOG, LIMITS)).toEqual(state);
  });

  it('drops unknown ids and duplicates and keeps at most two dimensions and five metrics', () => {
    const state = parseState({
      fwdeDims: 'nope,browser,browser,country,pageUrl',
      fwdeMetrics: 'nb_visits,nb_hits,nb_actions,bounce_rate,avg_time_on_site,nb_conversions,revenue',
    }, CATALOG, LIMITS);
    expect(state.dimensions).toEqual(['browser', 'country']);
    expect(state.metrics).toEqual(['nb_visits', 'nb_actions', 'bounce_rate', 'avg_time_on_site', 'nb_conversions']);
  });

  it('ignores broken filters and a limit that is not offered', () => {
    const state = parseState({
      fwdeDims: 'browser',
      fwdeMetrics: 'nb_visits',
      fwdeFilters: 'label~bogus~x;nb_visits~gt~abc;secondary_label~contains~x;label~contains~%E0%A4%A;'
        + 'nb_actions~lt~3;label~equals~Chrome',
      fwdeLimit: '5000',
      fwdeSort: 'nb_actions',
      fwdeOrder: 'up',
      fwdeGoal: '-3',
    }, CATALOG, LIMITS);
    expect(state.filters).toEqual([{ target: 'label', operator: 'equals', value: 'Chrome' }]);
    expect(state.limit).toBe(100);
    expect(state.sort).toBe('nb_visits');
    expect(state.sortOrder).toBe('desc');
    expect(state.idGoal).toBeNull();
  });

  it('keeps a limit up to maxRows only', () => {
    expect(parseState({ fwdeLimit: '500' }, CATALOG, { defaultRows: 50, maxRows: 250 }).limit).toBe(50);
    expect(parseState({ fwdeLimit: '250' }, CATALOG, { defaultRows: 50, maxRows: 250 }).limit).toBe(250);
  });

  it('keeps at most five filters and texts of up to 1,000 characters', () => {
    const filter = 'label~contains~a';
    const long = `label~contains~${'b'.repeat(1001)}`;
    const state = parseState({ fwdeFilters: [long, ...Array(7).fill(filter)].join(';') }, CATALOG, LIMITS);
    expect(state.filters.length).toBe(5);
  });
});

describe('defaultMetricsFor', () => {
  it('starts with the count of the dimension\'s first action kind, then visits', () => {
    expect(defaultMetricsFor('pageUrl', CATALOG)).toEqual(['nb_pageviews', 'nb_visits']);
    expect(defaultMetricsFor('pageTitle', CATALOG)).toEqual(['nb_pageviews', 'nb_visits']);
    expect(defaultMetricsFor('eventCategory', CATALOG)).toEqual(['nb_events', 'nb_visits']);
    expect(defaultMetricsFor('downloadUrl', CATALOG)).toEqual(['nb_downloads', 'nb_visits']);
    expect(defaultMetricsFor('siteSearchKeyword', CATALOG)).toEqual(['nb_searches', 'nb_visits']);
    expect(defaultMetricsFor('customDimension4', CATALOG)).toEqual(['nb_pageviews', 'nb_visits']);
  });

  it('keeps the general defaults for visit dimensions and unknown ids', () => {
    expect(defaultMetricsFor('browser', CATALOG)).toEqual(['nb_visits', 'bounce_rate', 'nb_conversions']);
    expect(defaultMetricsFor('nope', CATALOG)).toEqual(['nb_visits', 'bounce_rate', 'nb_conversions']);
  });

  it('leaves out metrics the catalog does not have', () => {
    const withoutEvents = { ...CATALOG, metrics: CATALOG.metrics.filter((m) => m.id !== 'nb_events') };
    expect(defaultMetricsFor('eventCategory', withoutEvents)).toEqual(['nb_visits']);
  });

  it('is what a link with only a dimension opens', () => {
    expect(parseState({ fwdeDims: 'pageTitle' }, CATALOG, LIMITS).metrics)
      .toEqual(['nb_pageviews', 'nb_visits']);
    expect(parseState({ fwdeDims: 'pageTitle', fwdeMetrics: 'nb_visits' }, CATALOG, LIMITS).metrics)
      .toEqual(['nb_visits']);
  });
});

describe('toHashParams', () => {
  it('writes every parameter, empty ones too, so an older value is cleared', () => {
    const params = toHashParams(parseState({}, CATALOG, LIMITS));
    expect(params).toEqual({
      fwdeDims: 'channelType',
      fwdeMetrics: 'nb_visits,bounce_rate,nb_conversions',
      fwdeGoal: '',
      fwdeSort: 'nb_visits',
      fwdeOrder: 'desc',
      fwdeLimit: '100',
      fwdeFilters: '',
      fwdeView: '',
      fwdeChart: '',
    });
  });

  it('keeps the view and the chart metric', () => {
    const state = parseState({
      fwdeMetrics: 'nb_visits,bounce_rate', fwdeView: 'chart', fwdeChart: 'bounce_rate',
    }, CATALOG, LIMITS);
    expect(state.view).toBe('chart');
    expect(state.chartMetric).toBe('bounce_rate');
    expect(toHashParams(state)).toMatchObject({ fwdeView: 'chart', fwdeChart: 'bounce_rate' });
  });

  it('drops an unknown view and a chart metric that is not selected', () => {
    const state = parseState({
      fwdeMetrics: 'nb_visits', fwdeView: 'pie', fwdeChart: 'revenue',
    }, CATALOG, LIMITS);
    expect(state.view).toBeNull();
    expect(state.chartMetric).toBeNull();
  });
});

describe('toDefinition', () => {
  it('builds the API definition with numbers for number filters', () => {
    const state = parseState({
      fwdeDims: 'pageUrl', fwdeMetrics: 'nb_pageviews', fwdeGoal: '3', fwdeFilters: 'nb_pageviews~gt~2.5',
    }, CATALOG, LIMITS);
    expect(toDefinition(state)).toEqual({
      dimensions: ['pageUrl'],
      metrics: ['nb_pageviews'],
      idGoal: 3,
      sort: 'nb_pageviews',
      sortOrder: 'desc',
      limit: 100,
      filters: [{ target: 'nb_pageviews', operator: 'gt', value: 2.5 }],
    });
  });

  it('sends rate filters as fractions, they are entered in percent like the table shows them', () => {
    const state = parseState({
      fwdeDims: 'browser',
      fwdeMetrics: 'bounce_rate,nb_visits,avg_time_on_site',
      fwdeFilters: 'bounce_rate~gt~50;avg_time_on_site~lt~90',
    }, CATALOG, LIMITS);
    expect(toDefinition(state).filters).toEqual([
      { target: 'bounce_rate', operator: 'gt', value: 0.5 },
      { target: 'avg_time_on_site', operator: 'lt', value: 90 },
    ]);
  });

  it('leaves out an unset goal', () => {
    expect(toDefinition(parseState({}, CATALOG, LIMITS))).not.toHaveProperty('idGoal');
  });
});
