/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import ResultChart from '../src/ResultChart.vue';
import { parseState } from '../src/state';
import { CATALOG, LIMITS } from './fixtures';

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
