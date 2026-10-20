/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  dialogDefaults, differsFromSaved, findSaved, groupSaved, linkNamesOnlySaved, savedHashParams,
  stateToSave, STATE_KEYS,
} from '../src/saved';
import { SavedExploration } from '../src/api';
import { parseState, toHashParams } from '../src/state';
import { CATALOG, LIMITS } from './fixtures';

const saved = (id: number, owner: string, shared = false): SavedExploration => ({
  id,
  name: `Exploration ${id}`,
  shared,
  owner,
  canEdit: owner === 'anna',
  state: { fwdeDims: 'browser', fwdeMetrics: 'nb_visits', fwdeView: 'chart' },
  segment: id === 2 ? 'browserCode==FF' : '',
});

describe('savedHashParams', () => {
  it('opens a saved exploration: its state, its segment and its id, every other state key emptied', () => {
    const params = savedHashParams(saved(2, 'bert', true));
    expect(params.fwdeDims).toBe('browser');
    expect(params.fwdeView).toBe('chart');
    expect(params.fwdeFilters).toBe('');
    expect(params.segment).toBe('browserCode==FF');
    expect(params.fwdeSaved).toBe('2');
    expect(Object.keys(params).sort()).toEqual([...STATE_KEYS, 'fwdeSaved', 'segment'].sort());
  });

  it('ignores keys and values a saved exploration must not set', () => {
    const odd = { ...saved(3, 'anna'), state: { fwdeDims: 'browser', idSite: '7' } as Record<string, string> };
    expect(savedHashParams(odd).idSite).toBeUndefined();
  });
});

describe('stateToSave', () => {
  it('keeps the state keys of the page only', () => {
    expect(stateToSave({
      fwdeDims: 'country', fwdeMetrics: 'nb_visits', fwdeSaved: '4', segment: 'x', period: 'day',
    })).toEqual({ fwdeDims: 'country', fwdeMetrics: 'nb_visits' });
  });
});

describe('groupSaved', () => {
  it('puts the user\'s own explorations first and those shared by others second', () => {
    const groups = groupSaved([saved(1, 'bert', true), saved(2, 'anna'), saved(3, 'anna', true)], 'anna');
    expect(groups.mine.map((s) => s.id)).toEqual([2, 3]);
    expect(groups.shared.map((s) => s.id)).toEqual([1]);
  });
});

describe('findSaved', () => {
  it('finds the open exploration by the id in the link', () => {
    const list = [saved(1, 'anna'), saved(2, 'bert', true)];
    expect(findSaved(list, '2')?.id).toBe(2);
    expect(findSaved(list, '')).toBeNull();
    expect(findSaved(list, '9')).toBeNull();
  });
});

describe('dialogDefaults', () => {
  it('starts with the open exploration\'s name and visibility when the user may change it', () => {
    expect(dialogDefaults(saved(3, 'anna', true), 'Browser', true))
      .toEqual({ name: 'Exploration 3', shared: true });
  });

  it('starts a copy of someone else\'s exploration as private', () => {
    expect(dialogDefaults(saved(1, 'bert', true), 'Browser', true))
      .toEqual({ name: 'Exploration 1', shared: false });
  });

  it('suggests the result title for a new exploration, at most 100 characters', () => {
    expect(dialogDefaults(null, 'x'.repeat(120), true)).toEqual({ name: 'x'.repeat(100), shared: false });
  });

  it('starts private for users who may not share (no write access)', () => {
    expect(dialogDefaults(saved(3, 'anna', true), 'Browser', false))
      .toEqual({ name: 'Exploration 3', shared: false });
  });
});

describe('differsFromSaved', () => {
  const opened = (s: SavedExploration) => toHashParams(parseState(s.state, CATALOG, LIMITS));

  it('is false right after opening a saved exploration with its segment', () => {
    const s = saved(2, 'bert', true);
    expect(differsFromSaved(s, opened(s), 'browserCode==FF', CATALOG, LIMITS)).toBe(false);
  });

  it('is true when a setting or the segment differs', () => {
    const s = saved(2, 'bert', true);
    expect(differsFromSaved(s, { ...opened(s), fwdeMetrics: 'nb_actions' }, 'browserCode==FF', CATALOG, LIMITS))
      .toBe(true);
    expect(differsFromSaved(s, opened(s), '', CATALOG, LIMITS)).toBe(true);
  });
});

describe('linkNamesOnlySaved', () => {
  it('is true for a link with a saved exploration and no settings of its own', () => {
    expect(linkNamesOnlySaved({ fwdeSaved: '4', idSite: '1', period: 'day' })).toBe(true);
    expect(linkNamesOnlySaved({ fwdeSaved: '4', fwdeDims: 'browser' })).toBe(false);
    expect(linkNamesOnlySaved({ fwdeSaved: '', idSite: '1' })).toBe(false);
  });
});
