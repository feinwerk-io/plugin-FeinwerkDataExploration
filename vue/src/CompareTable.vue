<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->
<template>
  <div class="fwde-table fwde-compare">
    <div class="fwde-table__scroller">
      <table class="dataTable fwde-table__table fwde-compare__table">
        <thead>
          <tr>
            <th
              v-for="dimension in dimensionColumns"
              :key="dimension.key"
              :class="{
                label: true,
                'fwde-table__dimension': true,
                'fwde-table__dimension-last': dimension.key === lastDimension,
                'fwde-table__dimension--fit': dimension.key === 'label' && firstDimensionFits,
              }"
              rowspan="2"
            >{{ dimension.label }}</th>
            <th
              v-for="metric in metricColumns"
              :key="metric.key"
              :colspan="columns.length"
              :class="{
                'fwde-compare__group': true,
                sortable: true,
                columnSorted: state.sort === metric.key,
              }"
              :aria-sort="ariaSort(metric.key)"
            >
              <button
                type="button"
                class="fwde-table__sort"
                :title="translate('FeinwerkDataExploration_SortBy', metric.label)"
                @click="$emit('sort', metric.key)"
              >
                <span>{{ metric.label }}</span>
                <span
                  v-if="state.sort === metric.key"
                  :class="`sortIcon ${state.sortOrder}`"
                  aria-hidden="true"
                />
              </button>
            </th>
            <th
              class="fwde-table__menu"
              rowspan="2"
            >
              <span class="fwde-visually-hidden">
                {{ translate('FeinwerkDataExploration_RowActions') }}
              </span>
            </th>
          </tr>
          <tr>
            <template
              v-for="metric in metricColumns"
              :key="metric.key"
            >
              <th
                v-for="(column, n) in columns"
                :key="`${metric.key}-${n}`"
                :class="{
                  'fwde-table__number': true,
                  'fwde-compare__column': true,
                  'fwde-compare__group-start': n === 0,
                }"
                :title="column.title"
              >
                <span
                  :class="dotClasses(n)"
                  :data-name="dotName(n)"
                  aria-hidden="true"
                />
                <span class="fwde-compare__segment">{{ column.segmentTitle || column.title }}</span>
                <span
                  v-if="column.periodTitle"
                  class="fwde-compare__period"
                >{{ column.periodTitle }}</span>
              </th>
            </template>
          </tr>
        </thead>
        <tbody>
          <tr
            v-if="total"
            class="fwde-compare__total"
          >
            <td
              v-for="dimension in dimensionColumns"
              :key="dimension.key"
              :class="{
                label: true,
                'fwde-table__label': true,
                'fwde-table__dimension': true,
                'fwde-table__dimension-last': dimension.key === lastDimension,
                'fwde-table__dimension--fit': dimension.key === 'label' && firstDimensionFits,
              }"
            >{{ dimension.key === 'label' ? total.label : '' }}</td>
            <template
              v-for="metric in metricColumns"
              :key="metric.key"
            >
              <td
                v-for="(column, n) in columns"
                :key="`${metric.key}-${n}`"
                :class="{
                  'fwde-table__number': true,
                  'fwde-compare__cell': true,
                  'fwde-compare__group-start': n === 0,
                }"
              >{{ columnErrors[n] ? '' : value(total, metric.key, n) }}</td>
            </template>
            <td class="fwde-table__menu" />
          </tr>
          <tr
            v-for="(row, index) in sortedRows"
            :key="index"
          >
            <td
              v-for="dimension in dimensionColumns"
              :key="dimension.key"
              :class="{
                label: true,
                'fwde-table__label': true,
                'fwde-table__dimension': true,
                'fwde-table__dimension-last': dimension.key === lastDimension,
                'fwde-table__dimension--fit': dimension.key === 'label' && firstDimensionFits,
              }"
              :title="String(row[dimension.key])"
            >
              <span :class="ellipsisClasses(row[dimension.key])">
                <span class="fwde-ellipsis__head">{{ labelPart(row[dimension.key], 0) }}</span>
                <span class="fwde-ellipsis__tail">{{ labelPart(row[dimension.key], 1) }}</span>
              </span>
            </td>
            <template
              v-for="metric in metricColumns"
              :key="metric.key"
            >
              <td
                v-for="(column, n) in columns"
                :key="`${metric.key}-${n}`"
                :class="{
                  'fwde-table__number': true,
                  'fwde-compare__cell': true,
                  'fwde-compare__group-start': n === 0,
                  'fwde-cellbar': barStyle(row, metric.key, n) !== null,
                }"
                :style="barStyle(row, metric.key, n) || undefined"
              >
                <template v-if="!columnErrors[n]">
                  <span class="fwde-compare__value">{{ value(row, metric.key, n) }}</span>
                  <span
                    v-if="extra(row, metric.key, column, n)"
                    class="fwde-compare__extra"
                  >{{ extra(row, metric.key, column, n) }}</span>
                </template>
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
import {
  ComparisonsStoreInstance, Matomo, NumberFormatter, translate,
} from 'CoreHome';
import { Catalog, ComparisonColumn, ExplorationState } from './types';
import { ResultRow } from './api';
import {
  formatDuration, formatMetric, Formatters, rowCountKey,
} from './format';
import { shouldSortClientSide, sortByLabel } from './sorting';
import RowMenu from './RowMenu.vue';
import { canFilterTo } from './rows';
import {
  changeKey, SHARE_METRICS, shareKey, valueKey,
} from './comparison';
import { barMaximum, cellBarWidth, headAndTail } from './cellBars';

