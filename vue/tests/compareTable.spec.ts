/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import CompareTable from '../src/CompareTable.vue';
import { parseState } from '../src/state';
import { CATALOG, LIMITS } from './fixtures';
import { ComparisonColumn } from '../src/types';

// Matomo 5 runs the specs with Jest; CoreHome is Matomo's UMD global, so the mock is virtual
jest.mock('CoreHome', () => ({
  translate: (key: string, ...params: string[]) => [key, ...params].join(' '),
  Matomo: { language: 'en' },
  ComparisonsStoreInstance: { getSeriesColorName: (seriesIndex: number) => `series${seriesIndex}` },
  NumberFormatter: {
    formatNumber: (v: number) => String(v),
    formatPercent: (v: number) => `${v} %`,
    formatCurrency: (v: number) => `$${v}`,
  },
}), { virtual: true });

const state = parseState({ fwdeDims: 'browser', fwdeMetrics: 'nb_visits,bounce_rate' }, CATALOG, LIMITS);
const columns: ComparisonColumn[] = [
  {
    segment: '', period: 'day', date: '2026-11-13', title: 'All visits', kind: 'own',
  },
  {
    segment: 'browserCode==FF', period: 'day', date: '2026-11-13', title: 'Firefox', kind: 'segment',
  },
  {
    segment: '', period: 'day', date: '2026-11-12', title: 'Nov 12', kind: 'period',
  },
];
const rows = [
  {
    label: 'Total', fwde_total: 1, nb_visits: 10, bounce_rate: 0.5, nb_visits__c1: 4, bounce_rate__c1: 0.25, nb_visits__c2: 8, bounce_rate__c2: 0.4,
  },
  {
    label: 'Chrome', nb_visits: 7, nb_visits__share: 70, bounce_rate: 0.5, nb_visits__c1: 3, nb_visits__c1__share: 75, bounce_rate__c1: 0.25, nb_visits__c2: 5, nb_visits__c2__change: 40, bounce_rate__c2: 0.4, bounce_rate__c2__change: 25,
  },
];

function table(columnErrors: Record<number, string> = {}) {
  return mount(CompareTable, {
    props: {
      rows, state, catalog: CATALOG, columns, columnErrors, actionScope: false, currencySymbol: '$',
    },
  });
}

describe('comparison table', () => {
  it('one header group per metric over its columns, with the column titles below', () => {
    const groups = table().findAll('.fwde-compare__group');
    expect(groups).toHaveLength(2);
    expect(groups[0].attributes('colspan')).toBe('3');
    expect(table().findAll('.fwde-compare__column').map((th) => th.text()))
      .toEqual(['All visits', 'Firefox', 'Nov 12', 'All visits', 'Firefox', 'Nov 12']);
  });

  it('shares for counts in own and segment columns, changes in period columns, none for rates', () => {
    const cells = table().findAll('tbody tr')[1].findAll('.fwde-compare__cell');
    expect(cells[0].find('.fwde-compare__extra').text()).toBe('70 %');
    expect(cells[1].find('.fwde-compare__extra').text()).toBe('75 %');
    expect(cells[2].find('.fwde-compare__extra').text()).toBe('+40 %');
    expect(cells[3].find('.fwde-compare__extra').exists()).toBe(false);
    expect(cells[5].find('.fwde-compare__extra').text()).toBe('+25 %');
  });

  it('a bar behind each count, scaled against the metric\'s largest value in all its columns', () => {
    const cells = table().findAll('tbody tr')[1].findAll('.fwde-compare__cell');
    // Chrome: 7 own, 3 Firefox, 5 Nov 12: the largest of the metric is 7
    expect(cells.slice(0, 3).map((c) => c.attributes('style'))).toEqual([
      '--fwde-bar: 100%;', '--fwde-bar: 42.9%;', '--fwde-bar: 71.4%;',
    ]);
    expect(cells[3].classes()).not.toContain('fwde-cellbar');
    expect(table().find('.fwde-compare__bar').exists()).toBe(false);
  });

  it('no bar in the total row or in a failing column', () => {
    expect(table().findAll('tbody tr')[0].find('.fwde-cellbar').exists()).toBe(false);
    const cells = table({ 1: 'Column Firefox: failed' }).findAll('tbody tr')[1].findAll('.fwde-compare__cell');
    expect(cells[1].classes()).not.toContain('fwde-cellbar');
  });

  it('each column with the dot of Matomo\'s comparison colour, groups apart', () => {
    const heads = table().findAll('.fwde-compare__column');
    expect(heads.slice(0, 3).map((th) => th.find('.fwde-compare__dot').attributes('data-name')))
      .toEqual(['series0', 'series1', 'series2']);
    expect(heads.map((th) => th.classes().includes('fwde-compare__group-start')))
      .toEqual([true, false, false, true, false, false]);
    const cells = table().findAll('tbody tr')[1].findAll('.fwde-compare__cell');
    expect(cells.map((td) => td.classes().includes('fwde-compare__group-start')))
      .toEqual([true, false, false, true, false, false]);
  });

  it('the total row comes first and has no row menu', () => {
    const totalRow = table().findAll('tbody tr')[0];
    expect(totalRow.classes()).toContain('fwde-compare__total');
    expect(totalRow.findComponent({ name: 'RowMenu' }).exists()).toBe(false);
    expect(table().findAll('tbody tr')[1].findComponent({ name: 'RowMenu' }).exists()).toBe(true);
  });

  it('a failing column keeps the others', () => {
    const cells = table({ 2: 'Column Nov 12: too long' }).findAll('tbody tr')[1].findAll('.fwde-compare__cell');
    expect(cells[2].text()).toBe('');
    expect(cells[1].text()).toContain('3');
  });
});
