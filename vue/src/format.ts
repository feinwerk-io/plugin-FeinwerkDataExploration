/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export interface Formatters {
  number(v: number): string;
  /** v in percent, e.g. 34 for 34 % */
  percent(v: number): string;
  currency(v: number): string;
  duration(seconds: number): string;
}

const RATES = ['bounce_rate', 'conversion_rate'];

export function formatMetric(metricId: string, value: number, f: Formatters): string {
  if (RATES.includes(metricId)) {
    return f.percent(Math.round(value * 10000) / 100);
  }
  if (metricId === 'revenue') {
    return f.currency(value);
  }
  if (metricId === 'avg_time_on_site') {
    return f.duration(value);
  }
  return f.number(value);
}

/** M:SS below one hour, H:MM:SS from one hour. */
export function formatDuration(seconds: number): string {
  const total = Math.max(0, Math.round(seconds));
  const hours = Math.floor(total / 3600);
  const minutes = Math.floor((total % 3600) / 60);
  const secs = String(total % 60).padStart(2, '0');
  return hours > 0 ? `${hours}:${String(minutes).padStart(2, '0')}:${secs}` : `${minutes}:${secs}`;
}
