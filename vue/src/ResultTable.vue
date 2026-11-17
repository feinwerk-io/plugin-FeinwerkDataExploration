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
                'fwde-table__metric': column.isMetric,
                label: !column.isMetric,
                'fwde-table__dimension': !column.isMetric,
                'fwde-table__dimension-last': column.key === lastDimension,
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
            <template
              v-for="column in columns"
              :key="column.key"
            >
              <td
                v-if="column.isMetric"
                :class="{
                  'fwde-table__number': true,
                  'fwde-table__metric': true,
                  'fwde-cellbar': barStyle(column.key, row) !== null,
                }"
                :style="barStyle(column.key, row) || undefined"
              >{{ format(column.key, row[column.key]) }}</td>
              <td
                v-else
                :class="{
                  label: true,
                  'fwde-table__label': true,
                  'fwde-table__dimension': true,
                  'fwde-table__dimension-last': column.key === lastDimension,
                }"
                :title="String(row[column.key])"
              >
                <span :class="ellipsisClasses(row[column.key])">
                  <span class="fwde-ellipsis__head">{{ labelPart(row[column.key], 0) }}</span>
                  <span class="fwde-ellipsis__tail">{{ labelPart(row[column.key], 1) }}</span>
                </span>
              </td>
            </template>
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
      {{ translate(rowCountKey(dataRows.length), formatNumber(dataRows.length)) }}
      <span v-if="dataRows.length >= state.limit">
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
import {
  formatDuration, formatMetric, Formatters, rowCountKey,
} from './format';
import { shouldSortClientSide, sortByLabel } from './sorting';
import RowMenu from './RowMenu.vue';
import { canFilterTo } from './rows';
import { barMaximum, cellBarWidth, headAndTail } from './cellBars';
import { SHARE_METRICS } from './comparison';

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
    /** the key of the last dimension column: a line separates the dimensions from the metrics */
    lastDimension(): string {
      return this.state.dimensions.length > 1 ? 'secondary_label' : 'label';
    },
    /** what each count's bar is measured against: the column's largest value (not the sorting) */
    barMaxima(): Record<string, number> {
      const maxima: Record<string, number> = {};
      this.state.metrics.filter((id) => SHARE_METRICS.includes(id)).forEach((id) => {
        maxima[id] = barMaximum(this.dataRows, [id]);
      });
      return maxima;
    },
    /** the rows without a total row: one stays from a comparison until the new result arrives */
    dataRows(): ResultRow[] {
      return this.rows.filter((row) => !row.fwde_total);
    },
    sortedRows(): ResultRow[] {
      if (!shouldSortClientSide(this.dataRows.length, this.state.limit, this.state.sort)) {
        return this.dataRows;
      }
      return sortByLabel(this.dataRows, this.state.sort, this.state.sortOrder, Matomo.language);
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
    /** head (0) or tail (1) of a dimension value, shortened in the middle by the stylesheet */
    labelPart(value: unknown, part: 0|1): string {
      return headAndTail(String(value))[part];
    },
    /** a long value (with a tail) may shrink when the table runs out of room; a short one never */
    ellipsisClasses(value: unknown): Record<string, boolean> {
      return { 'fwde-ellipsis': true, 'fwde-ellipsis--long': headAndTail(String(value))[1] !== '' };
    },
    /** the cell's bar behind a count or sum; null for rates, averages and empty values */
    barStyle(metricId: string, row: ResultRow): Record<string, string>|null {
      if (!(metricId in this.barMaxima)) {
        return null;
      }
      const width = cellBarWidth(row[metricId], this.barMaxima[metricId]);
      return width === null ? null : { '--fwde-bar': `${width}%` };
    },
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
    rowCountKey,
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
