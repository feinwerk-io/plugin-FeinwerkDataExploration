/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { flushPromises, mount } from '@vue/test-utils';
import ManageSavedDialog from '../src/ManageSavedDialog.vue';
import { SavedExploration } from '../src/api';

// Matomo 5 runs the specs with Jest; CoreHome is Matomo's UMD global, so the mock is virtual
jest.mock('CoreHome', () => ({
  translate: (key: string, ...params: string[]) => [key, ...params].join(' '),
}), { virtual: true });

const saved = (fields: Partial<SavedExploration>): SavedExploration => ({
  id: 1,
  name: 'Name',
  shared: true,
  owner: 'bert',
  canEdit: false,
  canDelete: true,
  kind: 'table',
  updated: '2026-11-19 08:30:00',
  state: {},
  segment: '',
  ...fields,
});

describe('managing shared explorations (design 2026-11-20)', () => {
  const remove = jest.fn((id: number) => Promise.resolve({ id }));

  // Peter, 2026-11-24 ("B"): admins may change a shared exploration's menu place
  it('changes the menu place of a shared exploration', async () => {
    const place = jest.fn(() => Promise.resolve({}));
    const wrapper = mount(ManageSavedDialog, {
      props: {
        remove,
        place,
        menuSections: [{ id: 'Goals_Goals', name: 'Goals' }],
        saved: [
          saved({ id: 4, name: 'Team view', canPlace: true, menu: null }),
          saved({ id: 5, name: 'Read only', canPlace: false, menu: 'Goals_Goals' }),
        ],
      },
    });
    const selects = wrapper.findAll('select.fwde-manage__menu');
    expect(selects.length).toBe(1);
    expect(wrapper.find('tr[data-id="5"]').text()).toContain('Goals');
    await selects[0].setValue('Goals_Goals');
    await flushPromises();
    expect(place).toHaveBeenCalledWith(4, 'Goals_Goals');
    expect(wrapper.emitted('placed')).toEqual([[4]]);
  });

  it('lists only the shared explorations with owner, kind and last change', () => {
    const wrapper = mount(ManageSavedDialog, {
      props: {
        remove,
        saved: [
          saved({ id: 1, name: 'Team view' }),
          saved({ id: 2, name: 'Mine', shared: false }),
          saved({ id: 3, name: 'Funnel of the shop', kind: 'funnel', owner: 'carla' }),
        ],
      },
    });
    const rows = wrapper.findAll('tbody tr');
    expect(rows.map((row) => row.find('.fwde-manage__name').text())).toEqual(['Team view', 'Funnel of the shop']);
    expect(rows[1].text()).toContain('carla');
    expect(rows[1].text()).toContain('FeinwerkDataExploration_Funnel');
    expect(rows[0].text()).toContain('2026-11-19');
  });

  // UI review 2026-11-24 (F3): a KPI card is named as such and has no menu place
  it('names a KPI card and offers it no menu place', () => {
    const wrapper = mount(ManageSavedDialog, {
      props: {
        remove,
        place: jest.fn(() => Promise.resolve({})),
        menuSections: [{ id: 'Goals_Goals', name: 'Goals' }],
        saved: [saved({ id: 6, name: 'Visits today', kind: 'kpi', canPlace: true, menu: null })],
      },
    });
    const row = wrapper.find('tr[data-id="6"]');
    expect(row.find('.fwde-manage__kind').text()).toBe('FeinwerkDataExploration_SaveAsKpiCard');
    expect(row.find('select.fwde-manage__menu').exists()).toBe(false);
    expect(row.text()).not.toContain('FeinwerkDataExploration_MenuNone');
  });

  // UI review 2026-11-24 (F4): the confirmation says where else it disappears, except for a funnel
  it('says that a delete also takes it from the menu, dashboards and email reports', async () => {
    const wrapper = mount(ManageSavedDialog, {
      props: {
        remove,
        saved: [saved({ id: 7, name: 'Team view' }), saved({ id: 9, name: 'Steps', kind: 'funnel' })],
      },
    });
    await wrapper.find('tr[data-id="7"] button.fwde-manage__delete').trigger('click');
    expect(wrapper.find('tr[data-id="7"] .fwde-manage__confirm').text())
      .toContain('FeinwerkDataExploration_DeleteAlsoRemoves');
    await wrapper.find('tr[data-id="9"] button.fwde-manage__delete').trigger('click');
    expect(wrapper.find('tr[data-id="9"] .fwde-manage__confirm').text())
      .not.toContain('FeinwerkDataExploration_DeleteAlsoRemoves');
  });

  it('asks before deleting for everyone, then deletes and tells the page', async () => {
    const wrapper = mount(ManageSavedDialog, { props: { remove, saved: [saved({ id: 7, name: 'Team view' })] } });
    await wrapper.find('button.fwde-manage__delete').trigger('click');
    expect(remove).not.toHaveBeenCalled();
    expect(wrapper.find('.fwde-manage__confirm').text()).toContain('FeinwerkDataExploration_ManageSharedConfirm Team view');
    await wrapper.find('button.fwde-manage__confirm-yes').trigger('click');
    await flushPromises();
    expect(remove).toHaveBeenCalledWith(7);
    expect(wrapper.emitted('deleted')).toEqual([[7]]);
  });

  it('offers no delete where the user may not, and says when there is nothing to manage', () => {
    const readOnly = mount(ManageSavedDialog, { props: { remove, saved: [saved({ canDelete: false })] } });
    expect(readOnly.find('button.fwde-manage__delete').exists()).toBe(false);
    const empty = mount(ManageSavedDialog, { props: { remove, saved: [saved({ shared: false })] } });
    expect(empty.find('.fwde-manage__empty').text()).toBe('FeinwerkDataExploration_ManageEmpty');
  });

  it('Delete moves the focus to its confirmation, and Cancel back to Delete', async () => {
    const wrapper = mount(ManageSavedDialog, {
      props: { remove, saved: [saved({ id: 7, name: 'Team view' })] },
      attachTo: document.body,
    });
    await wrapper.find('button.fwde-manage__delete').trigger('click');
    await wrapper.vm.$nextTick();
    expect(document.activeElement).toBe(wrapper.find('button.fwde-manage__confirm-yes').element);
    await wrapper.find('button.fwde-manage__confirm-no').trigger('click');
    await wrapper.vm.$nextTick();
    expect(document.activeElement).toBe(wrapper.find('button.fwde-manage__delete').element);
    wrapper.unmount();
  });

  it('shows why a delete failed', async () => {
    const failing = jest.fn((id: number) => Promise.reject(new Error(`Not allowed ${id}`)));
    const wrapper = mount(ManageSavedDialog, { props: { remove: failing, saved: [saved({ id: 8 })] } });
    await wrapper.find('button.fwde-manage__delete').trigger('click');
    await wrapper.find('button.fwde-manage__confirm-yes').trigger('click');
    await flushPromises();
    expect(wrapper.find('.fwde-manage__error').text()).toBe('Not allowed 8');
    expect(wrapper.emitted('deleted')).toBeUndefined();
  });
});

