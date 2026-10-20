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
      <p
        v-if="note"
        class="fwde-chart__note"
      >{{ note }}</p>
    </div>
    <svg
      class="fwde-chart__svg"
      :width="width"
      :height="height"
      :viewBox="`0 0 ${width} ${height}`"
      role="img"
      :aria-label="ariaLabel"
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
        <g
          v-for="(point, index) in model.series[0].points"
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
            class="fwde-chart__tick"
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
          class="fwde-chart__tick"
          :x="lineX(index)"
          :y="LINE_HEIGHT - 10"
          text-anchor="middle"
        >{{ truncateLabel(category, 12) }}</text>
        <g
          v-for="(series, seriesIndex) in model.series"
          :key="`series${seriesIndex}`"
          :class="`fwde-chart__series-${seriesIndex % 6 + 1}`"
        >
          <path
            class="fwde-chart__line"
            :d="linePath(series.points.map((p) => p.value), lineX, lineY)"
          />
          <template
            v-for="(point, index) in series.points"
            :key="`point${index}`"
          >
            <circle
              v-if="point.value !== null"
              class="fwde-chart__point"
              :cx="lineX(index)"
              :cy="lineY(point.value)"
              r="3.5"
            >
              <title>{{ pointTitle(series.label, point.label, point.value) }}</title>
            </circle>
          </template>
        </g>
      </g>
    </svg>
    <ul
      v-if="model.type === 'line' && hasSecondDimension"
      class="fwde-chart__legend"
    >
      <li
        v-for="(series, seriesIndex) in model.series"
        :key="series.label"
        :class="`fwde-chart__legend-item fwde-chart__series-${seriesIndex % 6 + 1}`"
      >
        <span
          class="fwde-chart__swatch"
          aria-hidden="true"
        />
        {{ series.label }}
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { NumberFormatter, translate } from 'CoreHome';
import { Catalog, ExplorationState } from './types';
import { ResultRow } from './api';
import { formatDuration, formatMetric, Formatters } from './format';
import {
  ChartModel, chartModel, linePath, TIME_DIMENSIONS, truncateLabel,
} from './chart';

const TOP = 8;
const BAR_HEIGHT = 26;
const AXIS_HEIGHT = 26;
const LINE_HEIGHT = 300;
const LINE_LEFT = 64;
const LINE_RIGHT = 16;
const LINE_TOP = 12;
const LINE_BOTTOM = 34;
const VALUE_SPACE = 80;
const CHAR_WIDTH = 7;

export interface MetricOption {
  id: string;
  name: string;
}

export default defineComponent({
  props: {
    rows: { type: Array as PropType<ResultRow[]>, required: true },
    state: { type: Object as PropType<ExplorationState>, required: true },
    catalog: { type: Object as PropType<Catalog>, required: true },
    metric: { type: String, required: true },
    actionScope: { type: Boolean, required: true },
    currencySymbol: { type: String, required: true },
    hourLabels: { type: Array as PropType<string[]>, default: () => [] },
    periodDays: { type: Array as PropType<string[]|null>, default: null },
  },
  emits: ['metric'],
  data() {
    return {
      width: 800,
      observer: null as ResizeObserver|null,
      TOP,
      BAR_HEIGHT,
      LINE_HEIGHT,
      LINE_LEFT,
      LINE_RIGHT,
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
      const weekdays = [1, 2, 3, 4, 5, 6, 7]
        .map((day) => translate(`Intl_Day_Long_StandAlone_${day}`));
      return chartModel(this.rows, this.state, this.metric, {
        weekdayNames: weekdays,
        hourLabels: this.hourLabels,
        periodDays: this.periodDays,
        complete: this.rows.length < this.state.limit,
        // about 70 pixels per axis label; the line chart's axis is vertical and always fits five
        tickCount: TIME_DIMENSIONS.includes(this.state.dimensions[0])
          ? 5
          : Math.min(5, Math.max(2, Math.floor(this.barPlotWidth / 70))),
      });
    },
    hasSecondDimension(): boolean {
      return this.state.dimensions.length > 1;
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
      return TOP + this.model.categories.length * BAR_HEIGHT + AXIS_HEIGHT;
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
      return TOP + this.model.categories.length * BAR_HEIGHT;
    },
    categoryStep(): number {
      const fit = Math.max(1, Math.floor((this.width - LINE_LEFT - LINE_RIGHT) / 80));
      return Math.max(1, Math.ceil(this.model.categories.length / fit));
    },
    note(): string {
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
    format(value: number|null): string {
      return value === null ? '' : formatMetric(this.metric, value, this.formatters);
    },
    formatTick(value: number): string {
      return formatMetric(this.metric, value, this.formatters);
    },
    pointTitle(series: string, category: string, value: number|null): string {
      const text = `${category}: ${this.format(value)}`;
      return this.hasSecondDimension ? `${series}, ${text}` : text;
    },
    barX(value: number): number {
      const { min, max } = this.model.scale;
      return this.labelWidth + 12 + ((value - min) / (max - min)) * this.barPlotWidth;
    },
    barY(index: number): number {
      return TOP + index * BAR_HEIGHT;
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
