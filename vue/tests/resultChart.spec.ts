/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import ResultChart from '../src/ResultChart.vue';
import { parseState } from '../src/state';
import { CATALOG, LIMITS } from './fixtures';
import { ResultRow } from '../src/api';
import { ComparisonColumn } from '../src/types';

// Matomo 5 runs the specs with Jest; CoreHome is Matomo's UMD global, so the mock is virtual
jest.mock('CoreHome', () => ({
  translate: (key: string, ...params: string[]) => [key, ...params].join(' '),
  ActivityIndicator: { name: 'ActivityIndicator', template: '<div class="loadingPiwik" />' },
  Matomo: { language: 'en' },
  ComparisonsStoreInstance: { getSeriesColorName: (seriesIndex: number) => `series${seriesIndex}` },
  NumberFormatter: {
    formatNumber: (v: number) => String(v),
    formatPercent: (v: number) => `${v} %`,
    formatCurrency: (v: number) => `$${v}`,
  },
}), { virtual: true });

const state = parseState({ fwdeDims: 'browser', fwdeMetrics: 'nb_visits', fwdeChartType: 'trend' }, CATALOG, LIMITS);
const rows = Array.from({ length: 12 }, (_, i) => ({ label: `Browser ${i}`, nb_visits: 12 - i }));

function chart(extra: Record<string, unknown>) {
  return mount(ResultChart, {
    props: {
      rows,
      state,
      catalog: CATALOG,
      metric: 'nb_visits',
      actionScope: false,
      currencySymbol: '$',
      chartType: 'trend',
      showTypeSwitch: true,
      ...extra,
    },
  });
}

const twoLines = [
  {
    label: 'Chrome', time: '2026-03-27', fwde_line: 0, nb_visits: 3,
  },
  {
    label: 'Chrome', time: '2026-03-28', fwde_line: 0, nb_visits: 4,
  },
  {
    label: 'Firefox', time: '2026-03-27', fwde_line: 1, nb_visits: null,
  },
  {
    label: 'Firefox', time: '2026-03-28', fwde_line: 1, nb_visits: 2,
  },
];

