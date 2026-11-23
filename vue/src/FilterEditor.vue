<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fwde-filters">
    <SegmentedSwitch
      v-if="rows.length > 1"
      class="fwde-filters__match"
      :options="matchOptions"
      :model-value="state.match"
      :label="translate('FeinwerkDataExploration_FilterMatch')"
      @update:model-value="$emit('match', $event)"
    />
    <div
      v-for="(row, index) in rows"
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
          :maxlength="MAX_FILTER_TEXT"
          :value="row.value"
          @input="setValue(index, $event)"
        />
      </label>
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
      v-if="rows.length < MAX_FILTERS"
      type="button"
      class="fwde-button fwde-filters__add"
      @click="add"
    >
      <span
        class="icon-add"
        aria-hidden="true"
      />
      {{ translate('FeinwerkDataExploration_AddFilter') }}
    </button>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import SegmentedSwitch from './SegmentedSwitch.vue';
import { filterTargets, OPERATOR_KEYS } from './filterSummary';
import {
  Catalog, ExplorationState, FilterMatch, FilterOperator, RowFilter,
} from './types';
import {
  isValidFilter, MAX_FILTERS, MAX_FILTER_TEXT, NUMBER_OPERATORS, RATE_METRICS, TEXT_OPERATORS,
} from './state';

export interface Row extends RowFilter {
  id: number;
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
  emits: ['change', 'match'],
  data() {
    return {
      rows: [] as Row[],
      timer: 0 as number,
      OPERATOR_KEYS,
      MAX_FILTERS,
      MAX_FILTER_TEXT,
    };
  },
  created() {
    this.rows = this.state.filters.map((f) => ({ ...f, id: nextRowId() }));
    if (this.startWithRow && !this.rows.length) {
      this.add();
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
    targets(): { key: string, label: string }[] {
      return filterTargets(this.state, this.catalog, this.actionScope);
    },
  },
  methods: {
    translate,
    isNumberTarget(target: string): boolean {
      return this.state.metrics.includes(target);
    },
    /** Rates are entered in percent like the table shows them, the visit duration in seconds. */
    unitSuffix(target: string): string {
      if (RATE_METRICS.includes(target)) {
        return ` (${translate('FeinwerkDataExploration_UnitPercent')})`;
      }
      if (target === 'avg_time_on_site') {
        return ` (${translate('FeinwerkDataExploration_UnitSeconds')})`;
      }
      return '';
    },
    operatorsFor(target: string): FilterOperator[] {
      return this.isNumberTarget(target) ? NUMBER_OPERATORS : TEXT_OPERATORS;
    },
    add() {
      this.rows.push({
        id: nextRowId(), target: 'label', operator: 'contains', value: '',
      });
    },
    remove(index: number) {
      this.rows.splice(index, 1);
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
      if (!this.operatorsFor(row.target).includes(row.operator)) {
        [row.operator] = this.operatorsFor(row.target);
        row.value = '';
      }
      this.emitNow();
    },
    setOperator(index: number, event: Event) {
      this.rows[index].operator = (event.target as HTMLSelectElement).value as FilterOperator;
      this.emitNow();
    },
    setValue(index: number, event: Event) {
      this.rows[index].value = (event.target as HTMLInputElement).value;
      window.clearTimeout(this.timer);
      this.timer = window.setTimeout(() => this.emitNow(), DEBOUNCE_MS);
    },
    /** hands over what is typed now, without waiting for the typing pause (the panel's Apply) */
    flush() {
      this.emitNow();
    },
    emitNow() {
      window.clearTimeout(this.timer);
      const valid = this.rows
        .map(({ target, operator, value }) => ({ target, operator, value: value.trim() }))
        .filter((f) => isValidFilter(f, this.state.dimensions.length, this.state.metrics));
      if (JSON.stringify(valid) !== JSON.stringify(this.state.filters)) {
        this.$emit('change', valid);
      }
    },
  },
});
</script>
