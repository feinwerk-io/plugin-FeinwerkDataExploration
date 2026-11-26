/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import SidePanel from '../src/SidePanel.vue';

const mounted: Array<{ unmount: () => void }> = [];

function mountPanel(open = false, extra: Record<string, unknown> = {}) {
  const wrapper = mount(SidePanel, {
    props: {
      open, title: 'Save exploration', closeLabel: 'Close', ...extra,
    },
    slots: {
      default: '<label>Name <input class="spec-name" type="text"></label><button class="spec-middle">More</button>',
      footer: '<button class="spec-save">Save</button>',
    },
    attachTo: document.body,
  });
  mounted.push(wrapper);
  return wrapper;
}

const panel = () => document.querySelector<HTMLElement>('section.fwde-sidepanel');
const key = (target: Element, name: string, shiftKey = false) => {
  target.dispatchEvent(new KeyboardEvent('keydown', { key: name, shiftKey, bubbles: true }));
};

describe('side panel (UI item 2, self-contained)', () => {
  afterEach(() => {
    // unmounted, so the count of open panels (module state) starts at 0 in every test
    mounted.splice(0).forEach((wrapper) => {
      try {
        wrapper.unmount();
      } catch (e) {
        // unmounted by the test already
      }
    });
    while (document.body.firstChild) {
      document.body.removeChild(document.body.firstChild);
    }
    document.documentElement.classList.remove('fwde-sidepanel-open');
  });

  it('shows the title and the slots only while open', async () => {
    const wrapper = mountPanel(false, { scopeClass: 'fwde-page' });
    expect(panel()).toBeNull();
    await wrapper.setProps({ open: true });
    const section = panel() as HTMLElement;
    expect(section.getAttribute('role')).toBe('dialog');
    expect(section.getAttribute('aria-modal')).toBe('true');
    const title = section.querySelector('h2') as HTMLElement;
    expect(title.textContent).toBe('Save exploration');
    expect(section.getAttribute('aria-labelledby')).toBe(title.id);
    expect(section.classList.contains('fwde-page')).toBe(true);
    expect(section.querySelector('.fwde-sidepanel__body .spec-name')).not.toBeNull();
    expect(section.querySelector('.fwde-sidepanel__footer .spec-save')).not.toBeNull();
    expect(section.querySelector('.fwde-sidepanel__close')?.getAttribute('aria-label')).toBe('Close');
    expect(document.querySelector('.fwde-sidepanel__backdrop')).not.toBeNull();
  });

  it('focuses the first field when it opens and keeps Tab and Shift+Tab inside', async () => {
    const wrapper = mountPanel();
    await wrapper.setProps({ open: true });
    await nextTick();
    const section = panel() as HTMLElement;
    expect(document.activeElement).toBe(section.querySelector('.spec-name'));
    const save = section.querySelector('.spec-save') as HTMLElement;
    const close = section.querySelector('.fwde-sidepanel__close') as HTMLElement;
    save.focus();
    key(save, 'Tab');
    expect(document.activeElement).toBe(close);
    key(close, 'Tab', true);
    expect(document.activeElement).toBe(save);
  });

  // UI review 2026-11-26 F5: a panel whose first control acts on change (the manage panel's menu
  // select) opens with the focus on the dialog itself; Tab then reaches the controls
  it('can open with the focus on the dialog instead of its first control', async () => {
    const wrapper = mountPanel(false, { initialFocus: 'panel' });
    await wrapper.setProps({ open: true });
    await nextTick();
    const section = panel() as HTMLElement;
    expect(document.activeElement).toBe(section);
  });

  it('Escape and Tab still work when the focused control is gone (removed from the page)', async () => {
    const wrapper = mountPanel(true);
    await nextTick();
    const middle = panel()?.querySelector('.spec-middle') as HTMLElement;
    middle.focus();
    middle.remove();
    expect(panel()?.contains(document.activeElement)).toBe(false);
    key(document.body, 'Tab');
    expect(document.activeElement).toBe(panel()?.querySelector('.fwde-sidepanel__close'));
    (document.activeElement as HTMLElement).blur();
    key(document.body, 'Tab', true);
    expect(document.activeElement).toBe(panel()?.querySelector('.spec-save'));
    (document.activeElement as HTMLElement).blur();
    key(document.body, 'Escape');
    expect(wrapper.emitted('update:open')).toEqual([[false]]);
  });

  it('the trap skips buttons that are not tab stops (a switch\'s other options)', async () => {
    mounted.push(mount(SidePanel, {
      props: { open: true, title: 'Filters', closeLabel: 'Close' },
      slots: { default: '<input class="spec-only" type="text"><button class="spec-roving" tabindex="-1">Any</button>' },
      attachTo: document.body,
    }));
    await nextTick();
    await nextTick();
    const only = panel()?.querySelector('.spec-only') as HTMLElement;
    expect(document.activeElement).toBe(only);
    key(only, 'Tab');
    expect(document.activeElement).toBe(panel()?.querySelector('.fwde-sidepanel__close'));
  });

  it('Escape, the backdrop and the close button ask to close', async () => {
    const wrapper = mountPanel(true);
    await nextTick();
    key(panel() as HTMLElement, 'Escape');
    (document.querySelector('.fwde-sidepanel__backdrop') as HTMLElement).click();
    (document.querySelector('.fwde-sidepanel__close') as HTMLElement).click();
    expect(wrapper.emitted('update:open')).toEqual([[false], [false], [false]]);
  });

  it('returns the focus to the element that had it before', async () => {
    const opener = document.createElement('button');
    opener.textContent = 'Save';
    document.body.appendChild(opener);
    opener.focus();
    const wrapper = mountPanel();
    await wrapper.setProps({ open: true });
    await nextTick();
    expect(document.activeElement).not.toBe(opener);
    await wrapper.setProps({ open: false });
    await nextTick();
    expect(document.activeElement).toBe(opener);
  });

  it('locks the page scroll while open and releases it when closed or unmounted', async () => {
    const root = document.documentElement;
    const wrapper = mountPanel();
    expect(root.classList.contains('fwde-sidepanel-open')).toBe(false);
    await wrapper.setProps({ open: true });
    expect(root.classList.contains('fwde-sidepanel-open')).toBe(true);
    await wrapper.setProps({ open: false });
    expect(root.classList.contains('fwde-sidepanel-open')).toBe(false);
    await wrapper.setProps({ open: true });
    const second = mountPanel(true);
    await nextTick();
    second.unmount();
    expect(root.classList.contains('fwde-sidepanel-open')).toBe(true);
    wrapper.unmount();
    expect(root.classList.contains('fwde-sidepanel-open')).toBe(false);
  });
});
