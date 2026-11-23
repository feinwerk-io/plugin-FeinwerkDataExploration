/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { Catalog, ExplorationState } from './types';
import { RATE_METRICS } from './state';

/** the condition's text per operator (the filter editor's choices and the summary) */
export const OPERATOR_KEYS: Record<string, string> = {
  contains: 'FeinwerkDataExploration_OperatorContains',
  equals: 'FeinwerkDataExploration_OperatorEquals',
  notContains: 'FeinwerkDataExploration_OperatorNotContains',
  notEquals: 'FeinwerkDataExploration_OperatorNotEquals',
  startsWith: 'FeinwerkDataExploration_OperatorStartsWith',
  endsWith: 'FeinwerkDataExploration_OperatorEndsWith',
  gt: 'FeinwerkDataExploration_OperatorGt',
  lt: 'FeinwerkDataExploration_OperatorLt',
};

/**
 * The columns a filter can test, by the names the table shows: the dimensions ("label",
 * "secondary_label"), then the metrics (next to an action dimension by their "with this action"
 * name).
 */
export function filterTargets(
  state: ExplorationState,
  catalog: Catalog,
  actionScope: boolean,
): { key: string, label: string }[] {
  const name = (id: string) => catalog.dimensions.find((d) => d.id === id)?.name || id;
  const targets = state.dimensions.map((id, i) => ({
    key: i === 0 ? 'label' : 'secondary_label',
    label: name(id),
  }));
  state.metrics.forEach((id) => {
    const metric = catalog.metrics.find((m) => m.id === id);
    const label = metric && actionScope && metric.nameInActionScope
      ? metric.nameInActionScope
      : metric?.name || id;
    targets.push({ key: id, label });
  });
  return targets;
}

/**
 * One line per applied filter for the left column (UI item 4): column, condition and value, a text
 * in quotes, a rate with "%" and the visit duration with "s" as they are entered.
 */
export function filterSummary(
  state: ExplorationState,
  catalog: Catalog,
  actionScope: boolean,
  translate: (key: string) => string,
): string[] {
  const labels = Object.fromEntries(
    filterTargets(state, catalog, actionScope).map((t) => [t.key, t.label]),
  );
  return state.filters.map((filter) => {
    const condition = translate(OPERATOR_KEYS[filter.operator] || filter.operator);
    const isMetric = state.metrics.includes(filter.target);
    let value = isMetric ? String(filter.value) : `"${filter.value}"`;
    if (RATE_METRICS.includes(filter.target)) {
      value += ' %';
    } else if (filter.target === 'avg_time_on_site') {
      value += ' s';
    }
    return `${labels[filter.target] || filter.target} ${condition} ${value}`;
  });
}
