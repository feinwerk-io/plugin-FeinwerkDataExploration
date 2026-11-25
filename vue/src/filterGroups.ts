/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  ExplorationState, FilterMatch, FilterOperator, RowFilter,
} from './types';
import { isRate } from './metricMeta';
import { labelColumns } from './labels';

/** conditions in all groups together (filter groups, 2026-11-23) */
export const MAX_FILTERS = 10;
export const MAX_GROUPS = 5;
/** Long enough for a stored URL ("filter to this value" in the row menu). */
export const MAX_FILTER_TEXT = 1000;
/** the longest regular expression of "matches" */
export const MAX_PATTERN = 200;
/** in the order the filter panel offers them: each condition next to its negation */
export const TEXT_OPERATORS: FilterOperator[] = [
  'contains', 'notContains', 'equals', 'notEquals', 'startsWith', 'notStartsWith', 'endsWith',
  'notEndsWith', 'matches', 'empty', 'notEmpty',
];
/**
 * conditions without a value (Peter, 2026-11-25: "Es muss auch eine Bedingung 'nicht leer'
 * geben"): empty is what the table shows as "... not defined" or "Unknown"
 */
export const VALUELESS_OPERATORS: FilterOperator[] = ['empty', 'notEmpty'];
export const NUMBER_OPERATORS: FilterOperator[] = ['gt', 'gte', 'lt', 'lte'];

/** A condition without its group: what a filter row in the panel holds. */
export type Condition = Pick<RowFilter, 'target'|'operator'|'value'>;

export interface ParsedFilters {
  filters: RowFilter[];
  groupMatch: FilterMatch[];
  match: FilterMatch;
}

export function isValidFilter(
  filter: Condition,
  dimensionCount: number,
  metrics: string[],
): boolean {
  if (VALUELESS_OPERATORS.includes(filter.operator)) {
    return labelColumns(dimensionCount).includes(filter.target);
  }
  if (TEXT_OPERATORS.includes(filter.operator)) {
    const text = filter.value.trim();
    const max = filter.operator === 'matches' ? MAX_PATTERN : MAX_FILTER_TEXT;
    return labelColumns(dimensionCount).includes(filter.target)
      && text !== '' && text.length <= max;
  }
  if (NUMBER_OPERATORS.includes(filter.operator)) {
    return metrics.includes(filter.target)
      && filter.value.trim() !== '' && Number.isFinite(Number(filter.value));
  }
  return false;
}

const GROUP_PREFIX = /^(all|any):/;

/**
 * One condition "target~operator~value" with the value URI-encoded; only the first two "~"
 * separate.
 */
function parseCondition(entry: string): Condition|null {
  const first = entry.indexOf('~');
  const second = first < 0 ? -1 : entry.indexOf('~', first + 1);
  if (second < 0) {
    return null;
  }
  try {
    const operator = entry.substring(first + 1, second) as FilterOperator;
    return {
      target: entry.substring(0, first),
      operator,
      // "is empty" and "is not empty" have no value; one in the link is ignored
      value: VALUELESS_OPERATORS.includes(operator) ? '' : decodeURIComponent(entry.substring(second + 1)),
    };
  } catch (e) {
    return null;
  }
}

/**
 * The filters of a link: groups joined by "|", each "[any:]condition;condition" ("all" without a
 * prefix), fwdeMatch the switch between the groups. A value with neither "|" nor a prefix is a
 * link from before filter groups: one group whose switch is fwdeMatch. Broken conditions, the
 * groups they empty and what passes the limits are dropped.
 */
export function parseFilterParam(
  value: unknown,
  match: unknown,
  dimensionCount: number,
  metrics: string[],
): ParsedFilters {
  const raw = String(value || '');
  const parts = raw === '' ? [] : raw.split('|');
  const legacy = parts.length === 1 && !GROUP_PREFIX.test(parts[0]);
  const filters: RowFilter[] = [];
  const groupMatch: FilterMatch[] = [];
  parts.forEach((part) => {
    const prefix = part.match(GROUP_PREFIX);
    let switchValue: FilterMatch = prefix && prefix[1] === 'any' ? 'any' : 'all';
    if (legacy) {
      switchValue = match === 'any' ? 'any' : 'all';
    }
    if (groupMatch.length >= MAX_GROUPS) {
      return;
    }
    const group = groupMatch.length;
    let kept = 0;
    (prefix ? part.substring(prefix[0].length) : part).split(';').forEach((entry) => {
      const condition = parseCondition(entry);
      if (condition && filters.length < MAX_FILTERS
        && isValidFilter(condition, dimensionCount, metrics)) {
        filters.push({ ...condition, value: condition.value.trim(), group });
        kept += 1;
      }
    });
    if (kept) {
      groupMatch.push(switchValue);
    }
  });
  return {
    filters,
    groupMatch,
    match: !legacy && groupMatch.length > 1 && match === 'any' ? 'any' : 'all',
  };
}

/** The groups in order, each with its switch and its conditions. */
export function groupsOf(
  filters: RowFilter[],
  groupMatch: FilterMatch[],
): { match: FilterMatch, filters: RowFilter[] }[] {
  const indexes = [...new Set(filters.map((f) => f.group))].sort((a, b) => a - b);
  return indexes.map((group) => ({
    match: groupMatch[group] === 'any' ? 'any' : 'all',
    filters: filters.filter((f) => f.group === group),
  }));
}

/** fwdeFilters of the link (the new form only: an "any" group carries its prefix). */
export function filterParam(filters: RowFilter[], groupMatch: FilterMatch[]): string {
  return groupsOf(filters, groupMatch)
    .map((group) => (group.match === 'any' ? 'any:' : '') + group.filters
      .map((f) => `${f.target}~${f.operator}~${encodeURIComponent(f.value)}`)
      .join(';'))
    .join('|');
}

/** fwdeMatch of the link: "any" only between two groups or more. */
export function matchParam(filters: RowFilter[], match: FilterMatch): string {
  return match === 'any' && groupsOf(filters, []).length > 1 ? 'any' : '';
}

/** The API's filterGroups (rates as fractions), null without filters. */
export function filterGroupsDefinition(state: ExplorationState): Record<string, unknown>|null {
  if (!state.filters.length) {
    return null;
  }
  return {
    match: state.match,
    groups: groupsOf(state.filters, state.groupMatch).map((group) => ({
      match: group.match,
      filters: group.filters.map((f) => {
        let { value }: { value: string|number } = f;
        if (NUMBER_OPERATORS.includes(f.operator)) {
          // rates are shown and entered in percent, sent as fractions
          value = isRate(f.target) ? Number(f.value) / 100 : Number(f.value);
        }
        return { target: f.target, operator: f.operator, value };
      }),
    })),
  };
}
