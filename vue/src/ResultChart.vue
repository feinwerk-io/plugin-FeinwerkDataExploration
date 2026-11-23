<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    ref="root"
    class="fwde-chart"
  >
    <div class="fwde-chart__header">
      <label class="fwde-chart__metric">
        <span class="fwde-field__label">
          {{ translate('FeinwerkDataExploration_ChartMetric') }}
        </span>
        <select
          class="browser-default fwde-select"
          :value="metric"
          @change="onMetric"
        >
          <option
            v-for="option in metricOptions"
            :key="option.id"
            :value="option.id"
          >{{ option.name }}</option>
        </select>
      </label>
      <SegmentedSwitch
        v-if="showTypeSwitch"
        class="fwde-chart__type"
        :options="typeSwitch"
        :model-value="chartType"
        :label="translate('FeinwerkDataExploration_ChartType')"
        @update:model-value="$emit('chart-type', $event)"
      />
      <p
        v-if="note"
        class="fwde-chart__note"
      >{{ note }}</p>
    </div>
    <ActivityIndicator
      v-if="chartType === 'trend' && trendLoading"
      :loading="true"
      :loading-message="translate('FeinwerkDataExploration_LoadingTrend')"
    />
    <div
      v-else-if="chartType === 'donut'"
      class="fwde-chart__donut"
    >
      <svg
        class="fwde-chart__donut-svg"
        viewBox="0 0 200 200"
        width="200"
        height="200"
        role="img"
        :aria-label="donutLabel"
      >
        <path
          v-for="(slice, index) in donut.slices"
          :key="index"
          :class="['fwde-chart__slice', `fwde-chart__series-${(index % SERIES_COLOURS) + 1}`]"
          :d="donutPath(index)"
        ><title>{{ sliceTitle(slice) }}</title></path>
        <text
          class="fwde-chart__donut-total"
          x="100"
          y="93"
          text-anchor="middle"
          dominant-baseline="central"
        >{{ format(donut.total) }}</text>
        <text
          class="fwde-chart__donut-metric"
          x="100"
          y="118"
          text-anchor="middle"
          dominant-baseline="central"
        >{{ metricName }}</text>
      </svg>
      <ul class="fwde-chart__donut-legend">
        <li
          v-for="(slice, index) in donut.slices"
          :key="index"
        >
          <span
            :class="['fwde-chart__swatch', `fwde-chart__series-${(index % SERIES_COLOURS) + 1}`]"
            aria-hidden="true"
          />
          <span class="fwde-chart__donut-name">{{ sliceName(slice) }}</span>
          <span class="fwde-chart__donut-value">{{ format(slice.value) }}</span>
          <span class="fwde-chart__donut-share">{{ sharePercent(slice.share) }}</span>
        </li>
      </ul>
    </div>
    <div
      v-else-if="chartType !== 'trend' || model.series.length"
      class="fwde-chart__plot"
    >
      <svg
        class="fwde-chart__svg"
        :width="width"
        :height="height"
        :viewBox="`0 0 ${width} ${height}`"
        role="img"
        :aria-label="ariaLabel"
        :tabindex="model.type === 'line' ? 0 : undefined"
        @mouseleave="hoverIndex = null"
        @blur="hoverIndex = null"
        @focus="onFocus"
        @keydown="onKey"
      >
        <g v-if="model.type === 'bar'">
          <g
            v-for="tick in model.scale.ticks"
            :key="`grid${tick}`"
          >
            <line
              class="fwde-chart__grid"
              :x1="barX(tick)"
              :x2="barX(tick)"
              :y1="TOP"
              :y2="barAreaBottom"
            />
            <text
              class="fwde-chart__tick"
              :x="barX(tick)"
              :y="barAreaBottom + 16"
              text-anchor="middle"
            >{{ formatTick(tick) }}</text>
          </g>
          <template v-if="model.series.length > 1">
            <g
              v-for="(category, index) in model.categories"
              :key="`group${index}`"
              class="fwde-chart__group"
            >
              <text
                class="fwde-chart__category"
                :x="labelWidth"
                :y="barY(index) + bandHeight / 2"
                text-anchor="end"
                dominant-baseline="central"
              >{{ truncateLabel(category, labelChars) }}</text>
              <g
                v-for="(series, seriesIndex) in model.series"
                :key="`group${index}-${seriesIndex}`"
              >
                <title>{{ groupTitle(series, series.points[index]) }}</title>
                <template v-if="series.points[index].value !== null">
                  <rect
                    :class="['fwde-chart__bar', ...seriesClasses(seriesIndex, series)]"
                    :data-name="seriesColourName(seriesIndex, series)"
                    :x="Math.min(barX(0), barX(series.points[index].value || 0))"
                    :y="groupBarY(index, seriesIndex)"
                    :width="Math.max(1, Math.abs(barX(series.points[index].value || 0) - barX(0)))"
                    :height="GROUP_BAR_HEIGHT - 2"
                    rx="2"
                  />
                  <text
                    class="fwde-chart__value fwde-chart__value--grouped"
                    :x="Math.max(barX(0), barX(series.points[index].value || 0)) + 6"
                    :y="groupBarY(index, seriesIndex) + (GROUP_BAR_HEIGHT - 2) / 2"
                    dominant-baseline="central"
                  >{{ format(series.points[index].value) }}</text>
                </template>
              </g>
            </g>
          </template>
          <g
            v-for="(point, index) in model.series.length > 1 ? [] : model.series[0].points"
            :key="`bar${index}`"
          >
            <title>{{ point.label }}: {{ format(point.value) }}</title>
            <text
              class="fwde-chart__category"
              :x="labelWidth"
              :y="barY(index) + BAR_HEIGHT / 2"
              text-anchor="end"
              dominant-baseline="central"
            >{{ truncateLabel(point.label, labelChars) }}</text>
            <rect
              class="fwde-chart__bar fwde-chart__series-1"
              :x="Math.min(barX(0), barX(point.value || 0))"
              :y="barY(index) + 3"
              :width="Math.max(1, Math.abs(barX(point.value || 0) - barX(0)))"
              :height="BAR_HEIGHT - 6"
              rx="2"
            />
            <text
              class="fwde-chart__value"
              :x="Math.max(barX(0), barX(point.value || 0)) + 6"
              :y="barY(index) + BAR_HEIGHT / 2"
              dominant-baseline="central"
            >{{ format(point.value) }}</text>
          </g>
        </g>
        <g v-else>
          <g
            v-for="tick in model.scale.ticks"
            :key="`grid${tick}`"
          >
            <line
              class="fwde-chart__grid"
              :x1="LINE_LEFT"
              :x2="width - LINE_RIGHT"
              :y1="lineY(tick)"
              :y2="lineY(tick)"
            />
            <text
              class="fwde-chart__tick fwde-chart__value-tick"
              :x="LINE_LEFT - 8"
              :y="lineY(tick)"
              text-anchor="end"
              dominant-baseline="central"
            >{{ formatTick(tick) }}</text>
          </g>
          <text
            v-for="(category, index) in model.categories"
            v-show="index % categoryStep === 0"
            :key="`cat${index}`"
            class="fwde-chart__tick fwde-chart__category-tick"
            :x="lineX(index)"
            :y="LINE_HEIGHT - 10"
            text-anchor="middle"
          >{{ axisLabel(category) }}</text>
          <template
            v-for="(category, index) in model.categories"
            :key="`daygrid${index}`"
          >
            <line
              v-if="index % categoryStep === 0"
              class="fwde-chart__grid fwde-chart__day-grid"
              :x1="lineX(index)"
              :x2="lineX(index)"
              :y1="LINE_TOP"
              :y2="LINE_HEIGHT - LINE_BOTTOM"
            />
          </template>
          <g
            v-for="(series, seriesIndex) in model.series"
            :key="`series${seriesIndex}`"
            :class="seriesClasses(seriesIndex, series)"
            :data-name="seriesColourName(seriesIndex, series)"
          >
            <path
              :class="{ 'fwde-chart__line': true, 'fwde-chart__line--dashed': series.dashed }"
              :d="linePath(series.points.map((p) => p.value), lineX, lineY)"
            />
            <template
              v-for="(point, index) in series.points"
              :key="`point${index}`"
            >
              <circle
                v-if="point.value !== null && showsDot(series.points, index)"
                class="fwde-chart__point"
                :cx="lineX(index)"
                :cy="lineY(point.value)"
                r="3.5"
              />
            </template>
          </g>
          <rect
            v-for="(category, index) in model.categories"
            :key="`hover${index}`"
            class="fwde-chart__hover"
            :x="hoverX(index)"
            :y="LINE_TOP"
            :width="hoverWidth"
            :height="LINE_HEIGHT - LINE_TOP - LINE_BOTTOM"
            tabindex="-1"
            @mouseenter="hoverIndex = index"
            @focus="hoverIndex = index"
          />
        </g>
      </svg>
      <div
        v-if="model.type === 'line' && hoverIndex !== null && tooltipRows.length"
        class="ui-tooltip fwde-chart__tooltip"
        :style="tooltipStyle"
        role="presentation"
      >
        <div class="ui-tooltip-content">
          <h3 class="evolution-tooltip-header">
            {{ tooltipTitle }}
          </h3>
          <div
            v-for="row in tooltipRows"
            :key="row.key"
            class="fwde-chart__tooltip-row"
          >
            <span
              :class="[...row.classes, 'tooltip-series-color']"
              :data-name="row.colourName"
            /> <strong>{{ row.value }}</strong> {{ row.label }}
          </div>
        </div>
      </div>
    </div>
    <p
      v-if="axisTitle && model.type === 'line' && !(chartType === 'trend' && trendLoading)"
      class="fwde-chart__axis-title"
    >{{ axisTitle }}</p>
    <ul
      v-if="showsLegend && !(chartType === 'trend' && trendLoading)"
      class="fwde-chart__legend"
    >
      <li
        v-for="(series, seriesIndex) in model.series"
        :key="`${seriesIndex}-${series.label}`"
        class="fwde-chart__legend-item"
      >
        <span
          :class="[
            ...seriesClasses(seriesIndex, series),
            'fwde-chart__swatch',
            { 'fwde-chart__swatch--dashed': series.dashed },
          ]"
          :data-name="seriesColourName(seriesIndex, series)"
          aria-hidden="true"
        />
        {{ series.label }}
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  ActivityIndicator, ComparisonsStoreInstance, Matomo, NumberFormatter, translate,
} from 'CoreHome';
import {
  Catalog, ChartType, ComparisonColumn, ExplorationState,
} from './types';
import { ResultRow, TrendRow } from './api';
import { compareTimeModel, trendModel } from './trend';
import { columnLabel } from './comparison';
import SegmentedSwitch from './SegmentedSwitch.vue';
import {
  formatDay, formatDuration, formatHour, formatMetric, Formatters,
} from './format';
import {
  axisLabelStep, canTrend, ChartModel, chartModel, ChartPoint, ChartSeries, compareBarModel,
  donutArc, DonutModel, donutModel, DonutSlice, linePath, TIME_DIMENSIONS, truncateLabel,
} from './chart';

