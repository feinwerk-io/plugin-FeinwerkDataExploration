/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  changeText, changeTone, comparisonLabel, KpiCardData, weekdayKey,
} from '../src/kpi';
import { Formatters } from '../src/format';

const formatters: Formatters = {
  number: (v) => String(v),
  percent: (v) => `${v} %`,
  currency: (v) => `$${v}`,
  duration: (v) => `${v}s`,
};
const translate = (key: string, ...params: (string|number)[]) => [key, ...params].join(' ');

function card(
  extra: Partial<KpiCardData> = {},
  comparison: Partial<NonNullable<KpiCardData['comparison']>> = {},
): KpiCardData {
  return {
    title: 'Newsletter visits',
    metric: 'nb_visits',
    metricName: 'Visits',
    unit: 'count',
    value: 1284,
    partial: false,
    until: null,
    firstDay: '2026-11-18',
    lastDay: '2026-11-18',
    changeAs: 'percent',
    comparison: {
      kind: 'average4',
      period: 'day',
      baseline: 1146,
      used: 4,
      wanted: 4,
      spans: [],
      change: 0.123,
      absolute: 138,
      ...comparison,
    },
    comparisonNote: null,
    line: null,
    lineNote: null,
    lowerIsBetter: false,
    ...extra,
  };
}

describe('changeText', () => {
  it('writes the change in percent or absolute with its direction', () => {
    expect(changeText(card(), formatters)).toBe('▲ 12 %');
    expect(changeText(card({}, { change: -0.05, absolute: -60 }), formatters)).toBe('▼ 5 %');
    expect(changeText(card({}, { change: 0, absolute: 0 }), formatters)).toBe('± 0 %');
    expect(changeText(card({ changeAs: 'absolute' }), formatters)).toBe('▲ 138');
    expect(changeText(card({ changeAs: 'absolute', metric: 'bounce_rate' }, { absolute: -0.05 }), formatters))
      .toBe('▼ 5 %');
  });

  it('writes nothing without a comparison or with a baseline of 0', () => {
    expect(changeText(card({}, { change: null, baseline: 0 }), formatters)).toBe('');
    expect(changeText({ ...card(), comparison: null }, formatters)).toBe('');
  });
});

describe('changeTone', () => {
  it('follows the metric: more visits is good, a higher bounce rate is bad', () => {
    expect(changeTone(card())).toBe('good');
    expect(changeTone(card({ metric: 'bounce_rate', lowerIsBetter: true }))).toBe('bad');
    expect(changeTone(card({}, { absolute: -3 }))).toBe('bad');
    expect(changeTone(card({}, { absolute: 0, change: 0 }))).toBe('neutral');
    expect(changeTone({ ...card(), comparison: null })).toBe('neutral');
  });
});

describe('comparisonLabel', () => {
  it('names the baseline of each comparison', () => {
    const label = (kind: 'previous'|'weekday'|'average4', period: string, used = 4) => comparisonLabel(
      card({}, { kind, period, used }),
      translate,
    );
    expect(label('previous', 'day')).toBe('FeinwerkDataExploration_KpiVsPreviousDay');
    expect(label('previous', 'week')).toBe('FeinwerkDataExploration_KpiVsPreviousWeek');
    expect(label('weekday', 'month')).toBe('FeinwerkDataExploration_KpiVsPreviousMonth');
    expect(label('weekday', 'day')).toBe('FeinwerkDataExploration_KpiVsWeekday Intl_Day_Long_StandAlone_3');
    expect(label('average4', 'day', 3))
      .toBe('FeinwerkDataExploration_KpiVsAverageWeekdays Intl_Day_Long_StandAlone_3 3');
    expect(label('average4', 'week')).toBe('FeinwerkDataExploration_KpiVsAverageWeeks 4');
    expect(label('average4', 'month', 2)).toBe('FeinwerkDataExploration_KpiVsAverageMonths 2');
    expect(comparisonLabel({ ...card(), comparison: null }, translate)).toBe('');
  });

  it('knows the weekday of a day, Monday first', () => {
    expect(weekdayKey('2026-11-18')).toBe('Intl_Day_Long_StandAlone_3');
    expect(weekdayKey('2026-11-22')).toBe('Intl_Day_Long_StandAlone_7');
    expect(weekdayKey('2026-11-16')).toBe('Intl_Day_Long_StandAlone_1');
  });
});
