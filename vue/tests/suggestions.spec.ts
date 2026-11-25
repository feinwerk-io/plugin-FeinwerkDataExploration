/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { flushPromises } from '@vue/test-utils';
import {
  FIXED_DIMENSIONS, SuggestionSource, suggestsFor,
} from '../src/suggestions';

vi.mock('CoreHome', () => ({
  AjaxHelper: { fetch: () => Promise.resolve([]) },
}));

// Peter, 2026-11-25: "felder wo sinnvoll mit autovervollständigen", from Matomo's reports
describe('suggestions for a filter value', () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });
  afterEach(() => {
    vi.useRealTimers();
  });

  it('asks once after the typing pause, from two characters on', async () => {
    const load = vi.fn((dimension: string, text: string, abort: AbortController) => Promise.resolve(['Firefox']));
    const source = new SuggestionSource(load);
    const seen: string[][] = [];
    source.ask('browser', 'f', (values) => seen.push(values));
    expect(seen).toEqual([[]]);
    source.ask('browser', 'fi', (values) => seen.push(values));
    source.ask('browser', 'fir', (values) => seen.push(values));
    vi.advanceTimersByTime(300);
    await flushPromises();
    expect(load).toHaveBeenCalledTimes(1);
    expect(load.mock.calls[0].slice(0, 2)).toEqual(['browser', 'fir']);
    expect(seen[seen.length - 1]).toEqual(['Firefox']);
  });

  it('answers the same text from its cache and cancels an outdated request', async () => {
    const aborted: boolean[] = [];
    const load = vi.fn((dimension: string, text: string, abort: AbortController) => {
      abort.signal.addEventListener('abort', () => aborted.push(true));
      return Promise.resolve([`${text}!`]);
    });
    const source = new SuggestionSource(load);
    const seen: string[][] = [];
    source.ask('pageUrl', 'shop', (values) => seen.push(values));
    vi.advanceTimersByTime(300);
    source.ask('pageUrl', 'shoes', (values) => seen.push(values));
    vi.advanceTimersByTime(300);
    await flushPromises();
    expect(aborted).toEqual([true]);
    source.ask('pageUrl', 'SHOES', (values) => seen.push(values));
    expect(load).toHaveBeenCalledTimes(2);
    expect(seen[seen.length - 1]).toEqual(['shoes!']);
  });

  it('fixed lists answer from the first character and when the field is empty', async () => {
    const load = vi.fn((dimension: string, text: string, abort: AbortController) => Promise.resolve(['Monday']));
    const source = new SuggestionSource(load);
    const seen: string[][] = [];
    expect(FIXED_DIMENSIONS).toContain('weekday');
    source.ask('weekday', '', (values) => seen.push(values));
    vi.advanceTimersByTime(300);
    await flushPromises();
    expect(seen).toEqual([['Monday']]);
  });

  it('a failing request suggests nothing', async () => {
    const source = new SuggestionSource(() => Promise.reject(new Error('busy')));
    const seen: string[][] = [];
    source.ask('browser', 'chr', (values) => seen.push(values));
    vi.advanceTimersByTime(300);
    await flushPromises();
    expect(seen).toEqual([[]]);
  });

  // release review 2026-11-25: a list for another column never lands on the field
  it('cancel stops a waiting or running request', async () => {
    const load = vi.fn((dimension: string, text: string, abort: AbortController) => Promise.resolve(['Chrome']));
    const source = new SuggestionSource(load);
    const seen: string[][] = [];
    source.ask('browser', 'chr', (values) => seen.push(values));
    source.cancel();
    vi.advanceTimersByTime(300);
    await flushPromises();
    expect(load).not.toHaveBeenCalled();
    source.ask('browser', 'chr', (values) => seen.push(values));
    vi.advanceTimersByTime(300);
    source.cancel();
    await flushPromises();
    expect(seen).toEqual([]);
  });

  it('suggests for text conditions only', () => {
    expect(['contains', 'equals', 'notEquals', 'startsWith'].every((o) => suggestsFor(o))).toBe(true);
    expect(['matches', 'empty', 'notEmpty', 'gt'].some((o) => suggestsFor(o))).toBe(false);
  });
});
