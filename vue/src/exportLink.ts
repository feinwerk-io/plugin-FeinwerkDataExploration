/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { Matomo, MatomoUrl } from 'CoreHome';
import { ComparisonColumn, ExplorationState, FunnelStepState } from './types';
import { toDefinition } from './state';
import { toFunnelDefinition } from './funnelState';

/**
 * A download link for the current exploration, built like Matomo's own report export links.
 */
export function exportUrl(
  state: ExplorationState,
  format: 'CSV'|'TSV',
  columns: ComparisonColumn[] = [],
): string {
  const parsed = MatomoUrl.parsed.value;
  const params: Record<string, unknown> = {
    module: 'API',
    method: 'FeinwerkDataExploration.getExploration',
    format,
    idSite: parsed.idSite,
    period: parsed.period,
    date: parsed.date,
    segment: parsed.segment || '',
    // with comparison columns the pivot is paused (the comparison wins)
    definition: JSON.stringify(toDefinition(state, columns.length > 1)),
    readableColumns: 1,
    filter_limit: -1,
    language: Matomo.language,
    token_auth: Matomo.token_auth,
    force_api_session: 1,
  };
  if (columns.length > 1) {
    // all comparison columns in one request
    params.comparisons = JSON.stringify(columns.slice(1)
      .map((c) => ({ segment: c.segment, period: c.period, date: c.date })));
  }
  return `${window.location.href.split(/[?#]/)[0]}?${MatomoUrl.stringify(params)}`;
}

/**
 * A download link for the current funnel, with the columns named in the user's language.
 */
export function funnelExportUrl(steps: FunnelStepState[], format: 'CSV'|'TSV'): string {
  const parsed = MatomoUrl.parsed.value;
  const params = {
    module: 'API',
    method: 'FeinwerkDataExploration.getFunnel',
    format,
    idSite: parsed.idSite,
    period: parsed.period,
    date: parsed.date,
    segment: parsed.segment || '',
    definition: JSON.stringify(toFunnelDefinition(steps)),
    readableColumns: 1,
    filter_limit: -1,
    language: Matomo.language,
    token_auth: Matomo.token_auth,
    force_api_session: 1,
  };
  return `${window.location.href.split(/[?#]/)[0]}?${MatomoUrl.stringify(params)}`;
}
