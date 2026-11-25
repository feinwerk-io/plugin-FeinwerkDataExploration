/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import MetricPicker from '../src/MetricPicker.vue';
import { ListItem } from '../src/SearchableList.vue';
import { parseState } from '../src/state';
import { Catalog } from '../src/types';
import { CATALOG, GOAL_CATALOG, LIMITS } from './fixtures';

vi.mock('CoreHome', () => ({
  translate: (key: string, ...params: unknown[]) => (params.length ? `${key}(${params.join(',')})` : key),
  SearchInput: {
    name: 'SearchInput',
    props: ['modelValue', 'placeholder', 'showClear'],
    emits: ['update:modelValue'],
    template: '<input class="search-input" :value="modelValue" />',
  },
}));

function picker(catalog: Catalog, metrics = 'nb_visits') {
  return mount(MetricPicker, {
    props: {
      catalog,
      state: parseState({ fwdeDims: 'browser', fwdeMetrics: metrics }, catalog, LIMITS),
      actionScope: false,
    },
  });
}

function items(catalog: Catalog): ListItem[] {
  return (picker(catalog).vm as unknown as { items: ListItem[] }).items;
}

describe('metric picker (more metrics, 2026-11-24)', () => {
  it('lists visits, all goals, each goal, e-commerce, then the actions', () => {
    const groups = items(GOAL_CATALOG).map((i) => i.group)
      .filter((group, i, all) => all.indexOf(group) === i);
    expect(groups).toEqual([
      'FeinwerkDataExploration_MetricGroupVisits',
      'FeinwerkDataExploration_MetricGroupGoals',
      'FeinwerkDataExploration_MetricGroupGoal(Newsletter)',
      'FeinwerkDataExploration_MetricGroupGoal(Q&A: Leads)',
      'Goals_Ecommerce',
      'FeinwerkDataExploration_MetricGroupActions',
    ]);
  });

  it('names a goal\'s metrics by their column under the goal, the all-goals ones say so', () => {
    const byKey = Object.fromEntries(items(GOAL_CATALOG).map((i) => [i.key, i]));
    expect(byKey.goal_5_revenue.label).toBe('Revenue');
    expect(byKey.goal_5_conversion_rate.label).toBe('Conversion rate');
    expect(byKey.nb_conversions.label).toBe('FeinwerkDataExploration_MetricAllGoals(Conversions)');
    expect(byKey.conversion_rate.label)
      .toBe('FeinwerkDataExploration_MetricAllGoals(Conversion rate)');
    expect(byKey.nb_visits.label).toBe('Visits');
    expect(byKey.goal_ecommerceOrder_revenue.label).toBe('Order revenue');
  });

  it('finds a goal\'s metrics by the goal\'s name', async () => {
    const wrapper = picker(GOAL_CATALOG);
    await wrapper.findComponent({ name: 'SearchInput' }).vm.$emit('update:modelValue', 'q&a');
    const shown = wrapper.findAll('.fwde-list__group-name').map((h) => h.text());
    expect(shown).toEqual(['FeinwerkDataExploration_MetricGroupGoal(Q&A: Leads)']);
    // review focus 2: the name as plain text, "&" and ":" included
    expect(wrapper.text()).toContain('Q&A: Leads');
  });

  it('has no goal selector any more', () => {
    expect(picker(GOAL_CATALOG, 'nb_conversions').find('select').exists()).toBe(false);
  });

  it('keeps a website without goals as before', () => {
    const groups = items(CATALOG).map((i) => i.group)
      .filter((group, i, all) => all.indexOf(group) === i);
    expect(groups).toEqual([
      'FeinwerkDataExploration_MetricGroupVisits',
      'FeinwerkDataExploration_MetricGroupGoals',
      'FeinwerkDataExploration_MetricGroupActions',
    ]);
  });
});