// Peter, 2026-11-25: "Das delete über den save button ist so überhaupt nicht praktisch": the panel
// lists the user's own explorations too, private ones included, each with Delete
describe('managing my own explorations', () => {
  const remove = jest.fn((id: number) => Promise.resolve({ id }));
  const list = [
    saved({ id: 2, name: 'Mine', owner: 'anna', shared: false }),
    saved({ id: 3, name: 'Mine for the team', owner: 'anna' }),
    saved({ id: 4, name: 'Team view', owner: 'ben' }),
    saved({ id: 5, name: 'Read only', owner: 'carla', canDelete: false, canPlace: false }),
  ];

  it('lists my explorations, private and shared, apart from the ones shared by others', () => {
    const wrapper = mount(ManageSavedDialog, { props: { remove, login: 'anna', saved: list } });
    const ids = (selector: string) => wrapper.findAll(`${selector} tbody tr`).map((row) => row.attributes('data-id'));
    expect(ids('.fwde-manage__mine')).toEqual(['2', '3']);
    expect(ids('.fwde-manage__others')).toEqual(['4']);
    expect(wrapper.find('.fwde-manage__mine .fwde-manage__heading').text()).toBe('FeinwerkDataExploration_MyExplorations');
    expect(wrapper.find('.fwde-manage__others .fwde-manage__heading').text())
      .toBe('FeinwerkDataExploration_SharedByOthers');
    expect(wrapper.find('tr[data-id="2"] .fwde-manage__kind').text()).toBe('FeinwerkDataExploration_FreeFormTable');
    expect(wrapper.find('tr[data-id="3"] .fwde-manage__kind').text())
      .toBe('FeinwerkDataExploration_SavedShared FeinwerkDataExploration_FreeFormTable');
    expect(wrapper.find('.fwde-manage__mine').text()).not.toContain('FeinwerkDataExploration_ColumnOwner');
    expect(wrapper.find('tr[data-id="4"]').text()).toContain('ben');
    expect(wrapper.findAll('.fwde-manage__mine button.fwde-manage__delete').length).toBe(2);
  });

  it('a private one is deleted after a plain question, a shared one "for everyone"', async () => {
    const wrapper = mount(ManageSavedDialog, { props: { remove, login: 'anna', saved: list } });
    await wrapper.find('tr[data-id="2"] button.fwde-manage__delete').trigger('click');
    const question = wrapper.find('tr[data-id="2"] .fwde-manage__confirm').text();
    expect(question).toContain('FeinwerkDataExploration_SavedDeleteConfirm Mine');
    expect(question).not.toContain('FeinwerkDataExploration_ManageSharedConfirm');
    await wrapper.find('tr[data-id="2"] button.fwde-manage__confirm-yes').trigger('click');
    await flushPromises();
    expect(remove).toHaveBeenCalledWith(2);
    expect(wrapper.emitted('deleted')).toEqual([[2]]);
    await wrapper.find('tr[data-id="3"] button.fwde-manage__delete').trigger('click');
    expect(wrapper.find('tr[data-id="3"] .fwde-manage__confirm').text())
      .toContain('FeinwerkDataExploration_ManageSharedConfirm Mine for the team');
  });

  it('says there is nothing to manage when nothing is mine and nothing shared may be changed', () => {
    const wrapper = mount(ManageSavedDialog, { props: { remove, login: 'dora', saved: [list[3]] } });
    expect(wrapper.find('.fwde-manage__empty').text()).toBe('FeinwerkDataExploration_ManageEmpty');
    expect(wrapper.find('table').exists()).toBe(false);
  });
});
