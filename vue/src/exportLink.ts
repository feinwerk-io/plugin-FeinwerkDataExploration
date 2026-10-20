/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { Matomo, MatomoUrl } from 'CoreHome';
import { ExplorationState } from './types';
import { toDefinition } from './state';

/**
 * A download link for the current exploration, built like Matomo's own report export links.
 */
export function exportUrl(state: ExplorationState, format: 'CSV'|'TSV'): string {
  const parsed = MatomoUrl.parsed.value;
  const params = {
    module: 'API',
    method: 'FeinwerkDataExploration.getExploration',
    format,
    idSite: parsed.idSite,
    period: parsed.period,
    date: parsed.date,
    segment: parsed.segment || '',
    definition: JSON.stringify(toDefinition(state)),
    readableColumns: 1,
    filter_limit: -1,
    language: Matomo.language,
    token_auth: Matomo.token_auth,
    force_api_session: 1,
  };
  return `${window.location.href.split(/[?#]/)[0]}?${MatomoUrl.stringify(params)}`;
}
