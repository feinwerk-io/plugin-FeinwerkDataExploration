/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { flushPromises } from '@vue/test-utils';
import {
  addSaved, deleteSaved, setSavedMenu, updateSaved,
} from '../src/api';

const { post, reload } = vi.hoisted(() => ({
  post: vi.fn((): Promise<unknown> => Promise.resolve({ id: 3 })),
  reload: vi.fn((): Promise<unknown> => Promise.resolve([])),
}));

vi.mock('CoreHome', () => ({
  AjaxHelper: { post },
  Matomo: {},
  ReportingMenuStore: { reloadMenuItems: reload },
}));

const fields = {
  name: 'Team view', state: { fwdeDims: 'browser' }, segment: '', shared: true,
};

// UI review 2026-11-24 (F2): saved explorations have menu entries of their own, so Matomo's menu is
// read again after each change, as Goals does after a goal changes
describe('Matomo\'s menu after a change to a saved exploration', () => {
  beforeEach(() => {
    post.mockClear();
    reload.mockClear();
  });

  it('is reloaded after saving, placing and deleting', async () => {
    expect(await addSaved(fields)).toEqual({ id: 3 });
    await updateSaved(3, fields);
    await setSavedMenu(3, 'Goals_Goals');
    await deleteSaved(3);
    await flushPromises();
    expect(post).toHaveBeenCalledTimes(4);
    expect(reload).toHaveBeenCalledTimes(4);
  });

  it('is left alone when the change fails', async () => {
    post.mockImplementationOnce(() => Promise.reject(new Error('Not allowed')));
    await expect(deleteSaved(3)).rejects.toThrow('Not allowed');
    await flushPromises();
    expect(reload).not.toHaveBeenCalled();
  });

  it('never fails the change when the menu cannot be reloaded', async () => {
    reload.mockImplementationOnce(() => Promise.reject(new Error('offline')));
    await expect(setSavedMenu(3, '')).resolves.toEqual({ id: 3 });
    await flushPromises();
  });
});
