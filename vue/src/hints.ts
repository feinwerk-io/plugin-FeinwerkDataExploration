/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/** A changed exploration runs after this pause, so a few quick clicks start one request. */
export const RUN_DELAY_MS = 400;

/**
 * Whether to warn before exploring: the period has more than a quarter of the visits an
 * exploration may read. Above the threshold the exploration refuses with its own message.
 */
export function isLargePeriod(visits: number, maxVisits: number): boolean {
  return maxVisits > 0 && visits > maxVisits / 4 && visits <= maxVisits;
}

/**
 * Whether to offer the most recent days that fit: the period has more visits than an exploration
 * reads, or it could not be counted (e.g. longer than an exploration covers).
 */
export function needsSmallerPeriod(
  info: { visits: number, maxVisits: number }|null,
  countFailed: boolean,
): boolean {
  return info ? info.visits > info.maxVisits : countFailed;
}

/**
 * Whether "try again" can help: only when the instance was busy or the time limit stopped the
 * query; any other error would come back the same way.
 *
 * @param busy the translated busy message
 * @param timeoutStart the translated time limit message up to its number of seconds
 */
export function canRetry(message: string, busy: string, timeoutStart: string): boolean {
  return message !== ''
    && (message === busy || (timeoutStart !== '' && message.startsWith(timeoutStart)));
}

/**
 * The text when even the most recent day is above the visit threshold. A segment does not help (the
 * threshold counts all visits of the period); only the threshold setting does.
 */
export function noSmallerPeriodKey(isSuperUser: boolean): string {
  return isSuperUser
    ? 'FeinwerkDataExploration_SmallerPeriodNoneSuperUser'
    : 'FeinwerkDataExploration_SmallerPeriodNone';
}

/**
 * Whether a finished request was slow enough to say how to make it faster: at least 40 % of the
 * time limit and at least 3 seconds; 10 seconds when there is no limit.
 */
export function isSlow(elapsedMs: number, timeoutSeconds: number): boolean {
  if (timeoutSeconds <= 0) {
    return elapsedMs >= 10000;
  }
  return elapsedMs >= 3000 && elapsedMs >= timeoutSeconds * 400;
}
