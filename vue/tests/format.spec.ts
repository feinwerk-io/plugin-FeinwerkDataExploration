/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { formatMetric, formatDuration, Formatters } from '../src/format';

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
});

describe('formatDuration', () => {
  it('uses M:SS below an hour and H:MM:SS above', () => {
    expect(formatDuration(0)).toBe('0:00');
    expect(formatDuration(104)).toBe('1:44');
    expect(formatDuration(3599)).toBe('59:59');
    expect(formatDuration(3725)).toBe('1:02:05');
  });
});
