/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  axisLabelStep, canChart, chartMetricFor, chartModel, compareBarModel, compareChartHint, effectiveView, linePath,
  niceScale, resultTitle, truncateLabel,
} from '../src/chart';
import { ResultRow } from '../src/api';
import { ComparisonColumn } from '../src/types';

describe('axisLabelStep', () => {
  it('labels days weekly when every day does not fit, as Matomo\'s graphs do', () => {
    // October: 31 days on 690 pixels, labels like "Thu, Oct 1" about 78 pixels wide
    expect(axisLabelStep(31, 690, 78, 'day')).toBe(7);
  });

  it('labels every day or every second day when there is room', () => {
    expect(axisLabelStep(7, 690, 78, 'day')).toBe(1);
    expect(axisLabelStep(14, 690, 78, 'day')).toBe(2);
  });

  it('labels a narrow month fortnightly, never just its first day', () => {
    expect(axisLabelStep(31, 320, 78, 'day')).toBe(14);
    expect(axisLabelStep(31, 170, 78, 'day')).toBe(14);
  });

  it('labels hours in steps of 1, 2, 3, 4, 6 or 12', () => {
    expect(axisLabelStep(24, 690, 40, 'hour')).toBe(2);
    expect(axisLabelStep(24, 320, 40, 'hour')).toBe(3);
    expect(axisLabelStep(24, 150, 40, 'hour')).toBe(12);
  });

  it('labels other categories as many as fit', () => {
    expect(axisLabelStep(7, 690, 80, 'other')).toBe(1);
    expect(axisLabelStep(7, 200, 80, 'other')).toBe(3);
    expect(axisLabelStep(1, 690, 80, 'day')).toBe(1);
  });
});
import { parseState } from '../src/state';
import { CATALOG, LIMITS } from './fixtures';

describe('resultTitle', () => {
  it('names the dimensions of the result', () => {
    const one = parseState({ fwdeDims: 'channelType' }, CATALOG, LIMITS);
    const two = parseState({ fwdeDims: 'channelType,browser' }, CATALOG, LIMITS);
    expect(resultTitle(one, CATALOG)).toBe('Channel type');
    expect(resultTitle(two, CATALOG)).toBe('Channel type × Browser');
  });

  it('shows an unknown id as it is', () => {
    const state = { ...parseState({}, CATALOG, LIMITS), dimensions: ['nope'] };
    expect(resultTitle(state, CATALOG)).toBe('nope');
  });
});

const WEEKDAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

function state(hash: Record<string, string>) {
  return parseState(hash, CATALOG, LIMITS);
}

describe('niceScale', () => {
  it('has a unit scale for no values', () => {
    expect(niceScale(0, 0)).toEqual({ min: 0, max: 1, ticks: [0, 0.2, 0.4, 0.6, 0.8, 1] });
  });

  it('rounds up to a nice maximum', () => {
    expect(niceScale(0, 3820)).toEqual({ min: 0, max: 4000, ticks: [0, 1000, 2000, 3000, 4000] });
    expect(niceScale(0, 0.44).max).toBe(0.5);
  });

  it('starts below zero for negative values', () => {
    expect(niceScale(-12.5, 40)).toEqual({ min: -20, max: 40, ticks: [-20, 0, 20, 40] });
  });

  it('steps by whole numbers for counts', () => {
    expect(niceScale(0, 2, 5, true).ticks).toEqual([0, 1, 2]);
    expect(niceScale(0, 12, 5, true).ticks).toEqual([0, 5, 10, 15]);
  });

  it('ends for extreme values', () => {
    [niceScale(-1e308, 1e308), niceScale(0, 1e-200), niceScale(0, 1.7e308)].forEach((scale) => {
      expect(scale.ticks.length).toBeLessThan(20);
      [scale.min, scale.max, ...scale.ticks].forEach((value) => expect(Number.isFinite(value)).toBe(true));
    });
  });

  it('never returns NaN', () => {
    [niceScale(0, Infinity), niceScale(NaN, NaN), niceScale(-Infinity, 5)].forEach((scale) => {
      [scale.min, scale.max, ...scale.ticks].forEach((value) => expect(Number.isFinite(value)).toBe(true));
    });
  });
});

