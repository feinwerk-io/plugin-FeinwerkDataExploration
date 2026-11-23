/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { viewChips } from '../src/viewChips';

const period = { title: 'From 2026-09-15 to 2026-10-14', params: { period: 'range', date: '2026-09-15,2026-10-14' } };

describe('period and segment in the result header (UI item 5)', () => {
  it('the current period and segment from Matomo\'s comparison list', () => {
    expect(viewChips([{ title: 'All visits', params: { segment: '' } }], [period], 'Unknown')).toEqual({
      period: 'From 2026-09-15 to 2026-10-14',
      segment: 'All visits',
    });
  });

  it('a segment Matomo has no name for shows its definition', () => {
    expect(viewChips([{ title: 'Unknown', params: { segment: 'deviceType==smartphone' } }], [period], 'Unknown'))
      .toEqual({ period: 'From 2026-09-15 to 2026-10-14', segment: 'deviceType==smartphone' });
  });

  it('nothing while comparing or without a list', () => {
    const all = { title: 'All visits', params: { segment: '' } };
    expect(viewChips([all, { title: 'Mobile', params: { segment: 'deviceType==smartphone' } }], [period], 'Unknown')).toBeNull();
    const previous = { title: 'Previous period', params: { period: 'range', date: '2026-08-16,2026-09-14' } };
    expect(viewChips([all], [period, previous], 'Unknown')).toBeNull();
    expect(viewChips([], [period], 'Unknown')).toBeNull();
    expect(viewChips([all], [], 'Unknown')).toBeNull();
  });
});
