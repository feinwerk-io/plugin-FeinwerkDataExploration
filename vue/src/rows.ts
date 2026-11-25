/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { ExplorationState, FilterMatch, RowFilter } from './types';
import { ResultRow } from './api';
import {
  groupsOf, MAX_FILTERS, MAX_FILTER_TEXT, MAX_GROUPS,
} from './filterGroups';
import { LABEL_COLUMNS, STORED_VALUES } from './labels';

/**
 * The value a filter on the row's dimension matches: the stored value the API sends for
 * dimensions filtered on it (the label is URL- and HTML-decoded), else the label.
 */
function filterValue(row: ResultRow, index: number): string {
  const stored = row[STORED_VALUES[index]];
  if (stored !== undefined && stored !== null) {
    return String(stored);
  }
  return String(row[LABEL_COLUMNS[index]]);
}

/** The label columns the row has, in order: one per row dimension. */
function rowLabelIndexes(row: ResultRow, dimensionCount: number): number[] {
  return LABEL_COLUMNS.map((_, i) => i)
    .filter((i) => i === 0 || (i < dimensionCount && row[LABEL_COLUMNS[i]] !== undefined));
}

/** The row's dimension values as "equals" conditions of the given group. */
function rowConditions(state: ExplorationState, row: ResultRow, group: number): RowFilter[] {
  return rowLabelIndexes(row, state.dimensions.length).map((i) => ({
    target: LABEL_COLUMNS[i], operator: 'equals', value: filterValue(row, i), group,
  }));
}

/**
 * The conditions of one "all" list after "filter to this row": the row's dimension values as
 * "equals" conditions, in place of conditions on the same columns; the oldest give way at the
 * limit.
 */
export function filtersForRow(state: ExplorationState, row: ResultRow): RowFilter[] {
  const added = rowConditions(state, row, 0);
  const targets = added.map((f) => f.target);
  const kept = state.filters
    .filter((f) => !targets.includes(f.target))
    .map((f) => ({ ...f, group: 0 }));
  return [...kept, ...added].slice(-MAX_FILTERS);
}

/**
 * The filters after "filter to this value" or "break down" (filter groups, 2026-11-23): one "all"
 * list (or one condition) takes the row in (filtersForRow); with groups joined by "all", or one
 * "any" group, the row becomes a group of its own: "(A or B) and the row"; with groups joined by
 * "any", or when a new group would pass the limits, the row's conditions replace the filters
 * (another group would widen the table).
 */
export function rowFilterState(
  state: ExplorationState,
  row: ResultRow,
): { filters: RowFilter[], groupMatch: FilterMatch[], match: FilterMatch } {
  const groups = groupsOf(state.filters, state.groupMatch);
  const oneList = groups.length === 1
    && (groups[0].match === 'all' || groups[0].filters.length < 2);
  if (groups.length === 0 || oneList) {
    return { filters: filtersForRow(state, row), groupMatch: ['all'], match: 'all' };
  }
  const added = rowConditions(state, row, groups.length);
  const fits = groups.length < MAX_GROUPS && state.filters.length + added.length <= MAX_FILTERS;
  if (state.match === 'all' && fits) {
    // the groups renumbered in order, the row's group after them
    const filters = groups.flatMap((group, i) => group.filters.map((f) => ({ ...f, group: i })));
    return {
      filters: [...filters, ...added],
      groupMatch: [...groups.map((group) => group.match), 'all'],
      match: 'all',
    };
  }
  return { filters: rowConditions(state, row, 0), groupMatch: ['all'], match: 'all' };
}

/**
 * Whether "filter to this value" can select the row: not a row without a stored value ("… not
 * defined", marked by the API), not a value longer than a filter takes.
 */
export function canFilterTo(row: ResultRow): boolean {
  if (row.fwde_no_value) {
    return false;
  }
  const values = rowLabelIndexes(row, LABEL_COLUMNS.length).map((i) => filterValue(row, i));
  return values.every((value) => value.length <= MAX_FILTER_TEXT);
}

/** The visits of a row within the page's segment, or null when the row has no segment. */
export function combinedSegment(pageSegment: string, rowSegment: string|null): string|null {
  if (!rowSegment) {
    return null;
  }
  return pageSegment ? `${pageSegment};${rowSegment}` : rowSegment;
}
