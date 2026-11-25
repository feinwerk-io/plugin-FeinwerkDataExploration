/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import SavedReportFooter from '../src/SavedReportFooter.vue';
import KpiCardView from '../src/KpiCardView.vue';
import { embeddedPage } from '../src/embed';

vi.mock('CoreHome', () => ({
  translate: (key: string) => key,
  AjaxHelper: { post: vi.fn() },
  NumberFormatter: {
    formatNumber: (v: number) => String(v),
    formatPercent: (v: number) => `${v} %`,
    formatCurrency: (v: number) => `$${v}`,
  },
}));

describe('saved report footer (saved reports, 2026-11-24)', () => {
  it('links to the exploration and asks for feedback on the report', () => {
    const url = 'index.php?module=CoreHome&action=index&idSite=1#?idSite=1&fwdeSaved=7';
    const wrapper = mount(SavedReportFooter, {
      props: {
        idSite: 1,
        exploreUrl: url,
        feedback: { enabled: true, supportUrl: 'https://example.com/support', version: '6.1.0' },
      },
    });
    const link = wrapper.find('a.fwde-saved-footer__open');
    expect(link.attributes('href')).toBe(url);
    expect(link.text()).toBe('FeinwerkDataExploration_OpenInExploration');
    const bar = wrapper.findComponent({ name: 'FeedbackBar' });
    expect(bar.props('topic')).toBe('saved_report');
    expect(bar.props('question')).toBe('FeinwerkDataExploration_FeedbackQuestion_saved_report');
  });
});

// Release review 2026-11-25: Matomo loads a container's widgets with module=FeinwerkDataExploration,
// so in a Widgetize embed only the page's own address tells the footer to stay away
describe('footers in an embed', () => {
  const props = {
    idSite: 1,
    exploreUrl: 'index.php?module=CoreHome&action=index#?fwdeSaved=7',
    feedback: { enabled: true, supportUrl: 'https://example.com/support', version: '6.1.0' },
  };
  const at = (search: string) => window.history.replaceState(null, '', `/index.php${search}`);

  afterEach(() => at('?module=CoreHome&action=index'));

  it('knows an embed by the page address', () => {
    expect(embeddedPage('?module=Widgetize&action=iframe&containerId=fwdeSavedContainer7')).toBe(true);
    expect(embeddedPage('?module=CoreHome&action=index&disableLink=1')).toBe(true);
    expect(embeddedPage('?module=CoreHome&action=index&idSite=1')).toBe(false);
    expect(embeddedPage('?module=CoreHome&disableLink=0')).toBe(false);
    expect(embeddedPage('')).toBe(false);
  });

  it('shows no saved report footer in a Widgetize page', () => {
    at('?module=Widgetize&action=iframe&moduleToWidgetize=CoreHome&actionToWidgetize=renderWidgetContainer');
    const wrapper = mount(SavedReportFooter, { props });
    expect(wrapper.find('.fwde-saved-footer').exists()).toBe(false);
    expect(wrapper.findComponent({ name: 'FeedbackBar' }).exists()).toBe(false);
  });

  it('shows the footers on Matomo\'s own pages', () => {
    at('?module=CoreHome&action=index&idSite=1');
    expect(mount(SavedReportFooter, { props }).find('a.fwde-saved-footer__open').exists()).toBe(true);
  });

  it('shows no card footer in an embedded dashboard', () => {
    at('?module=Widgetize&action=iframe&moduleToWidgetize=Dashboard&actionToWidgetize=index');
    const card = {
      title: 'Visits', metric: 'nb_visits', metricName: 'Visits', unit: 'count', value: 3, partial: false,
      until: null, firstDay: '2026-11-24', lastDay: '2026-11-24', changeAs: 'percent', comparison: null,
      comparisonNote: null, line: null, lineNote: null, lowerIsBetter: false,
    };
    const wrapper = mount(KpiCardView, { props: { ...props, card } });
    expect(wrapper.find('.fwde-kpi__value').text()).toBe('3');
    expect(wrapper.find('.fwde-kpi__footer').exists()).toBe(false);
  });
});
