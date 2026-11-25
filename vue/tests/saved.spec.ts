/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  dialogDefaults, differsFromSaved, findSaved, funnelDiffersFromSaved, groupSaved, linkNamesOnlySaved,
  savedHashParams, savedKind, stateToSave, STATE_KEYS, withMenu,
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
  canDelete: owner === 'anna',
  kind: 'table',
  updated: '2026-11-19 08:30:00',
  state: { fwdeDims: 'browser', fwdeMetrics: 'nb_visits', fwdeView: 'chart' },
  segment: id === 2 ? 'browserCode==FF' : '',
});

describe('savedHashParams, chart type', () => {
  it('saves and opens the chart type', () => {
    const trend = { ...saved(3, 'anna'), state: { fwdeDims: 'browser', fwdeChartType: 'trend' } };
    expect(savedHashParams(trend).fwdeChartType).toBe('trend');
    expect(STATE_KEYS).toContain('fwdeChartType');
  });
});

describe('savedHashParams', () => {
  it('opens a saved exploration: its state, its segment and its id, every other state key emptied', () => {
    const params = savedHashParams(saved(2, 'bert', true));
    expect(params.fwdeDims).toBe('browser');
    expect(params.fwdeView).toBe('chart');
    expect(params.fwdeFilters).toBe('');
    expect(params.segment).toBe('browserCode==FF');
    expect(params.fwdeSaved).toBe('2');
    expect(Object.keys(params).sort()).toEqual([...STATE_KEYS, 'fwdeSaved', 'segment', 'subcategory'].sort());
    expect(params.subcategory).toBe('FeinwerkDataExploration_FreeFormTable');
    expect(params.fwdeSteps).toBe('');
  });

  it('a funnel opens the funnel page with its steps and empties the table keys', () => {
    const funnel = {
      ...saved(5, 'anna'),
      state: { fwdeKind: 'funnel', fwdeSteps: 'pageUrl~contains~%2Fa;anyGoal~~', fwdeDims: 'browser' },
    };
    expect(savedKind(funnel)).toBe('funnel');
    expect(savedKind(saved(1, 'anna'))).toBe('table');
    const params = savedHashParams(funnel);
    expect(params.subcategory).toBe('FeinwerkDataExploration_Funnel');
    expect(params.fwdeSteps).toBe('pageUrl~contains~%2Fa;anyGoal~~');
    expect(params.fwdeDims).toBe('');
    expect(params.fwdeKind).toBe('');
    expect(params.fwdeSaved).toBe('5');
  });

  it('ignores keys and values a saved exploration must not set', () => {
    const odd = { ...saved(3, 'anna'), state: { fwdeDims: 'browser', idSite: '7' } as Record<string, string> };
    expect(savedHashParams(odd).idSite).toBeUndefined();
  });
});

describe('stateToSave', () => {
  it('keeps the state keys of the page only', () => {
    expect(stateToSave({
      fwdeDims: 'country', fwdeMetrics: 'nb_visits', fwdeSaved: '4', segment: 'x', period: 'day', fwdeSteps: 'anyGoal~~',
    })).toEqual({ fwdeDims: 'country', fwdeMetrics: 'nb_visits' });
  });

  it('a funnel saves its kind and steps only', () => {
    expect(stateToSave({ fwdeSteps: 'anyGoal~~;anyGoal~~', fwdeDims: 'country' }, 'funnel'))
      .toEqual({ fwdeKind: 'funnel', fwdeSteps: 'anyGoal~~;anyGoal~~' });
  });
});

describe('funnelDiffersFromSaved', () => {
  const funnel = {
    ...saved(6, 'anna'),
    segment: '',
    state: { fwdeKind: 'funnel', fwdeSteps: 'pageUrl~contains~%2Fa;goal~~1' },
  };
  it('compares the steps as the page reads them, and the segment', () => {
    expect(funnelDiffersFromSaved(funnel, 'pageUrl~contains~%2Fa;goal~~1', '', [1], 3)).toBe(false);
    expect(funnelDiffersFromSaved(funnel, 'pageUrl~contains~%2Fb;goal~~1', '', [1], 3)).toBe(true);
    expect(funnelDiffersFromSaved(funnel, 'pageUrl~contains~%2Fa;goal~~1', 'browserCode==FF', [1], 3)).toBe(true);
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
  it('a page takes only saved explorations of its own kind as the open one', () => {
    const funnel = { ...saved(5, 'anna'), state: { fwdeKind: 'funnel', fwdeSteps: 'anyGoal~~;anyGoal~~' } };
    const list = [saved(1, 'anna'), funnel];
    expect(findSaved(list, '1', 'funnel')).toBeNull();
    expect(findSaved(list, '5', 'funnel')?.id).toBe(5);
    expect(findSaved(list, '5', 'table')).toBeNull();
    expect(findSaved(list, '1', 'table')?.id).toBe(1);
  });

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
      .toEqual({ name: 'Exploration 3', shared: true, menu: '' });
  });

  it('starts a copy of someone else\'s exploration as private', () => {
    expect(dialogDefaults(saved(1, 'bert', true), 'Browser', true))
      .toEqual({ name: 'Exploration 1', shared: false, menu: 'FeinwerkDataExploration_Explorations' });
  });

  it('suggests the result title for a new exploration, at most 100 characters', () => {
    expect(dialogDefaults(null, 'x'.repeat(120), true))
      .toEqual({ name: 'x'.repeat(100), shared: false, menu: 'FeinwerkDataExploration_Explorations' });
  });

  it('starts private for users who may not share (no write access)', () => {
    expect(dialogDefaults(saved(3, 'anna', true), 'Browser', false))
      .toEqual({ name: 'Exploration 3', shared: false, menu: '' });
  });

  // saved reports (2026-11-24): new saves go into Explorations, an own one keeps its place
  it('keeps the menu place of an own exploration', () => {
    expect(dialogDefaults({ ...saved(3, 'anna'), menu: 'Goals_Goals' }, 'Browser', true).menu)
      .toBe('Goals_Goals');
  });
});

describe('menu place in the saved state (saved reports, 2026-11-24)', () => {
  it('goes into the saved state, never into the link', () => {
    expect(withMenu({ fwdeDims: 'browser' }, 'Goals_Goals'))
      .toEqual({ fwdeDims: 'browser', fwdeMenu: 'Goals_Goals' });
    expect(withMenu({ fwdeDims: 'browser' }, '')).toEqual({ fwdeDims: 'browser' });
    const placed = { ...saved(3, 'anna'), state: { fwdeDims: 'browser', fwdeMenu: 'Goals_Goals' } };
    expect(savedHashParams(placed)).not.toHaveProperty('fwdeMenu');
    expect(STATE_KEYS).not.toContain('fwdeMenu');
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
