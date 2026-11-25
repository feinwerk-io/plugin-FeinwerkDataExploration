/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { Catalog, ExplorationState } from './types';
import { groupsOf } from './filterGroups';
import { isRate, isSeconds } from './metricMeta';
import { LABEL_COLUMNS } from './labels';

/** the condition's text per operator (the filter editor's choices and the summary) */
export const OPERATOR_KEYS: Record<string, string> = {
  contains: 'FeinwerkDataExploration_OperatorContains',
  equals: 'FeinwerkDataExploration_OperatorEquals',
  notContains: 'FeinwerkDataExploration_OperatorNotContains',
  notEquals: 'FeinwerkDataExploration_OperatorNotEquals',
  startsWith: 'FeinwerkDataExploration_OperatorStartsWith',
  endsWith: 'FeinwerkDataExploration_OperatorEndsWith',
  notStartsWith: 'FeinwerkDataExploration_OperatorNotStartsWith',
  notEndsWith: 'FeinwerkDataExploration_OperatorNotEndsWith',
  matches: 'FeinwerkDataExploration_OperatorMatches',
  empty: 'FeinwerkDataExploration_OperatorEmpty',
  notEmpty: 'FeinwerkDataExploration_OperatorNotEmpty',
  gt: 'FeinwerkDataExploration_OperatorGt',
  gte: 'FeinwerkDataExploration_OperatorGte',
  lt: 'FeinwerkDataExploration_OperatorLt',
  lte: 'FeinwerkDataExploration_OperatorLte',
};

/**
 * The columns a filter can test, by the names the table shows: the dimensions ("label",
 * "secondary_label", "tertiary_label"), then the metrics (next to an action dimension by their
 * "with this action" name).
 */
export function filterTargets(
  state: ExplorationState,
  catalog: Catalog,
  actionScope: boolean,
): { key: string, label: string }[] {
  const name = (id: string) => catalog.dimensions.find((d) => d.id === id)?.name || id;
  const targets = state.dimensions.map((id, i) => ({
    key: LABEL_COLUMNS[i],
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
    if (filter.operator === 'empty' || filter.operator === 'notEmpty') {
      return `${labels[filter.target] || filter.target} ${condition}`;
    }
    const isMetric = state.metrics.includes(filter.target);
    let value = isMetric ? String(filter.value) : `"${filter.value}"`;
    if (isRate(filter.target)) {
      value += ' %';
    } else if (isSeconds(filter.target)) {
      value += ' s';
    }
    return `${labels[filter.target] || filter.target} ${condition} ${value}`;
  });
}

/** A line of the left column's filter summary; join lines are the "and" / "or" between groups. */
export interface SummaryLine {
  text: string;
  join: boolean;
}

/**
 * The applied filters for the left column (filter groups, 2026-11-23): one group reads as before
 * (a line per condition, "Match any" below from two on); from two groups on one line per group,
 * its conditions joined by "and" or "or", and a line "and" / "or" between the groups.
 */
export function filterSummaryLines(
  state: ExplorationState,
  catalog: Catalog,
  actionScope: boolean,
  translate: (key: string) => string,
): SummaryLine[] {
  const texts = filterSummary(state, catalog, actionScope, translate);
  const groups = groupsOf(state.filters, state.groupMatch);
  const join = (match: string) => translate(match === 'any'
    ? 'FeinwerkDataExploration_FilterJoinOr'
    : 'FeinwerkDataExploration_FilterJoinAnd');
  if (groups.length < 2) {
    const lines = texts.map((text) => ({ text, join: false }));
    if (groups.length === 1 && groups[0].match === 'any' && texts.length > 1) {
      lines.push({ text: translate('FeinwerkDataExploration_FilterMatchAny'), join: true });
    }
    return lines;
  }
  const lines: SummaryLine[] = [];
  groups.forEach((group, i) => {
    if (i > 0) {
      lines.push({ text: join(state.match), join: true });
    }
    const conditionTexts = group.filters.map((filter) => texts[state.filters.indexOf(filter)]);
    lines.push({ text: conditionTexts.join(` ${join(group.match)} `), join: false });
  });
  return lines;
}
