/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import PivotPicker from '../src/PivotPicker.vue';
import DimensionPicker from '../src/DimensionPicker.vue';
import { parseState } from '../src/state';
import { CATALOG, LIMITS } from './fixtures';

// Matomo 5 runs the specs with Jest; CoreHome is Matomo's UMD global, so the mock is virtual
jest.mock('CoreHome', () => ({
  translate: (key: string) => key,
  SearchInput: {
    name: 'SearchInput',
    props: ['modelValue', 'placeholder', 'showClear'],
    emits: ['update:modelValue'],
    template: '<input class="search-input" :value="modelValue" />',
  },
}), { virtual: true });

const state = (hash: Record<string, string>) => parseState({
  fwdeDims: 'browser', fwdeMetrics: 'nb_visits', ...hash,
}, CATALOG, LIMITS);

describe('pivot picker (rows and pivot, 2026-11-24)', () => {
  it('lists the dimensions with the reasons that rule one out and emits the choice', async () => {
    const wrapper = mount(PivotPicker, { props: { catalog: CATALOG, state: state({}) } });
    await wrapper.find('button.fwde-picker__current').trigger('click');
    const items = wrapper.findAll('.fwde-list li, .fwde-list [role="option"]');
    expect(items.length).toBeGreaterThan(0);
    expect(wrapper.text()).toContain('FeinwerkDataExploration_ReasonPivotTime');
    expect(wrapper.text()).toContain('FeinwerkDataExploration_ReasonAlreadyChosen');
    (wrapper.vm as unknown as { choose: (id: string|null) => void }).choose('country');
    expect(wrapper.emitted('change')?.[0]).toEqual([{ pivot: 'country', pivotLimit: 5 }]);
  });

  it('offers 3, 5 and 10 values, greying out what passes 60 cells', () => {
    const seven = 'nb_visits,nb_actions,bounce_rate,avg_time_on_site,nb_conversions,revenue,nb_pageviews';
    const wrapper = mount(PivotPicker, {
      props: { catalog: CATALOG, state: state({ fwdeMetrics: seven, fwdePivot: 'country' }) },
    });
    const options = wrapper.findAll('.fwde-pivot-picker__limit button');
    expect(options.map((o) => o.text())).toEqual(['3', '5', '10']);
    expect(options[2].attributes('disabled')).toBeDefined();
    expect(options[1].attributes('disabled')).toBeUndefined();
  });

  it('is off with a note while Matomo compares', () => {
    const wrapper = mount(PivotPicker, {
      props: { catalog: CATALOG, state: state({ fwdePivot: 'country' }), comparing: true },
    });
    expect(wrapper.find('button.fwde-picker__current').attributes('disabled')).toBeDefined();
    expect(wrapper.find('.fwde-pivot-picker__paused').text()).toBe('FeinwerkDataExploration_PivotPaused');
    expect(wrapper.find('.fwde-pivot-picker__limit').exists()).toBe(false);
  });
});

describe('dimension picker with three slots', () => {
  it('shows a third slot once two dimensions are chosen, and None takes the later ones away', async () => {
    const one = mount(DimensionPicker, { props: { catalog: CATALOG, state: state({}) } });
    expect(one.findAll('.fwde-picker__slot')).toHaveLength(2);
    const two = mount(DimensionPicker, {
      props: { catalog: CATALOG, state: state({ fwdeDims: 'browser,country,pageTitle' }) },
    });
    expect(two.findAll('.fwde-picker__slot')).toHaveLength(3);
    expect(two.text()).toContain('FeinwerkDataExploration_ThirdDimension');
    await two.findAll('button.fwde-picker__current')[1].trigger('click');
    await two.find('.fwde-picker__none').trigger('click');
    expect(two.emitted('change')?.[0]).toEqual([['browser']]);
  });
});
