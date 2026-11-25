/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import ExplorationToolbar from '../src/ExplorationToolbar.vue';

// Matomo 5 runs the specs with Jest; CoreHome is Matomo's UMD global, so the mock is virtual
jest.mock('CoreHome', () => ({
  translate: (key: string) => key,
  Matomo: {},
  MatomoUrl: { parsed: { value: {} }, stringify: () => '' },
}), { virtual: true });
// Matomo 5's CoreHome declarations for Jest lack `Matomo` (the build has it): the export links are not under test here
jest.mock('../src/exportLink', () => ({ exportUrl: () => '', funnelExportUrl: () => '' }));

describe('toolbar of a page without table state (funnel)', () => {
  it('offers export only with an export link, i.e. once the funnel can run', () => {
    expect(mount(ExplorationToolbar).find('.fwde-menu').exists()).toBe(false);
    const ready = mount(ExplorationToolbar, { props: { exportUrlFor: () => 'https://example.org/x' } });
    expect(ready.find('.fwde-menu').exists()).toBe(true);
    expect(ready.find('.fwde-toolbar__copy').exists()).toBe(true);
    expect(ready.find('.fwde-segmented').exists()).toBe(false);
  });
});

describe('"Copy link" never fails silently (UI review 2026-11-23, F4)', () => {
  const flush = () => new Promise((resolve) => { setTimeout(resolve, 0); });
  const original = Object.getOwnPropertyDescriptor(navigator, 'clipboard');
  const setClipboard = (value: unknown) => {
    Object.defineProperty(navigator, 'clipboard', { value, configurable: true });
  };
  const setCopyCommand = (result: boolean) => {
    const command = jest.fn(() => result);
    Object.defineProperty(document, 'execCommand', { value: command, configurable: true });
    return command;
  };
  const toolbar = () => mount(ExplorationToolbar, {
    props: { exportUrlFor: () => 'https://example.org/x' },
    attachTo: document.body,
  });

  afterEach(() => {
    if (original) {
      Object.defineProperty(navigator, 'clipboard', original);
    } else {
      setClipboard(undefined);
    }
    while (document.body.firstChild) {
      document.body.removeChild(document.body.firstChild);
    }
  });

  it('copies with the clipboard and says so', async () => {
    const writeText = jest.fn(() => Promise.resolve());
    setClipboard({ writeText });
    const wrapper = toolbar();
    await wrapper.find('.fwde-toolbar__copy').trigger('click');
    await flush();
    expect(writeText).toHaveBeenCalledWith(window.location.href);
    expect(wrapper.find('.fwde-toolbar__copy').attributes('title')).toBe('FeinwerkDataExploration_LinkCopied');
    expect(wrapper.find('.fwde-copylink').exists()).toBe(false);
  });

  it('without the clipboard (Matomo over http) it uses the browser\'s copy command', async () => {
    setClipboard(undefined);
    const command = setCopyCommand(true);
    const wrapper = toolbar();
    await wrapper.find('.fwde-toolbar__copy').trigger('click');
    await flush();
    expect(command).toHaveBeenCalledWith('copy');
    expect(wrapper.find('.fwde-toolbar__copy').attributes('title')).toBe('FeinwerkDataExploration_LinkCopied');
    expect(document.querySelectorAll('textarea')).toHaveLength(0);
  });

  it('when the browser refuses both, it shows the link selected for copying by hand', async () => {
    setClipboard({ writeText: () => Promise.reject(new Error('denied')) });
    setCopyCommand(false);
    const wrapper = toolbar();
    await wrapper.find('.fwde-toolbar__copy').trigger('click');
    await flush();
    await wrapper.vm.$nextTick();
    expect(wrapper.find('.fwde-toolbar__copy').attributes('title')).toBe('FeinwerkDataExploration_CopyLink');
    const box = wrapper.find('.fwde-copylink');
    expect(box.text()).toContain('FeinwerkDataExploration_CopyLinkByHand');
    const input = box.find('input');
    expect((input.element as HTMLInputElement).value).toBe(window.location.href);
    expect(input.attributes('readonly')).toBeDefined();
    expect(document.activeElement).toBe(input.element);
    await input.trigger('keydown', { key: 'Escape' });
    expect(wrapper.find('.fwde-copylink').exists()).toBe(false);
  });
});
