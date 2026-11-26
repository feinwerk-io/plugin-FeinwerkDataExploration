/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import SaveDialog from '../src/SaveDialog.vue';

// Matomo 5 runs the specs with Jest; CoreHome is Matomo's UMD global, so the mock is virtual
jest.mock('CoreHome', () => ({
  translate: (key: string) => key,
}), { virtual: true });

const mounted: Array<{ unmount: () => void }> = [];

function dialog(props: Record<string, unknown> = {}) {
  const wrapper = mount(SaveDialog, {
    props: { suggestedName: 'Browser', canShare: true, ...props },
    attachTo: document.body,
  });
  mounted.push(wrapper);
  return wrapper;
}

const panel = () => document.querySelector<HTMLElement>('section.fwde-sidepanel');
const name = () => document.querySelector<HTMLInputElement>('#fwde-savedialog-name');
const settle = async () => {
  await nextTick();
  await nextTick();
};

describe('saving in the side panel (UI item 2)', () => {
  afterEach(() => {
    mounted.splice(0).forEach((wrapper) => wrapper.unmount());
    while (document.body.firstChild) {
      document.body.removeChild(document.body.firstChild);
    }
  });

  it('opens the save panel from the Save button with the name selected', async () => {
    const wrapper = dialog();
    expect(panel()).toBeNull();
    await wrapper.find('.fwde-savedialog__toggle').trigger('click');
    await settle();
    expect(panel()?.querySelector('h2')?.textContent).toBe('FeinwerkDataExploration_SaveExploration');
    expect(panel()?.classList.contains('fwde-page')).toBe(true);
    expect(name()?.value).toBe('Browser');
    expect(document.activeElement).toBe(name());
    expect([name()?.selectionStart, name()?.selectionEnd]).toEqual([0, 7]);
    expect(panel()?.querySelector('.fwde-sidepanel__footer .fwde-savedialog__new')).not.toBeNull();
  });

  it('Cancel and Escape close it and the focus is back on Save', async () => {
    const wrapper = dialog();
    const toggle = wrapper.find('.fwde-savedialog__toggle');
    await toggle.trigger('click');
    await settle();
    (panel()?.querySelector('.fwde-savedialog__cancel') as HTMLElement).click();
    await settle();
    expect(panel()).toBeNull();
    expect(document.activeElement).toBe(toggle.element);

    await toggle.trigger('click');
    await settle();
    panel()?.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));
    await settle();
    expect(panel()).toBeNull();
    expect(document.activeElement).toBe(toggle.element);
  });

  it('Delete moves the focus to its confirmation, and Cancel back to Delete', async () => {
    const current = {
      id: 1, name: 'Mine', shared: false, owner: 'anna', canEdit: true, canDelete: true, kind: 'table',
      updated: '2026-11-21 10:00:00', state: {}, segment: '',
    };
    const wrapper = dialog({ current });
    await wrapper.find('.fwde-savedialog__toggle').trigger('click');
    await settle();
    (panel()?.querySelector('.fwde-savedialog__deleteask') as HTMLElement).click();
    await settle();
    expect(document.activeElement).toBe(panel()?.querySelector('.fwde-savedialog__deleteyes'));
    // UI review 2026-11-24 (F4): it says where else the exploration disappears
    expect(panel()?.querySelector('.fwde-savedialog__delete')?.textContent)
      .toContain('FeinwerkDataExploration_DeleteAlsoRemoves');
    (panel()?.querySelector('.fwde-savedialog__deleteno') as HTMLElement).click();
    await settle();
    expect(document.activeElement).toBe(panel()?.querySelector('.fwde-savedialog__deleteask'));
  });

  // saved reports (2026-11-24): where the exploration shows in Matomo's menu
  it('offers the menu place and sends it with the save', async () => {
    const wrapper = dialog({
      menuSections: [
        { id: 'Goals_Goals', name: 'Goals' },
        { id: 'FeinwerkDataExploration_Explorations', name: 'Explorations' },
      ],
    });
    await wrapper.find('.fwde-savedialog__toggle').trigger('click');
    await settle();
    const select = panel()?.querySelector('select.fwde-savedialog__menu') as HTMLSelectElement;
    expect(Array.from(select.options).map((o) => [o.value, o.textContent])).toEqual([
      ['', 'FeinwerkDataExploration_MenuNone'], ['Goals_Goals', 'Goals'],
      ['FeinwerkDataExploration_Explorations', 'Explorations'],
    ]);
    expect(select.value).toBe('FeinwerkDataExploration_Explorations');
    select.value = 'Goals_Goals';
    select.dispatchEvent(new Event('change'));
    (panel()?.querySelector('.fwde-savedialog__new') as HTMLElement).click();
    await settle();
    expect(wrapper.emitted('save')).toEqual([[{
      name: 'Browser', shared: false, asNew: true, menu: 'Goals_Goals',
    }]]);
  });

  it('saving emits once and a finished save closes the panel', async () => {
    const wrapper = dialog();
    await wrapper.find('.fwde-savedialog__toggle').trigger('click');
    await settle();
    const input = name() as HTMLInputElement;
    input.value = 'Browsers by device';
    input.dispatchEvent(new Event('input'));
    (panel()?.querySelector('.fwde-savedialog__new') as HTMLElement).click();
    await settle();
    // no menu sections (the funnel page): never a menu place
    expect(wrapper.emitted('save')).toEqual([[{
      name: 'Browsers by device', shared: false, asNew: true, menu: '',
    }]]);
    await wrapper.setProps({ busy: true });
    await wrapper.setProps({ busy: false });
    await settle();
    expect(panel()).toBeNull();
  });
});