describe('ResultChart, the look of Matomo\'s graphs', () => {
  it('puts the legend below the chart, as Matomo 6 does', () => {
    const html = chart({ trendRows: twoLines }).html();
    expect(html.indexOf('fwde-chart__legend')).toBeGreaterThan(html.indexOf('</svg>'));
  });

  it('labels a month weekly', () => {
    const october = Array.from({ length: 31 }, (_, d) => ({
      label: 'Chrome', time: `2026-10-${String(d + 1).padStart(2, '0')}`, fwde_line: 0, nb_visits: d,
    }));
    const visible = chart({ trendRows: october }).findAll('.fwde-chart__category-tick')
      .filter((t) => (t.attributes('style') || '').indexOf('display: none') < 0)
      .map((t) => t.text());
    expect(visible).toEqual(['Thu, Oct 1', 'Thu, Oct 8', 'Thu, Oct 15', 'Thu, Oct 22', 'Thu, Oct 29']);
  });

  it('labels the hours of a single day as times, and names the day in the tooltip', async () => {
    const hours = Array.from({ length: 24 }, (_, h) => ({
      label: 'Chrome', time: String(h), fwde_line: 0, nb_visits: h,
    }));
    // Matomo's hour labels in English with a 24-hour clock are bare numbers ("HH")
    const hourLabels = Array.from({ length: 24 }, (_, h) => String(h).padStart(2, '0'));
    const wrapper = chart({ trendRows: hours, hourLabels, periodDays: ['2026-10-22'] });
    const visible = wrapper.findAll('.fwde-chart__category-tick')
      .filter((t) => (t.attributes('style') || '').indexOf('display: none') < 0)
      .map((t) => t.text());
    expect(visible[0]).toBe('00:00');
    expect(visible).toContain('12:00');
    expect(wrapper.find('.fwde-chart__axis-title').text())
      .toBe('FeinwerkDataExploration_TrendHours Thursday, October 22, 2026');
    await wrapper.findAll('.fwde-chart__hover')[14].trigger('mouseenter');
    expect(wrapper.find('h3.evolution-tooltip-header').text()).toBe('Thursday, October 22, 2026, 14:00');
  });

  it('has no axis caption for days', () => {
    expect(chart({ trendRows: twoLines }).find('.fwde-chart__axis-title').exists()).toBe(false);
  });

  it('has three value ticks and a faint line at each labelled day', () => {
    const wrapper = chart({ trendRows: twoLines });
    expect(wrapper.findAll('.fwde-chart__value-tick')).toHaveLength(3);
    expect(wrapper.findAll('.fwde-chart__day-grid')).toHaveLength(2);
  });

  it('writes the days as Matomo does', () => {
    const ticks = chart({ trendRows: twoLines }).findAll('.fwde-chart__category-tick').map((t) => t.text());
    expect(ticks).toEqual(['Fri, Mar 27', 'Sat, Mar 28']);
  });

  it('shows one tooltip with every line\'s value for the hovered day', async () => {
    const wrapper = chart({ trendRows: twoLines });
    expect(wrapper.find('.fwde-chart__tooltip').exists()).toBe(false);
    await wrapper.findAll('.fwde-chart__hover')[1].trigger('mouseenter');
    const tooltip = wrapper.find('.fwde-chart__tooltip');
    expect(tooltip.classes()).toContain('ui-tooltip');
    expect(tooltip.find('.ui-tooltip-content h3.evolution-tooltip-header').text()).toBe('Saturday, March 28, 2026');
    expect(tooltip.findAll('.fwde-chart__tooltip-row').map((r) => r.text())).toEqual(['4 Chrome', '2 Firefox']);
    await wrapper.find('svg').trigger('mouseleave');
    expect(wrapper.find('.fwde-chart__tooltip').exists()).toBe(false);
  });

  it('moves the tooltip with the arrow keys and closes it on leaving', async () => {
    const wrapper = chart({ trendRows: twoLines });
    const svg = wrapper.find('svg');
    expect(svg.attributes('tabindex')).toBe('0');
    await svg.trigger('focus');
    expect(wrapper.find('h3.evolution-tooltip-header').text()).toBe('Friday, March 27, 2026');
    await svg.trigger('keydown', { key: 'ArrowRight' });
    expect(wrapper.find('h3.evolution-tooltip-header').text()).toBe('Saturday, March 28, 2026');
    await svg.trigger('keydown', { key: 'ArrowRight' });
    expect(wrapper.find('h3.evolution-tooltip-header').text()).toBe('Saturday, March 28, 2026');
    await svg.trigger('blur');
    expect(wrapper.find('.fwde-chart__tooltip').exists()).toBe(false);
  });

  it('shows dots only for the hovered day and for single points', async () => {
    const lonely = [...twoLines, {
      label: 'Safari', time: '2026-03-27', fwde_line: 2, nb_visits: 5,
    }, {
      label: 'Safari', time: '2026-03-28', fwde_line: 2, nb_visits: null,
    }];
    const wrapper = chart({ trendRows: lonely });
    // Safari's only value (day 1) and Firefox's only value (day 2) have no line
    expect(wrapper.findAll('circle.fwde-chart__point')).toHaveLength(2);
    await wrapper.findAll('.fwde-chart__hover')[1].trigger('mouseenter');
    expect(wrapper.findAll('circle.fwde-chart__point')).toHaveLength(3);
  });

  it('colours comparison lines as Matomo\'s comparison does', () => {
    const compared = twoLines.map((row) => ({ ...row, fwde_kind: row.fwde_line === 0 ? 'own' : 'segment' }));
    const series = chart({ trendRows: compared }).findAll('g.fwde-chart__series');
    expect(series.map((g) => g.attributes('data-name'))).toEqual(['series0', 'series1']);
    expect(series[1].classes()).toContain('comparison-series-color');
    const plain = chart({ trendRows: twoLines }).findAll('g.fwde-chart__series');
    expect(plain[1].classes()).not.toContain('comparison-series-color');
  });
});

describe('ResultChart, trend', () => {
  it('shows neither a note nor an empty chart while the trend loads', () => {
    const wrapper = chart({ trendRows: null, trendLoading: true });
    expect(wrapper.find('.fwde-chart__note').exists()).toBe(false);
    expect(wrapper.find('svg').exists()).toBe(false);
  });

  it('shows no empty chart when the trend has no lines (an error)', () => {
    const wrapper = chart({ trendRows: null, trendLoading: false });
    expect(wrapper.find('svg').exists()).toBe(false);
    expect(wrapper.find('.fwde-chart__note').exists()).toBe(false);
  });

  it('draws the lines it has', () => {
    const wrapper = chart({
      trendRows: [
        {
          label: 'Browser 0', time: '2026-03-27', fwde_line: 0, nb_visits: 3,
        },
        {
          label: 'Browser 0', time: '2026-03-28', fwde_line: 0, nb_visits: 4,
        },
      ],
      trendLoading: false,
    });
    expect(wrapper.findAll('path.fwde-chart__line')).toHaveLength(1);
    expect(wrapper.find('.fwde-chart__note').text()).toContain('FeinwerkDataExploration_TrendFirstRows 1 12');
  });
});

