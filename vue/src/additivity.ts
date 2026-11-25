/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { Catalog, ExplorationState } from './types';
import { metricMeta } from './metricMeta';

/**
 * Whether the rows of a table add up to its total (design 2026-11-20): every counted unit of
 * every chosen metric belongs to exactly one row. Only then an "Others" row and a donut are exact;
 * otherwise the table shows a hint. The same rule as Engine/Additivity.php.
 */
/** what a distinct count counts (it can fall into several rows whatever the dimensions) */
const DISTINCT_UNIT: Record<string, 'visit'|'visitor'> = {
  nb_uniq_visitors: 'visitor',
  nb_uniq_pageviews: 'visit',
};

/** The unit that can count in several rows, null when the rows add up. */
export function overcountUnit(state: ExplorationState, catalog: Catalog): 'visit'|'visitor'|null {
  const distinct = state.metrics.find((id) => metricMeta(id)?.aggregation === 'distinct');
  if (distinct) {
    return DISTINCT_UNIT[distinct] || 'visit';
  }
  const dimensions = state.dimensions.map((id) => catalog.dimensions.find((d) => d.id === id));
  const metrics = state.metrics.map((id) => catalog.metrics.find((m) => m.id === id));
  const actionTable = dimensions.some((d) => d?.scope === 'action')
    || metrics.some((m) => m?.scope === 'action');
  // the time dimensions read the action time in an action table, so a visit can span several
  const actionDimension = dimensions.some((d) => d?.scope === 'action'
    || (actionTable && d?.group === 'time'));
  if (!actionDimension) {
    return null;
  }
  return metrics.some((m) => m && m.scope !== 'action') ? 'visit' : null;
}

export function addsUp(state: ExplorationState, catalog: Catalog): boolean {
  return overcountUnit(state, catalog) === null;
}

/**
 * Whether a count of the table can add up to more than its Total across the pivot's values (rows
 * and pivot, 2026-11-24): a distinct count, or a visit count under a pivot that a visit can have
 * several values of (an action dimension, or a time dimension read at the action time). Rates and
 * averages never add up and need no hint. The same rule as Additivity::addsUpAcross.
 */
export function pivotOvercounts(state: ExplorationState, catalog: Catalog): boolean {
  const pivot = catalog.dimensions.find((d) => d.id === state.pivot);
  if (!pivot) {
    return false;
  }
  const dimensions = state.dimensions.map((id) => catalog.dimensions.find((d) => d.id === id));
  const metrics = state.metrics.map((id) => catalog.metrics.find((m) => m.id === id));
  const actionTable = pivot.scope === 'action' || dimensions.some((d) => d?.scope === 'action')
    || metrics.some((m) => m?.scope === 'action');
  const perAction = pivot.scope === 'action' || (actionTable && pivot.group === 'time');
  return state.metrics.some((id) => {
    const meta = metricMeta(id);
    if (meta?.aggregation === 'distinct') {
      return true;
    }
    if (meta && meta.aggregation !== 'sum') {
      return false;
    }
    const metric = catalog.metrics.find((m) => m.id === id);
    return perAction && !!metric && metric.scope !== 'action';
  });
}
