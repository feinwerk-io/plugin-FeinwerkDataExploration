/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * What an empty result says (UI review 2026-11-23, "say what to do"): with filters set, that no row
 * matches them, with a button that opens the filter panel; without filters, Matomo's own message.
 */
export function emptyState(filterCount: number): { message: string, editFilters: boolean } {
  return filterCount > 0
    ? { message: 'FeinwerkDataExploration_NoRowsMatchFilters', editFilters: true }
    : { message: 'CoreHome_ThereIsNoDataForThisReport', editFilters: false };
}