describe('ResultChart, grouped bars while comparing', () => {
  const barState = parseState({ fwdeDims: 'browser', fwdeMetrics: 'nb_visits' }, CATALOG, LIMITS);
  const column = (title: string, kind: ComparisonColumn['kind']): ComparisonColumn => ({
    segment: '', period: 'range', date: '2026-03-27,2026-03-30', title, kind,
  });
  const columns = [column('All visits', 'own'), column('Mobile visitors', 'segment'), column('Previous', 'period')];
  const compared: ResultRow[] = [
    {
      label: 'Chrome', nb_visits: 10, nb_visits__c1: 4, nb_visits__c2: 8, nb_visits__c2__change: 25,
    },
    {
      label: 'Firefox', nb_visits: 20, nb_visits__c1: 6, nb_visits__c2: 16, nb_visits__c2__change: 25,
    },
    {
      label: 'Safari', nb_visits: 5, nb_visits__c1: 2, nb_visits__c2: 4, nb_visits__c2__change: 25,
    },
  ];
  const grouped = (compareColumns: ComparisonColumn[]) => mount(ResultChart, {
    props: {
      rows: compared,
      state: barState,
      catalog: CATALOG,
      metric: 'nb_visits',
      actionScope: false,
      currencySymbol: '$',
      chartType: 'bar',
      compareColumns,
    },
  });

  it('one bar per comparison column and row, in Matomo\'s comparison colours', () => {
    const bars = grouped(columns).findAll('rect.fwde-chart__bar');
    expect(bars).toHaveLength(9);
    expect(bars.slice(0, 3).map((b) => b.attributes('data-name'))).toEqual(['series0', 'series1', 'series2']);
    expect(bars[1].classes()).toContain('comparison-series-color');
  });

  it('names the columns in a legend below the chart, bars without dashes', () => {
    const chart = grouped(columns);
    expect(chart.findAll('.fwde-chart__legend-item').map((li) => li.text()))
      .toEqual(['All visits', 'Mobile visitors', 'Previous']);
    expect(chart.findAll('.fwde-chart__swatch--dashed')).toHaveLength(0);
  });

  it('names the column, the row, the value and a period\'s change in each bar\'s tooltip', () => {
    const titles = grouped(columns).findAll('g.fwde-chart__group title').map((t) => t.text());
    expect(titles[2]).toContain('Previous');
    expect(titles[2]).toContain('Firefox');
    expect(titles[2]).toContain('16');
    expect(titles[2]).toContain('+25 %');
    expect(titles[1]).not.toContain('%');
  });

  it('never overlaps the next row, also with four and ten columns', () => {
    // every column has a value, so each row draws one bar per column (the last bar of row 0 is bars[n - 1])
    const full: ResultRow[] = compared.map((row) => {
      const values: ResultRow = { ...row };
      for (let c = 1; c < 10; c += 1) {
        values[`nb_visits__c${c}`] = Number(row.nb_visits) - c / 10;
      }
      return values;
    });
    [4, 10].forEach((count) => {
      const many = Array.from({ length: count }, (_, c) => column(`Column ${c}`, c ? 'segment' : 'own'));
      const bars = mount(ResultChart, {
        props: {
          rows: full,
          state: barState,
          catalog: CATALOG,
          metric: 'nb_visits',
          actionScope: false,
          currencySymbol: '$',
          chartType: 'bar',
          compareColumns: many,
        },
      }).findAll('rect.fwde-chart__bar');
      expect(bars).toHaveLength(3 * count);
      const lastOfFirstRow = bars[count - 1];
      const firstOfSecondRow = bars[count];
      expect(Number(firstOfSecondRow.attributes('y')))
        .toBeGreaterThanOrEqual(Number(lastOfFirstRow.attributes('y')) + Number(lastOfFirstRow.attributes('height')));
    });
  });

  it('labels the trend\'s comparison lines like the bars: segment and period', () => {
    const withTitles = (title: string, segment: string, period: string, kind: ComparisonColumn['kind']) => ({
      ...column(title, kind), segmentTitle: segment, periodTitle: period,
    });
    const trendColumns = [
      withTitles('All visits, Nov 2026', 'All visits', 'Nov 2026', 'own'),
      withTitles('Mobile visitors', 'Mobile visitors', 'Nov 2026', 'segment'),
      withTitles('Oct 2026', 'All visits', 'Oct 2026', 'period'),
    ];
    const lines = [0, 1, 2].flatMap((line) => ['2026-03-27', '2026-03-28'].map((time) => ({
      label: `server title ${line}`, time, fwde_line: line, fwde_kind: line ? 'segment' : 'own', nb_visits: 3 + line,
    })));
    const legend = mount(ResultChart, {
      props: {
        rows: compared,
        state: barState,
        catalog: CATALOG,
        metric: 'nb_visits',
        actionScope: false,
        currencySymbol: '$',
        chartType: 'trend',
        trendRows: lines,
        compareColumns: trendColumns,
      },
    }).findAll('.fwde-chart__legend-item').map((li) => li.text());
    expect(legend).toEqual(['All visits, Nov 2026', 'Mobile visitors, Nov 2026', 'All visits, Oct 2026']);
  });

  it('names and colours a trend line by its column, also when a column before it failed', () => {
    const withTitles = (title: string, segment: string, kind: ComparisonColumn['kind']) => ({
      ...column(title, kind), segmentTitle: segment, periodTitle: '',
    });
    const fourColumns = [
      withTitles('All visits', 'All visits', 'own'),
      withTitles('Mobile visitors', 'Mobile visitors', 'segment'),
      withTitles('Desktop visitors', 'Desktop visitors', 'segment'),
      withTitles('Tablet visitors', 'Tablet visitors', 'segment'),
    ];
    // column 1 failed (busy or the time limit): its line is missing, lines 0, 2 and 3 arrive
    const lines = [0, 2, 3].flatMap((line) => ['2026-03-27', '2026-03-28'].map((time) => ({
      label: `server title ${line}`, time, fwde_line: line, fwde_kind: line ? 'segment' : 'own', nb_visits: 3 + line,
    })));
    const chart = mount(ResultChart, {
      props: {
        rows: compared,
        state: barState,
        catalog: CATALOG,
        metric: 'nb_visits',
        actionScope: false,
        currencySymbol: '$',
        chartType: 'trend',
        trendRows: lines,
        compareColumns: fourColumns,
      },
    });
    expect(chart.findAll('.fwde-chart__legend-item').map((li) => li.text()))
      .toEqual(['All visits', 'Desktop visitors', 'Tablet visitors']);
    expect(chart.findAll('.fwde-chart__legend-item .fwde-chart__swatch').map((sw) => sw.attributes('data-name')))
      .toEqual(['series0', 'series2', 'series3']);
  });

  it('draws single bars of the own view without a comparison', () => {
    const plain = grouped([columns[0]]);
    expect(plain.findAll('rect.fwde-chart__bar')).toHaveLength(3);
    expect(plain.find('.fwde-chart__legend').exists()).toBe(false);
  });
});

