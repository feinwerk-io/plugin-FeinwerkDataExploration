<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fwde-table">
    <TableScroller @layout="onLayout">
      <table
        class="dataTable fwde-table__table"
        :class="{ 'fwde-table__table--natural': naturalFirst }"
      >
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
                'fwde-table__dimension--fit': column.key === 'label' && firstColumnFits,
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
                  'fwde-table__dimension--fit': column.key === 'label' && firstColumnFits,
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
                :can-break-down="state.dimensions.length < MAX_DIMENSIONS"
                :visits-reason="visitsReason(row)"
                :filter-reason="filterReason(row)"
                @filter="$emit('row-filter', row)"
                @breakdown="$emit('row-breakdown', row)"
                @visits="$emit('row-visits', row)"
              />
            </td>
          </tr>
          <tr
            v-if="showOthers"
            class="fwde-table__others"
            :title="translate('FeinwerkDataExploration_OthersTitle')"
          >
            <template
              v-for="column in dimensionColumns"
              :key="column.key"
            >
              <td
                :class="{
                  label: true,
                  'fwde-table__label': true,
                  'fwde-table__dimension': true,
                  'fwde-table__dimension-last': column.key === lastDimension,
                  'fwde-table__dimension--fit': column.key === 'label' && firstColumnFits,
                }"
              >{{ column.key === 'label' ? translate('General_Others') : '' }}</td>
            </template>
            <template v-if="others && othersState === 'done'">
              <td
                v-for="column in metricColumns"
                :key="column.key"
                class="fwde-table__number fwde-table__metric"
              >{{ formatRest(column.key, others[column.key]) }}</td>
            </template>
            <td
              v-else
              class="fwde-table__others-action"
              :colspan="metricColumns.length"
            >
              <span
                v-if="othersState === 'loading'"
                class="fwde-table__others-loading"
              >{{ translate('General_LoadingData') }}</span>
              <template v-else>
                <span
                  v-if="othersState === 'failed'"
                  class="fwde-table__others-failed"
                >{{ translate('FeinwerkDataExploration_OthersFailed') }}</span>
                <button
                  type="button"
                  class="fwde-button fwde-table__others-calculate"
                  @click="$emit('others-calculate')"
                >{{ translate('FeinwerkDataExploration_OthersCalculate') }}</button>
              </template>
            </td>
            <td class="fwde-table__menu" />
          </tr>
        </tbody>
      </table>
    </TableScroller>
    <p
      v-if="overcountKey"
      class="fwde-table__overcount"
    >{{ translate(overcountKey) }}</p>
    <p class="fwde-table__footer">
      {{ translate(rowCountKey(dataRows.length), formatNumber(dataRows.length)) }}
    </p>
    <p
      v-if="dataRows.length >= state.limit"
      class="fwde-table__limit"
    >{{ translate('FeinwerkDataExploration_LimitReached') }}</p>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Matomo, NumberFormatter, translate } from 'CoreHome';
import { hasShare } from './metricMeta';
import { Catalog, ExplorationState, OthersState } from './types';
import { ResultRow } from './api';
import {
  formatDuration, formatMetric, Formatters, rowCountKey,
} from './format';
import { shouldSortClientSide, sortByLabel } from './sorting';
import RowMenu from './RowMenu.vue';
import TableScroller from './TableScroller.vue';
import { roomyAfter, TableLayout } from './scrollEdges';
import { canFilterTo } from './rows';
import { barMaximum, cellBarWidth, headAndTail } from './cellBars';
import { LABEL_COLUMNS } from './labels';
import { MAX_DIMENSIONS } from './state';
import { addsUp, overcountUnit } from './additivity';

export interface Column {
  key: string;
  label: string;
  isMetric: boolean;
}

