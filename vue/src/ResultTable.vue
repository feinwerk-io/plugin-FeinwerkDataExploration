<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fwde-table">
    <div class="fwde-table__scroller">
      <table class="dataTable fwde-table__table">
        <thead>
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              :class="{
                sortable: true,
                columnSorted: state.sort === column.key,
                'fwde-table__number': column.isMetric,
                label: !column.isMetric,
              }"
              :aria-sort="ariaSort(column.key)"
            >
              <button
                type="button"
                class="fwde-table__sort"
                :title="translate('FeinwerkDataExploration_SortBy', column.label)"
                @click="$emit('sort', column.key)"
              >
                <span>{{ column.label }}</span>
                <span
                  v-if="state.sort === column.key"
                  :class="`sortIcon ${state.sortOrder}`"
                  aria-hidden="true"
                />
              </button>
            </th>
            <th class="fwde-table__menu">
              <span class="fwde-visually-hidden">
                {{ translate('FeinwerkDataExploration_RowActions') }}
              </span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(row, index) in sortedRows"
            :key="index"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              :class="column.isMetric ? 'fwde-table__number' : 'label fwde-table__label'"
              :title="column.isMetric ? undefined : String(row[column.key])"
            >{{ column.isMetric ? format(column.key, row[column.key]) : row[column.key] }}</td>
            <td class="fwde-table__menu">
              <RowMenu
                :label="rowLabel(row)"
                :can-break-down="state.dimensions.length === 1"
                :visits-reason="visitsReason(row)"
                :filter-reason="filterReason(row)"
                @filter="$emit('row-filter', row)"
                @breakdown="$emit('row-breakdown', row)"
                @visits="$emit('row-visits', row)"
              />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p class="fwde-table__footer">
      {{ translate('FeinwerkDataExploration_RowCount', formatNumber(rows.length)) }}
      <span v-if="rows.length >= state.limit">
        {{ translate('FeinwerkDataExploration_LimitReached') }}
      </span>
    </p>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Matomo, NumberFormatter, translate } from 'CoreHome';
import { Catalog, ExplorationState } from './types';
import { ResultRow } from './api';
import { formatDuration, formatMetric, Formatters } from './format';
import { shouldSortClientSide, sortByLabel } from './sorting';
import RowMenu from './RowMenu.vue';
import { canFilterTo } from './rows';

export interface Column {
  key: string;
  label: string;
  isMetric: boolean;
}

export default defineComponent({
  components: { RowMenu },
  props: {
    rows: { type: Array as PropType<ResultRow[]>, required: true },
    state: { type: Object as PropType<ExplorationState>, required: true },
    catalog: { type: Object as PropType<Catalog>, required: true },
    actionScope: { type: Boolean, required: true },
    currencySymbol: { type: String, required: true },
    /** Matomo's visits log is on for this site */
    visitsLogEnabled: { type: Boolean, default: false },
  },
  emits: ['sort', 'row-filter', 'row-breakdown', 'row-visits'],
  computed: {
    columns(): Column[] {
      const dimensionName = (id: string) => this.catalog.dimensions
        .find((d) => d.id === id)?.name || id;
      const columns: Column[] = this.state.dimensions.map((id, i) => ({
        key: i === 0 ? 'label' : 'secondary_label',
        label: dimensionName(id),
        isMetric: false,
      }));
      this.state.metrics.forEach((id) => {
        const metric = this.catalog.metrics.find((m) => m.id === id);
        const label = metric && this.actionScope && metric.nameInActionScope
          ? metric.nameInActionScope
          : metric?.name || id;
        columns.push({ key: id, label, isMetric: true });
      });
      return columns;
    },
    sortedRows(): ResultRow[] {
      if (!shouldSortClientSide(this.rows.length, this.state.limit, this.state.sort)) {
        return this.rows;
      }
      return sortByLabel(this.rows, this.state.sort, this.state.sortOrder, Matomo.language);
    },
    formatters(): Formatters {
      return {
        number: (v) => NumberFormatter.formatNumber(v, 2, 0),
        percent: (v) => NumberFormatter.formatPercent(v, 2, 0),
        currency: (v) => NumberFormatter.formatCurrency(v, this.currencySymbol, 2, 2),
        duration: (v) => formatDuration(v),
      };
    },
  },
  methods: {
    rowLabel(row: ResultRow): string {
      return row.secondary_label === undefined
        ? String(row.label)
        : `${row.label} · ${row.secondary_label}`;
    },
    filterReason(row: ResultRow): string {
      return canFilterTo(row) ? '' : translate('FeinwerkDataExploration_NoFilterForRow');
    },
    visitsReason(row: ResultRow): string {
      if (!this.visitsLogEnabled) {
        return translate('FeinwerkDataExploration_VisitsLogDisabled');
      }
      return row.segment ? '' : translate('FeinwerkDataExploration_NoVisitsForRow');
    },
    translate,
    formatNumber(value: number): string {
      return NumberFormatter.formatNumber(value, 0, 0);
    },
    format(metricId: string, value: string|number): string {
      return formatMetric(metricId, Number(value), this.formatters);
    },
    ariaSort(key: string): 'none'|'ascending'|'descending' {
      if (this.state.sort !== key) {
        return 'none';
      }
      return this.state.sortOrder === 'asc' ? 'ascending' : 'descending';
    },
  },
});
</script>
