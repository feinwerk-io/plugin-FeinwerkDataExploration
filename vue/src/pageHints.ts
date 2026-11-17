/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { MatomoUrl, NumberFormatter, translate } from 'CoreHome';
import {
  canRetry, isLargePeriod, isSlow, noSmallerPeriodKey,
} from './hints';
import { rangeFromDay, startsBefore } from './period';
import { PeriodVisits } from './api';

/** Globals Matomo sets for the page. */
interface WindowWithPiwik {
  piwik?: { hasSuperUserAccess?: boolean|number };
}

/** The hints both exploration pages (free-form table, funnel) show around their result. */

/** Before exploring a period with many visits; '' when it is small enough. */
export function largePeriodHint(info: PeriodVisits|null): string {
  if (!info || !isLargePeriod(info.visits, info.maxVisits)) {
    return '';
  }
  const visits = NumberFormatter.formatNumber(info.visits, 0, 0);
  return info.timeoutSeconds > 0
    ? translate('FeinwerkDataExploration_LargePeriodHint', visits, String(info.timeoutSeconds))
    : translate('FeinwerkDataExploration_LargePeriodHintNoLimit', visits);
}

/** After a slow result; '' otherwise. */
export function slowHint(
  elapsedMs: number|null,
  info: PeriodVisits|null,
  loading: boolean,
  error: string,
): string {
  const timeout = info ? info.timeoutSeconds : 0;
  if (loading || error || elapsedMs === null || !isSlow(elapsedMs, timeout)) {
    return '';
  }
  const seconds = NumberFormatter.formatNumber(elapsedMs / 1000, 0, 0);
  return timeout > 0
    ? translate('FeinwerkDataExploration_SlowQueryHint', seconds, String(timeout))
    : translate('FeinwerkDataExploration_SlowQueryHintNoLimit', seconds);
}

export function suggestedPeriodLabel(days: number): string {
  return days === 1
    ? translate('FeinwerkDataExploration_LoadLastDay')
    : translate('FeinwerkDataExploration_LoadLastDays', String(days));
}

/** A busy or timed-out request may be tried again. */
export function retryAllowed(error: string): boolean {
  // both role variants of the time limit message start with the same sentence
  const timeout = translate('FeinwerkDataExploration_ErrorTimeout', '\u0000');
  return canRetry(
    error,
    translate('FeinwerkDataExploration_ErrorBusy'),
    timeout.includes('\u0000') ? timeout.slice(0, timeout.indexOf('\u0000')) : '',
  );
}

export function noSmallerPeriodKeyForUser(): string {
  const { piwik } = window as unknown as WindowWithPiwik;
  return noSmallerPeriodKey(!!(piwik && piwik.hasSuperUserAccess));
}

function today(): string {
  return new Date().toISOString().substring(0, 10);
}

/** The part of the period that still has raw data, null when all of it has. */
export function rawDataRange(rawDataFrom: string|null): string|null {
  if (!rawDataFrom) {
    return null;
  }
  const parsed = MatomoUrl.parsed.value;
  return rangeFromDay(String(parsed.period), String(parsed.date), rawDataFrom, today());
}

/** The period starts before the first day with complete raw data. */
export function showsRawDataWarning(rawDataFrom: string|null): boolean {
  const parsed = MatomoUrl.parsed.value;
  return startsBefore(String(parsed.period), String(parsed.date), rawDataFrom, today());
}