// archived saved explorations (design 2026-11-25): a change that starts the history again says so before saving
describe('the save panel and an archived history', () => {
  it('warns when a change cannot keep the history, only when changing a saved one', async () => {
    const current = {
      id: 4, name: 'Pages', shared: true, owner: 'anna', canEdit: true, canDelete: true, kind: 'table' as const,
      updated: '2026-11-19 08:30:00', state: {}, segment: '', archived: true,
    };
    const changing = mount(SaveDialog, {
      props: { current, suggestedName: 'Pages', historyLostFrom: '2026-05-01' },
      attachTo: document.body,
    });
    await changing.find('.fwde-savedialog__toggle').trigger('click');
    await changing.vm.$nextTick();
    const warning = document.querySelector('.fwde-savedialog__history');
    expect(warning?.textContent).toContain('FeinwerkDataExploration_ArchiveHistoryLost');
    changing.unmount();

    const quiet = mount(SaveDialog, { props: { current, suggestedName: 'Pages', historyLostFrom: '' }, attachTo: document.body });
    await quiet.find('.fwde-savedialog__toggle').trigger('click');
    await quiet.vm.$nextTick();
    expect(document.querySelector('.fwde-savedialog__history')).toBeNull();
    quiet.unmount();
  });

  // UI review 2026-11-26 F1: the note is a warning above the buttons, and Enter keeps the history
  it('keeps the history by default while the note shows', async () => {
    const current = {
      id: 4, name: 'Pages', shared: true, owner: 'anna', canEdit: true, canDelete: true, kind: 'table' as const,
      updated: '2026-11-19 08:30:00', state: {}, segment: '', archived: true,
    };
    const wrapper = mount(SaveDialog, {
      props: { current, suggestedName: 'Pages', historyLostFrom: 'May 26, 2026' },
      attachTo: document.body,
    });
    await wrapper.find('.fwde-savedialog__toggle').trigger('click');
    await wrapper.vm.$nextTick();
    await wrapper.vm.$nextTick();
    const form = document.querySelector('#fwde-savedialog-form') as HTMLFormElement;
    const note = document.querySelector('.fwde-savedialog__history') as HTMLElement;
    expect(note.classList.contains('alert')).toBe(true);
    expect(note.classList.contains('alert-warning')).toBe(true);
    expect(form.lastElementChild).toBe(note);
    const asNew = document.querySelector('.fwde-savedialog__asnew') as HTMLButtonElement;
    const changes = document.querySelector('.fwde-savedialog__changes') as HTMLButtonElement;
    expect([asNew.type, asNew.classList.contains('btn')]).toEqual(['submit', true]);
    expect([changes.type, changes.classList.contains('btn')]).toEqual(['button', false]);
    form.dispatchEvent(new Event('submit', { cancelable: true }));
    changes.click();
    expect((wrapper.emitted('save') || []).map((args) => (args[0] as { asNew: boolean }).asNew)).toEqual([true, false]);
    wrapper.unmount();
  });
});
