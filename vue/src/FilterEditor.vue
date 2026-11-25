<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later

  The row filters in groups (filter groups, 2026-11-23; Peter chose two levels with a switch on
  each): one group looks like the plain list it was before; from two groups on every group has a
  frame, a title and its own "all | any" switch, and a switch above joins the groups.
-->
<template>
  <div class="fwde-filters">
    <SegmentedSwitch
      v-if="grouped"
      class="fwde-filters__groups-match"
      :options="groupsMatchOptions"
      :model-value="outer"
      :label="translate('FeinwerkDataExploration_FilterGroupsMatch')"
      @update:model-value="setOuter"
    />
    <component
      :is="grouped ? 'fieldset' : 'div'"
      v-for="group in groups"
      :key="group.index"
      :class="grouped ? 'fwde-filters__group' : 'fwde-filters__list'"
    >
      <legend
        v-if="grouped"
        class="fwde-filters__legend"
      >{{ translate('FeinwerkDataExploration_FilterGroup', group.number) }}</legend>
      <SegmentedSwitch
        v-if="group.rows.length > 1"
        class="fwde-filters__match"
        :options="matchOptions"
        :model-value="groupSwitches[group.index] || 'all'"
        :label="translate('FeinwerkDataExploration_FilterMatch')"
        @update:model-value="setGroupMatch(group.index, $event)"
      />
      <div
        v-for="{ row, index } in group.rows"
        :key="row.id"
        class="fwde-filters__row"
      >
        <label class="fwde-filters__field fwde-filters__field--target">
          <span class="fwde-field__label">
            {{ translate('FeinwerkDataExploration_FilterColumn') }}
          </span>
          <select
            class="browser-default fwde-select"
            :value="row.target"
            @change="setTarget(index, $event)"
          >
            <option
              v-for="target in targets"
              :key="target.key"
              :value="target.key"
            >{{ target.label }}</option>
          </select>
        </label>
        <label class="fwde-filters__field fwde-filters__field--operator">
          <span class="fwde-field__label">
            {{ translate('FeinwerkDataExploration_FilterCondition') }}
          </span>
          <select
            class="browser-default fwde-select"
            :value="row.operator"
            @change="setOperator(index, $event)"
          >
            <option
              v-for="operator in operatorsFor(row.target)"
              :key="operator"
              :value="operator"
            >{{ translate(OPERATOR_KEYS[operator]) }}</option>
          </select>
        </label>
        <label class="fwde-filters__field fwde-filters__field--value">
          <span class="fwde-field__label">
            {{ translate('FeinwerkDataExploration_FilterValue') }}{{ unitSuffix(row.target) }}
          </span>
          <input
            class="browser-default fwde-input"
            :type="isNumberTarget(row.target) ? 'number' : 'text'"
            :step="isNumberTarget(row.target) ? 'any' : undefined"
            :maxlength="row.operator === 'matches' ? MAX_PATTERN : MAX_FILTER_TEXT"
            :value="row.value"
            :aria-invalid="row.problem ? 'true' : undefined"
            :aria-describedby="describedBy(row)"
            @input="setValue(index, $event)"
          />
        </label>
        <p
          v-if="row.operator === 'matches'"
          :id="`fwde-filter-help-${row.id}`"
          class="fwde-filters__help"
        >{{ translate('FeinwerkDataExploration_FilterPatternHelp') }}</p>
        <p
          v-if="row.problem"
          :id="`fwde-filter-error-${row.id}`"
          class="fwde-filters__error"
        >{{ problemText(row) }}</p>
        <button
          type="button"
          class="fwde-icon-button fwde-filters__remove"
          :title="translate('FeinwerkDataExploration_RemoveFilter')"
          @click="remove(index)"
        >
          <span
            class="icon-close"
            aria-hidden="true"
          />
          <span class="fwde-visually-hidden">
            {{ translate('FeinwerkDataExploration_RemoveFilter') }}
          </span>
        </button>
      </div>
      <button
        v-if="grouped"
        type="button"
        class="fwde-button fwde-filters__add"
        :disabled="atConditionLimit"
        :title="atConditionLimit ? conditionLimitText : undefined"
        @click="add(group.index)"
      >
        <span
          class="icon-add"
          aria-hidden="true"
        />
        {{ translate('FeinwerkDataExploration_FilterAddCondition') }}
      </button>
    </component>
    <div class="fwde-filters__actions">
      <button
        v-if="!grouped"
        type="button"
        class="fwde-button fwde-filters__add"
        :disabled="atConditionLimit"
        :title="atConditionLimit ? conditionLimitText : undefined"
        @click="add(groups.length ? groups[0].index : 0)"
      >
        <span
          class="icon-add"
          aria-hidden="true"
        />
        {{ translate('FeinwerkDataExploration_AddFilter') }}
      </button>
      <button
        type="button"
        class="fwde-button fwde-filters__add-group"
        :disabled="atGroupLimit"
        :title="atGroupLimit ? groupLimitText : undefined"
        @click="addGroup"
      >
        <span
          class="icon-add"
          aria-hidden="true"
        />
        {{ translate('FeinwerkDataExploration_FilterAddGroup') }}
      </button>
      <span
        v-if="rows.length"
        class="fwde-filters__count"
      >{{ countText }}</span>
      <p
        v-if="atGroupLimit"
        class="fwde-filters__limit"
      >{{ groupLimitText }}</p>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import { isRate, isSeconds } from './metricMeta';
