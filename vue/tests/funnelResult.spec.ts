/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { barPercent, totalRate } from '../src/funnelResult';
import { FunnelRow } from '../src/types';

const row = (step: number, visits: number, share: number): FunnelRow => ({
  label: `Step ${step}`,
  step,
  nb_visits: visits,
  share_of_start: share,
  share_of_previous: share,
  nb_dropoffs: 0,
});

describe('funnel result', () => {
  it('total rate is the last share of step 1', () => {
    expect(totalRate([row(1, 9, 100), row(2, 3, 33.3)])).toBe(33.3);
    expect(totalRate([row(1, 0, 0), row(2, 0, 0)])).toBeNull();
    expect(totalRate([])).toBeNull();
  });

  it('bars stay within 0 to 100 percent', () => {
    expect(barPercent(row(2, 3, 33.3))).toBe(33.3);
    expect(barPercent(row(2, 3, 120))).toBe(100);
    expect(barPercent(row(2, 3, -1))).toBe(0);
  });
});
