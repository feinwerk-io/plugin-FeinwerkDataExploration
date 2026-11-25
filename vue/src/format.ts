/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { isMoney, isRate, isSeconds } from './metricMeta';

export interface Formatters {
  number(v: number): string;
  /** v in percent, e.g. 34 for 34 % */
  percent(v: number): string;
  currency(v: number): string;
  duration(seconds: number): string;
}

/**
 * An hour as a time of day: Matomo's hour labels are bare numbers in some languages ("14" in
 * English with a 24-hour clock), which do not read as a time; those become "14:00". Labels such
 * as "2 PM" or "14 Uhr" stay.
 */
export function formatHour(label: string): string {
  return /^\d{1,2}$/.test(label) ? `${label.padStart(2, '0')}:00` : label;
}

/**
 * A day (YYYY-MM-DD) as Matomo's graphs write it, in the user's language: on the axis short
 * ("Tue, Sep 15"), in the tooltip in full ("Tuesday, September 15, 2026"); anything else stays.
 */
export function formatDay(value: string, language: string, full = false): string {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    return value;
  }
  const options: Intl.DateTimeFormatOptions = full
    ? {
      weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', timeZone: 'UTC',
    }
    : {
      weekday: 'short', month: 'short', day: 'numeric', timeZone: 'UTC',
    };
  try {
    return new Intl.DateTimeFormat(language || 'en', options)
      .format(new Date(`${value}T00:00:00Z`));
  } catch (e) {
    return value;
  }
}

/** By the metric's unit: rates in percent, money in the website's currency, seconds as time. */
export function formatMetric(metricId: string, value: number, f: Formatters): string {
  if (isRate(metricId)) {
    return f.percent(Math.round(value * 10000) / 100);
  }
  if (isMoney(metricId)) {
    return f.currency(value);
  }
  if (isSeconds(metricId)) {
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

/** The text of the table footer: "1 row", otherwise "%s rows". */
export function rowCountKey(count: number): string {
  return count === 1 ? 'FeinwerkDataExploration_RowCountOne' : 'FeinwerkDataExploration_RowCount';
}
