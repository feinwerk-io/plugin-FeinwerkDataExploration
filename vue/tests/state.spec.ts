/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  parseState, toHashParams, toDefinition, defaultMetricsFor, DEFAULT_DIMENSIONS, DEFAULT_METRICS,
  STATE_HASH_KEYS,
} from '../src/state';
import { CATALOG, GOAL_CATALOG, LIMITS } from './fixtures';

describe('parseState', () => {
  it('falls back to the defaults for an empty hash', () => {
    const state = parseState({}, CATALOG, LIMITS);
    expect(state.dimensions).toEqual(DEFAULT_DIMENSIONS);
    expect(state.metrics).toEqual(['nb_visits', 'bounce_rate', 'nb_conversions']);
    expect(DEFAULT_METRICS).toEqual(state.metrics);
    expect(state.sort).toBe('nb_visits');
    expect(state.sortOrder).toBe('desc');
    expect(state.limit).toBe(100);
    expect(state.filters).toEqual([]);
  });

  it('round-trips through the hash parameters', () => {
    const state = {
      dimensions: ['browser', 'pageUrl'],
      metrics: ['nb_visits', 'nb_pageviews'],
      sort: 'nb_pageviews',
      sortOrder: 'asc' as const,
      limit: 250,
      filters: [
        { target: 'secondary_label', operator: 'contains' as const, value: 'a;b~c %_<b>', group: 0 },
        { target: 'nb_visits', operator: 'gt' as const, value: '5', group: 1 },
      ],
      view: 'chart' as const,
      chartMetric: 'nb_pageviews',
      chartType: 'trend' as const,
      match: 'any' as const,
      groupMatch: ['all' as const, 'any' as const],
      pivot: null,
      pivotLimit: 5,
    };
    expect(parseState(toHashParams(state), CATALOG, LIMITS)).toEqual(state);
  });

  // rows and pivot (2026-11-24)
  it('round-trips three dimensions and a pivot', () => {
    const state = {
      dimensions: ['browser', 'country', 'pageTitle'],
      metrics: ['nb_visits'],
      sort: 'tertiary_label',
      sortOrder: 'asc' as const,
      limit: 100,
      filters: [{ target: 'tertiary_label', operator: 'contains' as const, value: 'shop', group: 0 }],
      view: null,
      chartMetric: null,
      chartType: null,
      match: 'all' as const,
      groupMatch: ['all' as const],
      pivot: 'channelType',
      pivotLimit: 10,
    };
    const params = toHashParams(state);
    expect(params.fwdePivot).toBe('channelType');
    expect(params.fwdePivotLimit).toBe('10');
    expect(parseState(params, CATALOG, LIMITS)).toEqual(state);
  });

  // review focus 4: an older link reads as before, without a pivot
  it('reads an older link without a pivot and writes empty pivot parameters', () => {
    const state = parseState({ fwdeDims: 'browser,country', fwdeMetrics: 'nb_visits' }, CATALOG, LIMITS);
    expect(state.dimensions).toEqual(['browser', 'country']);
    expect(state.pivot).toBeNull();
    expect(state.pivotLimit).toBe(5);
    expect(toHashParams(state)).toMatchObject({ fwdePivot: '', fwdePivotLimit: '' });
    expect(toDefinition(state)).not.toHaveProperty('pivot');
  });

  it('drops a pivot the table cannot take and fits its limit, as the server reads a saved state', () => {
    const pivot = (hash: Record<string, string>) => {
      const state = parseState({ fwdeDims: 'browser', fwdeMetrics: 'nb_visits', ...hash }, CATALOG, LIMITS);
      return [state.pivot, state.pivotLimit];
    };
    expect(pivot({ fwdePivot: 'nope' })).toEqual([null, 5]);
    expect(pivot({ fwdePivot: 'browser' })).toEqual([null, 5]);
    expect(pivot({ fwdePivot: 'date' })).toEqual([null, 5]);
    expect(pivot({ fwdePivot: 'hour' })).toEqual([null, 5]);
    expect(pivot({ fwdePivot: 'country', fwdePivotLimit: '7' })).toEqual(['country', 5]);
    const ten = 'nb_visits,nb_actions,bounce_rate,avg_time_on_site,nb_conversions,revenue,nb_pageviews,'
      + 'nb_events,nb_downloads,max_actions';
    const tenMetrics = parseState({
      fwdeDims: 'browser', fwdeMetrics: ten, fwdePivot: 'country', fwdePivotLimit: '10',
    }, CATALOG, LIMITS);
    expect([tenMetrics.pivot, tenMetrics.pivotLimit]).toEqual(['country', 5]);
    expect(parseState({
      fwdeDims: 'browser', fwdeMetrics: ten, fwdePivot: 'weekday',
    }, CATALOG, LIMITS).pivot).toBeNull();
  });

  // final review (rows and pivot): the page reloads on the keys it watches, the pivot's included
  it('names every key the hash holds, so the page watches each of them', () => {
    const written = Object.keys(toHashParams(parseState({}, CATALOG, LIMITS))).sort();
    expect([...STATE_HASH_KEYS].sort()).toEqual(written);
    expect(STATE_HASH_KEYS).toEqual(expect.arrayContaining(['fwdePivot', 'fwdePivotLimit']));
  });

  it('sends the pivot only while not comparing', () => {
    const state = parseState({
      fwdeDims: 'browser', fwdeMetrics: 'nb_visits', fwdePivot: 'country', fwdePivotLimit: '3',
    }, CATALOG, LIMITS);
    expect(toDefinition(state)).toMatchObject({ pivot: { dimension: 'country', limit: 3 } });
    expect(toDefinition(state, true)).not.toHaveProperty('pivot');
  });

  it('drops unknown ids and duplicates and keeps at most three dimensions and ten metrics', () => {
    const state = parseState({
      fwdeDims: 'nope,browser,browser,country,pageUrl,pageTitle',
      fwdeMetrics: 'nb_visits,nb_hits,nb_actions,bounce_rate,avg_time_on_site,nb_conversions,revenue,'
        + 'nb_pageviews,nb_events,nb_downloads,max_actions,avg_event_value,nb_searches',
    }, CATALOG, LIMITS);
    expect(state.dimensions).toEqual(['browser', 'country', 'pageUrl']);
    expect(state.metrics).toEqual(['nb_visits', 'nb_actions', 'bounce_rate', 'avg_time_on_site',
      'nb_conversions', 'revenue', 'nb_pageviews', 'nb_events', 'nb_downloads', 'max_actions']);
  });

  // review focus 1: a goal deleted after the link or the saved exploration was made
  it('drops the metrics of a goal the website no longer has', () => {
    const state = parseState({
      fwdeDims: 'browser', fwdeMetrics: 'nb_visits,goal_9_revenue,goal_3_revenue', fwdeSort: 'goal_9_revenue',
    }, GOAL_CATALOG, LIMITS);
    expect(state.metrics).toEqual(['nb_visits', 'goal_3_revenue']);
    expect(state.sort).toBe('nb_visits');
  });

  it('opens an old link with a chosen goal with that goal\'s metrics, same numbers', () => {
    const state = parseState({
      fwdeDims: 'browser',
      fwdeMetrics: 'nb_visits,nb_conversions,conversion_rate',
      fwdeGoal: '3',
      fwdeSort: 'nb_conversions',
      fwdeChart: 'conversion_rate',
      fwdeFilters: 'conversion_rate~gt~10|any:label~contains~nb_conversions',
    }, GOAL_CATALOG, LIMITS);
    expect(state.metrics).toEqual(['nb_visits', 'goal_3_nb_conversions', 'goal_3_conversion_rate']);
    expect(state.sort).toBe('goal_3_nb_conversions');
    expect(state.chartMetric).toBe('goal_3_conversion_rate');
    expect(state.filters.map((f) => [f.target, f.value])).toEqual([
      ['goal_3_conversion_rate', '10'], ['label', 'nb_conversions'],
    ]);
    expect(toHashParams(state).fwdeGoal).toBe('');
    expect(toDefinition(state)).not.toHaveProperty('idGoal');
  });

  it('keeps the all-goals metrics when the chosen goal no longer exists', () => {
    const state = parseState({
      fwdeDims: 'browser', fwdeMetrics: 'nb_conversions,revenue', fwdeGoal: '9',
    }, GOAL_CATALOG, LIMITS);
    expect(state.metrics).toEqual(['nb_conversions', 'revenue']);
    expect(toDefinition(state)).not.toHaveProperty('idGoal');
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
    expect(state.filters).toEqual([{
      target: 'label', operator: 'equals', value: 'Chrome', group: 0,
    }]);
    expect(state.limit).toBe(100);
    expect(state.sort).toBe('nb_visits');
    expect(state.sortOrder).toBe('desc');
  });

  it('keeps the filters is not, starts with and ends with', () => {
    const state = parseState({
      fwdeDims: 'pageUrl',
      fwdeMetrics: 'nb_pageviews',
      fwdeFilters: 'label~notEquals~%2F;label~startsWith~%2Fblog;label~endsWith~.pdf;nb_pageviews~endsWith~1',
    }, CATALOG, LIMITS);
    expect(state.filters).toEqual([
      { target: 'label', operator: 'notEquals', value: '/', group: 0 },
      { target: 'label', operator: 'startsWith', value: '/blog', group: 0 },
      { target: 'label', operator: 'endsWith', value: '.pdf', group: 0 },
    ]);
    expect(toHashParams(state).fwdeFilters).toBe('label~notEquals~%2F;label~startsWith~%2Fblog;label~endsWith~.pdf');
  });

  it('reads "match any" between groups; an old link\'s "match any" is the switch of its one group', () => {
    const base = { fwdeDims: 'browser', fwdeMetrics: 'nb_visits' };
    expect(parseState(base, CATALOG, LIMITS).match).toBe('all');
    expect(parseState({ ...base, fwdeMatch: 'bogus' }, CATALOG, LIMITS).match).toBe('all');
    const old = parseState({ ...base, fwdeFilters: 'label~contains~a;label~contains~e', fwdeMatch: 'any' }, CATALOG, LIMITS);
    expect(old.match).toBe('all');
    expect(old.groupMatch).toEqual(['any']);
    expect(toHashParams(old)).toMatchObject({ fwdeFilters: 'any:label~contains~a;label~contains~e', fwdeMatch: '' });
    const groups = parseState({ ...base, fwdeFilters: 'label~contains~a|label~contains~e', fwdeMatch: 'any' }, CATALOG, LIMITS);
    expect(groups.match).toBe('any');
    expect(toHashParams(groups).fwdeMatch).toBe('any');
    expect(toDefinition(groups)).not.toHaveProperty('match');
    expect(toDefinition(groups)).not.toHaveProperty('filters');
  });

  it('keeps a limit up to maxRows only', () => {
    expect(parseState({ fwdeLimit: '500' }, CATALOG, { defaultRows: 50, maxRows: 250 }).limit).toBe(50);
    expect(parseState({ fwdeLimit: '250' }, CATALOG, { defaultRows: 50, maxRows: 250 }).limit).toBe(250);
  });

  it('keeps at most ten conditions and texts of up to 1,000 characters', () => {
    const filter = 'label~contains~a';
    const long = `label~contains~${'b'.repeat(1001)}`;
    const state = parseState({ fwdeFilters: [long, ...Array(12).fill(filter)].join(';') }, CATALOG, LIMITS);
    expect(state.filters.length).toBe(10);
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
      fwdeChartType: '',
      fwdeMatch: '',
      fwdePivot: '',
      fwdePivotLimit: '',
    });
  });

  it('keeps the chart type in the hash', () => {
    expect(parseState({ fwdeDims: 'browser', fwdeChartType: 'trend' }, CATALOG, LIMITS).chartType)
      .toBe('trend');
    expect(parseState({ fwdeDims: 'browser', fwdeChartType: 'pie' }, CATALOG, LIMITS).chartType)
      .toBeNull();
    const state = parseState({ fwdeDims: 'browser', fwdeChartType: 'bar' }, CATALOG, LIMITS);
    expect(toHashParams(state).fwdeChartType).toBe('bar');
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
      sort: 'nb_pageviews',
      sortOrder: 'desc',
      limit: 100,
      filterGroups: {
        match: 'all',
        groups: [{ match: 'all', filters: [{ target: 'nb_pageviews', operator: 'gt', value: 2.5 }] }],
      },
    });
  });

  it('sends rate filters as fractions, they are entered in percent like the table shows them', () => {
    const state = parseState({
      fwdeDims: 'browser',
      fwdeMetrics: 'bounce_rate,nb_visits,avg_time_on_site',
      fwdeFilters: 'bounce_rate~gt~50;avg_time_on_site~lt~90',
    }, CATALOG, LIMITS);
    expect((toDefinition(state).filterGroups as { groups: { filters: unknown[] }[] }).groups[0].filters).toEqual([
      { target: 'bounce_rate', operator: 'gt', value: 0.5 },
      { target: 'avg_time_on_site', operator: 'lt', value: 90 },
    ]);
  });

  it('never sends the older idGoal: the goal\'s own metrics replace it', () => {
    expect(toDefinition(parseState({}, CATALOG, LIMITS))).not.toHaveProperty('idGoal');
    expect(toDefinition(parseState({ fwdeGoal: '3' }, GOAL_CATALOG, LIMITS))).not.toHaveProperty('idGoal');
  });

  // review focus 5: a goal's rate in percent, its revenue in the website's currency
  it('sends a goal\'s rate filter as a fraction and its revenue as it is', () => {
    const state = parseState({
      fwdeDims: 'browser',
      fwdeMetrics: 'goal_3_conversion_rate,goal_3_revenue',
      fwdeFilters: 'goal_3_conversion_rate~gte~25;goal_3_revenue~lt~99.5',
    }, GOAL_CATALOG, LIMITS);
    expect((toDefinition(state).filterGroups as { groups: { filters: unknown[] }[] }).groups[0].filters).toEqual([
      { target: 'goal_3_conversion_rate', operator: 'gte', value: 0.25 },
      { target: 'goal_3_revenue', operator: 'lt', value: 99.5 },
    ]);
  });
});
