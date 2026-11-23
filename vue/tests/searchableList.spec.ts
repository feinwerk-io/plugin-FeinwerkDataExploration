/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import SearchableList from '../src/SearchableList.vue';

vi.mock('CoreHome', () => ({
  translate: (key: string) => key,
  SearchInput: {
    name: 'SearchInput',
    props: ['modelValue', 'placeholder', 'showClear'],
    emits: ['update:modelValue'],
    template: '<input class="search-input" :value="modelValue" '
      + '@input="$emit(\'update:modelValue\', $event.target.value)" />',
  },
}));

// the list's items (ListItem in SearchableList.vue; Jest's .vue shim on Matomo 5 has no named types)
const items = [
  { key: 'browser', label: 'Browser', group: 'Technology', reason: null },
  { key: 'os', label: 'Operating system', group: 'Technology', reason: 'FeinwerkDataExploration_NotPossible' },
  { key: 'country', label: 'Country', group: 'Visitor', reason: null },
];

function list(multiple = false) {
  return mount(SearchableList, {
    props: {
      items, selected: [], multiple, searchLabel: 'Search',
    },
    attachTo: document.body,
  });
}

const key = (wrapper: ReturnType<typeof list>, code: string) => wrapper.find('.fwde-list')
  .trigger('keydown', { key: code });
const focusedKey = () => (document.activeElement as HTMLElement|null)?.getAttribute('data-key');

describe('searchable list keyboard (design 2026-11-20)', () => {
  afterEach(() => {
    while (document.body.firstChild) {
      document.body.removeChild(document.body.firstChild);
    }
  });

  it('moves with the arrow keys over the items that can be chosen and back to the search', async () => {
    const wrapper = list();
    (wrapper.find('input.search-input').element as HTMLInputElement).focus();
    await key(wrapper, 'ArrowDown');
    expect(focusedKey()).toBe('browser');
    await key(wrapper, 'ArrowDown');
    expect(focusedKey()).toBe('country');
    await key(wrapper, 'ArrowDown');
    expect(focusedKey()).toBe('country');
    await key(wrapper, 'ArrowUp');
    expect(focusedKey()).toBe('browser');
    await key(wrapper, 'ArrowUp');
    expect(document.activeElement).toBe(wrapper.find('input.search-input').element);
    expect(wrapper.emitted('toggle')).toBeUndefined();
  });

  it('chooses the focused item with Enter, and the first match from the search', async () => {
    const wrapper = list(true);
    (wrapper.find('input.search-input').element as HTMLInputElement).focus();
    await key(wrapper, 'Enter');
    await key(wrapper, 'ArrowDown');
    await key(wrapper, 'ArrowDown');
    await key(wrapper, 'Enter');
    expect(wrapper.emitted('toggle')).toEqual([['browser'], ['country']]);
  });

  it('adds with Enter from the search: the first match not chosen yet, never removing a chosen one', async () => {
    const wrapper = mount(SearchableList, {
      props: {
        items, selected: ['browser'], multiple: true, searchLabel: 'Search',
      },
      attachTo: document.body,
    });
    (wrapper.find('input.search-input').element as HTMLInputElement).focus();
    await key(wrapper, 'Enter');
    await wrapper.setProps({ selected: ['browser', 'country'] });
    (wrapper.find('input.search-input').element as HTMLInputElement).focus();
    await key(wrapper, 'Enter');
    expect(wrapper.emitted('toggle')).toEqual([['country']]);
  });

  it('asks to close with Escape', async () => {
    const wrapper = list();
    await key(wrapper, 'Escape');
    expect(wrapper.emitted('close')).toHaveLength(1);
  });
});
