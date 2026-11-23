/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
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

describe('dimension picker', () => {
  it('closes its list with Escape and gives the focus back to its button', async () => {
    const wrapper = mount(DimensionPicker, {
      props: { catalog: CATALOG, state: parseState({ fwdeDims: 'browser' }, CATALOG, LIMITS) },
      attachTo: document.body,
    });
    const button = wrapper.findAll('button.fwde-picker__current')[0];
    await button.trigger('click');
    expect(wrapper.find('.fwde-picker__panel').exists()).toBe(true);
    await wrapper.find('.fwde-list').trigger('keydown', { key: 'Escape' });
    expect(wrapper.find('.fwde-picker__panel').exists()).toBe(false);
    expect(document.activeElement).toBe(button.element);
    wrapper.unmount();
  });
});
