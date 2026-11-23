/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import FilterPanel from '../src/FilterPanel.vue';
import { parseState } from '../src/state';
import { CATALOG, LIMITS } from './fixtures';

// Matomo 5 runs the specs with Jest; CoreHome is Matomo's UMD global, so the mock is virtual
jest.mock('CoreHome', () => ({
  translate: (key: string) => key,
}), { virtual: true });

const mounted: Array<{ unmount: () => void }> = [];

function panel(filters = 'label~contains~fire') {
  const state = parseState({ fwdeDims: 'browser', fwdeMetrics: 'nb_visits', fwdeFilters: filters }, CATALOG, LIMITS);
  const wrapper = mount(FilterPanel, {
    props: {
      open: false, catalog: CATALOG, state, actionScope: false,
    },
    attachTo: document.body,
  });
  mounted.push(wrapper);
  return wrapper;
}

const inPanel = <T extends Element>(selector: string) => document.querySelector<T>(`section.fwde-sidepanel ${selector}`);
const settle = async () => {
  await nextTick();
  await nextTick();
};
const type = (input: HTMLInputElement, value: string) => {
  input.value = value;
  input.dispatchEvent(new Event('input'));
};

describe('filters in the side panel with Apply (UI item 4)', () => {
  afterEach(() => {
    mounted.splice(0).forEach((wrapper) => wrapper.unmount());
    while (document.body.firstChild) {
      document.body.removeChild(document.body.firstChild);
    }
  });

  it('Apply hands over filters and match once and closes the panel', async () => {
    const wrapper = panel('label~contains~fire;nb_visits~gt~2');
    await wrapper.setProps({ open: true });
    await settle();
    expect(inPanel('h2')?.textContent).toBe('FeinwerkDataExploration_Filters');
    (document.querySelectorAll<HTMLElement>('section.fwde-sidepanel .fwde-segmented__option')[1]).click();
    await settle();
    expect(wrapper.emitted('apply')).toBeUndefined();
    inPanel<HTMLElement>('.fwde-filters__apply')?.click();
    await settle();
    expect(wrapper.emitted('apply')).toEqual([[{
      filters: [
        { target: 'label', operator: 'contains', value: 'fire' },
        { target: 'nb_visits', operator: 'gt', value: '2' },
      ],
      match: 'any',
    }]]);
    expect(wrapper.emitted('update:open')).toEqual([[false]]);
  });

  it('Apply takes a value typed just before (no waiting for the typing pause)', async () => {
    const wrapper = panel();
    await wrapper.setProps({ open: true });
    await settle();
    type(inPanel<HTMLInputElement>('.fwde-filters__field--value input') as HTMLInputElement, 'chrome');
    inPanel<HTMLElement>('.fwde-filters__apply')?.click();
    await settle();
    expect(wrapper.emitted('apply')).toEqual([[{
      filters: [{ target: 'label', operator: 'contains', value: 'chrome' }],
      match: 'all',
    }]]);
  });

  it('applies a decimal number through Apply and through Enter', async () => {
    const wrapper = panel('nb_visits~gt~2');
    await wrapper.setProps({ open: true });
    await settle();
    type(inPanel<HTMLInputElement>('.fwde-filters__field--value input') as HTMLInputElement, '2.5');
    inPanel<HTMLElement>('.fwde-filters__apply')?.click();
    await settle();
    expect(wrapper.emitted('apply')?.[0]).toEqual([{
      filters: [{ target: 'nb_visits', operator: 'gt', value: '2.5' }],
      match: 'all',
    }]);
    await wrapper.setProps({ open: false });
    await wrapper.setProps({ open: true });
    await settle();
    type(inPanel<HTMLInputElement>('.fwde-filters__field--value input') as HTMLInputElement, '19.99');
    inPanel<HTMLFormElement>('form')?.requestSubmit();
    await settle();
    expect(wrapper.emitted('apply')?.[1]).toEqual([{
      filters: [{ target: 'nb_visits', operator: 'gt', value: '19.99' }],
      match: 'all',
    }]);
  });

  it('removing a row keeps the focus in the panel, on the next row', async () => {
    const wrapper = panel('label~contains~fire;nb_visits~gt~2');
    await wrapper.setProps({ open: true });
    await settle();
    const remove = inPanel<HTMLElement>('.fwde-filters__remove') as HTMLElement;
    remove.focus();
    remove.click();
    await settle();
    expect(document.querySelectorAll('section.fwde-sidepanel .fwde-filters__row')).toHaveLength(1);
    expect(document.activeElement).toBe(inPanel('.fwde-filters__row select'));
  });

  it('Cancel keeps the applied filters', async () => {
    const wrapper = panel();
    await wrapper.setProps({ open: true });
    await settle();
    type(inPanel<HTMLInputElement>('.fwde-filters__field--value input') as HTMLInputElement, 'chrome');
    inPanel<HTMLElement>('.fwde-filters__cancel')?.click();
    await settle();
    expect(wrapper.emitted('apply')).toBeUndefined();
    expect(wrapper.emitted('update:open')).toEqual([[false]]);
    await wrapper.setProps({ open: false });
    await wrapper.setProps({ open: true });
    await settle();
    expect(inPanel<HTMLInputElement>('.fwde-filters__field--value input')?.value).toBe('fire');
  });

  it('opens with an empty row to fill when there is no filter yet', async () => {
    const wrapper = panel('');
    await wrapper.setProps({ open: true });
    await settle();
    expect(document.querySelectorAll('section.fwde-sidepanel .fwde-filters__row')).toHaveLength(1);
    expect(document.activeElement?.closest('.fwde-filters__row')).not.toBeNull();
  });
});
