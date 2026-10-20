/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { SavedExploration } from './api';
import { Limits, parseState, toHashParams } from './state';
import { Catalog } from './types';

/** The page's state parameters a saved exploration holds (spec section 13; not the period). */
export const STATE_KEYS = [
  'fwdeDims', 'fwdeMetrics', 'fwdeGoal', 'fwdeSort', 'fwdeOrder', 'fwdeLimit', 'fwdeFilters',
  'fwdeView', 'fwdeChart',
];

/**
 * The hash that opens a saved exploration: its state (other state keys emptied), its segment and
 * its id. The state is checked like any link when the page reads it.
 */
export function savedHashParams(saved: SavedExploration): Record<string, string> {
  const params: Record<string, string> = {};
  STATE_KEYS.forEach((key) => {
    const value = saved.state[key];
    params[key] = typeof value === 'string' ? value : '';
  });
  params.segment = saved.segment || '';
  params.fwdeSaved = String(saved.id);
  return params;
}

/** The part of the page's hash parameters a save stores. */
export function stateToSave(params: Record<string, string>): Record<string, string> {
  const state: Record<string, string> = {};
  STATE_KEYS.forEach((key) => {
    if (typeof params[key] === 'string' && params[key] !== '') {
      state[key] = params[key];
    }
  });
  return state;
}

/** The user's own explorations and those shared by others, each in the API's order (by name). */
export function groupSaved(
  list: SavedExploration[],
  login: string,
): { mine: SavedExploration[], shared: SavedExploration[] } {
  return {
    mine: list.filter((s) => s.owner === login),
    shared: list.filter((s) => s.owner !== login),
  };
}

/** The saved exploration the link points to (fwdeSaved), or null. */
export function findSaved(list: SavedExploration[], id: string|undefined): SavedExploration|null {
  if (!id) {
    return null;
  }
  return list.find((s) => String(s.id) === String(id)) || null;
}

/**
 * What the save dialog starts with: the open exploration's name and visibility when the user may
 * change it; a copy of someone else's exploration starts private, and so does every exploration
 * of a user who may not share (no write access).
 */
export function dialogDefaults(
  current: SavedExploration|null,
  suggestedName: string,
  canShare: boolean,
): { name: string, shared: boolean } {
  if (current && current.canEdit) {
    return { name: current.name, shared: canShare && current.shared };
  }
  return { name: (current ? current.name : suggestedName).slice(0, 100), shared: false };
}

/**
 * Whether the page shows something other than the saved exploration (a later change, or an older
 * link): both states are read like a link, so defaults and removed dimensions compare alike.
 */
export function differsFromSaved(
  saved: SavedExploration,
  currentParams: Record<string, string>,
  currentSegment: string,
  catalog: Catalog,
  limits: Limits,
): boolean {
  const normalized = (params: Record<string, string>) => JSON.stringify(
    stateToSave(toHashParams(parseState(params, catalog, limits))),
  );
  return normalized(currentParams) !== normalized(saved.state)
    || (saved.segment || '') !== (currentSegment || '');
}

/** A link that names a saved exploration but carries none of its settings (e.g. a bookmark). */
export function linkNamesOnlySaved(hash: Record<string, unknown>): boolean {
  return !!hash.fwdeSaved && STATE_KEYS.every((key) => !hash[key]);
}
