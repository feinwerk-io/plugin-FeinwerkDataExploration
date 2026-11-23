/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import SegmentedSwitch from '../src/SegmentedSwitch.vue';

const options = [
  { value: 'table', label: 'Table' },
  { value: 'chart', label: 'Chart', disabled: true, title: 'Needs two rows' },
  { value: 'both', label: 'Both' },
];

function mountSwitch(modelValue = 'table') {
  return mount(SegmentedSwitch, {
    props: { options, modelValue, label: 'View' },
    attachTo: document.body,
  });
}

const press = async (wrapper: ReturnType<typeof mountSwitch>, key: string) => {
  await wrapper.find('.fwde-segmented').trigger('keydown', { key });
};

describe('segmented switch (UI item 1, from Live Dashboard)', () => {
  afterEach(() => {
    while (document.body.firstChild) {
      document.body.removeChild(document.body.firstChild);
    }
  });

  it('renders a radio group with the chosen option checked and the only tab stop', () => {
    const wrapper = mountSwitch('both');
    const group = wrapper.find('.fwde-segmented');
    expect(group.attributes('role')).toBe('radiogroup');
    expect(group.attributes('aria-label')).toBe('View');
    const buttons = wrapper.findAll('button.fwde-segmented__option');
    expect(buttons.map((b) => b.attributes('role'))).toEqual(['radio', 'radio', 'radio']);
    expect(buttons.map((b) => b.attributes('aria-checked'))).toEqual(['false', 'false', 'true']);
    expect(buttons.map((b) => b.attributes('tabindex'))).toEqual(['-1', '-1', '0']);
    expect(buttons[2].classes()).toContain('fwde-segmented__option--active');
  });

  it('arrow keys, Home and End choose another option and move the focus', async () => {
    const wrapper = mountSwitch('table');
    await press(wrapper, 'End');
    expect(wrapper.emitted('update:modelValue')).toEqual([['both']]);
    expect((document.activeElement as HTMLElement).textContent).toBe('Both');
    await wrapper.setProps({ modelValue: 'both' });
    await press(wrapper, 'Home');
    expect(wrapper.emitted('update:modelValue')?.[1]).toEqual(['table']);
    expect((document.activeElement as HTMLElement).textContent).toBe('Table');
  });

  it('the arrow keys skip disabled options and wrap around the ends', async () => {
    const wrapper = mountSwitch('table');
    await press(wrapper, 'ArrowRight');
    expect(wrapper.emitted('update:modelValue')).toEqual([['both']]);
    await wrapper.setProps({ modelValue: 'both' });
    await press(wrapper, 'ArrowRight');
    expect(wrapper.emitted('update:modelValue')?.[1]).toEqual(['table']);
    await wrapper.setProps({ modelValue: 'table' });
    await press(wrapper, 'ArrowLeft');
    expect(wrapper.emitted('update:modelValue')?.[2]).toEqual(['both']);
  });

  it('an arrow key emits nothing when every other option is disabled (no row: the view stays as it is)', async () => {
    const wrapper = mount(SegmentedSwitch, {
      props: {
        options: [
          { value: 'table', label: 'Table' },
          { value: 'chart', label: 'Chart', disabled: true },
          { value: 'both', label: 'Both', disabled: true },
        ],
        modelValue: 'table',
        label: 'View',
      },
      attachTo: document.body,
    });
    await press(wrapper, 'ArrowRight');
    await press(wrapper, 'End');
    await wrapper.find('button.fwde-segmented__option').trigger('click');
    expect(wrapper.emitted('update:modelValue')).toBeUndefined();
  });

  it('a click on a disabled option chooses nothing and the option keeps its title', async () => {
    const wrapper = mountSwitch('table');
    const chart = wrapper.findAll('button.fwde-segmented__option')[1];
    expect(chart.attributes('disabled')).toBeDefined();
    expect(chart.attributes('title')).toBe('Needs two rows');
    expect(chart.classes()).toContain('fwde-segmented__option--disabled');
    await chart.trigger('click');
    expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    await wrapper.findAll('button.fwde-segmented__option')[2].trigger('click');
    expect(wrapper.emitted('update:modelValue')).toEqual([['both']]);
  });
});
