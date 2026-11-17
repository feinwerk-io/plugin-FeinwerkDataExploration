/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { FunnelRow } from './types';

/** The share of step 1 that reached the last step, null when step 1 has no visits. */
export function totalRate(rows: FunnelRow[]): number|null {
  if (!rows.length || rows[0].nb_visits <= 0) {
    return null;
  }
  return rows[rows.length - 1].share_of_start;
}

/** The width of a step's bar: its share of step 1, within 0 to 100. */
export function barPercent(row: FunnelRow): number {
  return Math.max(0, Math.min(100, row.share_of_start));
}