import SegmentedSwitch from './SegmentedSwitch.vue';
import { filterTargets, OPERATOR_KEYS } from './filterSummary';
import {
  Catalog, ExplorationState, FilterMatch, FilterOperator, RowFilter,
} from './types';
import {
  isValidFilter, MAX_FILTERS, MAX_FILTER_TEXT, MAX_GROUPS, MAX_PATTERN, NUMBER_OPERATORS,
  TEXT_OPERATORS,
} from './filterGroups';

export interface Row extends RowFilter {
  id: number;
  /** the user changed the row: left incomplete, Apply asks for it instead of dropping it */
  touched: boolean;
  /** the translation key of what is missing, shown below the row ('' = nothing) */
  problem: string;
}

const DEBOUNCE_MS = 600;
let lastRowId = 0;

function nextRowId(): number {
  lastRowId += 1;
  return lastRowId;
}

export default defineComponent({
  components: { SegmentedSwitch },
  props: {
    catalog: { type: Object as PropType<Catalog>, required: true },
    state: { type: Object as PropType<ExplorationState>, required: true },
    actionScope: { type: Boolean, required: true },
    /** without filters, start with one empty row to fill (the filter panel) */
    startWithRow: { type: Boolean, default: false },
  },
  emits: ['change', 'match', 'groupMatch'],
  data() {
    return {
      rows: [] as Row[],
      /** each group's switch, by group index */
      groupSwitches: [] as FilterMatch[],
      /** the switch between the groups */
      outer: 'all' as FilterMatch,
      timer: 0 as number,
      OPERATOR_KEYS,
      MAX_FILTERS,
      MAX_FILTER_TEXT,
      MAX_PATTERN,
    };
  },
  created() {
    this.rows = this.state.filters.map((f) => ({
      ...f, id: nextRowId(), touched: true, problem: '',
    }));
    this.groupSwitches = (this.state.groupMatch || []).slice();
    this.outer = this.state.match;
    if (this.startWithRow && !this.rows.length) {
      this.add(0);
    }
  },
  beforeUnmount() {
    window.clearTimeout(this.timer);
  },
  computed: {
    matchOptions(): { value: FilterMatch, label: string }[] {
      return [
        { value: 'all', label: translate('FeinwerkDataExploration_FilterMatchAll') },
        { value: 'any', label: translate('FeinwerkDataExploration_FilterMatchAny') },
      ];
    },
    groupsMatchOptions(): { value: FilterMatch, label: string }[] {
      return [
        { value: 'all', label: translate('FeinwerkDataExploration_FilterGroupsMatchAll') },
        { value: 'any', label: translate('FeinwerkDataExploration_FilterGroupsMatchAny') },
      ];
    },
    targets(): { key: string, label: string }[] {
      return filterTargets(this.state, this.catalog, this.actionScope);
    },
    /** the groups in order, each with its rows and their index in this.rows */
    groups(): { index: number, number: number, rows: { row: Row, index: number }[] }[] {
      // no array spread in .vue files: Matomo 5's Jest compiles them with an old tslib
      const indexes = Array.from(new Set(this.rows.map((row) => row.group))).sort((a, b) => a - b);
      return indexes.map((group, i) => ({
        index: group,
        number: i + 1,
        rows: this.rows
          .map((row, index) => ({ row, index }))
          .filter((entry) => entry.row.group === group),
      }));
    },
    grouped(): boolean {
      return this.groups.length > 1;
    },
    atConditionLimit(): boolean {
      return this.rows.length >= MAX_FILTERS;
    },
    atGroupLimit(): boolean {
      return this.groups.length >= MAX_GROUPS || this.atConditionLimit;
    },
    countText(): string {
      return translate('FeinwerkDataExploration_FilterConditionCount', this.rows.length, MAX_FILTERS);
    },
    conditionLimitText(): string {
      return translate('FeinwerkDataExploration_FilterLimitConditions', MAX_FILTERS);
    },
    groupLimitText(): string {
      return this.groups.length >= MAX_GROUPS
        ? translate('FeinwerkDataExploration_FilterLimitGroups', MAX_GROUPS)
        : this.conditionLimitText;
    },
  },
  methods: {
    translate,
    isNumberTarget(target: string): boolean {
      return this.state.metrics.includes(target);
    },
    /** Rates are entered in percent like the table shows them, the visit duration in seconds. */
    unitSuffix(target: string): string {
      if (isRate(target)) {
        return ` (${translate('FeinwerkDataExploration_UnitPercent')})`;
      }
      if (isSeconds(target)) {
        return ` (${translate('FeinwerkDataExploration_UnitSeconds')})`;
      }
      return '';
    },
    operatorsFor(target: string): FilterOperator[] {
      return this.isNumberTarget(target) ? NUMBER_OPERATORS : TEXT_OPERATORS;
    },
    problemText(row: Row): string {
      return row.problem === 'FeinwerkDataExploration_FilterPatternTooLong'
        ? translate(row.problem, MAX_PATTERN)
        : translate(row.problem);
    },
    describedBy(row: Row): string|undefined {
      const ids = [
        row.operator === 'matches' ? `fwde-filter-help-${row.id}` : '',
        row.problem ? `fwde-filter-error-${row.id}` : '',
      ].filter((id) => id !== '');
      return ids.length ? ids.join(' ') : undefined;
    },
    add(group: number) {
      if (this.atConditionLimit) {
        return;
      }
      if (this.groupSwitches[group] === undefined) {
        this.groupSwitches[group] = 'all';
      }
      // the new row goes after the last row of its group, so the rows stay in group order
      let at = this.rows.length;
      this.rows.forEach((row, index) => {
        if (row.group === group) {
          at = index + 1;
        }
      });
      this.rows.splice(at, 0, {
        id: nextRowId(),
        target: 'label',
        operator: 'contains',
        value: '',
        group,
        touched: false,
        problem: '',
      });
    },
    addGroup() {
      if (this.atGroupLimit) {
        return;
      }
      const group = this.rows.reduce((max, row) => Math.max(max, row.group + 1), 0);
      this.groupSwitches[group] = 'all';
      this.add(group);
      // the focus goes to the new group's first field
      this.$nextTick(() => {
        const groups = (this.$el as HTMLElement).querySelectorAll<HTMLElement>('.fwde-filters__group');
        const last = groups[groups.length - 1];
        const select = last ? last.querySelector<HTMLElement>('select') : null;
        if (select) {
          select.focus();
        }
      });
    },
    remove(index: number) {
      const { group } = this.rows[index];
      this.rows.splice(index, 1);
      if (!this.rows.some((row) => row.group === group)) {
        // the group is gone: the later groups move up, their switches with them
        this.rows.forEach((row) => {
          if (row.group > group) {
            row.group -= 1;
          }
        });
        this.groupSwitches.splice(group, 1);
      }
      this.emitNow();
      // the focus stays in the editor (the removed button took it along): the next row, the one
      // before, or "Add filter"
      this.$nextTick(() => {
        const root = this.$el as HTMLElement;
        const rows = root.querySelectorAll<HTMLElement>('.fwde-filters__row');
        const row = rows[Math.min(index, rows.length - 1)];
        const target = row
          ? row.querySelector<HTMLElement>('select')
          : root.querySelector<HTMLElement>('.fwde-filters__add');
        if (target) {
          target.focus();
        }
      });
    },
    setTarget(index: number, event: Event) {
      const row = this.rows[index];
      row.target = (event.target as HTMLSelectElement).value;
      row.touched = true;
      row.problem = '';
      if (!this.operatorsFor(row.target).includes(row.operator)) {
        [row.operator] = this.operatorsFor(row.target);
        row.value = '';
      }
      this.emitNow();
    },
    setOperator(index: number, event: Event) {
      this.rows[index].operator = (event.target as HTMLSelectElement).value as FilterOperator;
      this.rows[index].touched = true;
      this.rows[index].problem = '';
      this.emitNow();
    },
    setValue(index: number, event: Event) {
      const row = this.rows[index];
      row.value = (event.target as HTMLInputElement).value;
      row.touched = true;
      row.problem = '';
      window.clearTimeout(this.timer);
      this.timer = window.setTimeout(() => this.emitNow(), DEBOUNCE_MS);
    },
    setGroupMatch(group: number, value: FilterMatch) {
      this.groupSwitches[group] = value;
      this.emitNow();
    },
    setOuter(value: FilterMatch) {
      this.outer = value;
      this.$emit('match', value);
    },
    /**
     * Marks every row the user changed but left without a usable value, says what is missing and
     * moves the focus there (the panel's Apply, UI review 2026-11-23). An untouched empty row (the
     * starter row, or one just added) is no filter yet and passes. A regular expression is checked
     * for its length only: its syntax is the database's, the server checks it (final review,
     * 2026-11-24). Returns true when nothing is missing.
     */
    checkComplete(): boolean {
      this.rows.forEach((row) => {
        const filter = { target: row.target, operator: row.operator, value: row.value.trim() };
        const usable = isValidFilter(filter, this.state.dimensions.length, this.state.metrics);
        const meant = row.touched || row.value.trim() !== '';
        let problem = '';
        if (row.operator === 'matches' && filter.value.length > MAX_PATTERN) {
          problem = 'FeinwerkDataExploration_FilterPatternTooLong';
        } else if (!usable && meant) {
          problem = this.isNumberTarget(row.target)
            ? 'FeinwerkDataExploration_FilterNeedsNumber'
            : 'FeinwerkDataExploration_FilterNeedsValue';
        }
        row.problem = problem;
      });
      const first = this.rows.findIndex((row) => row.problem !== '');
      if (first < 0) {
        return true;
      }
      this.$nextTick(() => {
        const inputs = (this.$el as HTMLElement).querySelectorAll<HTMLElement>('.fwde-filters__field--value input');
        if (inputs[first]) {
          inputs[first].focus();
        }
      });
      return false;
    },
    /** hands over what is typed now, without waiting for the typing pause (the panel's Apply) */
    flush() {
      this.emitNow();
    },
    /**
     * The usable conditions with their groups numbered from 0 in order (a group left without a
     * usable condition drops out), and the switch of each.
     */
    emitNow() {
      window.clearTimeout(this.timer);
      const valid = this.rows
        .map((row) => ({
          target: row.target, operator: row.operator, value: row.value.trim(), group: row.group,
        }))
        .filter((f) => isValidFilter(f, this.state.dimensions.length, this.state.metrics));
      const kept = Array.from(new Set(valid.map((f) => f.group))).sort((a, b) => a - b);
      const filters = valid.map((f) => ({ ...f, group: kept.indexOf(f.group) }));
      const groupMatch = kept.map((group) => this.groupSwitches[group] || 'all');
      if (JSON.stringify(filters) !== JSON.stringify(this.state.filters)) {
        this.$emit('change', filters);
      }
      if (JSON.stringify(groupMatch) !== JSON.stringify(this.state.groupMatch)) {
        this.$emit('groupMatch', groupMatch);
      }
    },
  },
});
</script>