const TOP = 8;
const BAR_HEIGHT = 26;
/** grouped bars while comparing: one bar per comparison column, a gap between the rows */
const GROUP_BAR_HEIGHT = 16;
const GROUP_GAP = 10;
const AXIS_HEIGHT = 26;
// Matomo's evolution graph: a flat plot, three value ticks, a date label about every 150 pixels
const LINE_HEIGHT = 210;
const LINE_TICKS = 2;
const LINE_LEFT = 64;
const LINE_RIGHT = 16;
const LINE_TOP = 12;
const LINE_BOTTOM = 34;
const VALUE_SPACE = 80;
const CHAR_WIDTH = 7;
/** colours of lines and legend (--fwde-series-1 to -10, with dark variants) */
const SERIES_COLOURS = 10;

export interface MetricOption {
  id: string;
  name: string;
}

export default defineComponent({
  components: { ActivityIndicator, SegmentedSwitch },
  props: {
    rows: { type: Array as PropType<ResultRow[]>, required: true },
    state: { type: Object as PropType<ExplorationState>, required: true },
    catalog: { type: Object as PropType<Catalog>, required: true },
    metric: { type: String, required: true },
    actionScope: { type: Boolean, required: true },
    currencySymbol: { type: String, required: true },
    hourLabels: { type: Array as PropType<string[]>, default: () => [] },
    periodDays: { type: Array as PropType<string[]|null>, default: null },
    /** bars or the trend (trend design 3); the page decides (chartTypeFor) */
    chartType: { type: String as PropType<ChartType>, default: 'bar' },
    /** the "Bars | Trend" switch: a trend is possible and the table has two rows or more */
    showTypeSwitch: { type: Boolean, default: false },
    trendRows: { type: Array as PropType<TrendRow[]|null>, default: null },
    trendLoading: { type: Boolean, default: false },
    /** lines of a line chart (free: 5) */
    maxLines: { type: Number, default: 5 },
    /** comparison columns: one line per column (time dimension) or one bar per column and row */
    compareColumns: { type: Array as PropType<ComparisonColumn[]>, default: () => [] },
    /** why the donut cannot show the chart metric (a lang key), null when it can (donutBlocker) */
    donutReason: { type: String as PropType<string|null>, default: null },
    /** the exact rest of a cut table for the chart metric ("Others"), null when not calculated */
    othersValue: { type: Number as PropType<number|null>, default: null },
    /** lang key: why the donut's "Others" lacks the cut rows (loading, failed); '' when complete */
    othersNote: { type: String, default: '' },
  },
  emits: ['metric', 'chart-type'],
  data() {
    return {
      width: 800,
      observer: null as ResizeObserver|null,
      TOP,
      BAR_HEIGHT,
      GROUP_BAR_HEIGHT,
      LINE_HEIGHT,
      LINE_LEFT,
      LINE_RIGHT,
      LINE_TOP,
      LINE_BOTTOM,
      SERIES_COLOURS,
      /** the day (category) under the mouse: its tooltip and dots */
      hoverIndex: null as number|null,
    };
  },
  mounted() {
    const root = this.$refs.root as HTMLElement;
    this.measure(root);
    if (typeof ResizeObserver !== 'undefined') {
      this.observer = new ResizeObserver(() => this.measure(root));
      this.observer.observe(root);
    }
  },
  beforeUnmount() {
    if (this.observer) {
      this.observer.disconnect();
    }
  },
  computed: {
    model(): ChartModel {
      if (this.chartType === 'trend') {
        const trend = trendModel(this.trendRows || [], this.metric, {
          hourLabels: this.hourLabels,
          tableRows: this.rows.length,
          tickCount: LINE_TICKS,
        });
        // comparison line n is column n: named as table and bars name it, not by the server
        const columns = this.compareColumns;
        return columns.length > 1 ? {
          ...trend,
          series: trend.series.map((series) => {
            const column = series.line === undefined ? undefined : columns[series.line];
            return series.comparison && column ? { ...series, label: columnLabel(column) } : series;
          }),
        } : trend;
      }
      const weekdays = [1, 2, 3, 4, 5, 6, 7]
        .map((day) => translate(`Intl_Day_Long_StandAlone_${day}`));
      const options = {
        weekdayNames: weekdays,
        hourLabels: this.hourLabels,
        periodDays: this.periodDays,
        complete: this.rows.length < this.state.limit,
        // about 70 pixels per axis label; the line chart's axis is vertical and always fits five
        tickCount: this.state.dimensions.some((id) => TIME_DIMENSIONS.includes(id))
          ? LINE_TICKS
          : Math.min(5, Math.max(2, Math.floor(this.barPlotWidth / 70))),
        maxSeries: this.maxLines,
      };
      if (this.compareColumns.length > 1) {
        const { dimensions } = this.state;
        if (TIME_DIMENSIONS.includes(dimensions[0]) && dimensions.length === 1) {
          return compareTimeModel(this.rows, this.compareColumns, this.state, this.metric, options);
        }
        if (!dimensions.some((id) => TIME_DIMENSIONS.includes(id))) {
          return compareBarModel(this.rows, this.compareColumns, this.metric, {
            maxBars: 10,
            tickCount: options.tickCount,
          });
        }
      }
      return chartModel(this.rows, this.state, this.metric, options);
    },
    /** lines of several rows, values or comparison columns: a legend and the line's name */
    showsLegend(): boolean {
      if (this.model.type === 'bar') {
        return this.model.series.length > 1;
      }
      return this.state.dimensions.length > 1
        || this.chartType === 'trend' || this.compareColumns.length > 1;
    },
    /** the height of one row: a single bar, or one bar per comparison column */
    bandHeight(): number {
      const count = this.model.series.length;
      return count > 1 ? count * GROUP_BAR_HEIGHT + GROUP_GAP : BAR_HEIGHT;
    },
    /** the donut disabled with its reason as tooltip */
    typeSwitch(): { value: string, label: string, disabled: boolean, title?: string }[] {
      return this.typeOptions.map((option) => ({
        value: option.type,
        label: option.label,
        disabled: option.reason !== null,
        title: option.reason ? translate(option.reason) : undefined,
      }));
    },
    typeOptions(): { type: ChartType, label: string, reason: string|null }[] {
      const options: { type: ChartType, label: string, reason: string|null }[] = [
        { type: 'bar', label: translate('FeinwerkDataExploration_ChartTypeBars'), reason: null },
      ];
      if (canTrend(this.state)) {
        options.push({
          type: 'trend', label: translate('FeinwerkDataExploration_ChartTypeTrend'), reason: null,
        });
      }
      options.push({
        type: 'donut',
        label: translate('FeinwerkDataExploration_ChartTypeDonut'),
        reason: this.donutReason,
      });
      return options;
    },
    /** the donut of the chart metric (design 2026-11-20): the largest rows and "Others" */
    donut(): DonutModel {
      return donutModel(this.rows, this.metric, this.othersValue, 9);
    },
    donutLabel(): string {
      const option = this.metricOptions.find((o) => o.id === this.metric);
      const name = translate('FeinwerkDataExploration_ChartTypeDonut');
      return option ? `${name}: ${option.name}` : name;
    },
    metricOptions(): MetricOption[] {
      return this.state.metrics.map((id) => {
        const metric = this.catalog.metrics.find((m) => m.id === id);
        const name = metric && this.actionScope && metric.nameInActionScope
          ? metric.nameInActionScope
          : metric?.name || id;
        return { id, name };
      });
    },
    metricName(): string {
      return this.metricOptions.find((m) => m.id === this.metric)?.name || this.metric;
    },
    height(): number {
      if (this.model.type === 'line') {
        return LINE_HEIGHT;
      }
      return TOP + this.model.categories.length * this.bandHeight + AXIS_HEIGHT;
    },
    labelWidth(): number {
      return Math.min(Math.round(this.width * 0.4), 280);
    },
    labelChars(): number {
      return Math.max(4, Math.floor((this.labelWidth - 8) / CHAR_WIDTH));
    },
    barPlotWidth(): number {
      return Math.max(40, this.width - this.labelWidth - 12 - VALUE_SPACE);
    },
    barAreaBottom(): number {
      return TOP + this.model.categories.length * this.bandHeight;
    },
    /** what the time axis shows: days, the hours of a day, or other values (weekdays) */
    axisKind(): 'day'|'hour'|'other' {
      const { categories } = this.model;
      if (!categories.length) {
        return 'other';
      }
      if (categories.every((c) => /^\d{4}-\d{2}-\d{2}$/.test(c))) {
        return 'day';
      }
      const isHour = (c: string) => this.hourLabels.includes(c) || /^\d{1,2}$/.test(c);
      return categories.every(isHour) ? 'hour' : 'other';
    },
    /** the one day whose hours the axis shows, '' otherwise */
    hoursOfDay(): string {
      const days = this.periodDays || [];
      return this.axisKind === 'hour' && days.length === 1 ? days[0] : '';
    },
    /** under the axis, when it shows hours: whose hours they are */
    axisTitle(): string {
      return this.hoursOfDay
        ? translate('FeinwerkDataExploration_TrendHours', this.dayLabel(this.hoursOfDay, true))
        : '';
    },
    tooltipTitle(): string {
      const category = this.model.categories[this.hoverIndex === null ? 0 : this.hoverIndex] || '';
      if (this.axisKind !== 'hour') {
        return this.dayLabel(category, true);
      }
      const hour = formatHour(category);
      return this.hoursOfDay ? `${this.dayLabel(this.hoursOfDay, true)}, ${hour}` : hour;
    },
    /** every how many days (hours, values) the axis has a label: readable steps that fit */
    categoryStep(): number {
      const { categories } = this.model;
      const labels = categories.map((category) => this.axisLabel(category));
      // about 6.5 pixels per character at 11 pixels, and a gap
      const labelWidth = Math.max(0, ...labels.map((label) => label.length)) * 6.5 + 16;
      const plotWidth = this.width - LINE_LEFT - LINE_RIGHT;
      return axisLabelStep(categories.length, plotWidth, labelWidth, this.axisKind);
    },
    note(): string {
      if (this.chartType === 'donut') {
        return this.othersNote ? translate(this.othersNote) : '';
      }
      if (this.chartType === 'trend') {
        return this.model.omittedRows > 0
          ? translate(
            'FeinwerkDataExploration_TrendFirstRows',
            String(this.model.series.length),
            String(this.rows.length),
          )
          : '';
      }
      if (this.model.incomplete) {
        return translate('FeinwerkDataExploration_ChartIncomplete');
      }
      if (this.model.omittedRows > 0) {
        return translate(
          'FeinwerkDataExploration_ChartOmittedRows',
          String(this.model.categories.length),
        );
      }
      if (this.model.omittedSeries > 0) {
        const dimension = this.catalog.dimensions.find((d) => d.id === this.state.dimensions[1]);
        return translate(
          'FeinwerkDataExploration_ChartOmittedSeries',
          String(this.model.series.length),
          dimension?.name || '',
        );
      }
      return '';
    },
    ariaLabel(): string {
      return translate(
        'FeinwerkDataExploration_ChartLabel',
        this.metricName,
        String(this.model.categories.length),
      );
    },
    /** the width of the hover area of one day */
    hoverWidth(): number {
      const count = this.model.categories.length;
      const plotWidth = this.width - LINE_LEFT - LINE_RIGHT;
      return count < 2 ? plotWidth : plotWidth / (count - 1);
    },
    /** the hovered day's value of every line that has one, as Matomo's tooltip lists them */
    tooltipRows(): {
      key: string, label: string, value: string, classes: string[], colourName?: string,
    }[] {
      const index = this.hoverIndex;
      if (index === null) {
        return [];
      }
      return this.model.series.flatMap((series, seriesIndex) => {
        const point = series.points[index];
        if (!point || point.value === null) {
          return [];
        }
        return [{
          key: `${seriesIndex}`,
          label: point.date ? `${series.label} (${this.dayLabel(point.date)})` : series.label,
          value: this.format(point.value),
          classes: this.seriesClasses(seriesIndex, series),
          colourName: this.seriesColourName(seriesIndex, series),
        }];
      });
    },
    /** next to the hovered day, on its left in the right half of the chart */
    tooltipStyle(): Record<string, string> {
      const index = this.hoverIndex === null ? 0 : this.hoverIndex;
      const x = this.lineX(index);
      const top = `${LINE_TOP}px`;
      return x > this.width / 2
        ? { right: `${Math.round(this.width - x + 12)}px`, top, left: 'auto' }
        : { left: `${Math.round(x + 12)}px`, top };
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
    linePath,
    truncateLabel,
    measure(root: HTMLElement) {
      const width = Math.round(root.clientWidth);
      if (width > 0 && width !== this.width) {
        this.width = width;
      }
    },
    onMetric(event: Event) {
      this.$emit('metric', (event.target as HTMLSelectElement).value);
    },
    donutPath(index: number): string {
      const before = this.donut.slices.slice(0, index).reduce((sum, slice) => sum + slice.share, 0);
      return donutArc(before, this.donut.slices[index].share, 90, 56, 100);
    },
    sliceName(slice: DonutSlice): string {
      return slice.others ? translate(slice.label) : slice.label;
    },
    sharePercent(share: number): string {
      return NumberFormatter.formatPercent(Math.round(share * 1000) / 10);
    },
    sliceTitle(slice: DonutSlice): string {
      const share = this.sharePercent(slice.share);
      return `${this.sliceName(slice)}: ${this.format(slice.value)} (${share})`;
    },
    format(value: number|null): string {
      return value === null ? '' : formatMetric(this.metric, value, this.formatters);
    },
    formatTick(value: number): string {
      return formatMetric(this.metric, value, this.formatters);
    },
    dayLabel(value: string, full = false): string {
      return formatDay(value, Matomo.language, full);
    },
    /** a label of the time axis: a short day, an hour as a time of day, or the value */
    axisLabel(category: string): string {
      return this.axisKind === 'hour'
        ? formatHour(category)
        : truncateLabel(this.dayLabel(category), 16);
    },
    /** a dot on the hovered day, and on points without a line (no neighbour) */
    showsDot(points: ChartPoint[], index: number): boolean {
      return points[index].value !== null
        && (index === this.hoverIndex || this.isolated(points, index));
    },
    /** a point without a neighbour has no line: it keeps its dot */
    isolated(points: ChartPoint[], index: number): boolean {
      const before = index > 0 ? points[index - 1].value : null;
      const after = index < points.length - 1 ? points[index + 1].value : null;
      return points[index].value !== null && before === null && after === null;
    },
    /** colours: the plugin's series (Matomo's palette), or Matomo's colours of a comparison */
    seriesClasses(seriesIndex: number, series: ChartSeries): string[] {
      const colour = (this.colourIndex(seriesIndex, series) % SERIES_COLOURS) + 1;
      const classes = ['fwde-chart__series', `fwde-chart__series-${colour}`];
      return series.comparison ? [...classes, 'comparison-series-color'] : classes;
    },
    seriesColourName(seriesIndex: number, series: ChartSeries): string|undefined {
      return series.comparison
        ? ComparisonsStoreInstance.getSeriesColorName(this.colourIndex(seriesIndex, series), 0)
        : undefined;
    },
    /** a comparison line keeps its column's colour, also when a line before it is missing */
    colourIndex(seriesIndex: number, series: ChartSeries): number {
      return series.comparison && series.line !== undefined ? series.line : seriesIndex;
    },
    /** keyboard: the tooltip starts at the first day */
    onFocus() {
      if (this.model.type === 'line' && this.hoverIndex === null && this.model.categories.length) {
        this.hoverIndex = 0;
      }
    },
    /** keyboard: the arrow keys move the tooltip from day to day, Escape closes it */
    onKey(event: KeyboardEvent) {
      if (this.model.type !== 'line' || this.hoverIndex === null) {
        return;
      }
      const last = this.model.categories.length - 1;
      if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
        const step = event.key === 'ArrowRight' ? 1 : -1;
        this.hoverIndex = Math.min(last, Math.max(0, this.hoverIndex + step));
        event.preventDefault();
      } else if (event.key === 'Escape') {
        this.hoverIndex = null;
      }
    },
    hoverX(index: number): number {
      return Math.max(LINE_LEFT, this.lineX(index) - this.hoverWidth / 2);
    },
    barX(value: number): number {
      const { min, max } = this.model.scale;
      return this.labelWidth + 12 + ((value - min) / (max - min)) * this.barPlotWidth;
    },
    barY(index: number): number {
      return TOP + index * this.bandHeight;
    },
    /** the top of a comparison column's bar in its row */
    groupBarY(index: number, seriesIndex: number): number {
      return this.barY(index) + GROUP_GAP / 2 + seriesIndex * GROUP_BAR_HEIGHT;
    },
    /** a grouped bar's tooltip: column, row, value, and a compared period's change */
    groupTitle(series: ChartSeries, point: ChartPoint): string {
      const value = point.value === null ? '' : this.format(point.value);
      let title = `${series.label} · ${point.label}: ${value}`;
      if (point.change !== undefined && point.change !== null) {
        const change = NumberFormatter.formatPercent(point.change, 1, 0);
        title += ` (${point.change > 0 ? `+${change}` : change})`;
      }
      return title;
    },
    lineX(index: number): number {
      const plotWidth = this.width - LINE_LEFT - LINE_RIGHT;
      const count = this.model.categories.length;
      return count < 2 ? LINE_LEFT + plotWidth / 2 : LINE_LEFT + (index * plotWidth) / (count - 1);
    },
    lineY(value: number): number {
      const { min, max } = this.model.scale;
      const plotHeight = LINE_HEIGHT - LINE_TOP - LINE_BOTTOM;
      return LINE_TOP + ((max - value) / (max - min)) * plotHeight;
    },
  },
});
</script>
