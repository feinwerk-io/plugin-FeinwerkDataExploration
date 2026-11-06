/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/** Feinwerk feedback kit: what the plugin delivers to the feedback bar (widget props). */
export interface FeedbackSettings {
  enabled: boolean;
  supportUrl: string;
  version: string;
}

export type Vote = 'up' | 'down';

export const FEEDBACK_PLUGIN = 'FeinwerkDataExploration';

function storageKey(topic: string): string {
  return `${FEEDBACK_PLUGIN}.feedback.${topic}`;
}

/** This browser's vote on the question for this plugin version (a convenience only), or ''. */
export function storedVote(topic: string, version: string, storage: Storage): '' | Vote {
  try {
    const stored = JSON.parse(storage.getItem(storageKey(topic)) || '{}');
    return stored && stored.version === version && (stored.vote === 'up' || stored.vote === 'down')
      ? stored.vote
      : '';
  } catch (e) {
    return '';
  }
}

export function rememberVote(topic: string, version: string, vote: Vote, storage: Storage): void {
  try {
    storage.setItem(storageKey(topic), JSON.stringify({ version, vote }));
  } catch (e) {
    // private mode or blocked storage: the thanks is shown anyway
  }
}
