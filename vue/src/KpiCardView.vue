<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fwde-page fwde-kpi">
    <div
      class="fwde-kpi__title"
      :title="card.metricName"
    >{{ card.title }}</div>
    <div
      v-if="showMetric"
      class="fwde-kpi__metric"
    >{{ card.metricName }}</div>
    <div class="fwde-kpi__main">
      <span class="fwde-kpi__value">{{ valueText }}</span>
      <span
        v-if="card.partial"
        class="fwde-kpi__partial"
      >{{ translate('FeinwerkDataExploration_KpiSoFar', card.until || '') }}</span>
    </div>
    <div
      v-if="card.comparison"
      class="fwde-kpi__compare"
    >
      <span
        v-if="change"
        :class="['fwde-kpi__change', `fwde-kpi__change--${tone}`]"
      >{{ change }}</span>
      <span
        class="fwde-kpi__baseline"
        :title="baselineTitle"
      >{{ label }}</span>
    </div>
    <p
      v-else-if="card.comparisonNote"
      class="fwde-kpi__note"
    >{{ card.comparisonNote }}</p>
    <svg
      v-if="card.line && card.line.length > 1"
      class="fwde-kpi__spark"
      :viewBox="`0 0 ${WIDTH} ${HEIGHT}`"
      preserveAspectRatio="none"
      role="img"
      :aria-label="lineLabel"
    >
      <path
        class="fwde-kpi__line"
        :d="path"
      />
      <line
        v-for="mark in marks"
        :key="mark.day"
        class="fwde-kpi__mark"
        :x1="mark.x"
        :x2="mark.x"
        :y1="HEIGHT - 4"
        :y2="HEIGHT"
      ><title>{{ mark.title }}</title></line>
    </svg>
    <p
      v-else-if="card.lineNote"
      class="fwde-kpi__note"
    >{{ card.lineNote }}</p>
    <div
      v-if="!embedded && (exploreUrl || feedback.enabled)"
      class="fwde-kpi__footer"
    >
      <a
        v-if="exploreUrl"
        class="fwde-kpi__open"
        :href="exploreUrl"
      >{{ translate('FeinwerkDataExploration_OpenInExploration') }}</a>
      <FeedbackBar
        :id-site="idSite"
        topic="kpi_card"
        :question="translate('FeinwerkDataExploration_FeedbackQuestion_kpi_card')"
        :feedback="feedback"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { NumberFormatter, translate } from 'CoreHome';
import FeedbackBar from './FeedbackBar.vue';
import { embeddedPage } from './embed';
import { FeedbackSettings } from './feedback';
import { formatDuration, formatMetric, Formatters } from './format';
import { linePath } from './chart';
import {
  changeText, changeTone, comparisonLabel, KpiCardData,
} from './kpi';

const WIDTH = 300;
const HEIGHT = 44;
const PAD = 3;

/**
 * A KPI card (2026-11-24): its title, the number for the dashboard's period ("so far" when it
 * includes today), the change against its comparison, the line of the last days with the
 * comparison days marked, and the footer (Open in exploration, the feedback thumbs; no support
 * link, the dashboard widget rule).
 */
export default defineComponent({
  components: { FeedbackBar },
  props: {
    card: { type: Object as PropType<KpiCardData>, required: true },
    idSite: { type: Number, required: true },
    /** "Open in exploration"; empty in an embed, which shows no footer (UI review 2026-11-24) */
    exploreUrl: { type: String, required: true },
    /** the website's currency symbol */
    currencySymbol: { type: String, default: '' },
    feedback: {
      type: Object as PropType<FeedbackSettings>,
      default: () => ({ enabled: false, supportUrl: '', version: '' }),
    },
  },
  data() {
    // an embedded dashboard loads the card with its own module: the page's address tells
    return { WIDTH, HEIGHT, embedded: embeddedPage(window.location.search) };
  },
  computed: {
    /** the metric under the title, unless the title already is its name (UI review 2026-11-24) */
    showMetric(): boolean {
      const fold = (text: string) => text.trim().toLocaleLowerCase();
      return fold(this.card.metricName) !== '' && fold(this.card.title) !== fold(this.card.metricName);
    },
    formatters(): Formatters {
      return {
        number: (v) => NumberFormatter.formatNumber(v, 2, 0),
        percent: (v) => NumberFormatter.formatPercent(v, 2, 0),
        currency: (v) => NumberFormatter.formatCurrency(v, this.currencySymbol, 2, 2),
        duration: (v) => formatDuration(v),
      };
    },
    valueText(): string {
      return this.card.value === null
        ? translate('FeinwerkDataExploration_KpiNoValue')
        : formatMetric(this.card.metric, this.card.value, this.formatters);
    },
    change(): string {
      return changeText(this.card, this.formatters);
    },
    tone(): string {
      return changeTone(this.card);
    },
    label(): string {
      return comparisonLabel(this.card, translate);
    },
    /** the baseline's value and its days, for the tooltip */
    baselineTitle(): string {
      const { comparison } = this.card;
      if (!comparison) {
        return '';
      }
      const days = comparison.spans.filter((span) => span.value !== null)
        .map((span) => (span.firstDay === span.lastDay ? span.firstDay : `${span.firstDay}/${span.lastDay}`));
      return `${formatMetric(this.card.metric, comparison.baseline, this.formatters)} (${days.join(', ')})`;
    },
    values(): (number|null)[] {
      return (this.card.line || []).map((point) => point.value);
    },
    scale(): { min: number, max: number } {
      const numbers = this.values.filter((v): v is number => v !== null);
      const min = numbers.length ? Math.min(...numbers) : 0;
      const max = numbers.length ? Math.max(...numbers) : 1;
      return { min, max: max === min ? min + 1 : max };
    },
    path(): string {
      return linePath(this.values, this.x, this.y);
    },
    marks(): { day: string, x: number, title: string }[] {
      return (this.card.line || []).map((point, i) => ({ point, i }))
        .filter(({ point }) => point.baseline && point.value !== null)
        .map(({ point, i }) => ({
          day: point.day,
          x: this.x(i),
          title: `${point.day}: ${formatMetric(this.card.metric, point.value as number, this.formatters)}`,
        }));
    },
    lineLabel(): string {
      const line = this.card.line || [];
      return line.length ? `${this.card.metricName}: ${line[0].day} ${line[line.length - 1].day}` : '';
    },
  },
  methods: {
    translate,
    x(index: number): number {
      const count = Math.max(1, (this.card.line || []).length - 1);
      return PAD + (index * (WIDTH - 2 * PAD)) / count;
    },
    y(value: number): number {
      const { min, max } = this.scale;
      return HEIGHT - 6 - ((value - min) / (max - min)) * (HEIGHT - 6 - PAD);
    },
  },
});
</script>
