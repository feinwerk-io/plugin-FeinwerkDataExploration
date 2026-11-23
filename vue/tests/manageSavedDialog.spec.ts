/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { flushPromises, mount } from '@vue/test-utils';
import ManageSavedDialog from '../src/ManageSavedDialog.vue';
import { SavedExploration } from '../src/api';

vi.mock('CoreHome', () => ({
  translate: (key: string, ...params: string[]) => [key, ...params].join(' '),
}));

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
  const remove = vi.fn((id: number) => Promise.resolve({ id }));

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
    expect(empty.find('.fwde-manage__empty').text()).toBe('FeinwerkDataExploration_ManageSharedEmpty');
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
    const failing = vi.fn(() => Promise.reject(new Error('Not allowed')));
    const wrapper = mount(ManageSavedDialog, { props: { remove: failing, saved: [saved({ id: 8 })] } });
    await wrapper.find('button.fwde-manage__delete').trigger('click');
    await wrapper.find('button.fwde-manage__confirm-yes').trigger('click');
    await flushPromises();
    expect(wrapper.find('.fwde-manage__error').text()).toBe('Not allowed');
    expect(wrapper.emitted('deleted')).toBeUndefined();
  });
});
