/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { SavedExploration } from './api';
import { Limits, parseState, toHashParams } from './state';
import { Catalog } from './types';
import { parseSteps, toStepsParam } from './funnelState';

/** The page's state parameters a saved exploration holds (spec section 13; not the period). */
export type SavedKind = 'table'|'funnel';

/** The hash keys of each kind of exploration. */
const KIND_KEYS: Record<SavedKind, string[]> = {
  table: [
    'fwdeDims', 'fwdeMetrics', 'fwdeGoal', 'fwdeSort', 'fwdeOrder', 'fwdeLimit', 'fwdeFilters',
    'fwdeView', 'fwdeChart', 'fwdeChartType', 'fwdeMatch',
  ],
  funnel: ['fwdeSteps'],
};

/** Every key a saved exploration may set (fwdeKind: 'funnel', absent for tables). */
export const STATE_KEYS = [...KIND_KEYS.table, 'fwdeKind', ...KIND_KEYS.funnel];

/** The page (Matomo subcategory) of each kind. */
export const SUBCATEGORY: Record<SavedKind, string> = {
  table: 'FeinwerkDataExploration_FreeFormTable',
  funnel: 'FeinwerkDataExploration_Funnel',
};

export function savedKind(saved: SavedExploration): SavedKind {
  return saved.state.fwdeKind === 'funnel' ? 'funnel' : 'table';
}

/**
 * The hash that opens a saved exploration on its page: the state of its kind (every other state
 * key emptied), its segment, its id and the page. The state is checked like any link when the page
 * reads it.
 */
export function savedHashParams(saved: SavedExploration): Record<string, string> {
  const kind = savedKind(saved);
  const params: Record<string, string> = {};
  STATE_KEYS.forEach((key) => {
    const value = saved.state[key];
    params[key] = KIND_KEYS[kind].includes(key) && typeof value === 'string' ? value : '';
  });
  params.segment = saved.segment || '';
  params.fwdeSaved = String(saved.id);
  params.subcategory = SUBCATEGORY[kind];
  return params;
}

/** The part of the page's hash parameters a save stores; a funnel also stores its kind. */
export function stateToSave(
  params: Record<string, string>,
  kind: SavedKind = 'table',
): Record<string, string> {
  const state: Record<string, string> = kind === 'funnel' ? { fwdeKind: 'funnel' } : {};
  KIND_KEYS[kind].forEach((key) => {
    if (typeof params[key] === 'string' && params[key] !== '') {
      state[key] = params[key];
    }
  });
  return state;
}

/**
 * Whether the funnel page shows other steps or another segment than the saved funnel: both step
 * lists are read like a link.
 */
export function funnelDiffersFromSaved(
  saved: SavedExploration,
  currentSteps: string,
  currentSegment: string,
  goalIds: number[],
  maxSteps: number,
): boolean {
  const normalized = (value: unknown) => toStepsParam(parseSteps(value, goalIds, maxSteps));
  return normalized(currentSteps) !== normalized(saved.state.fwdeSteps)
    || (saved.segment || '') !== (currentSegment || '');
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

/**
 * The saved exploration the link points to (fwdeSaved), or null; with a kind, only one of that
 * kind (Matomo's menu keeps the hash, so a table's id can reach the funnel page and the reverse).
 */
export function findSaved(
  list: SavedExploration[],
  id: string|undefined,
  kind: SavedKind|null = null,
): SavedExploration|null {
  if (!id) {
    return null;
  }
  const found = list.find((s) => String(s.id) === String(id)) || null;
  return found && kind && savedKind(found) !== kind ? null : found;
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

/**
 * Matomo's "copy to other websites" (CoreHome's entity duplicator, Matomo 5.14 and
 * newer): the action, the dialog and the store builder, or null on an older Matomo
 * (the 5.x line allows 5.12), where the plugin shows no copy action.
 */
export interface CopySupport {
  action: unknown;
  modal: unknown;
  buildStore: (entityType: string, adapter: { method: string }) => unknown;
}

export function copySupport(core: Record<string, unknown>): CopySupport | null {
  const store = core.EntityDuplicatorStore as { buildStoreInstance?: unknown } | undefined;
  if (!core.EntityDuplicatorAction || !core.EntityDuplicatorModal || !store
    || typeof store.buildStoreInstance !== 'function') {
    return null;
  }
  const build = store.buildStoreInstance as CopySupport['buildStore'];
  return {
    action: core.EntityDuplicatorAction,
    modal: core.EntityDuplicatorModal,
    buildStore: (entityType, adapter) => build.call(store, entityType, adapter),
  };
}
