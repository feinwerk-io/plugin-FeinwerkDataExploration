/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  canRetry, isLargePeriod, isSlow, needsSmallerPeriod, noSmallerPeriodKey, RUN_DELAY_MS,
} from '../src/hints';

describe('isLargePeriod', () => {
  it('warns above a quarter of the visit threshold, not above the threshold', () => {
    expect(isLargePeriod(500000, 2000000)).toBe(false);
    expect(isLargePeriod(500001, 2000000)).toBe(true);
    expect(isLargePeriod(2000000, 2000000)).toBe(true);
    // above the threshold the exploration itself refuses with its own message
    expect(isLargePeriod(2000001, 2000000)).toBe(false);
    expect(isLargePeriod(0, 0)).toBe(false);
  });
});

describe('isSlow', () => {
  it('flags a request that took at least 40 % of the time limit and at least 3 seconds', () => {
    expect(isSlow(11999, 30)).toBe(false);
    expect(isSlow(12000, 30)).toBe(true);
    expect(isSlow(2999, 5)).toBe(false);
    expect(isSlow(3000, 5)).toBe(true);
  });

  it('uses 10 seconds without a time limit', () => {
    expect(isSlow(9999, 0)).toBe(false);
    expect(isSlow(10000, 0)).toBe(true);
  });

  it('waits a moment before a request', () => {
    expect(RUN_DELAY_MS).toBe(400);
  });
});

describe('needsSmallerPeriod', () => {
  it('asks for a smaller period above the threshold or when the period could not be counted', () => {
    expect(needsSmallerPeriod({ visits: 2000001, maxVisits: 2000000 }, false)).toBe(true);
    expect(needsSmallerPeriod({ visits: 10, maxVisits: 2000000 }, false)).toBe(false);
    // a period longer than an exploration covers cannot be counted
    expect(needsSmallerPeriod(null, true)).toBe(true);
    expect(needsSmallerPeriod(null, false)).toBe(false);
  });
});

describe('noSmallerPeriodKey', () => {
  it('points super users to the setting and everyone else to the administrator', () => {
    // a segment does not help: the visit threshold counts all visits of the period
    expect(noSmallerPeriodKey(true)).toBe('FeinwerkDataExploration_SmallerPeriodNoneSuperUser');
    expect(noSmallerPeriodKey(false)).toBe('FeinwerkDataExploration_SmallerPeriodNone');
  });
});

describe('canRetry', () => {
  const busy = 'Other explorations are running right now. Please try again in a moment.';
  const timeoutStart = 'The exploration took longer than ';
  it('offers "try again" for a busy instance and the time limit only', () => {
    expect(canRetry(busy, busy, timeoutStart)).toBe(true);
    expect(canRetry(`${timeoutStart}30 seconds and was stopped.`, busy, timeoutStart)).toBe(true);
    // the same request would fail the same way
    expect(canRetry('An exploration covers at most 31 days.', busy, timeoutStart)).toBe(false);
    expect(canRetry('', busy, timeoutStart)).toBe(false);
  });
});
