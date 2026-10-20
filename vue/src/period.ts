/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function toDate(ymd: string): Date {
  return new Date(`${ymd}T00:00:00Z`);
}

function toYmd(date: Date): string {
  return date.toISOString().substring(0, 10);
}

function addDays(ymd: string, days: number): string {
  const date = toDate(ymd);
  date.setUTCDate(date.getUTCDate() + days);
  return toYmd(date);
}

function resolveDay(date: string, today: string): string|null {
  if (date === 'today' || date === 'now') {
    return today;
  }
  if (date === 'yesterday') {
    return addDays(today, -1);
  }
  return /^\d{4}-\d{2}-\d{2}$/.test(date) ? date : null;
}

/**
 * The first day (Y-m-d) of a Matomo period, or null when it cannot be told.
 */
export function periodStart(period: string, date: string, today: string): string|null {
  if (period === 'range') {
    const last = /^(last|previous)(\d+)$/.exec(date);
    if (last) {
      const count = Number(last[2]);
      return addDays(today, last[1] === 'last' ? 1 - count : -count);
    }
    return resolveDay(date.split(',')[0], today);
  }
  const day = resolveDay(date, today);
  if (day === null) {
    return null;
  }
  if (period === 'day') {
    return day;
  }
  if (period === 'week') {
    const weekday = (toDate(day).getUTCDay() + 6) % 7; // Monday = 0
    return addDays(day, -weekday);
  }
  if (period === 'month') {
    return `${day.substring(0, 8)}01`;
  }
  return null;
}

function lastDayOfMonth(day: string): string {
  const date = toDate(`${day.substring(0, 8)}01`);
  date.setUTCMonth(date.getUTCMonth() + 1);
  date.setUTCDate(0);
  return toYmd(date);
}

function periodEnd(period: string, date: string, start: string, today: string): string|null {
  if (period === 'range') {
    const last = /^(last|previous)(\d+)$/.exec(date);
    if (last) {
      return last[1] === 'last' ? today : addDays(today, -1);
    }
    return resolveDay(date.split(',')[1] || '', today);
  }
  if (period === 'day') {
    return start;
  }
  if (period === 'week') {
    return addDays(start, 6);
  }
  return period === 'month' ? lastDayOfMonth(start) : null;
}

/**
 * Every day (Y-m-d) of a Matomo period up to today, or null when it cannot be told (or is longer
 * than two months, more than an exploration covers).
 */
export function periodDays(period: string, date: string, today: string): string[]|null {
  const start = periodStart(period, date, today);
  const end = start === null ? null : periodEnd(period, date, start, today);
  if (start === null || end === null) {
    return null;
  }
  const last = end < today ? end : today;
  const days: string[] = [];
  for (let day = start; day <= last; day = addDays(day, 1)) {
    if (days.length >= 62) {
      return null;
    }
    days.push(day);
  }
  return days;
}

/**
 * A date range from the given day to the end of the period (for "show from the first complete
 * day"), or null when the period cannot be read or ends before that day.
 */
export function rangeFromDay(
  period: string,
  date: string,
  day: string,
  today: string,
): string|null {
  const start = periodStart(period, date, today);
  const end = start === null ? null : periodEnd(period, date, start, today);
  if (start === null || end === null) {
    return null;
  }
  const last = end < today ? end : today;
  return last < day ? null : `${day},${last}`;
}

/** Whether the period starts before the earliest complete day of raw data. */
export function startsBefore(
  period: string,
  date: string,
  earliest: string|null,
  today: string,
): boolean {
  const start = periodStart(period, date, today);
  return earliest !== null && start !== null && start < earliest;
}
