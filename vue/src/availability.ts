/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { Catalog, ExplorationState } from './types';
import { MAX_METRICS } from './state';

type Reasons = Record<string, string|null>;

/**
 * Why a dimension cannot go into a slot (translation key), or null.
 */
export function dimensionReasons(state: ExplorationState, catalog: Catalog, slot: 0|1): Reasons {
  const otherId = state.dimensions[slot === 0 ? 1 : 0];
  const other = catalog.dimensions.find((d) => d.id === otherId);
  const reasons: Reasons = {};
  catalog.dimensions.forEach((dimension) => {
    let reason: string|null = null;
    if (dimension.id === otherId) {
      reason = 'FeinwerkDataExploration_ReasonAlreadyChosen';
    } else if (other && other.kinds.length && dimension.kinds.length
      && !dimension.kinds.some((kind) => other.kinds.includes(kind))) {
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
    .filter((d) => state.dimensions.includes(d.id) && d.kinds.length);
  const reasons: Reasons = {};
  catalog.metrics.forEach((metric) => {
    let reason: string|null = null;
    if (!state.metrics.includes(metric.id) && state.metrics.length >= MAX_METRICS) {
      reason = 'FeinwerkDataExploration_ReasonMaxMetrics';
    } else if (metric.kind !== null
      && actionDimensions.some((d) => !d.kinds.includes(metric.kind as string))) {
      reason = 'FeinwerkDataExploration_ReasonMetricNeverMatches';
    }
    reasons[metric.id] = reason;
  });
  return reasons;
}
