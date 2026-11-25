/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { formatMetric, Formatters } from './format';

/** A KPI card as FeinwerkDataExploration.getKpiCard returns it (2026-11-24). */
export interface KpiCardData {
  title: string;
  metric: string;
  metricName: string;
  unit: string;
  value: number|null;
  /** the period includes today: the number so far, until the time (HH:MM, website's timezone) */
  partial: boolean;
  until: string|null;
  firstDay: string;
  lastDay: string;
  /** the card's setting: the change in percent or absolute */
  changeAs: 'percent'|'absolute';
  comparison: null|{
    kind: 'previous'|'weekday'|'average4';
    period: string;
    baseline: number;
    /** comparison days or periods with a value, of those wanted */
    used: number;
    wanted: number;
    spans: { firstDay: string, lastDay: string, value: number|null }[];
    /** a fraction, null when the baseline is 0 */
    change: number|null;
    absolute: number|null;
  };
  comparisonNote: string|null;
  line: null|{ day: string, value: number|null, baseline: boolean }[];
  lineNote: string|null;
  lowerIsBetter: boolean;
}

type Translate = (key: string, ...params: (string|number)[]) => string;

function arrow(sign: number): string {
  if (sign > 0) {
    return '▲';
  }
  return sign < 0 ? '▼' : '±';
}

/** "▲ 12 %" or "▼ 140" as the card's setting says; '' without a comparison or a change. */
export function changeText(card: KpiCardData, formatters: Formatters): string {
  const { comparison } = card;
  if (!comparison) {
    return '';
  }
  if (card.changeAs === 'absolute') {
    if (comparison.absolute === null) {
      return '';
    }
    const amount = formatMetric(card.metric, Math.abs(comparison.absolute), formatters);
    return `${arrow(Math.sign(comparison.absolute))} ${amount}`;
  }
  if (comparison.change === null) {
    return '';
  }
  const percent = Math.round(Math.abs(comparison.change) * 100);
  return `${arrow(percent === 0 ? 0 : Math.sign(comparison.change))} ${formatters.percent(percent)}`;
}

/** Whether the change is good news: up for most metrics, down where less is better. */
export function changeTone(card: KpiCardData): 'good'|'bad'|'neutral' {
  const absolute = card.comparison ? card.comparison.absolute : null;
  if (absolute === null || absolute === 0) {
    return 'neutral';
  }
  const up = absolute > 0;
  return up !== card.lowerIsBetter ? 'good' : 'bad';
}

/** Matomo's name of a day's weekday (Intl_Day_Long_StandAlone_1 is Monday). */
export function weekdayKey(day: string): string {
  const weekday = new Date(`${day}T00:00:00Z`).getUTCDay();
  return `Intl_Day_Long_StandAlone_${weekday === 0 ? 7 : weekday}`;
}

/** What the change is measured against, e.g. "vs. ⌀ Wednesday, last 4 weeks"; '' without one. */
export function comparisonLabel(card: KpiCardData, translate: Translate): string {
  const { comparison } = card;
  if (!comparison) {
    return '';
  }
  const { kind, period, used } = comparison;
  if (period === 'day' && kind === 'weekday') {
    return translate('FeinwerkDataExploration_KpiVsWeekday', translate(weekdayKey(card.lastDay)));
  }
  if (kind === 'average4') {
    if (period === 'day') {
      return translate('FeinwerkDataExploration_KpiVsAverageWeekdays', translate(weekdayKey(card.lastDay)), used);
    }
    return translate(period === 'week'
      ? 'FeinwerkDataExploration_KpiVsAverageWeeks'
      : 'FeinwerkDataExploration_KpiVsAverageMonths', used);
  }
  const keys: Record<string, string> = {
    day: 'FeinwerkDataExploration_KpiVsPreviousDay',
    week: 'FeinwerkDataExploration_KpiVsPreviousWeek',
    month: 'FeinwerkDataExploration_KpiVsPreviousMonth',
  };
  return translate(keys[period] || keys.day);
}
