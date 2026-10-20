<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fwde-filters">
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
import {
  Catalog, ExplorationState, FilterOperator, RowFilter,
} from './types';
import {
  isValidFilter, MAX_FILTERS, MAX_FILTER_TEXT, NUMBER_OPERATORS, RATE_METRICS, TEXT_OPERATORS,
} from './state';

export interface Row extends RowFilter {
  id: number;
}

const OPERATOR_KEYS: Record<string, string> = {
  contains: 'FeinwerkDataExploration_OperatorContains',
  equals: 'FeinwerkDataExploration_OperatorEquals',
  notContains: 'FeinwerkDataExploration_OperatorNotContains',
  gt: 'FeinwerkDataExploration_OperatorGt',
  lt: 'FeinwerkDataExploration_OperatorLt',
};

const DEBOUNCE_MS = 600;
let lastRowId = 0;

function nextRowId(): number {
  lastRowId += 1;
  return lastRowId;
}

export default defineComponent({
  props: {
    catalog: { type: Object as PropType<Catalog>, required: true },
    state: { type: Object as PropType<ExplorationState>, required: true },
    actionScope: { type: Boolean, required: true },
  },
  emits: ['change'],
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
  },
  beforeUnmount() {
    window.clearTimeout(this.timer);
  },
  computed: {
    targets(): { key: string, label: string }[] {
      const name = (id: string) => this.catalog.dimensions.find((d) => d.id === id)?.name || id;
      const targets = this.state.dimensions.map((id, i) => ({
        key: i === 0 ? 'label' : 'secondary_label',
        label: name(id),
      }));
      this.state.metrics.forEach((id) => {
        const metric = this.catalog.metrics.find((m) => m.id === id);
        const label = metric && this.actionScope && metric.nameInActionScope
          ? metric.nameInActionScope
          : metric?.name || id;
        targets.push({ key: id, label });
      });
      return targets;
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
