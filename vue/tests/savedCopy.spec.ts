/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import SavedPicker from '../src/SavedPicker.vue';
import { copySupport } from '../src/saved';

// Matomo 5 runs the specs with Jest; CoreHome is Matomo's UMD global, so the mock is virtual. __esModule keeps
// `import * as CoreHome` on the same object, so the tests can add and remove exports.
jest.mock('CoreHome', () => ({ __esModule: true, translate: (key: string) => key }), { virtual: true });
const core = jest.requireMock('CoreHome') as Record<string, unknown>;
// the list's delete call (manage dialog) comes from api.ts; mocked, so Jest does not type-check api.ts against the
// small CoreHome mock
jest.mock('../src/api', () => ({
  deleteSaved: jest.fn(() => Promise.resolve({})),
  errorText: (e: unknown) => String(e),
}));

const saved = [{
  id: 7, name: 'Browsers', shared: false, owner: 'anna', canEdit: true, state: {}, segment: '',
}];

function picker(canCopy = true) {
  return mount(SavedPicker, {
    props: {
      saved, current: saved[0], login: 'anna', canCopy,
    },
  });
}

describe('copy to other websites (Matomo 5.14+)', () => {
  it('needs all three parts of the core duplicator', () => {
    expect(copySupport({})).toBeNull();
    expect(copySupport({ EntityDuplicatorAction: {}, EntityDuplicatorModal: {} })).toBeNull();
    const parts = {
      EntityDuplicatorAction: {},
      EntityDuplicatorModal: {},
      EntityDuplicatorStore: { buildStoreInstance: () => ({}) },
    };
    expect(copySupport(parts)).not.toBeNull();
  });

  it('copy action is absent without the core component', () => {
    // Matomo before 5.14: CoreHome has no such exports (undefined, as the UMD global returns them)
    core.EntityDuplicatorAction = undefined;
    core.EntityDuplicatorModal = undefined;
    core.EntityDuplicatorStore = undefined;
    expect(picker().find('.fwde-saved__copy').exists()).toBe(false);
  });

  it('copy action for the open exploration with the core component', () => {
    core.EntityDuplicatorAction = { name: 'EntityDuplicatorAction', props: ['actionFormData'], render: () => null };
    core.EntityDuplicatorModal = { name: 'EntityDuplicatorModal', render: () => null };
    core.EntityDuplicatorStore = { buildStoreInstance: () => ({ state: {} }) };
    const wrapper = picker();
    expect(wrapper.find('.fwde-saved__copy').exists()).toBe(true);
    expect(wrapper.findComponent({ name: 'EntityDuplicatorAction' }).props('actionFormData')).toEqual({ idSaved: 7 });
  });

  it('a saved funnel is named as such in the list', () => {
    const funnel = {
      ...saved[0], id: 8, name: 'Shop', state: { fwdeKind: 'funnel', fwdeSteps: 'anyGoal~~;anyGoal~~' },
    };
    const wrapper = mount(SavedPicker, {
      props: {
        saved: [saved[0], funnel], current: null, login: 'anna', canCopy: false,
      },
    });
    const texts = wrapper.findAll('option').map((o) => o.text());
    expect(texts).toContain('FeinwerkDataExploration_SavedFunnel');
    expect(texts).toContain('Browsers');
  });

  it('copy action is absent without write access on any website', () => {
    expect(picker(false).find('.fwde-saved__copy').exists()).toBe(false);
  });
});

describe('managing shared explorations in the side panel (UI item 3)', () => {
  afterEach(() => {
    while (document.body.firstChild) {
      document.body.removeChild(document.body.firstChild);
    }
    document.documentElement.classList.remove('fwde-sidepanel-open');
  });

  it('the manage link opens the panel with the shared explorations', async () => {
    const shared = [{
      id: 9, name: 'Team view', shared: true, owner: 'ben', canEdit: false, canDelete: true, kind: 'table',
      updated: '2026-11-20 10:00:00', state: {}, segment: '',
    }];
    const wrapper = mount(SavedPicker, {
      props: {
        saved: shared, current: null, login: 'anna', canCopy: false, canManage: true,
      },
      attachTo: document.body,
    });
    expect(document.querySelector('section.fwde-sidepanel')).toBeNull();
    await wrapper.find('.fwde-saved__manage').trigger('click');
    await wrapper.vm.$nextTick();
    const panel = document.querySelector('section.fwde-sidepanel') as HTMLElement;
    expect(panel.querySelector('h2')?.textContent).toBe('FeinwerkDataExploration_ManageExplorations');
    expect(panel.querySelector('.fwde-manage .fwde-manage__name')?.textContent).toBe('Team view');
    expect(wrapper.find('.fwde-saved .fwde-manage').exists()).toBe(false);
    (panel.querySelector('.fwde-sidepanel__close') as HTMLElement).click();
    await wrapper.vm.$nextTick();
    expect(document.querySelector('section.fwde-sidepanel')).toBeNull();
    wrapper.unmount();
  });
});

// Peter, 2026-11-25: everyone manages (and deletes) their own explorations in the panel
describe('the manage link without admin access', () => {
  const own = {
    id: 3, name: 'Mine', shared: false, owner: 'anna', canEdit: true, canDelete: true, kind: 'table' as const,
    updated: '2026-11-20 10:00:00', state: {}, segment: '',
  };

  it('shows for a user with explorations of their own, and not without', () => {
    const mine = mount(SavedPicker, {
      props: {
        saved: [own], current: null, login: 'anna', canCopy: false, canManage: false,
      },
    });
    expect(mine.find('.fwde-saved__manage').text()).toBe('FeinwerkDataExploration_ManageExplorations');
    const none = mount(SavedPicker, {
      props: {
        saved: [{ ...own, owner: 'ben', shared: true, canDelete: false }],
        current: null,
        login: 'anna',
        canCopy: false,
        canManage: false,
      },
    });
    expect(none.find('.fwde-saved__manage').exists()).toBe(false);
  });
});
