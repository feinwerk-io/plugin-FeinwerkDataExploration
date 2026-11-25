/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { Catalog, DimensionInfo, ExplorationState } from './types';

/**
 * A row of getExploration, as api.ts ResultRow (not imported: api.ts imports state.ts, which
 * imports this module).
 */
type ResultRow = Record<string, string|number>;

/** How many of the pivot's values become columns (ExplorationDefinition::PIVOT_LIMITS). */
export const PIVOT_LIMITS = [3, 5, 10];
export const PIVOT_DEFAULT_LIMIT = 5;
/** pivot values times metrics (ExplorationDefinition::MAX_PIVOT_CELLS) */
export const MAX_PIVOT_CELLS = 60;
/** dimensions that cannot be the pivot: too many values (they serve as row dimensions) */
export const NO_PIVOT = ['date', 'hour'];
/** the pivot that shows all its seven values, in weekday order */
export const PIVOT_WEEKDAY = 'weekday';

/** One pivot value of a result: its label, raw value and Matomo segment (null: none). */
export interface PivotValue {
  label: string;
  value: string|number|null;
  segment: string|null;
}

export interface PivotResult {
  /** the pivot dimension's id, null for a result without a pivot */
  dimension: string|null;
  values: PivotValue[];
  /** whether the rows have the rest of the values ({metric}__p_others where it is exact) */
  others: boolean;
  /** the total row (fwde_total), null without one */
  total: ResultRow|null;
  /** the rows without the total row */
  rows: ResultRow[];
}

type Reasons = Record<string, string|null>;

/** The columns per metric a pivot makes: Weekday its seven days, otherwise its limit. */
export function pivotColumns(pivot: string|null, limit: number): number {
  if (!pivot) {
    return 0;
  }
  return pivot === PIVOT_WEEKDAY ? 7 : limit;
}

/** The pivot cells of a row: values times metrics (Others and Total not counted). */
export function pivotCells(state: ExplorationState): number {
  return pivotColumns(state.pivot, state.pivotLimit) * state.metrics.length;
}

/** Whether two dimensions can be in one table: action dimensions need an action kind in common. */
export function commonActions(a: DimensionInfo|undefined, b: DimensionInfo|undefined): boolean {
  return !a || !b || !a.kinds.length || !b.kinds.length
    || a.kinds.some((kind) => b.kinds.includes(kind));
}

/**
 * The pivot of a link or saved exploration, as the server reads a saved state
 * (SavedStateDefinition::pivot): dropped when its dimension is unknown, a row dimension, Date or
 * Hour, or (Weekday) when its seven days pass the cell limit; a limit other than 3, 5 or 10 is 5,
 * then the largest that keeps the cells within the limit.
 */
export function parsePivot(
  rawPivot: unknown,
  rawLimit: unknown,
  dimensions: string[],
  metricCount: number,
  catalog: Catalog,
): { pivot: string|null, pivotLimit: number } {
  const none = { pivot: null, pivotLimit: PIVOT_DEFAULT_LIMIT };
  const id = String(rawPivot || '');
  if (!id || dimensions.includes(id) || NO_PIVOT.includes(id)
    || !catalog.dimensions.some((d) => d.id === id)) {
    return none;
  }
  if (id === PIVOT_WEEKDAY) {
    return 7 * metricCount <= MAX_PIVOT_CELLS
      ? { pivot: id, pivotLimit: PIVOT_DEFAULT_LIMIT }
      : none;
  }
  const asked = Number(rawLimit);
  const limit = PIVOT_LIMITS.includes(asked) ? asked : PIVOT_DEFAULT_LIMIT;
  const fitting = [...PIVOT_LIMITS].reverse()
    .find((option) => option <= limit && option * metricCount <= MAX_PIVOT_CELLS);
  return fitting ? { pivot: id, pivotLimit: fitting } : none;
}

/**
 * Why a dimension cannot be the pivot (translation key), or null: a row dimension, Date or Hour,
 * an action dimension without actions in common with the row dimensions, a Weekday whose seven
 * days pass the cell limit.
 */
export function pivotReasons(state: ExplorationState, catalog: Catalog): Reasons {
  const rows = catalog.dimensions.filter((d) => state.dimensions.includes(d.id));
  const reasons: Reasons = {};
  catalog.dimensions.forEach((dimension) => {
    let reason: string|null = null;
    if (state.dimensions.includes(dimension.id)) {
      reason = 'FeinwerkDataExploration_ReasonAlreadyChosen';
    } else if (NO_PIVOT.includes(dimension.id)) {
      reason = 'FeinwerkDataExploration_ReasonPivotTime';
    } else if (rows.some((row) => !commonActions(row, dimension))) {
      reason = 'FeinwerkDataExploration_ReasonNoCommonActions';
    } else if (dimension.id === PIVOT_WEEKDAY && 7 * state.metrics.length > MAX_PIVOT_CELLS) {
      reason = 'FeinwerkDataExploration_ReasonPivotCells';
    }
    reasons[dimension.id] = reason;
  });
  return reasons;
}

/** Why a number of pivot values cannot be chosen (translation key), or null. */
export function pivotLimitReasons(state: ExplorationState): Record<number, string|null> {
  const reasons: Record<number, string|null> = {};
  PIVOT_LIMITS.forEach((limit) => {
    reasons[limit] = limit * state.metrics.length > MAX_PIVOT_CELLS
      ? 'FeinwerkDataExploration_ReasonPivotCells'
      : null;
  });
  return reasons;
}

/** The pivot of a getExploration result: its total row's fwde_pivot, the rows without it. */
export function parsePivotResult(rows: ResultRow[]): PivotResult {
  const total = rows.find((row) => row.fwde_total) || null;
  const pivot = total ? (total.fwde_pivot as unknown as {
    dimension?: string, values?: PivotValue[], others?: boolean,
  }|undefined) : undefined;
  return {
    dimension: pivot && pivot.dimension ? String(pivot.dimension) : null,
    values: pivot && Array.isArray(pivot.values) ? pivot.values.map((value) => ({
      label: String(value.label),
      value: value.value,
      segment: value.segment || null,
    })) : [],
    others: !!(pivot && pivot.others),
    total,
    rows: rows.filter((row) => !row.fwde_total),
  };
}