describe('chartMetricFor', () => {
  it('takes the chosen chart metric, else the sorted metric, else the first', () => {
    expect(chartMetricFor(state({ fwdeMetrics: 'nb_visits,bounce_rate', fwdeChart: 'bounce_rate' })))
      .toBe('bounce_rate');
    expect(chartMetricFor(state({ fwdeMetrics: 'nb_visits,bounce_rate', fwdeSort: 'bounce_rate' })))
      .toBe('bounce_rate');
    expect(chartMetricFor(state({ fwdeMetrics: 'nb_visits,bounce_rate', fwdeSort: 'label' })))
      .toBe('nb_visits');
  });
});

describe('effectiveView', () => {
  it('shows both where a chart makes sense, the table otherwise', () => {
    expect(effectiveView(state({}), 5)).toBe('both');
    expect(effectiveView(state({}), 1)).toBe('table');
    expect(effectiveView(state({ fwdeView: 'chart' }), 5)).toBe('chart');
    expect(effectiveView(state({ fwdeView: 'table' }), 5)).toBe('table');
    expect(canChart(2)).toBe(true);
  });
});

describe('effectiveView, one row', () => {
  it('offers the chart for one row when a trend is possible, the table by default', () => {
    expect(effectiveView(state({ fwdeDims: 'browser' }), 1)).toBe('table');
    expect(effectiveView(state({ fwdeDims: 'browser', fwdeView: 'chart' }), 1)).toBe('chart');
    expect(effectiveView(state({ fwdeDims: 'date', fwdeView: 'chart' }), 1)).toBe('table');
    expect(canChart(1, state({ fwdeDims: 'browser' }))).toBe(true);
    expect(canChart(1)).toBe(false);
  });
});

describe('chartModel, time in second place', () => {
  it('takes a time dimension in second place as the axis', () => {
    const rows = [
      { label: 'Chrome', secondary_label: '2026-03-27', nb_visits: 2 },
      { label: 'Chrome', secondary_label: '2026-03-28', nb_visits: 3 },
      { label: 'Firefox', secondary_label: '2026-03-27', nb_visits: 1 },
    ];
    const model = chartModel(rows, state({ fwdeDims: 'browser,date', fwdeMetrics: 'nb_visits' }), 'nb_visits', {
      weekdayNames: [], periodDays: ['2026-03-27', '2026-03-28'],
    });
    expect(model.type).toBe('line');
    expect(model.categories).toEqual(['2026-03-27', '2026-03-28']);
    expect(model.series.map((s) => s.label)).toEqual(['Chrome', 'Firefox']);
  });

  it('draws as many time lines as allowed', () => {
    const rows = ['A', 'B', 'C', 'D', 'E', 'F'].map((browser, i) => ({
      label: '2026-03-27', secondary_label: browser, nb_visits: 10 - i,
    }));
    const lines = (maxSeries: number) => chartModel(rows, state({ fwdeDims: 'date,browser', fwdeMetrics: 'nb_visits' }),
      'nb_visits', { weekdayNames: [], maxSeries }).series.length;
    expect(lines(6)).toBe(6);
    expect(lines(5)).toBe(5);
  });
});

const HOURS_24 = Array.from({ length: 24 }, (_, h) => `${h}h`);
const HOURS_12 = Array.from({ length: 24 }, (_, h) => `${h % 12 || 12} ${h < 12 ? 'AM' : 'PM'}`);

function options(extra: Record<string, unknown> = {}) {
  return { weekdayNames: WEEKDAYS, hourLabels: HOURS_24, ...extra };
}

