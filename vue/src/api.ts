/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { AjaxHelper, Matomo } from 'CoreHome';
import {
  Catalog, ComparisonColumn, DimensionInfo, ExplorationState, FunnelRow, FunnelStepState,
  MetricInfo,
} from './types';
import { toDefinition } from './state';
import { toFunnelDefinition } from './funnelState';

export interface Goal {
  id: number;
  name: string;
}

export type ResultRow = Record<string, string|number>;

/** A row of getTrend: one line (fwde_line) at one day or hour (time); rates may be null. */
export type TrendRow = Record<string, string|number|null> & {
  time: string;
  fwde_line: number;
  fwde_kind?: string;
};

const OPTIONS = { createErrorNotification: false };

/** The API HTML-escapes exception messages. */
export function errorText(error: unknown): string {
  const message = error instanceof Error ? error.message : String(error);
  return Matomo.helper.htmlDecode(message);
}

export function loadCatalog(): Promise<Catalog> {
  return Promise.all([
    AjaxHelper.fetch<DimensionInfo[]>({
      method: 'FeinwerkDataExploration.getAvailableDimensions', filter_limit: -1,
    }, OPTIONS),
    AjaxHelper.fetch<MetricInfo[]>({
      method: 'FeinwerkDataExploration.getAvailableMetrics', filter_limit: -1,
    }, OPTIONS),
  ]).then(([dimensions, metrics]) => ({ dimensions, metrics }));
}

export interface PeriodVisits {
  /** counted only up to one above maxVisits */
  visits: number;
  maxVisits: number;
  timeoutSeconds: number;
}

/** The visits of the period in the URL, with the limits that apply (for the warnings). */
export function loadPeriodVisits(): Promise<PeriodVisits> {
  return AjaxHelper.fetch<PeriodVisits>({
    method: 'FeinwerkDataExploration.getPeriodVisits',
  }, OPTIONS);
}

export interface SuggestedPeriod {
  /** "YYYY-MM-DD,YYYY-MM-DD" */
  date?: string;
  days?: number;
}

/** The most recent days of the period in the URL that an exploration may read; empty when none. */
export function loadSuggestedPeriod(): Promise<SuggestedPeriod> {
  return AjaxHelper.fetch<SuggestedPeriod>({
    method: 'FeinwerkDataExploration.getSuggestedPeriod',
  }, OPTIONS);
}

/** A saved exploration as the API lists it (own ones and those shared by others). */
export interface SavedExploration {
  id: number;
  name: string;
  shared: boolean;
  owner: string;
  canEdit: boolean;
  /** the owner, super users, and admins of the website for a shared one (design 2026-11-20) */
  canDelete: boolean;
  kind: 'table'|'funnel';
  /** last change, UTC (YYYY-MM-DD HH:MM:SS) */
  updated: string;
  /** the page's state parameters (fwdeDims, fwdeMetrics, ...) */
  state: Record<string, string>;
  segment: string;
}

export function loadSaved(): Promise<SavedExploration[]> {
  return AjaxHelper.fetch<SavedExploration[]>({
    method: 'FeinwerkDataExploration.getSavedExplorations', filter_limit: -1,
  }, OPTIONS).then((list) => (Array.isArray(list) ? list : []));
}

/** What a save sends: the stored segment goes as segmentDefinition (Matomo fills "segment"). */
interface SaveFields {
  name: string;
  state: Record<string, string>;
  segment: string;
  shared: boolean;
}

function saveBody(fields: SaveFields): Record<string, string> {
  return {
    name: fields.name,
    state: JSON.stringify(fields.state),
    segmentDefinition: fields.segment,
    shared: fields.shared ? '1' : '0',
  };
}

export function addSaved(fields: SaveFields): Promise<{ id: number }> {
  return AjaxHelper.post<{ id: number }>(
    { method: 'FeinwerkDataExploration.addSavedExploration' },
    saveBody(fields),
    OPTIONS,
  );
}

