/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { ExplorationState, RowFilter } from './types';
import { ResultRow } from './api';
import { MAX_FILTERS, MAX_FILTER_TEXT } from './state';

/**
 * The value a filter on the row's dimension matches: the stored value the API sends for
 * dimensions filtered on it (the label is URL- and HTML-decoded), else the label.
 */
function filterValue(row: ResultRow, dimension: 1|2): string {
  const stored = dimension === 1 ? row.fwde_value : row.fwde_value2;
  if (stored !== undefined && stored !== null) {
    return String(stored);
  }
  return String(dimension === 1 ? row.label : row.secondary_label);
}

/**
 * The filters after "filter to this row": the row's dimension values as "equals" filters, in
 * place of filters on the same columns; the oldest filters give way when more than five.
 */
export function filtersForRow(state: ExplorationState, row: ResultRow): RowFilter[] {
  const added: RowFilter[] = [{ target: 'label', operator: 'equals', value: filterValue(row, 1) }];
  if (state.dimensions.length > 1 && row.secondary_label !== undefined) {
    added.push({
      target: 'secondary_label', operator: 'equals', value: filterValue(row, 2),
    });
  }
  const targets = added.map((f) => f.target);
  const kept = state.filters.filter((f) => !targets.includes(f.target));
  return [...kept, ...added].slice(-MAX_FILTERS);
}

/**
 * Whether "filter to this value" can select the row: not a row without a stored value ("… not
 * defined", marked by the API), not a value longer than a filter takes.
 */
export function canFilterTo(row: ResultRow): boolean {
  if (row.fwde_no_value) {
    return false;
  }
  const values = [filterValue(row, 1)];
  if (row.secondary_label !== undefined) {
    values.push(filterValue(row, 2));
  }
  return values.every((value) => value.length <= MAX_FILTER_TEXT);
}

/** The visits of a row within the page's segment, or null when the row has no segment. */
export function combinedSegment(pageSegment: string, rowSegment: string|null): string|null {
  if (!rowSegment) {
    return null;
  }
  return pageSegment ? `${pageSegment};${rowSegment}` : rowSegment;
}
