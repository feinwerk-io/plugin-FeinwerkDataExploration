/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { AjaxHelper } from 'CoreHome';

/**
 * Suggestions for a filter's value (Peter, 2026-11-25: "felder wo sinnvoll mit
 * autovervollständigen", from Matomo's reports): the server asks the dimension's archived Matomo
 * report (FeinwerkDataExploration.getFilterSuggestions), never the raw data. Here: after a typing
 * pause, from two characters on (the fixed lists from the first, also for an empty field), one
 * request at a time (an outdated one is cancelled), each answer cached for the page. A failing
 * request suggests nothing; the field works as before. In its own module: it needs CoreHome's
 * AjaxHelper only (Matomo 5's spec runner knows no more of CoreHome).
 */

/** the dimensions whose every value the plugin knows (LabelFilterResolver::STATIC_DOMAINS) */
export const FIXED_DIMENSIONS = ['weekday', 'hour', 'visitorType', 'visitConverted', 'channelType'];
/** characters typed before a dimension with a Matomo report is asked */
export const MIN_CHARS = 2;
/** the typing pause in milliseconds */
export const DELAY = 250;

/** the conditions that compare a text: "matches" takes a pattern, "is (not) empty" no value */
const SUGGESTING = [
  'contains', 'notContains', 'equals', 'notEquals', 'startsWith', 'notStartsWith', 'endsWith',
  'notEndsWith',
];

export function suggestsFor(operator: string): boolean {
  return SUGGESTING.includes(operator);
}

export type SuggestionLoader = (
  dimension: string,
  text: string,
  abort: AbortController,
) => Promise<string[]>;

export function loadSuggestions(
  dimension: string,
  text: string,
  abort: AbortController,
): Promise<string[]> {
  return AjaxHelper.fetch<string[]>({
    method: 'FeinwerkDataExploration.getFilterSuggestions',
    dimension,
    text,
  }, { createErrorNotification: false, abortController: abort });
}

export class SuggestionSource {
  private cache = new Map<string, string[]>();

  private timer = 0;

  private pending: AbortController|null = null;

  private readonly load: SuggestionLoader;

  private readonly delay: number;

  constructor(load: SuggestionLoader = loadSuggestions, delay: number = DELAY) {
    this.load = load;
    this.delay = delay;
  }

  /** stops a waiting or running request: its answer never comes (another column, condition) */
  cancel(): void {
    window.clearTimeout(this.timer);
    if (this.pending) {
      this.pending.abort();
      this.pending = null;
    }
  }

  /** answers with the suggestions for the typed text: from the cache at once, else after a pause */
  ask(dimension: string, text: string, answer: (values: string[]) => void): void {
    const typed = text.trim();
    this.cancel();
    if (!FIXED_DIMENSIONS.includes(dimension) && typed.length < MIN_CHARS) {
      answer([]);
      return;
    }
    const key = `${dimension}\u0001${typed.toLowerCase()}`;
    const cached = this.cache.get(key);
    if (cached) {
      answer(cached);
      return;
    }
    this.timer = window.setTimeout(() => {
      const abort = new AbortController();
      this.pending = abort;
      let request: Promise<string[]>;
      try {
        request = this.load(dimension, typed, abort);
      } catch (e) {
        // a loader that throws at once counts as a failed request
        request = Promise.reject(e);
      }
      request.then((values) => {
        if (abort.signal.aborted) {
          return;
        }
        const list = Array.isArray(values) ? values.map(String) : [];
        this.cache.set(key, list);
        this.pending = null;
        answer(list);
      }).catch(() => {
        if (!abort.signal.aborted) {
          this.pending = null;
          answer([]);
        }
      });
    }, this.delay);
  }
}
