/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import ResultTable from '../src/ResultTable.vue';
import { parseState } from '../src/state';
import { CATALOG, LIMITS } from './fixtures';
import { ResultRow } from '../src/api';

// Matomo 5 runs the specs with Jest; CoreHome is Matomo's UMD global, so the mock is virtual
jest.mock('CoreHome', () => ({
  translate: (key: string, ...params: string[]) => [key, ...params].join(' '),
  Matomo: { language: 'en' },
  NumberFormatter: {
    formatNumber: (v: number) => String(v),
    formatPercent: (v: number) => `${v} %`,
    formatCurrency: (v: number) => `$${v}`,
  },
}), { virtual: true });

const state = parseState({ fwdeDims: 'browser,pageUrl', fwdeMetrics: 'nb_visits,bounce_rate' }, CATALOG, LIMITS);
const rows: ResultRow[] = [
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
