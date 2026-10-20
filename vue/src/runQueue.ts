/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export interface RunQueue {
  /** Whether a run may start now; false while one is in flight (the newest runs after it). */
  request(): boolean;
  /** The in-flight run ended; true when a newer one was asked for meanwhile and starts now. */
  finish(): boolean;
  /** Whether a newer run waits, so the in-flight answer is outdated. */
  isPending(): boolean;
}

/**
 * At most one exploration of the page runs at a time. Aborting a request in the browser does not
 * stop its query on the server, so a new request next to it would only take another of the
 * instance's slots ("other explorations are running"); the newest change runs when it ends.
 */
export function createRunQueue(): RunQueue {
  let running = false;
  let pending = false;
  return {
    request: () => {
      if (running) {
        pending = true;
        return false;
      }
      running = true;
      return true;
    },
    finish: () => {
      if (pending) {
        pending = false;
        return true;
      }
      running = false;
      return false;
    },
    isPending: () => pending,
  };
}
