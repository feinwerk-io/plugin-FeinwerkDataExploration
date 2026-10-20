/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

type Row = Record<string, unknown>;

const LABEL_COLUMNS = ['label', 'secondary_label'];

/** A sorted copy, by the visible label: case-insensitive, numbers in natural order. */
export function sortByLabel<T extends Row>(
  rows: T[],
  column: string,
  order: 'asc'|'desc',
  locale?: string,
): T[] {
  const direction = order === 'asc' ? 1 : -1;
  return [...rows].sort((a, b) => direction * String(a[column] ?? '').localeCompare(
    String(b[column] ?? ''),
    locale,
    { numeric: true, sensitivity: 'base' },
  ));
}

/**
 * The database sorts dimensions by the stored value (e.g. browser codes). When every row is there,
 * the browser sorts by the label users see.
 */
export function shouldSortClientSide(rowCount: number, limit: number, sortColumn: string): boolean {
  return LABEL_COLUMNS.includes(sortColumn) && rowCount < limit;
}
