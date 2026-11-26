/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { SavedExploration } from './api';

/**
 * Archived saved explorations (design 2026-11-25). The parts of a saved state that shape the
 * archived data, as the server's version (Archive/ArchivedExploration.php): dimensions, metrics,
 * filters, goal, pivot dimension and segment. How it is shown (sort, row limit, view, chart, menu
 * place, pivot limit) keeps the archive and its history.
 */
const SHAPE_KEYS = ['fwdeDims', 'fwdeMetrics', 'fwdeFilters', 'fwdeMatch', 'fwdeGoal', 'fwdePivot'];

export function archiveShape(state: Record<string, unknown>, segment: string): string {
  return JSON.stringify([...SHAPE_KEYS.map((key) => String(state[key] ?? '')), segment || '']);
}

/**
 * What the page offers for a period an exploration cannot cover: the report of an archived one,
 * opened from its menu place ("open"), or the hint to give it one ("place"); nothing for others.
 */
export function archivedReportHint(saved: SavedExploration|null): ''|'open'|'place' {
  if (!saved || !saved.archived || saved.kind !== 'table') {
    return '';
  }
  return saved.menu ? 'open' : 'place';
}

/**
 * The first day a changed archived exploration's history reaches again, for the save panel's
 * warning (final review 2026-11-26): '' while the shape is unchanged, for explorations that are
 * not archived, and when nothing is lost ($from null: archiving off or a young website).
 */
export function historyLostFrom(
  saved: SavedExploration|null,
  state: Record<string, unknown>,
  segment: string,
  from: string|null,
): string {
  if (!saved || !saved.archived || !from) {
    return '';
  }
  return archiveShape(state, segment) === archiveShape(saved.state, saved.segment || '') ? '' : from;
}
