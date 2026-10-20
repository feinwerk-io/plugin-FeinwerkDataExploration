/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  periodDays, periodStart, rangeFromDay, startsBefore,
} from '../src/period';

const TODAY = '2026-10-19';

describe('periodStart', () => {
  it('handles days, weeks, months and ranges', () => {
    expect(periodStart('day', '2026-03-29', TODAY)).toBe('2026-03-29');
    expect(periodStart('week', '2026-03-29', TODAY)).toBe('2026-03-23');
    expect(periodStart('month', '2026-03-29', TODAY)).toBe('2026-03-01');
    expect(periodStart('range', '2026-03-27,2026-03-30', TODAY)).toBe('2026-03-27');
  });

  it('handles Matomo date keywords', () => {
    expect(periodStart('day', 'today', TODAY)).toBe(TODAY);
    expect(periodStart('day', 'yesterday', TODAY)).toBe('2026-10-18');
    expect(periodStart('range', 'last7', TODAY)).toBe('2026-10-13');
    expect(periodStart('range', 'previous7', TODAY)).toBe('2026-10-12');
    expect(periodStart('month', 'today', TODAY)).toBe('2026-10-01');
  });

  it('returns null for anything else', () => {
    expect(periodStart('year', '2026-03-29', TODAY)).toBeNull();
    expect(periodStart('day', 'soon', TODAY)).toBeNull();
  });
});

describe('startsBefore', () => {
  it('compares with the earliest complete day', () => {
    expect(startsBefore('range', '2026-03-27,2026-03-30', '2026-03-28', TODAY)).toBe(true);
    expect(startsBefore('day', '2026-03-28', '2026-03-28', TODAY)).toBe(false);
    expect(startsBefore('day', '2026-03-28', null, TODAY)).toBe(false);
  });
});

describe('periodDays', () => {
  const today = '2026-03-30';

  it('lists every day of the period up to today', () => {
    expect(periodDays('range', '2026-03-27,2026-03-29', today)).toEqual(['2026-03-27', '2026-03-28', '2026-03-29']);
    expect(periodDays('day', 'yesterday', today)).toEqual(['2026-03-29']);
    expect(periodDays('range', 'last3', today)).toEqual(['2026-03-28', '2026-03-29', '2026-03-30']);
    expect(periodDays('week', '2026-03-30', today)).toEqual(['2026-03-30']);
    expect(periodDays('month', '2026-02-10', today)).toHaveLength(28);
  });

  it('knows nothing about periods it cannot read', () => {
    expect(periodDays('year', '2026-01-01', today)).toBeNull();
    expect(periodDays('range', 'nonsense', today)).toBeNull();
  });
});

describe('rangeFromDay', () => {
  it('keeps the end of the period and starts on the given day', () => {
    expect(rangeFromDay('month', '2026-03-10', '2026-03-05', '2026-03-30')).toBe('2026-03-05,2026-03-30');
    expect(rangeFromDay('range', '2026-02-01,2026-02-20', '2026-02-10', '2026-03-30')).toBe('2026-02-10,2026-02-20');
    expect(rangeFromDay('year', '2026-01-01', '2026-02-10', '2026-03-30')).toBeNull();
  });

  it('has no range when the whole period lies before the day', () => {
    // raw data from 2026-04-05 on, the chosen month ended before: nothing to show from that day
    expect(rangeFromDay('month', '2026-03-10', '2026-04-05', '2026-10-19')).toBeNull();
    expect(rangeFromDay('day', '2026-04-04', '2026-04-05', '2026-10-19')).toBeNull();
    expect(rangeFromDay('day', '2026-04-05', '2026-04-05', '2026-10-19')).toBe('2026-04-05,2026-04-05');
  });
});
