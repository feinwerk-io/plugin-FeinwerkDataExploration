/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { addsUp, overcountUnit, pivotOvercounts } from '../src/additivity';
import { parseState } from '../src/state';
import { Catalog } from '../src/types';
import { CATALOG, LIMITS } from './fixtures';

const catalog: Catalog = {
  dimensions: CATALOG.dimensions,
  metrics: [
    ...CATALOG.metrics,
    { id: 'nb_uniq_visitors', scope: 'visit', name: 'Unique visitors', nameInActionScope: null, kind: null },
    { id: 'nb_uniq_pageviews', scope: 'action', name: 'Unique pageviews', nameInActionScope: null, kind: 'pageview' },
  ],
};

const state = (dims: string, metrics: string) => parseState(
  { fwdeDims: dims, fwdeMetrics: metrics },
  catalog,
  LIMITS,
);

// the same cases as tests/Unit/Engine/AdditivityTest.php
describe('overcountUnit', () => {
  it.each([
    ['pageUrl', 'nb_visits', 'visit'],
    ['pageUrl', 'nb_pageviews', null],
    ['hour', 'nb_visits,nb_pageviews', 'visit'],
    ['hour', 'nb_visits', null],
    ['browser', 'nb_uniq_visitors', 'visitor'],
    ['country', 'nb_conversions', null],
    ['pageUrl', 'nb_conversions', 'visit'],
    ['pageUrl', 'nb_uniq_pageviews', 'visit'],
    ['browser', 'nb_pageviews,nb_events', null],
  ])('%s with %s: %s', (dims, metrics, unit) => {
    const s = state(dims, metrics);
    expect(s.metrics).toEqual(metrics.split(','));
    expect(overcountUnit(s, catalog)).toBe(unit);
    expect(addsUp(s, catalog)).toBe(unit === null);
  });
});

// final review (rows and pivot): a count adds up across the pivot's values only when no visit counts under two
describe('pivotOvercounts', () => {
  const pivoted = (hash: Record<string, string>) => parseState({ fwdeDims: 'browser', ...hash }, CATALOG, LIMITS);

  it('finds visits under a page pivot and distinct counts under any pivot', () => {
    expect(pivotOvercounts(pivoted({ fwdeMetrics: 'nb_visits', fwdePivot: 'pageUrl' }), CATALOG)).toBe(true);
    expect(pivotOvercounts(pivoted({ fwdeMetrics: 'nb_pageviews', fwdePivot: 'pageUrl' }), CATALOG)).toBe(false);
    expect(pivotOvercounts(pivoted({ fwdeMetrics: 'nb_visits', fwdePivot: 'country' }), CATALOG)).toBe(false);
    expect(pivotOvercounts(pivoted({ fwdeMetrics: 'bounce_rate', fwdePivot: 'pageUrl' }), CATALOG)).toBe(false);
    expect(pivotOvercounts(pivoted({ fwdeMetrics: 'nb_visits' }), CATALOG)).toBe(false);
  });
});