/** colours of the column dots: the plugin's series; Matomo's comparison colour wins */
const SERIES_COLOURS = 10;

export interface HeaderColumn {
  key: string;
  label: string;
}

/**
 * The free-form table while comparing (comparison design 3): one column group per metric, one
 * column per comparison, the total row first; counts and sums with their share and a bar, period
 * columns with the change of the page's period against them.
 */
export default defineComponent({
  components: { RowMenu },
  props: {
    /** the total row (fwde_total) first, then the own rows */
    rows: { type: Array as PropType<ResultRow[]>, required: true },
    state: { type: Object as PropType<ExplorationState>, required: true },
    catalog: { type: Object as PropType<Catalog>, required: true },
    columns: { type: Array as PropType<ComparisonColumn[]>, required: true },
    /** by column index: the message of a column that could not be computed */
    columnErrors: { type: Object as PropType<Record<number, string>>, default: () => ({}) },
    actionScope: { type: Boolean, required: true },
    currencySymbol: { type: String, required: true },
    visitsLogEnabled: { type: Boolean, default: false },
  },
  emits: ['sort', 'row-filter', 'row-breakdown', 'row-visits'],
  computed: {
    dimensionColumns(): HeaderColumn[] {
      const name = (id: string) => this.catalog.dimensions.find((d) => d.id === id)?.name || id;
      return this.state.dimensions.map((id, i) => ({
        key: i === 0 ? 'label' : 'secondary_label',
        label: name(id),
      }));
    },
    metricColumns(): HeaderColumn[] {
      return this.state.metrics.map((id) => {
        const metric = this.catalog.metrics.find((m) => m.id === id);
        const label = metric && this.actionScope && metric.nameInActionScope
          ? metric.nameInActionScope
          : metric?.name || id;
        return { key: id, label };
      });
    },
    lastDimension(): string {
      return this.state.dimensions.length > 1 ? 'secondary_label' : 'label';
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
     * What each count's bars are measured against: the metric's largest value in all its columns,
     * so a smaller segment shows smaller bars (sorting does not change it).
     */
    barMaxima(): Record<string, number> {
      const maxima: Record<string, number> = {};
      this.state.metrics.filter((id) => SHARE_METRICS.includes(id)).forEach((id) => {
        maxima[id] = barMaximum(this.rows, this.columns.map((_, n) => valueKey(id, n)));
      });
      return maxima;
    },
    total(): ResultRow|null {
      return this.rows.find((row) => row.fwde_total) || null;
    },
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
    translate,
    rowCountKey,
    formatNumber(value: number): string {
      return NumberFormatter.formatNumber(value, 0, 0);
    },
    value(row: ResultRow, metric: string, n: number): string {
      const raw = row[valueKey(metric, n)];
      if (raw === null || raw === undefined || raw === '') {
        return '';
      }
      return formatMetric(metric, Number(raw), this.formatters);
    },
    /** the share (own and segment columns, counts and sums) or the change (period columns) */
    extra(row: ResultRow, metric: string, column: ComparisonColumn, n: number): string {
      if (column.kind === 'period' || column.kind === 'both') {
        const change = row[changeKey(metric, n)];
        if (change === null || change === undefined) {
          return '';
        }
        const formatted = NumberFormatter.formatPercent(Number(change), 1, 0);
        return Number(change) > 0 ? `+${formatted}` : formatted;
      }
      const share = row[shareKey(metric, n)];
      if (!SHARE_METRICS.includes(metric) || share === null || share === undefined) {
        return '';
      }
      return NumberFormatter.formatPercent(Number(share), 1, 0);
    },
    /** head (0) or tail (1) of a dimension value, shortened in the middle by the stylesheet */
    labelPart(value: unknown, part: 0|1): string {
      return headAndTail(String(value))[part];
    },
    /** a long value (with a tail) may shrink when the table runs out of room; a short one never */
    ellipsisClasses(value: unknown): Record<string, boolean> {
      return { 'fwde-ellipsis': true, 'fwde-ellipsis--long': headAndTail(String(value))[1] !== '' };
    },
    /** the cell's bar behind a count or sum of a column that has values; null otherwise */
    barStyle(row: ResultRow, metric: string, n: number): Record<string, string>|null {
      if (this.columnErrors[n] || row.fwde_total || !(metric in this.barMaxima)) {
        return null;
      }
      const width = cellBarWidth(row[valueKey(metric, n)], this.barMaxima[metric]);
      return width === null ? null : { '--fwde-bar': `${width}%` };
    },
    /** the dot of a column in Matomo's comparison colour (its comparison box, the trend's lines) */
    dotClasses(n: number): string[] {
      return [
        'fwde-compare__dot',
        'fwde-chart__series',
        `fwde-chart__series-${(n % SERIES_COLOURS) + 1}`,
        'comparison-series-color',
      ];
    },
    dotName(n: number): string {
      return ComparisonsStoreInstance.getSeriesColorName(n, 0);
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
    ariaSort(key: string): 'none'|'ascending'|'descending' {
      if (this.state.sort !== key) {
        return 'none';
      }
      return this.state.sortOrder === 'asc' ? 'ascending' : 'descending';
    },
  },
});
</script>
