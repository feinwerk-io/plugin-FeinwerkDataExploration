<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->
<template>
  <div class="fwde-table fwde-compare fwde-pivot">
    <TableScroller>
      <table class="dataTable fwde-table__table fwde-compare__table">
        <thead>
          <tr>
            <th
              v-for="dimension in dimensionColumns"
              :key="dimension.key"
              :class="{
                label: true,
                sortable: true,
                columnSorted: state.sort === dimension.key,
                'fwde-table__dimension': true,
                'fwde-table__dimension-last': dimension.key === lastDimension,
              }"
              :aria-sort="ariaSort(dimension.key)"
              rowspan="2"
            >
              <button
                type="button"
                class="fwde-table__sort"
                :title="translate('FeinwerkDataExploration_SortBy', dimension.label)"
                @click="$emit('sort', dimension.key)"
              >
                <span>{{ dimension.label }}</span>
                <span
                  v-if="state.sort === dimension.key"
                  :class="`sortIcon ${state.sortOrder}`"
                  aria-hidden="true"
                />
              </button>
            </th>
            <th
              v-for="group in groups"
              :key="group.key"
              :colspan="group.metrics.length"
              :class="{
                'fwde-compare__group': true,
                'fwde-pivot__group': true,
                'fwde-pivot__group--total': group.key === 'total',
              }"
              :title="group.title"
            >{{ group.title }}</th>
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
              v-for="group in groups"
              :key="group.key"
            >
              <th
                v-for="(metric, m) in group.metrics"
                :key="`${group.key}-${metric.id}`"
                :class="{
                  'fwde-table__number': true,
                  'fwde-compare__column': true,
                  'fwde-compare__group-start': m === 0,
                  sortable: group.key === 'total',
                  columnSorted: group.key === 'total' && state.sort === metric.id,
                }"
                :aria-sort="group.key === 'total' ? ariaSort(metric.id) : undefined"
              >
                <button
                  v-if="group.key === 'total'"
                  type="button"
                  class="fwde-table__sort"
                  :title="translate('FeinwerkDataExploration_SortBy', metric.label)"
                  @click="$emit('sort', metric.id)"
                >
                  <span>{{ metric.label }}</span>
                  <span
                    v-if="state.sort === metric.id"
                    :class="`sortIcon ${state.sortOrder}`"
                    aria-hidden="true"
                  />
                </button>
                <span v-else>{{ metric.label }}</span>
              </th>
            </template>
          </tr>
        </thead>
        <tbody>
          <tr
            v-if="pivot.total"
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
              }"
            >{{ dimension.key === 'label' ? pivot.total.label : '' }}</td>
            <template
              v-for="group in groups"
              :key="group.key"
            >
              <td
                v-for="(metric, m) in group.metrics"
                :key="`${group.key}-${metric.id}`"
                :class="{
                  'fwde-table__number': true,
                  'fwde-compare__cell': true,
                  'fwde-pivot__cell': true,
                  'fwde-compare__group-start': m === 0,
                }"
              >{{ format(metric.id, pivot.total[cellKey(group.key, metric.id)]) }}</td>
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
              }"
              :title="String(row[dimension.key])"
            >
              <span :class="ellipsisClasses(row[dimension.key])">
                <span class="fwde-ellipsis__head">{{ labelPart(row[dimension.key], 0) }}</span>
                <span class="fwde-ellipsis__tail">{{ labelPart(row[dimension.key], 1) }}</span>
              </span>
            </td>
            <template
              v-for="group in groups"
              :key="group.key"
            >
              <td
                v-for="(metric, m) in group.metrics"
                :key="`${group.key}-${metric.id}`"
                :class="{
                  'fwde-table__number': true,
                  'fwde-compare__cell': true,
                  'fwde-pivot__cell': true,
                  'fwde-compare__group-start': m === 0,
                  'fwde-cellbar': barStyle(cellKey(group.key, metric.id), row) !== null,
                }"
                :style="barStyle(cellKey(group.key, metric.id), row) || undefined"
              >{{ format(metric.id, row[cellKey(group.key, metric.id)]) }}</td>
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
        </tbody>
      </table>
    </TableScroller>
    <p
      v-if="overcountKey"
      class="fwde-table__overcount"
    >{{ translate(overcountKey) }}</p>
    <p
      v-if="pivotOvercount"
      class="fwde-table__overcount fwde-pivot__overcount"
    >{{ translate('FeinwerkDataExploration_PivotOvercountHint') }}</p>
    <p class="fwde-table__footer">
      {{ translate(rowCountKey(pivot.rows.length), formatNumber(pivot.rows.length)) }}
    </p>
    <p
      v-if="pivot.rows.length >= state.limit"
      class="fwde-table__limit"
    >{{ translate('FeinwerkDataExploration_LimitReached') }}</p>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Matomo, NumberFormatter, translate } from 'CoreHome';