describe('chartModel, bars', () => {
  const rows = Array.from({ length: 30 }, (_, i) => ({ label: `Row ${i}`, nb_visits: i }));

  it('shows the 20 largest rows', () => {
    const model = chartModel(rows, state({ fwdeDims: 'browser' }), 'nb_visits', options());
    expect(model.type).toBe('bar');
    expect(model.series).toHaveLength(1);
    expect(model.categories).toHaveLength(20);
    expect(model.categories[0]).toBe('Row 29');
    expect(model.series[0].points[0].value).toBe(29);
    expect(model.omittedRows).toBe(10);
    expect(model.scale.max).toBe(30);
  });

  it('names a bar after both dimensions', () => {
    const model = chartModel([
      { label: 'Chrome', secondary_label: '/shop', nb_visits: 3 },
      { label: 'Firefox', secondary_label: '/', nb_visits: 1 },
    ], state({ fwdeDims: 'browser,pageUrl' }), 'nb_visits', options());
    expect(model.categories).toEqual(['Chrome · /shop', 'Firefox · /']);
  });

  it('uses as many ticks as fit', () => {
    const model = chartModel(rows, state({ fwdeDims: 'browser' }), 'nb_visits', options({ tickCount: 2 }));
    expect(model.scale.ticks).toEqual([0, 20, 40]);
  });
});

