/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { AjaxHelper, Matomo } from 'CoreHome';
import {
  Catalog, DimensionInfo, ExplorationState, MetricInfo,
} from './types';
import { toDefinition } from './state';

export interface Goal {
  id: number;
  name: string;
}

export type ResultRow = Record<string, string|number>;

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
