/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import ResultTable from '../src/ResultTable.vue';
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

const state = parseState({ fwdeDims: 'browser,pageUrl', fwdeMetrics: 'nb_visits,bounce_rate' }, CATALOG, LIMITS);
const rows = [
  {
    label: 'Mobile Safari', secondary_label: 'outdoor-shop-demo.example/wandern/', nb_visits: 80, bounce_rate: 0.4,
  },
  {
    label: 'Chrome', secondary_label: 'outdoor-shop-demo.example/', nb_visits: 20, bounce_rate: 0.6,
  },
];

function table() {
  return mount(ResultTable, {
    props: {
      rows, state, catalog: CATALOG, actionScope: false, currencySymbol: '$',
    },
  });
}

describe('result table', () => {
  it('keeps both dimensions together on the left, a line after the last one', () => {
    const head = table().findAll('thead th');
    expect(head.map((th) => th.classes().includes('fwde-table__dimension'))).toEqual([true, true, false, false, false]);
    expect(head[1].classes()).toContain('fwde-table__dimension-last');
    expect(table().findAll('tbody tr')[0].findAll('td')[1].classes()).toContain('fwde-table__dimension-last');
  });

  it('shows a long value in one line, shortened in the middle, the full text on hover', () => {
    const cell = table().findAll('tbody tr')[0].findAll('td')[1];
    expect(cell.attributes('title')).toBe('outdoor-shop-demo.example/wandern/');
    expect(cell.find('.fwde-ellipsis__head').text() + cell.find('.fwde-ellipsis__tail').text())
      .toBe('outdoor-shop-demo.example/wandern/');
    expect(cell.find('.fwde-ellipsis__tail').text().length).toBeGreaterThan(0);
  });

  it('keeps both dimensions together: a first dimension of short values is only as wide as they are', () => {
    // rows: browsers (short) by page URLs (long): the free width goes behind the second dimension, not between them
    const head = table().findAll('thead th');
    expect(head[0].classes()).toContain('fwde-table__dimension--fit');
    expect(head[1].classes()).not.toContain('fwde-table__dimension--fit');
    expect(table().findAll('tbody tr')[0].findAll('td')[0].classes()).toContain('fwde-table__dimension--fit');
    // a long first dimension (page URLs) keeps the free width; one dimension alone too
    const swapped = parseState({ fwdeDims: 'pageUrl,browser', fwdeMetrics: 'nb_visits,bounce_rate' }, CATALOG, LIMITS);
    const longFirst = mount(ResultTable, {
      props: {
        rows: rows.map((row) => ({ ...row, label: row.secondary_label, secondary_label: row.label })),
        state: swapped,
        catalog: CATALOG,
        actionScope: false,
        currencySymbol: '$',
      },
    });
    expect(longFirst.find('thead th').classes()).not.toContain('fwde-table__dimension--fit');
  });

  it('never shortens a short value; only a long one may shrink when the table runs out of room', () => {
    const [first] = table().findAll('tbody tr').map((tr) => tr.findAll('td'));
    expect(first[0].find('.fwde-ellipsis').classes()).not.toContain('fwde-ellipsis--long');
    expect(first[1].find('.fwde-ellipsis').classes()).toContain('fwde-ellipsis--long');
  });

  it('never shows a total row (left from a comparison until the new result arrives)', () => {
    const total = { label: 'Total', fwde_total: 1, nb_visits: 100, bounce_rate: 0.5 };
    const wrapper = mount(ResultTable, {
      props: {
        rows: [total, ...rows], state, catalog: CATALOG, actionScope: false, currencySymbol: '$',
      },
    });
    expect(wrapper.findAll('tbody tr')).toHaveLength(2);
    expect(wrapper.text()).not.toContain('Total');
    expect(wrapper.findAll('tbody tr')[0].findAll('td')[2].attributes('style')).toContain('--fwde-bar: 100%');
  });

  it('draws a bar behind counts, against the column\'s largest value, none for rates', () => {
    const [first, second] = table().findAll('tbody tr').map((tr) => tr.findAll('td'));
    expect(first[2].classes()).toContain('fwde-cellbar');
    expect(first[2].attributes('style')).toContain('--fwde-bar: 100%');
    expect(second[2].attributes('style')).toContain('--fwde-bar: 25%');
    expect(first[3].classes()).not.toContain('fwde-cellbar');
  });
});

