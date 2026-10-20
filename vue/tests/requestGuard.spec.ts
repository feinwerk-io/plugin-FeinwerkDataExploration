/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { createRequestGuard } from '../src/requestGuard';

describe('createRequestGuard', () => {
  it('accepts only the answer of the latest request', () => {
    const guard = createRequestGuard();
    const first = guard.start();
    const second = guard.start();
    expect(guard.isLatest(first)).toBe(false);
    expect(guard.isLatest(second)).toBe(true);
  });

  it('keeps guards apart', () => {
    const a = createRequestGuard();
    const b = createRequestGuard();
    const idA = a.start();
    b.start();
    b.start();
    expect(a.isLatest(idA)).toBe(true);
  });
});
