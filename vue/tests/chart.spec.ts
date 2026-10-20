/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  canChart, chartMetricFor, chartModel, effectiveView, linePath, niceScale, resultTitle,
  truncateLabel,
} from '../src/chart';
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
    expect(effectiveView(state({ fwdeView: 'chart' }), 1)).toBe('table');
    expect(effectiveView(state({ fwdeView: 'table' }), 5)).toBe('table');
    expect(canChart(2)).toBe(true);
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