describe('overcount hint', () => {
  const mountWith = (dims: string, metrics: string) => mount(ResultTable, {
    props: {
      rows: [{ label: 'a', nb_visits: 1, nb_pageviews: 2 }],
      state: parseState({ fwdeDims: dims, fwdeMetrics: metrics }, CATALOG, LIMITS),
      catalog: CATALOG,
      actionScope: true,
      currencySymbol: '$',
    },
  });

  it('says why the rows can add up to more than the total', () => {
    const hint = mountWith('pageUrl', 'nb_visits').find('.fwde-table__overcount');
    expect(hint.text()).toBe('FeinwerkDataExploration_OvercountHintVisit');
  });

  it('stays away when every unit falls into one row', () => {
    expect(mountWith('pageUrl', 'nb_pageviews').find('.fwde-table__overcount').exists()).toBe(false);
  });
});

describe('Others row (exact rest, on demand)', () => {
  const cutState = parseState({
    fwdeDims: 'browser', fwdeMetrics: 'nb_visits,bounce_rate', fwdeFilters: 'nb_visits~gt~1',
  }, CATALOG, LIMITS);
  const twoRows = [
    { label: 'Chrome', nb_visits: 80, bounce_rate: 0.4 },
    { label: 'Firefox', nb_visits: 20, bounce_rate: 0.6 },
  ];
  const mountOthers = (props: Record<string, unknown>) => mount(ResultTable, {
    props: {
      rows: twoRows, state: cutState, catalog: CATALOG, actionScope: false, currencySymbol: '$', ...props,
    },
  });
  const othersRow = (wrapper: ReturnType<typeof mountOthers>) => wrapper.find('tr.fwde-table__others');

  it('"Calculate" is the plugin\'s secondary button (readable on the row\'s tint, UI review 2026-11-23)', () => {
    const row = othersRow(mountOthers({ cut: true, othersState: 'idle', others: null }));
    expect(row.find('button.fwde-table__others-calculate').classes()).toContain('fwde-button');
  });

  it('the row count and the limit hint are two lines, not one run-on sentence', () => {
    const wrapper = mountOthers({
      cut: true, othersState: 'idle', others: null, state: { ...cutState, limit: 2 },
    });
    expect(wrapper.find('.fwde-table__footer').text()).toBe('FeinwerkDataExploration_RowCount 2');
    expect(wrapper.find('p.fwde-table__limit').text()).toBe('FeinwerkDataExploration_LimitReached');
  });

  it('offers "Calculate" below a cut table that adds up', () => {
    const wrapper = mountOthers({ cut: true, othersState: 'idle', others: null });
    const row = othersRow(wrapper);
    expect(row.exists()).toBe(true);
    expect(wrapper.findAll('tbody tr').at(-1)?.classes()).toContain('fwde-table__others');
    expect(row.find('.fwde-table__dimension').text()).toBe('General_Others');
    const button = row.find('button.fwde-table__others-calculate');
    expect(button.text()).toBe('FeinwerkDataExploration_OthersCalculate');
    expect(row.find('.fwde-table__menu').text()).toBe('');
    button.trigger('click');
    expect(wrapper.emitted('others-calculate')).toHaveLength(1);
  });

  it('shows no Others row when the server found no rest', () => {
    expect(othersRow(mountOthers({ cut: true, othersState: 'done', others: null })).exists()).toBe(false);
  });

  it('leaves a rate or average of an empty rest empty instead of showing 0', () => {
    const row = othersRow(mountOthers({ cut: true, othersState: 'done', others: { nb_visits: 0, bounce_rate: null } }));
    expect(row.findAll('td.fwde-table__metric').map((td) => td.text())).toEqual(['0', '']);
  });

  it('shows the values once calculated, without bars', () => {
    const row = othersRow(mountOthers({ cut: true, othersState: 'done', others: { nb_visits: 1234, bounce_rate: 0.25 } }));
    const cells = row.findAll('td.fwde-table__metric');
    expect(cells.map((td) => td.text())).toEqual(['1234', '25 %']);
    expect(row.find('.fwde-cellbar').exists()).toBe(false);
    expect(row.find('button').exists()).toBe(false);
  });

  it('says when the rest could not be calculated, and offers it again', () => {
    const row = othersRow(mountOthers({ cut: true, othersState: 'failed', others: null }));
    expect(row.text()).toContain('FeinwerkDataExploration_OthersFailed');
    expect(row.find('button.fwde-table__others-calculate').exists()).toBe(true);
  });

  it('is missing when nothing was cut or the rows overcount', () => {
    expect(othersRow(mountOthers({ cut: false, othersState: 'idle', others: null })).exists()).toBe(false);
    const overcounting = parseState({
      fwdeDims: 'pageUrl', fwdeMetrics: 'nb_visits', fwdeFilters: 'nb_visits~gt~1',
    }, CATALOG, LIMITS);
    expect(othersRow(mountOthers({
      state: overcounting, cut: true, othersState: 'idle', others: null,
    })).exists()).toBe(false);
  });
});
