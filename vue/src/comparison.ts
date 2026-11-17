/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { ComparisonColumn } from './types';

/** What Matomo's comparison store gives per segment or period (the first is the page's own). */
export interface StoreComparison {
  params: Record<string, string>;
  title: string;
}

/** Counts and sums: a share of the column total makes sense (ComparisonTable::SHARE_METRICS). */
export const SHARE_METRICS = [
  'nb_visits', 'nb_uniq_visitors', 'nb_actions', 'nb_conversions', 'revenue', 'nb_pageviews',
  'nb_uniq_pageviews', 'nb_events', 'sum_event_value', 'nb_downloads', 'nb_outlinks', 'nb_searches',
];

type Row = Record<string, unknown>;

/**
 * The table's columns in Matomo's order (periods outer, segments inner), at most maxColumns; cut
 * tells the page to show the limit hint. A segment Matomo has no name for (its title is
 * unknownTitle, Matomo's "Unknown") is titled by its definition, as the export names it.
 */
export function comparisonColumns(
  segments: StoreComparison[],
  periods: StoreComparison[],
  maxColumns: number,
  unknownTitle = '',
): { columns: ComparisonColumn[], cut: boolean } {
  if (!segments.length || !periods.length) {
    return { columns: [], cut: false };
  }
  const all: ComparisonColumn[] = [];
  const segmentTitle = (segment: StoreComparison) => (
    unknownTitle !== '' && segment.title === unknownTitle && segment.params.segment
      ? segment.params.segment
      : segment.title
  );
  periods.forEach((period, p) => {
    segments.forEach((segment, s) => {
      let kind: ComparisonColumn['kind'] = 'both';
      let title = segmentTitle(segment);
      if (p === 0 && s === 0) {
        kind = 'own';
        title = periods.length > 1 ? `${title}, ${period.title}` : title;
      } else if (p === 0) {
        kind = 'segment';
      } else if (s === 0) {
        kind = 'period';
        title = period.title;
      } else {
        title = `${title}, ${period.title}`;
      }
      all.push({
        segment: segment.params.segment || '',
        period: period.params.period || '',
        date: period.params.date || '',
        title,
        segmentTitle: segmentTitle(segment),
        periodTitle: periods.length > 1 ? period.title : '',
        kind,
      });
    });
  });
  return { columns: all.slice(0, maxColumns), cut: all.length > maxColumns };
}

export function isComparing(columns: ComparisonColumn[]): boolean {
  return columns.length > 1;
}

export function valueKey(metric: string, n: number): string {
  return n === 0 ? metric : `${metric}__c${n}`;
}

export function shareKey(metric: string, n: number): string {
  return `${valueKey(metric, n)}__share`;
}

export function changeKey(metric: string, n: number): string {
  return `${valueKey(metric, n)}__change`;
}

/** The total row, or the row's keys from the API (labels are not unique, e.g. "Unknown"). */
function rowKey(row: Row): string {
  if (row.fwde_total) {
    return '\u0000total';
  }
  return typeof row.fwde_key === 'string'
    ? row.fwde_key
    : `${row.label}\u0000${row.secondary_label ?? ''}`;
}

/**
 * The rows with the response's first comparison column (…__c1) as column n, matched by the rows'
 * keys and the total row; the given rows stay unchanged.
 */
export function mergeColumn<T extends Row>(rows: T[], response: Row[], n: number): T[] {
  const byKey = new Map(response.map((row) => [rowKey(row), row]));
  return rows.map((row) => {
    const from = byKey.get(rowKey(row));
    const merged: Row = { ...row };
    if (from) {
      Object.keys(from).forEach((key) => {
        const match = key.match(/^(.+)__c1(__share|__change)?$/);
        if (match) {
          merged[`${match[1]}__c${n}${match[2] || ''}`] = from[key];
        }
      });
    }
    return merged as T;
  });
}

/**
 * The comparison columns still to request after the first response (own view and totals): none
 * when the own view has no rows.
 */
export function remainingColumns(rows: Row[], columns: ComparisonColumn[]): number[] {
  if (!rows.some((row) => !row.fwde_total)) {
    return [];
  }
  return columns.map((c, n) => n).filter((n) => n > 0);
}

/** The rows without comparison columns: the first request asks for the own view as its column 1. */
export function withoutComparisonColumns<T extends Row>(rows: T[]): T[] {
  return rows.map((row) => {
    const own: Row = {};
    Object.keys(row).forEach((key) => {
      if (!/__c\d+(__share|__change)?$/.test(key)) {
        own[key] = row[key];
      }
    });
    return own as T;
  });
}

/**
 * Requests the comparison columns one after another; stops as soon as a newer setup started
 * (latest() false), so outdated full-size queries are not asked for. A failing column is reported
 * and the next one is still asked for.
 */
export function requestColumns(
  indices: number[],
  request: (n: number) => Promise<Row[]>,
  latest: () => boolean,
  onColumn: (n: number, rows: Row[]) => void,
  onError: (n: number, error: unknown) => void,
): Promise<void> {
  return indices.reduce((previous: Promise<void>, n) => previous.then(() => {
    if (!latest()) {
      return undefined;
    }
    return request(n).then((rows) => {
      if (latest()) {
        onColumn(n, rows);
      }
    }).catch((e) => {
      if (latest()) {
        onError(n, e);
      }
    });
  }), Promise.resolve());
}