export default defineComponent({
  components: { RowMenu, TableScroller },
  props: {
    rows: { type: Array as PropType<ResultRow[]>, required: true },
    state: { type: Object as PropType<ExplorationState>, required: true },
    catalog: { type: Object as PropType<Catalog>, required: true },
    actionScope: { type: Boolean, required: true },
    currencySymbol: { type: String, required: true },
    /** Matomo's visits log is on for this site */
    visitsLogEnabled: { type: Boolean, default: false },
    /** rows were cut (row limit reached or a number filter): the exact rest can be asked for */
    cut: { type: Boolean, default: false },
    /** the exact rest by metric id ("Others", on demand), null until calculated */
    others: { type: Object as PropType<Record<string, number|null>|null>, default: null },
    othersState: { type: String as PropType<OthersState>, default: 'idle' },
  },
  emits: ['sort', 'row-filter', 'row-breakdown', 'row-visits', 'others-calculate'],
  data() {
    return {
      MAX_DIMENSIONS,
      /** two dimensions: the first at its natural width while the table fits (Peter, 2026-11-23) */
      roomy: true,
      /** the scroller's last width; a shrunk table tries again only when it widens */
      boxWidth: 0,
    };
  },
  computed: {
    /** "Others" only below a cut table whose rows add up (design 2026-11-20) */
    showOthers(): boolean {
      // the server may find no rest after all (a final answer without values): then no row
      const noRest = this.othersState === 'done' && !this.others;
      return this.cut && !noRest && addsUp(this.state, this.catalog);
    },
    dimensionColumns(): Column[] {
      return this.columns.filter((column) => !column.isMetric);
    },
    metricColumns(): Column[] {
      return this.columns.filter((column) => column.isMetric);
    },
    /** why the rows can add up to more than the total (design 2026-11-20), '' when they add up */
    overcountKey(): string {
      const unit = overcountUnit(this.state, this.catalog);
      if (!unit) {
        return '';
      }
      return unit === 'visitor'
        ? 'FeinwerkDataExploration_OvercountHintVisitor'
        : 'FeinwerkDataExploration_OvercountHintVisit';
    },
    columns(): Column[] {
      const dimensionName = (id: string) => this.catalog.dimensions
        .find((d) => d.id === id)?.name || id;
      const columns: Column[] = this.state.dimensions.map((id, i) => ({
        key: LABEL_COLUMNS[i],
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
    /**
     * With two dimensions and only short values in the first one, the first column is only as
     * wide as its values: the free width goes behind the second dimension, so both dimensions stay
     * together on the left (Peter, 2026-11-17). A first dimension with long values (page URLs)
     * keeps the free width, it needs it.
     */
    firstDimensionFits(): boolean {
      return this.state.dimensions.length > 1
        && this.rows.every((row) => headAndTail(String(row.label))[1] === '');
    },
    /**
     * the first of two dimension columns is only as wide as its values: always for short values,
     * for long ones while the table has room; the second dimension then takes the free width
     */
    firstColumnFits(): boolean {
      return this.state.dimensions.length > 1 && (this.firstDimensionFits || this.roomy);
    },
    /** long first values shown on one line at their natural width (up to the usual 32em) */
    naturalFirst(): boolean {
      return this.state.dimensions.length > 1 && !this.firstDimensionFits && this.roomy;
    },
    /** the key of the last dimension column: a line separates the dimensions from the metrics */
    lastDimension(): string {
      return LABEL_COLUMNS[this.state.dimensions.length - 1];
    },
    /** what each count's bar is measured against: the column's largest value (not the sorting) */
    barMaxima(): Record<string, number> {
      const maxima: Record<string, number> = {};
      this.state.metrics.filter((id) => hasShare(id)).forEach((id) => {
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
  watch: {
    // a new result tries the natural width again
    rows() {
      this.roomy = true;
    },
  },
  methods: {
    onLayout(layout: TableLayout) {
      this.roomy = roomyAfter(this.roomy, this.naturalFirst, layout, this.boxWidth);
      this.boxWidth = layout.width;
    },
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
      return LABEL_COLUMNS.filter((column) => row[column] !== undefined)
        .map((column) => String(row[column])).join(' · ');
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
    /** a rate or average of an empty rest has no value: an empty cell, not "0 %" */
    formatRest(metricId: string, value: number|null|undefined): string {
      return value === null || value === undefined ? '' : this.format(metricId, value);
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
