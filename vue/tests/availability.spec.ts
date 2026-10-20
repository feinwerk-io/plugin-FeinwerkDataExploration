/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { dimensionReasons, metricReasons } from '../src/availability';
import { parseState } from '../src/state';
import { CATALOG, LIMITS } from './fixtures';

const state = (hash: Record<string, string>) => parseState(hash, CATALOG, LIMITS);

describe('dimensionReasons', () => {
  it('greys out a second action dimension without common actions', () => {
    const reasons = dimensionReasons(state({ fwdeDims: 'pageUrl' }), CATALOG, 1);
    expect(reasons.downloadUrl).toBe('FeinwerkDataExploration_ReasonNoCommonActions');
    expect(reasons.eventCategory).toBeNull();
    expect(reasons.pageTitle).toBeNull();
    expect(reasons.customDimension4).toBeNull();
    expect(reasons.browser).toBeNull();
  });

  it('marks the dimension of the other slot as already chosen', () => {
    const reasons = dimensionReasons(state({ fwdeDims: 'browser,country' }), CATALOG, 0);
    expect(reasons.country).toBe('FeinwerkDataExploration_ReasonAlreadyChosen');
    expect(reasons.browser).toBeNull();
  });
});

describe('metricReasons', () => {
  it('greys out action metrics the chosen action dimensions can never have', () => {
    const reasons = metricReasons(state({ fwdeDims: 'pageTitle', fwdeMetrics: 'nb_visits' }), CATALOG);
    expect(reasons.nb_downloads).toBe('FeinwerkDataExploration_ReasonMetricNeverMatches');
    expect(reasons.nb_events).toBe('FeinwerkDataExploration_ReasonMetricNeverMatches');
    expect(reasons.nb_pageviews).toBeNull();
    expect(reasons.nb_visits).toBeNull();
  });

  it('allows every action metric next to visit dimensions only', () => {
    const reasons = metricReasons(state({ fwdeDims: 'browser', fwdeMetrics: 'nb_visits' }), CATALOG);
    expect(reasons.nb_downloads).toBeNull();
  });

  it('greys out further metrics once five are chosen', () => {
    const reasons = metricReasons(state({
      fwdeDims: 'browser', fwdeMetrics: 'nb_visits,nb_actions,bounce_rate,avg_time_on_site,revenue',
    }), CATALOG);
    expect(reasons.nb_conversions).toBe('FeinwerkDataExploration_ReasonMaxMetrics');
    expect(reasons.revenue).toBeNull();
  });
});