describe('chartModel, lines', () => {
  it('orders dates', () => {
    const model = chartModel([
      { label: '2026-03-30', nb_visits: 3 },
      { label: '2026-03-27', nb_visits: 1 },
      { label: '2026-03-28', nb_visits: 2 },
    ], state({ fwdeDims: 'date' }), 'nb_visits', options());
    expect(model.type).toBe('line');
    expect(model.categories).toEqual(['2026-03-27', '2026-03-28', '2026-03-30']);
    expect(model.series[0].points.map((p) => p.value)).toEqual([1, 2, 3]);
  });

  it('shows every day of the period, a day without visits as zero', () => {
    const model = chartModel([
      { label: '2026-03-30', nb_visits: 3 },
      { label: '2026-03-28', nb_visits: 2 },
    ], state({ fwdeDims: 'date' }), 'nb_visits', options({
      periodDays: ['2026-03-28', '2026-03-29', '2026-03-30'],
    }));
    expect(model.categories).toEqual(['2026-03-28', '2026-03-29', '2026-03-30']);
    expect(model.series[0].points.map((p) => p.value)).toEqual([2, 0, 3]);
  });

  it('draws one line per value of the second dimension, the five largest', () => {
    const channels = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
    const rows = channels.flatMap((channel, i) => [
      { label: '2026-03-27', secondary_label: channel, nb_visits: i + 1 },
      { label: '2026-03-28', secondary_label: channel, nb_visits: i + 1 },
    ]);
    const model = chartModel(rows, state({ fwdeDims: 'date,channelType' }), 'nb_visits', options());
    expect(model.series.map((s) => s.label)).toEqual(['G', 'F', 'E', 'D', 'C']);
    expect(model.omittedSeries).toBe(2);
  });

  it('counts a missing combination as zero, but leaves a gap for rates', () => {
    const rows = [
      { label: '2026-03-27', secondary_label: 'A', nb_visits: 1, bounce_rate: 0.5 },
      { label: '2026-03-28', secondary_label: 'B', nb_visits: 2, bounce_rate: 0.25 },
    ];
    const counts = chartModel(rows, state({ fwdeDims: 'date,channelType' }), 'nb_visits', options());
    expect(counts.series.find((s) => s.label === 'A')?.points.map((p) => p.value)).toEqual([1, 0]);
    const rates = chartModel(rows, state({ fwdeDims: 'date,channelType', fwdeMetrics: 'bounce_rate' }),
      'bounce_rate', options());
    expect(rates.series.find((s) => s.label === 'A')?.points.map((p) => p.value)).toEqual([0.5, null]);
  });

  it('leaves a gap for averages and maxima too', () => {
    const rows = [
      {
        label: '2026-03-27', secondary_label: 'A', avg_event_value: 1.5, max_actions: 4,
      },
      {
        label: '2026-03-28', secondary_label: 'B', avg_event_value: 2, max_actions: 2,
      },
    ];
    ['avg_event_value', 'max_actions'].forEach((metric) => {
      const model = chartModel(rows, state({ fwdeDims: 'date,channelType', fwdeMetrics: metric }),
        metric, options());
      expect(model.series.find((s) => s.label === 'A')?.points[1].value).toBeNull();
    });
  });

  it('leaves a gap instead of zero when the result was cut at the row limit', () => {
    const rows = [
      { label: '2026-03-27', secondary_label: 'A', nb_visits: 1 },
      { label: '2026-03-28', secondary_label: 'B', nb_visits: 2 },
    ];
    const cut = chartModel(rows, state({ fwdeDims: 'date,channelType' }), 'nb_visits',
      options({ complete: false }));
    expect(cut.series.find((s) => s.label === 'A')?.points.map((p) => p.value)).toEqual([1, null]);
    expect(cut.incomplete).toBe(true);
    const whole = chartModel(rows, state({ fwdeDims: 'date,channelType' }), 'nb_visits', options());
    expect(whole.incomplete).toBe(false);
  });

  it('leaves a gap instead of zero when a metric filter removed rows', () => {
    const rows = [
      { label: '2026-03-27', secondary_label: 'A', nb_visits: 30 },
      { label: '2026-03-28', secondary_label: 'B', nb_visits: 25 },
    ];
    const filtered = state({ fwdeDims: 'date,channelType', fwdeFilters: 'nb_visits~gt~20' });
    const model = chartModel(rows, filtered, 'nb_visits', options({ periodDays: ['2026-03-27', '2026-03-28'] }));
    expect(model.series.find((s) => s.label === 'A')?.points.map((p) => p.value)).toEqual([30, null]);
  });

  it('orders hours as Matomo labels them, also on a 12-hour clock', () => {
    const rows = [{ label: '1 PM', nb_visits: 1 }, { label: '1 AM', nb_visits: 2 }, { label: '12 AM', nb_visits: 3 }];
    const model = chartModel(rows, state({ fwdeDims: 'hour' }), 'nb_visits',
      options({ hourLabels: HOURS_12, complete: false }));
    expect(model.categories).toEqual(['12 AM', '1 AM', '1 PM']);
  });

  it('shows all 24 hours and all weekdays of a complete result', () => {
    const hours = chartModel([{ label: '8h', nb_visits: 2 }, { label: '0h', nb_visits: 3 }],
      state({ fwdeDims: 'hour' }), 'nb_visits', options());
    expect(hours.categories).toEqual(HOURS_24);
    expect(hours.series[0].points[8].value).toBe(2);
    expect(hours.series[0].points[1].value).toBe(0);
    const days = chartModel([
      { label: 'Sunday', nb_visits: 1 }, { label: 'Monday', nb_visits: 2 }, { label: 'Friday', nb_visits: 3 },
    ], state({ fwdeDims: 'weekday' }), 'nb_visits', options());
    expect(days.categories).toEqual(WEEKDAYS);
    expect(days.series[0].points.map((p) => p.value)).toEqual([2, 0, 0, 0, 3, 0, 1]);
  });

  it('shows only the hours with rows for rates', () => {
    const model = chartModel([{ label: '8h', bounce_rate: 0.5 }, { label: '0h', bounce_rate: 0.25 }],
      state({ fwdeDims: 'hour', fwdeMetrics: 'bounce_rate' }), 'bounce_rate', options());
    expect(model.categories).toEqual(['0h', '8h']);
  });
});

describe('linePath', () => {
  const x = (i: number) => i * 10;
  const y = (v: number) => 100 - v;

  it('draws one path through the values and starts again after a gap', () => {
    expect(linePath([1, 2, 3], x, y)).toBe('M0,99 L10,98 L20,97');
    expect(linePath([1, null, 3, 4], x, y)).toBe('M0,99 M20,97 L30,96');
    expect(linePath([null, null], x, y)).toBe('');
  });
});

