/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { Catalog, ExplorationState } from './types';

/**
 * Whether the rows of a table add up to its total (design 2026-11-20): every counted unit of
 * every chosen metric belongs to exactly one row. Only then an "Others" row and a donut are exact;
 * otherwise the table shows a hint. The same rule as Engine/Additivity.php.
 */
const NEVER_ADDING_UP: Record<string, 'visit'|'visitor'> = {
  nb_uniq_visitors: 'visitor',
  nb_uniq_pageviews: 'visit',
};

/** The unit that can count in several rows, null when the rows add up. */
export function overcountUnit(state: ExplorationState, catalog: Catalog): 'visit'|'visitor'|null {
  const never = state.metrics.find((id) => NEVER_ADDING_UP[id]);
  if (never) {
    return NEVER_ADDING_UP[never];
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
