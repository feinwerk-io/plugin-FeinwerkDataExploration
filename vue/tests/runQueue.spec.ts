/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { createRunQueue } from '../src/runQueue';

describe('createRunQueue', () => {
  it('starts a run when none is in flight', () => {
    const queue = createRunQueue();
    expect(queue.request()).toBe(true);
    expect(queue.isPending()).toBe(false);
    expect(queue.finish()).toBe(false);
    expect(queue.request()).toBe(true);
  });

  it('holds back changes while a run is in flight and starts the newest one after it', () => {
    // the server keeps running an aborted query, so a second request would only take another slot
    const queue = createRunQueue();
    expect(queue.request()).toBe(true);
    expect(queue.request()).toBe(false);
    expect(queue.request()).toBe(false);
    expect(queue.isPending()).toBe(true);
    expect(queue.finish()).toBe(true);
    expect(queue.isPending()).toBe(false);
    expect(queue.request()).toBe(false);
    expect(queue.finish()).toBe(true);
    expect(queue.finish()).toBe(false);
    expect(queue.request()).toBe(true);
  });
});