describe('truncateLabel', () => {
  it('keeps short labels and cuts long ones in the middle, so their ends stay apart', () => {
    expect(truncateLabel('Chrome', 10)).toBe('Chrome');
    expect(truncateLabel('outdoor-shop.example/wandern/', 10)).toBe('outdo…ern/');
    expect(truncateLabel('abc', 0)).toBe('…');
  });
});

const column = (title: string, kind: ComparisonColumn['kind']): ComparisonColumn => ({
  segment: '', period: 'range', date: '2026-03-27,2026-03-30', title, kind,
});

describe('compareBarModel', () => {
  const columns = [column('All visits', 'own'), column('Mobile visitors', 'segment'), column('Previous', 'period')];
  const rows: ResultRow[] = [
    {
      label: 'Total', fwde_total: 1, nb_visits: 30, nb_visits__c1: 12, nb_visits__c2: 25,
    },
    {
      label: 'Chrome', nb_visits: 10, nb_visits__c1: 4, nb_visits__c2: 8, nb_visits__c2__change: 25,
    },
    {
      label: 'Firefox', nb_visits: 20, nb_visits__c1: '', nb_visits__c2: 16, nb_visits__c2__change: 25,
    },
  ];

  it('one series per column, rows by the own value, without the total row', () => {
    const model = compareBarModel(rows, columns, 'nb_visits', {});
    expect(model.type).toBe('bar');
    expect(model.categories).toEqual(['Firefox', 'Chrome']);
    expect(model.series.map((s) => [s.label, s.comparison])).toEqual([
      ['All visits', true], ['Mobile visitors', true], ['Previous', true]]);
    expect(model.series.map((s) => s.points.map((p) => p.value))).toEqual([[20, 10], [null, 4], [16, 8]]);
    expect(model.series[2].points.map((p) => p.change)).toEqual([25, 25]);
    expect(model.series[1].points.map((p) => p.change)).toEqual([undefined, undefined]);
    expect(model.scale.min).toBe(0);
    expect(model.scale.max).toBeGreaterThanOrEqual(20);
  });

  it('at most 10 rows while comparing', () => {
    const many: ResultRow[] = Array.from({ length: 14 }, (_, i) => ({ label: `B${i}`, nb_visits: 14 - i, nb_visits__c1: i }));
    const model = compareBarModel(many, columns.slice(0, 2), 'nb_visits', {});
    expect(model.categories).toHaveLength(10);
    expect(model.omittedRows).toBe(4);
  });

  it('a missing column gives null points, never NaN', () => {
    const model = compareBarModel([{ label: 'Chrome', nb_visits: 3 }], columns.slice(0, 2), 'nb_visits', {});
    expect(model.series[1].points[0].value).toBeNull();
    expect(Number.isFinite(model.scale.max)).toBe(true);
  });
});

describe('compareChartHint', () => {
  it('no hint for grouped bars, the trend hint for lines, the own-view hint for a time dimension in second place', () => {
    expect(compareChartHint(state({ fwdeDims: 'browser' }), 'bar', 3)).toBe('');
    expect(compareChartHint(state({ fwdeDims: 'date' }), 'bar', 3)).toBe('FeinwerkDataExploration_CompareTrendHint');
    expect(compareChartHint(state({ fwdeDims: 'browser,date' }), 'bar', 3)).toBe('FeinwerkDataExploration_CompareChartHint');
    expect(compareChartHint(state({ fwdeDims: 'date,browser' }), 'bar', 3)).toBe('FeinwerkDataExploration_CompareChartHint');
    expect(compareChartHint(state({ fwdeDims: 'browser' }), 'trend', 3)).toBe('FeinwerkDataExploration_CompareTrendHint');
  });
});
