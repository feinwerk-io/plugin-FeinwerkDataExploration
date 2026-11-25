/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import KpiCardView from '../src/KpiCardView.vue';
import { KpiCardData } from '../src/kpi';

// Matomo 5 runs the specs with Jest; CoreHome is Matomo's UMD global, so the mock is virtual
jest.mock('CoreHome', () => ({
  translate: (key: string, ...params: string[]) => [key, ...params].join(' '),
  Matomo: { language: 'en' },
  NumberFormatter: {
    formatNumber: (v: number) => String(v),
    formatPercent: (v: number) => `${v} %`,
    formatCurrency: (v: number) => `$${v}`,
  },
  AjaxHelper: { post: () => Promise.resolve({}) },
}), { virtual: true });

const line = Array.from({ length: 30 }, (_, i) => ({
  day: `2026-11-${String(i + 1).padStart(2, '0')}`,
  value: i === 10 ? null : i,
  baseline: [3, 10, 17, 24].includes(i),
}));
const card: KpiCardData = {
  title: 'Newsletter visits',
  metric: 'nb_visits',
  metricName: 'Visits',
  unit: 'count',
  value: 1284,
  partial: true,
  until: '09:30',
  firstDay: '2026-11-30',
  lastDay: '2026-11-30',
  changeAs: 'percent',
  comparison: {
    kind: 'average4',
    period: 'day',
    baseline: 1146,
    used: 3,
    wanted: 4,
    spans: [],
    change: 0.12,
    absolute: 138,
  },
  comparisonNote: null,
  line,
  lineNote: null,
  lowerIsBetter: false,
};

function view(extra: Partial<KpiCardData> = {}) {
  return mount(KpiCardView, {
    props: {
      card: { ...card, ...extra },
      idSite: 1,
      exploreUrl: 'index.php?x',
      feedback: { enabled: true, supportUrl: '', version: '6.1.0' },
    },
  });
}

describe('KPI card (2026-11-24)', () => {
  it('shows the title, the number so far, the change and its baseline', () => {
    const wrapper = view();
    expect(wrapper.find('.fwde-kpi__title').text()).toBe('Newsletter visits');
    expect(wrapper.find('.fwde-kpi__value').text()).toBe('1284');
    expect(wrapper.find('.fwde-kpi__partial').text()).toBe('FeinwerkDataExploration_KpiSoFar 09:30');
    expect(wrapper.find('.fwde-kpi__change').text()).toBe('▲ 12 %');
    expect(wrapper.find('.fwde-kpi__change').classes()).toContain('fwde-kpi__change--good');
    expect(wrapper.find('.fwde-kpi__baseline').text())
      .toBe('FeinwerkDataExploration_KpiVsAverageWeekdays Intl_Day_Long_StandAlone_1 3');
  });

  it('draws the line with a gap and marks the comparison days that have a value', () => {
    const wrapper = view();
    const d = wrapper.find('path.fwde-kpi__line').attributes('d') || '';
    expect((d.match(/M/g) || []).length).toBe(2);
    expect(wrapper.findAll('.fwde-kpi__dot--day')).toHaveLength(3);
    expect(d).not.toContain('NaN');
  });

  it('says why a part is missing', () => {
    const wrapper = view({
      comparison: null, comparisonNote: 'No earlier data.', line: null, lineNote: 'Too long.',
    });
    expect(wrapper.find('.fwde-kpi__change').exists()).toBe(false);
    expect(wrapper.text()).toContain('No earlier data.');
    expect(wrapper.text()).toContain('Too long.');
    expect(wrapper.find('svg.fwde-kpi__spark').exists()).toBe(false);
  });

  it('has the footer without a support link', () => {
    const wrapper = view();
    expect(wrapper.find('a.fwde-kpi__open').attributes('href')).toBe('index.php?x');
    expect(wrapper.find('.fwde-feedback__support').exists()).toBe(false);
  });

  // UI review 2026-11-24 (F1): in an embed the server sends no link and no feedback
  it('shows no footer in an embed', () => {
    const wrapper = mount(KpiCardView, {
      props: {
        card,
        idSite: 1,
        exploreUrl: '',
        feedback: { enabled: false, supportUrl: '', version: '6.1.0' },
      },
    });
    expect(wrapper.find('.fwde-kpi__value').text()).toBe('1284');
    expect(wrapper.find('.fwde-kpi__open').exists()).toBe(false);
    expect(wrapper.find('.fwde-kpi__footer').exists()).toBe(false);
  });

  // UI review 2026-11-24 (F6): the card says which metric it counts, unless its name already does
  it('names the metric under a title that does not', () => {
    expect(view().find('.fwde-kpi__metric').text()).toBe('Visits');
    expect(view({ title: 'visits' }).find('.fwde-kpi__metric').exists()).toBe(false);
    expect(view({ title: ' Visits ' }).find('.fwde-kpi__metric').exists()).toBe(false);
  });

  // Peter, 2026-11-25: "Auch das Dashboard Widget sieht noch nicht so gut aus": the dashboard's widget
  // header names the card, so the card names its metric only (UI review D1)
  it('on a dashboard shows the metric under the widget header, not the name again', async () => {
    const widget = document.createElement('div');
    widget.className = 'widget';
    widget.innerHTML = '<div class="widgetTop">Newsletter visits</div><div class="widgetContent"></div>';
    document.body.appendChild(widget);
    const props = {
      idSite: 1, exploreUrl: 'index.php?x', feedback: { enabled: false, supportUrl: '', version: '' },
    };
    const dashboard = mount(KpiCardView, {
      props: { ...props, card },
      attachTo: widget.querySelector('.widgetContent') as HTMLElement,
    });
    await nextTick();
    expect(dashboard.find('.fwde-kpi__title').exists()).toBe(false);
    expect(dashboard.find('.fwde-kpi__metric').text()).toBe('Visits');
    dashboard.unmount();
    // a card named like its metric needs no line above its number
    const named = mount(KpiCardView, {
      props: { ...props, card: { ...card, title: 'Visits' } },
      attachTo: widget.querySelector('.widgetContent') as HTMLElement,
    });
    await nextTick();
    expect(named.find('.fwde-kpi__title').exists()).toBe(false);
    expect(named.find('.fwde-kpi__metric').exists()).toBe(false);
    named.unmount();
    document.body.removeChild(widget);
  });

  it('marks the last value and the compared days on the line, with dates in words', () => {
    const wrapper = view();
    const end = wrapper.findAll('.fwde-kpi__dot--end');
    expect(end).toHaveLength(1);
    expect(end[0].attributes('style')).toContain('left: 99%');
    const days = wrapper.findAll('.fwde-kpi__dot--day');
    // the fourth day (value 3) at its place on the line, its date in words, never as 2026-11-04
    expect(days[0].attributes('style')).toMatch(/left: 11\.1\d*%/);
    expect(days[0].attributes('title')).toMatch(/Nov.*4.*: 3$/);
    expect(days[0].attributes('title')).not.toContain('2026-11-04');
    expect(wrapper.find('.fwde-kpi__baseline').attributes('title')).not.toMatch(/2026-/);
    // also the line's label for screen readers (UI review 2026-11-25, W4)
    expect(wrapper.find('.fwde-kpi__spark svg').attributes('aria-label')).toMatch(/^Visits: .*Nov.*1.*Nov.*30/);
    expect(wrapper.find('.fwde-kpi__spark svg').attributes('aria-label')).not.toMatch(/2026-/);
  });
});
