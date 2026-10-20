/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export interface RequestGuard {
  start(): number;
  isLatest(id: number): boolean;
}

/**
 * Tells whether an answer belongs to the latest request, so an older answer never replaces a newer
 * table.
 */
export function createRequestGuard(): RequestGuard {
  let latest = 0;
  return {
    start: () => {
      latest += 1;
      return latest;
    },
    isLatest: (id: number) => id === latest,
  };
}
