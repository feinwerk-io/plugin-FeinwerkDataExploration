/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { sortByLabel, shouldSortClientSide } from '../src/sorting';

describe('sortByLabel', () => {
  const rows = [{ label: 'Safari' }, { label: 'chrome' }, { label: 'Firefox 10' }, { label: 'Firefox 9' }];

  it('sorts by the visible label, case-insensitive and numeric', () => {
    expect(sortByLabel(rows, 'label', 'asc', 'en').map((r) => r.label))
      .toEqual(['chrome', 'Firefox 9', 'Firefox 10', 'Safari']);
    expect(sortByLabel(rows, 'label', 'desc', 'en').map((r) => r.label))
      .toEqual(['Safari', 'Firefox 10', 'Firefox 9', 'chrome']);
  });

  it('does not change the given array', () => {
    sortByLabel(rows, 'label', 'asc', 'en');
    expect(rows[0].label).toBe('Safari');
  });
});

describe('shouldSortClientSide', () => {
  it('sorts label columns in the browser only when the result is complete', () => {
    expect(shouldSortClientSide(12, 100, 'label')).toBe(true);
    expect(shouldSortClientSide(100, 100, 'label')).toBe(false);
    expect(shouldSortClientSide(12, 100, 'secondary_label')).toBe(true);
    expect(shouldSortClientSide(12, 100, 'nb_visits')).toBe(false);
  });
});
