/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { emptyState } from '../src/emptyState';

describe('the empty result says what to do (UI review 2026-11-23)', () => {
  it('with filters: no row matches them, and the filters can be edited right there', () => {
    expect(emptyState(2)).toEqual({ message: 'FeinwerkDataExploration_NoRowsMatchFilters', editFilters: true });
  });

  it('without filters: Matomo\'s own message', () => {
    expect(emptyState(0)).toEqual({ message: 'CoreHome_ThereIsNoDataForThisReport', editFilters: false });
  });
});