export function updateSaved(id: number, fields: SaveFields): Promise<unknown> {
  return AjaxHelper.post(
    { method: 'FeinwerkDataExploration.updateSavedExploration' },
    { ...saveBody(fields), idSaved: String(id) },
    OPTIONS,
  );
}

export function deleteSaved(id: number): Promise<unknown> {
  return AjaxHelper.post(
    { method: 'FeinwerkDataExploration.deleteSavedExploration' },
    { idSaved: String(id) },
    OPTIONS,
  );
}

/** The site's goals; an empty list when they cannot be read. */
export function loadGoals(): Promise<Goal[]> {
  return AjaxHelper.fetch<{ idgoal: string|number, name: string }[]>({
    method: 'Goals.getGoals', filter_limit: -1,
  }, OPTIONS)
    .then((goals) => (goals || []).map((g) => ({ id: Number(g.idgoal), name: g.name })))
    .catch(() => []);
}

/**
 * Runs the exploration for the period and segment in the URL. The definition goes into the POST
 * body; idSite, period, date and segment are added by AjaxHelper.
 */
export function runExploration(
  state: ExplorationState,
  abortController: AbortController,
): Promise<ResultRow[]> {
  return AjaxHelper.post<ResultRow[]>(
    { method: 'FeinwerkDataExploration.getExploration', format: 'json', filter_limit: -1 },
    { definition: JSON.stringify(toDefinition(state)) },
    { ...OPTIONS, abortController },
  );
}

/**
 * The exact rest of the cut table ("Others", design 2026-11-20), asked on demand: null when nothing
 * was cut. Period, date and segment from the URL as for runExploration.
 */
export function runOthers(state: ExplorationState): Promise<Record<string, number|null>|null> {
  return AjaxHelper.post<{ others: Record<string, number|null>|null }>(
    { method: 'FeinwerkDataExploration.getOthers', format: 'json' },
    { definition: JSON.stringify(toDefinition(state)) },
    OPTIONS,
  ).then((result) => (result && result.others ? result.others : null));
}

/**
 * Runs the funnel for the period and segment in the URL (POST body as runExploration).
 */
export function runFunnel(
  steps: FunnelStepState[],
  abortController: AbortController,
): Promise<FunnelRow[]> {
  return AjaxHelper.post<FunnelRow[]>(
    { method: 'FeinwerkDataExploration.getFunnel', format: 'json', filter_limit: -1 },
    { definition: JSON.stringify(toFunnelDefinition(steps)) },
    { ...OPTIONS, abortController },
  );
}

/**
 * The exploration with one comparison column (…__c1) and, with it, the total row and the own
 * view's shares; the page asks for each column separately.
 */
export function runComparison(
  state: ExplorationState,
  column: ComparisonColumn,
  abortController: AbortController,
): Promise<ResultRow[]> {
  return AjaxHelper.post<ResultRow[]>(
    { method: 'FeinwerkDataExploration.getExploration', format: 'json', filter_limit: -1 },
    {
      definition: JSON.stringify(toDefinition(state)),
      comparisons: JSON.stringify([
        { segment: column.segment, period: column.period, date: column.date },
      ]),
    },
    { ...OPTIONS, abortController },
  );
}

/**
 * The trend (trend design): without a column the first rows of the table per day or hour; with a
 * comparison column the own total (line 0) and that column's total (line 1).
 */
export function runTrend(
  state: ExplorationState,
  column: ComparisonColumn|null,
  abortController: AbortController,
): Promise<TrendRow[]> {
  return AjaxHelper.post<TrendRow[]>(
    { method: 'FeinwerkDataExploration.getTrend', format: 'json', filter_limit: -1 },
    {
      definition: JSON.stringify(toDefinition(state)),
      comparisons: column === null ? '' : JSON.stringify([
        { segment: column.segment, period: column.period, date: column.date },
      ]),
    },
    { ...OPTIONS, abortController },
  );
}
