/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { parseState } from '../src/state';
import {
  parsePivotResult, pivotCells, pivotLimitReasons, pivotReasons,
} from '../src/pivot';
import { metricReasons } from '../src/availability';
import { CATALOG, LIMITS } from './fixtures';

const state = (hash: Record<string, string>) => parseState({
  fwdeDims: 'browser', fwdeMetrics: 'nb_visits', ...hash,
}, CATALOG, LIMITS);

describe('pivotCells', () => {
  it('counts values times metrics, Weekday seven values', () => {
    expect(pivotCells(state({}))).toBe(0);
    expect(pivotCells(state({ fwdeMetrics: 'nb_visits,bounce_rate', fwdePivot: 'country', fwdePivotLimit: '10' })))
      .toBe(20);
    expect(pivotCells(state({ fwdeMetrics: 'nb_visits,bounce_rate', fwdePivot: 'weekday' }))).toBe(14);
  });
});

describe('pivotReasons', () => {
  it('greys out row dimensions, Date, Hour and a Weekday over 60 cells', () => {
    const reasons = pivotReasons(state({ fwdeDims: 'browser,pageTitle' }), CATALOG);
    expect(reasons.browser).toBe('FeinwerkDataExploration_ReasonAlreadyChosen');
    expect(reasons.date).toBe('FeinwerkDataExploration_ReasonPivotTime');
    expect(reasons.hour).toBe('FeinwerkDataExploration_ReasonPivotTime');
    expect(reasons.country).toBeNull();
    expect(reasons.weekday).toBeNull();
    // an action dimension without actions in common with the row's page title
    expect(reasons.eventCategory).toBe('FeinwerkDataExploration_ReasonNoCommonActions');
    const ten = state({
      fwdeMetrics: 'nb_visits,nb_actions,bounce_rate,avg_time_on_site,nb_conversions,revenue,nb_pageviews,'
        + 'nb_events,nb_downloads',
    });
    expect(pivotReasons(ten, CATALOG).weekday).toBe('FeinwerkDataExploration_ReasonPivotCells');
  });
});

describe('the 60-cell limit', () => {
  it('greys out a limit and a metric that would pass it', () => {
    const six = state({
      fwdeMetrics: 'nb_visits,nb_actions,bounce_rate,avg_time_on_site,nb_conversions,revenue',
      fwdePivot: 'country',
      fwdePivotLimit: '10',
    });
    expect(pivotLimitReasons(six)).toEqual({ 3: null, 5: null, 10: null });
    expect(metricReasons(six, CATALOG).nb_pageviews).toBe('FeinwerkDataExploration_ReasonPivotCells');
    const seven = state({
      fwdeMetrics: 'nb_visits,nb_actions,bounce_rate,avg_time_on_site,nb_conversions,revenue,nb_pageviews',
      fwdePivot: 'country',
    });
    expect(pivotLimitReasons(seven)[10]).toBe('FeinwerkDataExploration_ReasonPivotCells');
    expect(metricReasons(state({}), CATALOG).nb_pageviews).toBeNull();
  });
});

describe('parsePivotResult', () => {
  it('reads the total row and its pivot values', () => {
    const rows = [
      {
        label: 'Total',
        nb_visits: 12,
        fwde_total: 1,
        fwde_pivot: {
          dimension: 'country',
          values: [{ label: 'Germany', value: 'de', segment: 'countryCode==de' }],
          others: true,
        },
      },
      { label: 'Chrome', nb_visits: 7 },
    ] as unknown as Record<string, string|number>[];
    const result = parsePivotResult(rows);
    expect(result.dimension).toBe('country');
    expect(result.values).toEqual([{ label: 'Germany', value: 'de', segment: 'countryCode==de' }]);
    expect(result.others).toBe(true);
    expect(result.total?.nb_visits).toBe(12);
    expect(result.rows).toEqual([{ label: 'Chrome', nb_visits: 7 }]);
  });

  it('reads a result without a pivot as rows only', () => {
    const result = parsePivotResult([{ label: 'Chrome', nb_visits: 7 }]);
    expect(result.dimension).toBeNull();
    expect(result.values).toEqual([]);
    expect(result.total).toBeNull();
    expect(result.rows).toHaveLength(1);
  });
});
