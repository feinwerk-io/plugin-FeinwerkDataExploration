/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { formatDay, formatHour, formatMetric, formatDuration, Formatters, rowCountKey, formatLongDate } from '../src/format';

describe('formatHour', () => {
  it('writes a bare hour as a time of day', () => {
    expect(formatHour('7')).toBe('07:00');
    expect(formatHour('00')).toBe('00:00');
    expect(formatHour('14')).toBe('14:00');
  });

  it('keeps Matomo\'s hour labels that already read as a time', () => {
    expect(formatHour('2 PM')).toBe('2 PM');
    expect(formatHour('00 Uhr')).toBe('00 Uhr');
  });
});

describe('formatDay', () => {
  it('writes a day as Matomo\'s graphs do: weekday, month and day in the user\'s language', () => {
    expect(formatDay('2026-09-15', 'en')).toBe('Tue, Sep 15');
    expect(formatDay('2026-03-29', 'en')).toBe('Sun, Mar 29');
    expect(formatDay('2026-09-15', 'de')).not.toBe('Tue, Sep 15');
  });

  it('writes the full date for a tooltip, as Matomo\'s tooltip does', () => {
    expect(formatDay('2026-09-23', 'en', true)).toBe('Wednesday, September 23, 2026');
  });

  it('leaves anything else as it is', () => {
    expect(formatDay('14h', 'en')).toBe('14h');
    expect(formatDay('Chrome', 'en')).toBe('Chrome');
  });
});

const formatters: Formatters = {
  number: (v) => `n${v}`,
  percent: (v) => `p${v}`,
  currency: (v) => `c${v}`,
  duration: (v) => `d${v}`,
};

describe('formatMetric', () => {
  it('shows rates as percent', () => {
    expect(formatMetric('bounce_rate', 0.34, formatters)).toBe('p34');
    expect(formatMetric('conversion_rate', 0.1364, formatters)).toBe('p13.64');
  });

  it('shows revenue as currency and the visit duration as time', () => {
    expect(formatMetric('revenue', 37205.85, formatters)).toBe('c37205.85');
    expect(formatMetric('avg_time_on_site', 104, formatters)).toBe('d104');
  });

  it('shows counts and amounts as numbers', () => {
    expect(formatMetric('nb_visits', 2138, formatters)).toBe('n2138');
    expect(formatMetric('sum_event_value', 3.75, formatters)).toBe('n3.75');
  });

  it('formats goal, e-commerce and visit metrics by their unit (more metrics)', () => {
    expect(formatMetric('goal_3_revenue', 12.5, formatters)).toBe('c12.5');
    expect(formatMetric('goal_3_conversion_rate', 0.25, formatters)).toBe('p25');
    expect(formatMetric('goal_ecommerceOrder_avg_order_revenue', 60, formatters)).toBe('c60');
    expect(formatMetric('revenue_per_visit', 55, formatters)).toBe('c55');
    expect(formatMetric('sum_visit_length', 3700, formatters)).toBe('d3700');
    expect(formatMetric('goal_ecommerceOrder_items', 5, formatters)).toBe('n5');
  });
});

describe('formatDuration', () => {
  it('uses M:SS below an hour and H:MM:SS above', () => {
    expect(formatDuration(0)).toBe('0:00');
    expect(formatDuration(104)).toBe('1:44');
    expect(formatDuration(3599)).toBe('59:59');
    expect(formatDuration(3725)).toBe('1:02:05');
  });
});

describe('rowCountKey', () => {
  it('says "1 row", not "1 rows"', () => {
    expect(rowCountKey(1)).toBe('FeinwerkDataExploration_RowCountOne');
    expect(rowCountKey(0)).toBe('FeinwerkDataExploration_RowCount');
    expect(rowCountKey(7)).toBe('FeinwerkDataExploration_RowCount');
  });
});

// UI review 2026-11-26 F1: the save panel names the day in the user's language
describe('a day in words', () => {
  it('writes the day with its month and year', () => {
    expect(formatLongDate('2026-05-26', 'de')).toBe('26. Mai 2026');
    expect(formatLongDate('2026-05-26', 'en')).toBe('May 26, 2026');
    expect(formatLongDate('not a day', 'en')).toBe('not a day');
  });
});
