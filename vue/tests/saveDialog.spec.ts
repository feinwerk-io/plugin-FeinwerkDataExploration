/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import SaveDialog from '../src/SaveDialog.vue';

vi.mock('CoreHome', () => ({
  translate: (key: string) => key,
}));

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
    (panel()?.querySelector('.fwde-savedialog__deleteno') as HTMLElement).click();
    await settle();
    expect(document.activeElement).toBe(panel()?.querySelector('.fwde-savedialog__deleteask'));
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
    expect(wrapper.emitted('save')).toEqual([[{ name: 'Browsers by device', shared: false, asNew: true }]]);
    await wrapper.setProps({ busy: true });
    await wrapper.setProps({ busy: false });
    await settle();
    expect(panel()).toBeNull();
  });
});