describe('donut chart (design 2026-11-20)', () => {
  const donutState = parseState({ fwdeDims: 'browser', fwdeMetrics: 'nb_visits', fwdeChartType: 'donut' }, CATALOG, LIMITS);

  it('names the metric under the total in the middle (UI review 2026-11-23)', () => {
    const wrapper = chart({ state: donutState, chartType: 'donut' });
    expect(wrapper.find('.fwde-chart__donut-metric').text()).toBe('Visits');
  });

  it('draws one arc per slice, the total in the middle and a legend with value and share', () => {
    const wrapper = chart({ state: donutState, chartType: 'donut' });
    expect(wrapper.findAll('path.fwde-chart__slice')).toHaveLength(10);
    expect(wrapper.find('.fwde-chart__donut-total').text()).toBe('78');
    const legend = wrapper.findAll('.fwde-chart__donut-legend li');
    expect(legend).toHaveLength(10);
    expect(legend[0].text()).toContain('Browser 0');
    expect(legend[0].text()).toContain('12');
    expect(legend[9].text()).toContain('General_Others');
    expect(wrapper.find('.fwde-chart__bar').exists()).toBe(false);
  });

  it('adds the exact rest of a cut table, and says while it is missing', () => {
    const loaded = chart({ state: donutState, chartType: 'donut', othersValue: 22 });
    expect(loaded.find('.fwde-chart__donut-total').text()).toBe('100');
    const missing = chart({ state: donutState, chartType: 'donut', othersNote: 'FeinwerkDataExploration_OthersFailed' });
    expect(missing.find('.fwde-chart__note').text()).toBe('FeinwerkDataExploration_OthersFailed');
  });

  it('offers the donut in the type switch, disabled with its reason', () => {
    const bars = chart({ state: donutState, chartType: 'bar', donutReason: 'FeinwerkDataExploration_DonutNeedsCount' });
    const button = bars.findAll('.fwde-segmented__option').find((b) => b.text() === 'FeinwerkDataExploration_ChartTypeDonut');
    expect(button?.attributes('disabled')).toBeDefined();
    expect(button?.attributes('title')).toBe('FeinwerkDataExploration_DonutNeedsCount');
    const allowed = chart({ state: donutState, chartType: 'bar', donutReason: null });
    const enabled = allowed.findAll('.fwde-segmented__option').find((b) => b.text() === 'FeinwerkDataExploration_ChartTypeDonut');
    expect(enabled?.attributes('disabled')).toBeUndefined();
    enabled?.trigger('click');
    expect(allowed.emitted('chart-type')?.[0]).toEqual(['donut']);
  });
});