import { hasShare } from './metricMeta';
import { Catalog, ExplorationState } from './types';
import { ResultRow } from './api';
import {
  formatDuration, formatMetric, Formatters, rowCountKey,
} from './format';
import { shouldSortClientSide, sortByLabel } from './sorting';
import RowMenu from './RowMenu.vue';
import TableScroller from './TableScroller.vue';
import { canFilterTo } from './rows';
import { barMaximum, cellBarWidth, headAndTail } from './cellBars';
import { LABEL_COLUMNS } from './labels';
import { MAX_DIMENSIONS } from './state';
import { parsePivotResult, PivotResult } from './pivot';
import { overcountUnit, pivotOvercounts } from './additivity';

interface PivotGroup {
  /** p1, p2, …, p_others or total */
  key: string;
  title: string;
  metrics: { id: string, label: string }[];
}

/**
 * A pivoted result (rows and pivot, 2026-11-24): the row dimensions on the left; one column group
 * per pivot value, then "Others" (the metrics whose rest is exact) and "Total", each with the
 * metrics; the total row first. Sorting by the row labels or a metric's Total.
 */
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
  },
  emits: ['sort', 'row-filter', 'row-breakdown', 'row-visits'],
  data() {
    return { MAX_DIMENSIONS };
  },
  computed: {
    pivot(): PivotResult {
      return parsePivotResult(this.rows);
    },
    /** why the rows can add up to more than the total, as the plain table says it; '' when not */
    overcountKey(): string {
      const unit = overcountUnit(this.state, this.catalog);
      if (!unit) {
        return '';
      }
      return unit === 'visitor'
        ? 'FeinwerkDataExploration_OvercountHintVisitor'
        : 'FeinwerkDataExploration_OvercountHintVisit';
    },
    /** a count can add up to more than its Total across the columns (final review, 2026-11-24) */
    pivotOvercount(): boolean {
      return pivotOvercounts(this.state, this.catalog);
    },
    dimensionColumns(): { key: string, label: string }[] {
      return this.state.dimensions.map((id, i) => ({
        key: LABEL_COLUMNS[i],
        label: this.catalog.dimensions.find((d) => d.id === id)?.name || id,
      }));
    },
    lastDimension(): string {
      return LABEL_COLUMNS[this.state.dimensions.length - 1];
    },
    metrics(): { id: string, label: string }[] {
      return this.state.metrics.map((id) => {
        const metric = this.catalog.metrics.find((m) => m.id === id);
        const label = metric && this.actionScope && metric.nameInActionScope
          ? metric.nameInActionScope
          : metric?.name || id;
        return { id, label };
      });
    },
    groups(): PivotGroup[] {
      const groups: PivotGroup[] = this.pivot.values.map((value, n) => ({
        key: `p${n + 1}`,
        title: value.label,
        metrics: this.metrics,
      }));
      const total = this.pivot.total || {};
      const others = this.metrics.filter((metric) => `${metric.id}__p_others` in total);
      if (this.pivot.others && others.length) {
        groups.push({ key: 'p_others', title: translate('General_Others'), metrics: others });
      }
      groups.push({ key: 'total', title: translate('General_Total'), metrics: this.metrics });
      return groups;
    },
    /** what each count's bar is measured against: its column's largest value */
    barMaxima(): Record<string, number> {
      const maxima: Record<string, number> = {};
      this.groups.forEach((group) => {
        group.metrics.filter((metric) => hasShare(metric.id)).forEach((metric) => {
          const key = this.cellKey(group.key, metric.id);
          maxima[key] = barMaximum(this.pivot.rows, [key]);
        });
      });
      return maxima;
    },
    sortedRows(): ResultRow[] {
      const { rows } = this.pivot;
      if (!shouldSortClientSide(rows.length, this.state.limit, this.state.sort)) {
        return rows;
      }
      return sortByLabel(rows, this.state.sort, this.state.sortOrder, Matomo.language);
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
    /** the column of a metric in a group: the plain metric is the row's Total */
    cellKey(group: string, metric: string): string {
      return group === 'total' ? metric : `${metric}__${group}`;
    },
    /** a cell without a value (a rate without visits) stays empty, not "0 %" */
    format(metricId: string, value: unknown): string {
      if (value === null || value === undefined || value === '') {
        return '';
      }
      return formatMetric(metricId, Number(value), this.formatters);
    },
    formatNumber(value: number): string {
      return NumberFormatter.formatNumber(value, 0, 0);
    },
    labelPart(value: unknown, part: 0|1): string {
      return headAndTail(String(value))[part];
    },
    ellipsisClasses(value: unknown): Record<string, boolean> {
      return { 'fwde-ellipsis': true, 'fwde-ellipsis--long': headAndTail(String(value))[1] !== '' };
    },
    barStyle(key: string, row: ResultRow): Record<string, string>|null {
      if (!(key in this.barMaxima)) {
        return null;
      }
      const width = cellBarWidth(row[key], this.barMaxima[key]);
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
    ariaSort(key: string): 'none'|'ascending'|'descending' {
      if (this.state.sort !== key) {
        return 'none';
      }
      return this.state.sortOrder === 'asc' ? 'ascending' : 'descending';
    },
  },
});
</script>
