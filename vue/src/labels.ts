/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/** The label columns of the row dimensions, in order (ExplorationDefinition::LABEL_COLUMNS). */
export const LABEL_COLUMNS = ['label', 'secondary_label', 'tertiary_label'];

/** The label columns of a table with that many row dimensions. */
export function labelColumns(dimensionCount: number): string[] {
  return LABEL_COLUMNS.slice(0, Math.max(1, Math.min(dimensionCount, LABEL_COLUMNS.length)));
}

/** The row metadata of the stored value behind each label column (Explorer::toDataTable). */
export const STORED_VALUES = ['fwde_value', 'fwde_value2', 'fwde_value3'];

/** the heading of each dimension group in the dimension lists */
export const GROUP_KEYS: Record<string, string> = {
  acquisition: 'FeinwerkDataExploration_GroupAcquisition',
  visitor: 'FeinwerkDataExploration_GroupVisitor',
  technology: 'FeinwerkDataExploration_GroupTechnology',
  time: 'FeinwerkDataExploration_GroupTime',
  visit: 'FeinwerkDataExploration_GroupVisit',
  behaviour: 'FeinwerkDataExploration_GroupBehaviour',
  custom: 'FeinwerkDataExploration_GroupCustom',
};
