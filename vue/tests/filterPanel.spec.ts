/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import FilterPanel from '../src/FilterPanel.vue';
import { parseState } from '../src/state';
import { CATALOG, LIMITS } from './fixtures';

vi.mock('CoreHome', () => ({
  translate: (key: string) => key,
  // suggestions for a filter's value (2026-11-25)
  AjaxHelper: {
    fetch: (params: { method: string, text: string }) => Promise.resolve(
      params.method === 'FeinwerkDataExploration.getFilterSuggestions' ? [`${params.text}efox`, `${params.text}ebird`] : [],
    ),
  },
}));

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
        { target: 'label', operator: 'contains', value: 'fire', group: 0 },
        { target: 'nb_visits', operator: 'gt', value: '2', group: 0 },
      ],
      groupMatch: ['any'],
      match: 'all',
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
      filters: [{
        target: 'label', operator: 'contains', value: 'chrome', group: 0,
      }],
      groupMatch: ['all'],
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
      filters: [{
        target: 'nb_visits', operator: 'gt', value: '2.5', group: 0,
      }],
      groupMatch: ['all'],
      match: 'all',
    }]);
    await wrapper.setProps({ open: false });
    await wrapper.setProps({ open: true });
    await settle();
    type(inPanel<HTMLInputElement>('.fwde-filters__field--value input') as HTMLInputElement, '19.99');
    inPanel<HTMLFormElement>('form')?.requestSubmit();
    await settle();
    expect(wrapper.emitted('apply')?.[1]).toEqual([{
      filters: [{
        target: 'nb_visits', operator: 'gt', value: '19.99', group: 0,
      }],
      groupMatch: ['all'],
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

describe('"is empty" and "is not empty" in the panel (2026-11-25)', () => {
  afterEach(() => {
    mounted.splice(0).forEach((wrapper) => wrapper.unmount());
    while (document.body.firstChild) {
      document.body.removeChild(document.body.firstChild);
    }
  });

  it('offers them for a dimension, shows no value field and applies them', async () => {
    const wrapper = panel('label~contains~fire');
    await wrapper.setProps({ open: true });
    await settle();
    const operator = inPanel<HTMLSelectElement>('.fwde-filters__field--operator select');
    expect(Array.from(operator?.options || []).map((o) => o.value)).toEqual(expect.arrayContaining(['empty', 'notEmpty']));
    if (operator) {
      operator.value = 'notEmpty';
      operator.dispatchEvent(new Event('change'));
    }
    await settle();
    expect(inPanel('.fwde-filters__field--value')).toBeNull();
    // what "empty" means, under the condition (UI review 2026-11-25, W2)
    expect(inPanel('.fwde-filters__help')?.textContent).toContain('FeinwerkDataExploration_FilterEmptyHelp');
    (inPanel<HTMLElement>('.fwde-filters__apply') as HTMLElement).click();
    await settle();
    const [[applied]] = wrapper.emitted('apply') as [[{ filters: { operator: string, value: string }[] }]];
    expect(applied.filters.map((f) => [f.operator, f.value])).toEqual([['notEmpty', '']]);
  });
});

describe('suggestions under the value field (2026-11-25)', () => {
  afterEach(() => {
    mounted.splice(0).forEach((wrapper) => wrapper.unmount());
    while (document.body.firstChild) {
      document.body.removeChild(document.body.firstChild);
    }
  });

  it('lists Matomo\'s values for the typed text, none for a pattern', async () => {
    const wrapper = panel('label~contains~fire');
    await wrapper.setProps({ open: true });
    await settle();
    const input = inPanel<HTMLInputElement>('.fwde-filters__field--value input') as HTMLInputElement;
    type(input, 'fir');
    await new Promise((resolve) => { setTimeout(resolve, 400); });
    await settle();
    const list = input.getAttribute('list');
    expect(list).toBeTruthy();
    // one list id from the start, so the browser opens the list as soon as options arrive (W1)
    expect(list).toMatch(/^fwde-suggest-\d+$/);
    const options = Array.from(document.querySelectorAll<HTMLOptionElement>(`#${list} option`)).map((o) => o.value);
    expect(options).toEqual(['firefox', 'firebird']);
    expect(input.getAttribute('autocomplete')).toBe('off');

    const operator = inPanel<HTMLSelectElement>('.fwde-filters__field--operator select') as HTMLSelectElement;
    operator.value = 'matches';
    operator.dispatchEvent(new Event('change'));
    await settle();
    expect(inPanel<HTMLInputElement>('.fwde-filters__field--value input')?.getAttribute('list')).toBeNull();
  });
});

describe('Apply with an incomplete filter (UI review 2026-11-23, F3)', () => {
  afterEach(() => {
    mounted.splice(0).forEach((wrapper) => wrapper.unmount());
    while (document.body.firstChild) {
      document.body.removeChild(document.body.firstChild);
    }
  });

  const selectTarget = async (value: string) => {
    const select = inPanel<HTMLSelectElement>('.fwde-filters__field--target select') as HTMLSelectElement;
    select.value = value;
    select.dispatchEvent(new Event('change'));
    await settle();
  };
  const valueInput = () => inPanel<HTMLInputElement>('.fwde-filters__field--value input') as HTMLInputElement;
  const error = () => inPanel<HTMLElement>('.fwde-filters__error');

  it('keeps the panel open and asks for a number when a chosen number filter has none', async () => {
    const wrapper = panel('');
    await wrapper.setProps({ open: true });
    await settle();
    await selectTarget('nb_visits');
    inPanel<HTMLElement>('.fwde-filters__apply')?.click();
    await settle();
    expect(wrapper.emitted('apply')).toBeUndefined();
    expect(wrapper.emitted('update:open')).toBeUndefined();
    expect(error()?.textContent?.trim()).toBe('FeinwerkDataExploration_FilterNeedsNumber');
    expect(valueInput().getAttribute('aria-invalid')).toBe('true');
    expect(valueInput().getAttribute('aria-describedby')).toBe(error()?.id);
    expect(document.activeElement).toBe(valueInput());
  });

  it('asks for a value when a typed text was cleared again, also through Enter', async () => {
    const wrapper = panel('');
    await wrapper.setProps({ open: true });
    await settle();
    type(valueInput(), 'fire');
    type(valueInput(), '  ');
    inPanel<HTMLFormElement>('form')?.requestSubmit();
    await settle();
    expect(wrapper.emitted('apply')).toBeUndefined();
    expect(error()?.textContent?.trim()).toBe('FeinwerkDataExploration_FilterNeedsValue');
  });

  it('the message goes once the value is there, and Apply then runs', async () => {
    const wrapper = panel('');
    await wrapper.setProps({ open: true });
    await settle();
    await selectTarget('nb_visits');
    inPanel<HTMLElement>('.fwde-filters__apply')?.click();
    await settle();
    type(valueInput(), '3');
    await settle();
    expect(error()).toBeNull();
    expect(valueInput().getAttribute('aria-invalid')).toBeNull();
    inPanel<HTMLElement>('.fwde-filters__apply')?.click();
    await settle();
    expect(wrapper.emitted('apply')).toEqual([[{
      filters: [{
        target: 'nb_visits', operator: 'gt', value: '3', group: 0,
      }],
      groupMatch: ['all'],
      match: 'all',
    }]]);
  });

  it('drops an untouched empty row without a word and closes', async () => {
    const wrapper = panel('label~contains~fire');
    await wrapper.setProps({ open: true });
    await settle();
    inPanel<HTMLElement>('.fwde-filters__add')?.click();
    await settle();
    inPanel<HTMLElement>('.fwde-filters__apply')?.click();
    await settle();
    expect(error()).toBeNull();
    expect(wrapper.emitted('apply')).toEqual([[{
      filters: [{
        target: 'label', operator: 'contains', value: 'fire', group: 0,
      }],
      groupMatch: ['all'],
      match: 'all',
    }]]);
    expect(wrapper.emitted('update:open')).toEqual([[false]]);
  });
});

describe('filter groups in the panel (2026-11-23)', () => {
  afterEach(() => {
    mounted.splice(0).forEach((wrapper) => wrapper.unmount());
    while (document.body.firstChild) {
      document.body.removeChild(document.body.firstChild);
    }
  });

  const grouped = (filters: string, match = '') => {
    const state = parseState({
      fwdeDims: 'browser', fwdeMetrics: 'nb_visits', fwdeFilters: filters, fwdeMatch: match,
    }, CATALOG, LIMITS);
    const wrapper = mount(FilterPanel, {
      props: {
        open: false, catalog: CATALOG, state, actionScope: false,
      },
      attachTo: document.body,
    });
    mounted.push(wrapper);
    return wrapper;
  };
  const all = <T extends Element>(selector: string) => [
    ...document.querySelectorAll<T>(`section.fwde-sidepanel ${selector}`),
  ];

  it('one group looks like today: no frame, no switch between groups', async () => {
    const wrapper = grouped('label~contains~fire;nb_visits~gt~2');
    await wrapper.setProps({ open: true });
    await settle();
    expect(all('.fwde-filters__group')).toHaveLength(0);
    expect(all('.fwde-filters__groups-match')).toHaveLength(0);
    expect(all('.fwde-filters__match')).toHaveLength(1);
  });

  it('"Add group" makes two framed groups with titles and the switch between them', async () => {
    const wrapper = grouped('label~contains~fire;nb_visits~gt~2');
    await wrapper.setProps({ open: true });
    await settle();
    inPanel<HTMLElement>('.fwde-filters__add-group')?.click();
    await settle();
    const groups = all<HTMLElement>('.fwde-filters__group');
    expect(groups).toHaveLength(2);
    expect(groups.map((g) => g.querySelector('legend')?.textContent?.trim()))
      .toEqual(['FeinwerkDataExploration_FilterGroup', 'FeinwerkDataExploration_FilterGroup']);
    expect(all('.fwde-filters__groups-match')).toHaveLength(1);
    // a group's own switch from two conditions on
    expect(groups[0].querySelector('.fwde-filters__match')).not.toBeNull();
    expect(groups[1].querySelector('.fwde-filters__match')).toBeNull();
    expect(document.activeElement?.closest('.fwde-filters__group')).toBe(groups[1]);
  });

  it('removing the last condition of group 1 renumbers the rest and returns to the simple look', async () => {
    const wrapper = grouped('label~contains~a|any:label~contains~b;label~contains~c');
    await wrapper.setProps({ open: true });
    await settle();
    all<HTMLElement>('.fwde-filters__group')[0].querySelector<HTMLElement>('.fwde-filters__remove')?.click();
    await settle();
    expect(all('.fwde-filters__group')).toHaveLength(0);
    expect(all('.fwde-filters__match .fwde-segmented__option--active')[0]?.textContent?.trim())
      .toBe('FeinwerkDataExploration_FilterMatchAny');
    inPanel<HTMLElement>('.fwde-filters__apply')?.click();
    await settle();
    expect(wrapper.emitted('apply')).toEqual([[{
      filters: [
        { target: 'label', operator: 'contains', value: 'b', group: 0 },
        { target: 'label', operator: 'contains', value: 'c', group: 0 },
      ],
      groupMatch: ['any'],
      match: 'all',
    }]]);
  });

  it('the limits disable the buttons with the reason and count the conditions', async () => {
    const wrapper = grouped(Array(5).fill('label~contains~a;label~contains~b').join('|'));
    await wrapper.setProps({ open: true });
    await settle();
    const addGroup = inPanel<HTMLButtonElement>('.fwde-filters__add-group') as HTMLButtonElement;
    expect(addGroup.disabled).toBe(true);
    expect(addGroup.title).toBe('FeinwerkDataExploration_FilterLimitGroups');
    const addCondition = all<HTMLButtonElement>('.fwde-filters__add');
    expect(addCondition.every((b) => b.disabled && b.title === 'FeinwerkDataExploration_FilterLimitConditions')).toBe(true);
    expect(inPanel('.fwde-filters__count')?.textContent?.trim()).toBe('FeinwerkDataExploration_FilterConditionCount');
    // the reason as text too, not only in the disabled button's title (keyboard and screen reader)
    expect(inPanel('.fwde-filters__limit')?.textContent?.trim()).toBe('FeinwerkDataExploration_FilterLimitGroups');
  });

  it('Apply hands over the conditions, the switch between the groups and each group\'s switch once', async () => {
    const wrapper = grouped('label~contains~a|any:label~contains~b;label~contains~c', 'any');
    await wrapper.setProps({ open: true });
    await settle();
    // the switch between the groups to "all"
    inPanel<HTMLElement>('.fwde-filters__groups-match .fwde-segmented__option')?.click();
    await settle();
    expect(wrapper.emitted('apply')).toBeUndefined();
    inPanel<HTMLElement>('.fwde-filters__apply')?.click();
    await settle();
    expect(wrapper.emitted('apply')).toEqual([[{
      filters: [
        { target: 'label', operator: 'contains', value: 'a', group: 0 },
        { target: 'label', operator: 'contains', value: 'b', group: 1 },
        { target: 'label', operator: 'contains', value: 'c', group: 1 },
      ],
      groupMatch: ['all', 'any'],
      match: 'all',
    }]]);
  });

  it('a pattern only the database knows goes through; a too long one stays, marked (final review 2026-11-24)', async () => {
    const wrapper = grouped('');
    await wrapper.setProps({ open: true });
    await settle();
    const operator = inPanel<HTMLSelectElement>('.fwde-filters__field--operator select') as HTMLSelectElement;
    operator.value = 'matches';
    operator.dispatchEvent(new Event('change'));
    await settle();
    expect(inPanel('.fwde-filters__help')?.textContent?.trim()).toBe('FeinwerkDataExploration_FilterPatternHelp');
    const value = inPanel<HTMLInputElement>('.fwde-filters__field--value input') as HTMLInputElement;
    type(value, 'p'.repeat(201));
    inPanel<HTMLElement>('.fwde-filters__apply')?.click();
    await settle();
    expect(wrapper.emitted('apply')).toBeUndefined();
    expect(inPanel('.fwde-filters__error')?.textContent?.trim()).toBe('FeinwerkDataExploration_FilterPatternTooLong');
    // PCRE and ICU know "(?i)", JavaScript does not: the server decides
    type(value, '(?i)^chr');
    inPanel<HTMLElement>('.fwde-filters__apply')?.click();
    await settle();
    expect(wrapper.emitted('apply')).toEqual([[{
      filters: [{
        target: 'label', operator: 'matches', value: '(?i)^chr', group: 0,
      }],
      groupMatch: ['all'],
      match: 'all',
    }]]);
  });
});

