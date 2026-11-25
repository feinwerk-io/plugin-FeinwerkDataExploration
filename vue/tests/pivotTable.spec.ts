/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import PivotTable from '../src/PivotTable.vue';
import { parseState } from '../src/state';
import { CATALOG, LIMITS } from './fixtures';

vi.mock('CoreHome', () => ({
  translate: (key: string, ...params: string[]) => [key, ...params].join(' '),
  Matomo: { language: 'en' },
  NumberFormatter: {
    formatNumber: (v: number) => String(v),
    formatPercent: (v: number) => `${v} %`,
    formatCurrency: (v: number) => `$${v}`,
  },
}));

const state = parseState({
  fwdeDims: 'browser', fwdeMetrics: 'nb_visits,bounce_rate', fwdePivot: 'country', fwdePivotLimit: '3',
}, CATALOG, LIMITS);
const rows = [
  {
    label: 'Total',
    fwde_total: 1,
    fwde_pivot: {
      dimension: 'country',
      values: [
        { label: 'Germany', value: 'de', segment: 'countryCode==de' },
        { label: 'France', value: 'fr', segment: 'countryCode==fr' },
      ],
      others: true,
    },
    nb_visits__p1: 12, bounce_rate__p1: 0.5, nb_visits__p2: 6, bounce_rate__p2: 0.25, nb_visits__p_others: 2, nb_visits: 20, bounce_rate: 0.4,
  },
  {
    label: 'Firefox', nb_visits__p1: 4, bounce_rate__p1: 0.5, nb_visits__p2: 0, bounce_rate__p2: null, nb_visits__p_others: 1, nb_visits: 5, bounce_rate: 0.4,
  },
] as unknown as Record<string, string|number>[];

function table() {
  return mount(PivotTable, {
    props: {
      rows, state, catalog: CATALOG, actionScope: false, currencySymbol: '$',
    },
  });
}

describe('pivot table (rows and pivot, 2026-11-24)', () => {
  it('heads the metrics with the pivot values, Others and Total', () => {
    const wrapper = table();
    const groups = wrapper.findAll('thead tr:first-child th.fwde-pivot__group').map((th) => th.text());
    expect(groups).toEqual(['Germany', 'France', 'General_Others', 'General_Total']);
    const spans = wrapper.findAll('thead tr:first-child th.fwde-pivot__group').map((th) => th.attributes('colspan'));
    expect(spans).toEqual(['2', '2', '1', '2']);
    expect(wrapper.findAll('thead tr:nth-child(2) th')).toHaveLength(7);
  });

  it('shows the total row first and a missing rate as an empty cell', () => {
    const wrapper = table();
    const bodyRows = wrapper.findAll('tbody tr');
    expect(bodyRows[0].classes()).toContain('fwde-compare__total');
    const firefox = bodyRows[1].findAll('td').map((td) => td.text());
    expect(firefox.slice(0, 8)).toEqual(['Firefox', '4', '50 %', '0', '', '1', '5', '40 %']);
  });

  it('sorts by a metric through the Total group only', async () => {
    const wrapper = table();
    const sortButtons = wrapper.findAll('thead tr:nth-child(2) button.fwde-table__sort');
    expect(sortButtons).toHaveLength(2);
    await sortButtons[0].trigger('click');
    expect(wrapper.emitted('sort')?.[0]).toEqual(['nb_visits']);
  });
});

// final review: the hints of the plain table, and one for columns whose counts can pass the total
describe('pivot table hints', () => {
  const mountWith = (hash: Record<string, string>) => mount(PivotTable, {
    props: {
      rows,
      state: parseState({ fwdeMetrics: 'nb_visits', fwdePivotLimit: '3', ...hash }, CATALOG, LIMITS),
      catalog: CATALOG,
      actionScope: true,
      currencySymbol: '$',
    },
  });

  it('says why the columns can add up to more than the total under a page pivot', () => {
    const wrapper = mountWith({ fwdeDims: 'browser', fwdePivot: 'pageUrl' });
    expect(wrapper.find('.fwde-pivot__overcount').text()).toBe('FeinwerkDataExploration_PivotOvercountHint');
    expect(mountWith({ fwdeDims: 'browser', fwdePivot: 'country' }).find('.fwde-pivot__overcount').exists()).toBe(false);
  });

  it('keeps the rows\' hint of the plain table', () => {
    const wrapper = mountWith({ fwdeDims: 'pageUrl', fwdePivot: 'country' });
    expect(wrapper.find('.fwde-table__overcount').text()).toBe('FeinwerkDataExploration_OvercountHintVisit');
  });
});
