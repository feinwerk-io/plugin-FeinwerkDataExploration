/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { Catalog, ExplorationState } from './types';
import { MAX_METRICS } from './state';
import { commonActions, MAX_PIVOT_CELLS, pivotColumns } from './pivot';

type Reasons = Record<string, string|null>;

/**
 * Why a dimension cannot go into a slot (translation key), or null: it is in another slot or the
 * pivot, or it is an action dimension without actions in common with another chosen one.
 */
export function dimensionReasons(state: ExplorationState, catalog: Catalog, slot: number): Reasons {
  const otherIds = state.dimensions.filter((_, i) => i !== slot);
  const others = catalog.dimensions.filter((d) => otherIds.includes(d.id) || d.id === state.pivot);
  const reasons: Reasons = {};
  catalog.dimensions.forEach((dimension) => {
    let reason: string|null = null;
    if (otherIds.includes(dimension.id) || dimension.id === state.pivot) {
      reason = 'FeinwerkDataExploration_ReasonAlreadyChosen';
    } else if (others.some((other) => !commonActions(other, dimension))) {
      reason = 'FeinwerkDataExploration_ReasonNoCommonActions';
    }
    reasons[dimension.id] = reason;
  });
  return reasons;
}

/**
 * Why a metric cannot be chosen (translation key), or null. An action metric counts rows of one
 * kind, and every chosen action dimension must be able to have that kind, otherwise the column is
 * always 0.
 */
export function metricReasons(state: ExplorationState, catalog: Catalog): Reasons {
  const actionDimensions = catalog.dimensions
    .filter((d) => (state.dimensions.includes(d.id) || d.id === state.pivot) && d.kinds.length);
  const columns = pivotColumns(state.pivot, state.pivotLimit);
  const reasons: Reasons = {};
  catalog.metrics.forEach((metric) => {
    let reason: string|null = null;
    if (!state.metrics.includes(metric.id) && state.metrics.length >= MAX_METRICS) {
      reason = 'FeinwerkDataExploration_ReasonMaxMetrics';
    } else if (!state.metrics.includes(metric.id)
      && columns * (state.metrics.length + 1) > MAX_PIVOT_CELLS) {
      // rows and pivot (2026-11-24): pivot values times metrics at most 60
      reason = 'FeinwerkDataExploration_ReasonPivotCells';
    } else if (metric.kind !== null
      && actionDimensions.some((d) => !d.kinds.includes(metric.kind as string))) {
      reason = 'FeinwerkDataExploration_ReasonMetricNeverMatches';
    }
    reasons[metric.id] = reason;
  });
  return reasons;
}
