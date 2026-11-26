<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fwde-page">
    <ActivityIndicator
      v-if="!catalog && !loadError"
      :loading="true"
    />
    <Alert
      v-if="loadError"
      severity="danger"
    >{{ loadError }}</Alert>
    <div
      v-if="catalog && state"
      class="fwde-page__layout"
    >
      <aside
        class="fwde-card fwde-sidebar"
        :aria-label="translate('FeinwerkDataExploration_Setup')"
      >
        <section
          v-if="savedLoaded"
          class="fwde-section fwde-section--saved"
        >
          <SavedPicker
            :saved="saved"
            :current="currentSaved"
            :login="login"
            :can-copy="canCopy"
            :can-manage="canManage"
            :menu-sections="menuSections"
            @open="openSaved"
            @deleted="savedDeleted($event)"
            @changed="loadSavedList()"
          />
        </section>
        <section class="fwde-section">
          <h3 class="fwde-section__title">
            {{ translate('FeinwerkDataExploration_Dimensions') }}
            <span class="fwde-section__count">
              {{ translate(
                'FeinwerkDataExploration_MetricsCount', state.dimensions.length, MAX_DIMENSIONS,
              ) }}
            </span>
          </h3>
          <DimensionPicker
            ref="dimensionPicker"
            :catalog="catalog"
            :state="state"
            @change="update({ dimensions: $event })"
          />
          <PivotPicker
            :catalog="catalog"
            :state="state"
            :comparing="comparing"
            @change="update($event)"
          />
        </section>
        <section class="fwde-section">
          <h3 class="fwde-section__title">
            {{ translate('FeinwerkDataExploration_Metrics') }}
            <span class="fwde-section__count">
              {{ metricsCount }}
            </span>
          </h3>
          <MetricPicker
            :catalog="catalog"
            :state="state"
            :action-scope="actionScope"
            @change="update({ metrics: $event })"
          />
        </section>
        <section class="fwde-section">
          <h3 class="fwde-section__title">{{ translate('FeinwerkDataExploration_Filters') }}</h3>
          <ul
            v-if="filterLines.length"
            class="fwde-filtersummary"
          >
            <li
              v-for="(line, i) in filterLines"
              :key="i"
              :class="['fwde-filtersummary__item', { 'fwde-filtersummary__match': line.join }]"
            >{{ line.text }}</li>
          </ul>
          <button
            type="button"
            class="fwde-button fwde-filters__edit"
            aria-haspopup="dialog"
            @click="filtersOpen = true"
          >
            <span
              :class="filterLines.length ? 'icon-edit' : 'icon-add'"
              aria-hidden="true"
            />
            {{ translate(filterLines.length
              ? 'FeinwerkDataExploration_EditFilters'
              : 'FeinwerkDataExploration_AddFilter') }}
          </button>
          <FilterPanel
            v-model:open="filtersOpen"
            :catalog="catalog"
            :state="state"
            :action-scope="actionScope"
            @apply="update($event)"
          />
        </section>
      </aside>
      <div class="fwde-page__content">
        <section class="fwde-card fwde-main">
          <header class="fwde-card__header">
            <div class="fwde-card__heading">
              <h2 class="fwde-card__title">{{ title }}</h2>
              <span
                v-if="savedChanged"
                class="fwde-card__changed"
              >{{ translate('FeinwerkDataExploration_SavedChanged') }}</span>
            </div>
            <ExplorationToolbar
              class="fwde-card__actions"
              :state="state"
              :max-rows="maxRows"
              :view="view"
              :can-chart="canChart"
              :export-url-for="comparing ? compareExportUrl : undefined"
              @limit="update({ limit: $event })"
              @view="update({ view: $event })"
            />
            <SaveDialog
              v-if="savedLoaded"
              class="fwde-card__save"
              :current="currentSaved"
              :suggested-name="resultName"
              :can-share="canShare"
              :busy="saveBusy"
              :error="saveError"
              :menu-sections="menuSections"
              :metrics="saveMetrics"
              :history-lost-from="historyLostFrom"
              @open="saveError = ''"
              @save="saveExploration"
              @delete="deleteCurrent"
            />
          </header>
          <div
            v-if="chips"
            class="fwde-card__chips"
          >
            <span class="fwde-chip fwde-chip--period">
              <span
                class="icon-calendar"
                aria-hidden="true"
              />{{ chips.period }}
            </span>
            <span class="fwde-chip fwde-chip--segment">
              <span
                class="icon-segment"
                aria-hidden="true"
              />{{ chips.segment }}
            </span>
          </div>
          <div class="fwde-card__body">
            <Alert
              v-if="showRawDataWarning"
              severity="warning"
            >
              {{ translate('FeinwerkDataExploration_RawDataWarning', rawDataFrom) }}
              <button
                v-if="rawDataRange"
                type="button"
                class="fwde-alert-link"
                @click="showFrom(rawDataRange)"
              >{{ translate('FeinwerkDataExploration_ShowFromFirstCompleteDay') }}</button>
            </Alert>
            <Alert
              v-if="largePeriodHint"
              severity="warning"
            >{{ largePeriodHint }}</Alert>
            <Alert
              v-if="error"
              severity="danger"
            >
              {{ error }}
              <button
                v-if="canRetry"
                type="button"
                class="fwde-alert-link fwde-retry"
                @click="run"
              >{{ translate('FeinwerkDataExploration_TryAgain') }}</button>
            </Alert>
            <Alert
              v-if="slowHint"
              severity="info"
            >{{ slowHint }}</Alert>
            <Alert
              v-if="suggestedPeriod"
              severity="info"
            >
              <template v-if="suggestedPeriod.date">
                {{ translate('FeinwerkDataExploration_SmallerPeriodHint') }}
                <button
                  type="button"
                  class="fwde-alert-link fwde-suggested-period"
                  @click="showFrom(suggestedPeriod.date)"
                >{{ suggestedPeriodLabel }}</button>
              </template>
              <template v-else>{{ translate(noSmallerPeriodKey) }}</template>
              <!-- archived saved explorations (2026-11-25): their report covers any
                   period; on its own line (UI review 2026-11-26 F3) -->
              <div
                v-if="archivedHint"
                class="fwde-archived-hint"
              >
                <button
                  v-if="archivedHint === 'open'"
                  type="button"
                  class="fwde-alert-link fwde-archived-report"
                  @click="openArchivedReport"
                >{{ translate('FeinwerkDataExploration_ArchivedOpenReport') }}</button>
                <span
                  v-else
                  class="fwde-archived-place"
                >{{ translate('FeinwerkDataExploration_ArchivedPlaceInMenu') }}</span>
              </div>
            </Alert>
            <ActivityIndicator
              :loading="loading"
              :loading-message="translate('FeinwerkDataExploration_LoadingExploration')"
            />
            <Alert
              v-if="comparing && comparison.cut"
              severity="info"
            >{{ compareLimitText }}</Alert>
            <Alert
              v-for="(message, n) in columnErrors"
              :key="`column-${n}`"
              severity="warning"
            >{{ message }}</Alert>
            <div
              v-if="!loading && !error && ownRows && !ownRows.length"
              class="pk-emptyDataTable fwde-empty"
            >
              <p class="fwde-empty__message">{{ translate(empty.message) }}</p>
              <button
                v-if="empty.editFilters"
                type="button"
                class="fwde-button fwde-empty__edit"
                aria-haspopup="dialog"
                @click="filtersOpen = true"
              >{{ translate('FeinwerkDataExploration_EditFilters') }}</button>
            </div>
            <p
              v-if="comparing && compareHint && !error && ownRows && ownRows.length
                && view !== 'table'"
              class="fwde-compare__hint"
            >{{ translate(compareHint) }}</p>
            <template v-if="showsTrend">
              <Alert
                v-if="trendError"
                severity="warning"
              >{{ trendError }}</Alert>
              <Alert
                v-for="(message, n) in trendErrors"
                :key="`trend-${n}`"
                severity="warning"
              >{{ message }}</Alert>
            </template>
            <ResultChart
              v-if="!error && ownRows && ownRows.length && view !== 'table'"
              :rows="fullRows && fullRowsKey === lastDefinition ? fullRows : ownRows"
              :row-limit="fullRows && fullRowsKey === lastDefinition ? maxRows : 0"
              :state="state"
              :catalog="catalog"
              :metric="chartMetric"
              :action-scope="actionScope"
              :currency-symbol="currencySymbol"
              :hour-labels="hourLabels"
              :period-days="periodDays"
              :chart-type="chartType"
              :show-type-switch="showTypeSwitch"
              :trend-rows="trendRows"
              :trend-loading="trendLoading"
              :max-lines="maxTrendLines"
              :compare-columns="chartCompareColumns"
              :donut-reason="donutReason"
              :others-value="donutOthers"
              :others-note="donutOthersNote"
              :pivot-values="pivotValues"
              :class="{ 'fwde-main__stale': loading }"
              @metric="update({ chartMetric: $event })"
              @chart-type="update({ chartType: $event })"
            />
            <CompareTable
              v-if="comparing && !error && rows && ownRows && ownRows.length && view !== 'chart'"
              :rows="rows"
              :state="state"
              :catalog="catalog"
              :columns="comparison.columns"
              :column-errors="columnErrors"
              :action-scope="actionScope"
              :currency-symbol="currencySymbol"
              :visits-log-enabled="visitsLogEnabled"
              :class="{ 'fwde-main__stale': loading }"
              @sort="sortBy"
              @row-filter="filterToRow"
              @row-breakdown="breakDownRow"
              @row-visits="showRowVisits"
            />
            <PivotTable
              v-if="pivoting && !error && rows && ownRows && ownRows.length && view !== 'chart'"
              :rows="rows"
              :state="state"
              :catalog="catalog"
              :action-scope="actionScope"
              :currency-symbol="currencySymbol"
              :visits-log-enabled="visitsLogEnabled"
              :class="{ 'fwde-main__stale': loading }"
              @sort="sortBy"
              @row-filter="filterToRow"
              @row-breakdown="breakDownRow"
              @row-visits="showRowVisits"
            />
            <ResultTable
              v-if="!comparing && !pivoting && !error && rows && rows.length && view !== 'chart'"
              :rows="rows"
              :state="state"
              :catalog="catalog"
              :action-scope="actionScope"
              :currency-symbol="currencySymbol"
              :visits-log-enabled="visitsLogEnabled"
              :cut="tableCut"
              :others="others"
              :others-state="othersState"
              :class="{ 'fwde-main__stale': loading }"
              @sort="sortBy"
              @row-filter="filterToRow"
              @row-breakdown="breakDownRow"
              @row-visits="showRowVisits"
              @others-calculate="calculateOthers"
            />
            <FeedbackBar
              v-if="!error && rows"
              :id-site="idSite"
              topic="free_form_table"
              :question="translate('FeinwerkDataExploration_FeedbackQuestion_free_form_table')"
              :feedback="feedback"
            />
            <PromoLine
              v-if="!error && rows"
              :promo="promo"
              :dismiss-label="translate('FeinwerkDataExploration_PromoDismiss')"
            />
          </div>
        </section>
        <PremiumBanner
          :premium="premium"
          :label="translate('FeinwerkDataExploration_PremiumLabel')"
          :more-label="translate('FeinwerkDataExploration_PremiumMore')"
          :dismiss-label="translate('FeinwerkDataExploration_PremiumDismiss')"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  ActivityIndicator, Alert, ComparisonsStoreInstance, Matomo, MatomoUrl, translate,
} from 'CoreHome';
import { RUN_DELAY_MS, needsSmallerPeriod } from './hints';
import {
  largePeriodHint, noSmallerPeriodKeyForUser, rawDataRange, retryAllowed, showsRawDataWarning,
  slowHint, suggestedPeriodLabel,
} from './pageHints';
import DimensionPicker from './DimensionPicker.vue';
import MetricPicker from './MetricPicker.vue';
import FilterPanel from './FilterPanel.vue';
import { filterSummaryLines, SummaryLine } from './filterSummary';
import { viewChips } from './viewChips';
import { emptyState } from './emptyState';
import ResultTable from './ResultTable.vue';
import CompareTable from './CompareTable.vue';
import PivotTable from './PivotTable.vue';
import PivotPicker from './PivotPicker.vue';
import { parsePivotResult, PivotValue } from './pivot';
import {
  comparisonColumns, isComparing, mergeColumn, remainingColumns, requestColumns,
  withoutComparisonColumns,
} from './comparison';
import { exportUrl } from './exportLink';
import ExplorationToolbar from './ExplorationToolbar.vue';
import SavedPicker from './SavedPicker.vue';
import { archivedReportHint, historyLostFrom } from './archiveShape';
import { formatLongDate } from './format';
import SaveDialog from './SaveDialog.vue';
import FeedbackBar from './FeedbackBar.vue';
import PromoLine, { PromoVariables } from './PromoLine.vue';
import PremiumBanner from './PremiumBanner.vue';
import { PremiumProps } from './premium';
import { FeedbackSettings } from './feedback';
import {
  CardSettings, differsFromSaved, findSaved, linkNamesOnlySaved, savedHashParams, savedKind,
  STATE_KEYS, stateToSave, withCard, withMenu,
} from './saved';
import ResultChart from './ResultChart.vue';
import {
  Catalog, ChartType, ChartView, ComparisonColumn, ExplorationState, OthersState,
} from './types';
import {
  MAX_DIMENSIONS, MAX_METRICS, NUMBER_OPERATORS, parseState, STATE_HASH_KEYS, toDefinition,
  toHashParams,
} from './state';
import {
  canChart, chartMetricFor, compareChartHint, donutBlocker, donutNeedsOthers, effectiveChartType,
  effectiveView, needsFullTimeRows, othersAnswered, resultTitle,
} from './chart';
import {
  canTrend, mergeTrendLines, needsOwnLine, trendColumns, trendIsCurrent, withOwnLine,
} from './trend';
import { combinedSegment, rowFilterState } from './rows';
import { periodDays as daysOfPeriod } from './period';
import { createRequestGuard } from './requestGuard';
import { createRunQueue } from './runQueue';
import {
  addSaved, deleteSaved, errorText, loadCatalog, loadPeriodVisits, loadSaved,
  loadSuggestedPeriod, MenuSection, PeriodVisits, ResultRow, runComparison, runExploration,
  runOthers, runTrend,
  SavedExploration, SuggestedPeriod, TrendRow, updateSaved,
} from './api';

const HASH_KEYS = STATE_HASH_KEYS.concat([
  // Matomo's comparison of segments and periods (comparison design)
  'compareSegments', 'comparePeriods', 'compareDates',
]);

/** Globals of Matomo's Live plugin (segmented visits log). */
interface WindowWithLive {
  piwik?: { visitorLogEnabled?: boolean, hasSuperUserAccess?: boolean|number, userLogin?: string };
  SegmentedVisitorLog?: {
    show(apiMethod: string, segment: string, params: Record<string, unknown>): void,
  };
}

/** The part of DimensionPicker the page opens. */
interface PickerRef {
  openSlot: number|null;
  $el?: HTMLElement;
}

interface Data {
  MAX_DIMENSIONS: number;
  saved: SavedExploration[];
  savedLoaded: boolean;
  saveBusy: boolean;
  saveError: string;
  catalog: Catalog|null;
  state: ExplorationState|null;
  rows: ResultRow[]|null;
  loading: boolean;
  error: string;
  loadError: string;
  /** the filter panel is open (UI item 4) */
  filtersOpen: boolean;
  /** the definition of the last request, as JSON: view and chart changes need no new request */
  lastDefinition: string;
  /** the period's visits and limits, null until known (or when they cannot be read) */
  periodVisits: PeriodVisits|null;
  /** how long the last request took, for the hint on slow explorations */
  elapsedMs: number|null;
  /** the most recent days that fit, when the period is too large; null while not asked for */
  suggestedPeriod: SuggestedPeriod|null;
  runTimer: number;
  abortController: AbortController|null;
  guard: ReturnType<typeof createRequestGuard>;
  /** the exact rest of the shown table ("Others", on demand), null until calculated */
  others: Record<string, number|null>|null;
  othersState: OthersState;
  /** a new run or a newer "Calculate" drops the answer of an older one */
  othersGuard: ReturnType<typeof createRequestGuard>;
  queue: ReturnType<typeof createRunQueue>;
  unmounted: boolean;
  /** by comparison column index: why that column could not be computed */
  columnErrors: Record<number, string>;
  /** the trend's lines (trend design), null until loaded */
  trendRows: TrendRow[]|null;
  trendLoading: boolean;
  /** why the trend could not be loaded (without comparing) */
  trendError: string;
  /** by comparison column index: why its trend line could not be computed */
  trendErrors: Record<number, string>;
  /** the definition the trend was asked for ('' none): the same setup needs no new request */
  trendKey: string;
  trendSequence: number;
  trendAbort: AbortController|null;
  /**
   * every row of an exploration with a time dimension, for the chart's lines when the table is cut
   * at its row limit (Peter, 2026-11-24); null until loaded
   */
  fullRows: ResultRow[]|null;
  /** the definition fullRows belong to ('' none) */
  fullRowsKey: string;
  fullRowsAbort: AbortController|null;
}

export default defineComponent({
  inheritAttrs: false,
  components: {
    ActivityIndicator,
    Alert,
    DimensionPicker,
    MetricPicker,
    FilterPanel,
    ResultTable,
    PivotTable,
    PivotPicker,
    CompareTable,
    ResultChart,
    ExplorationToolbar,
    SavedPicker,
    SaveDialog,
    FeedbackBar,
    PromoLine,
    PremiumBanner,
  },
  props: {
    idSite: { type: Number, required: true },
    maxRows: { type: Number, required: true },
    defaultRows: { type: Number, required: true },
    /** at least write access to the site: may share saved explorations */
    canShare: { type: Boolean, default: false },
    canCopy: { type: Boolean, default: false },
    /** admin access to the website: manage its shared explorations */
    canManage: { type: Boolean, default: false },
    /** Matomo's sections a saved exploration can show in as a report (saved reports, 2026-11-24) */
    menuSections: { type: Array as PropType<MenuSection[]>, default: () => [] },
    archiveHistoryFrom: { type: String, default: null },
    promo: {
      type: Object as PropType<PromoVariables|null>,
      default: null,
    },
    /** the premium banner below the result (PremiumPromo); null: none, the tip line may show */
    premium: {
      type: Object as PropType<PremiumProps|null>,
      default: null,
    },
    feedback: {
      type: Object as PropType<FeedbackSettings>,
      default: () => ({ enabled: false, supportUrl: '', version: '' }),
    },
    maxDays: { type: Number, required: true },
    rawDataFrom: { type: String, default: null },
    currencySymbol: { type: String, default: '' },
    hourLabels: { type: Array as PropType<string[]>, default: () => [] },
    /** columns per metric when comparing, the own one included (free: 4) */
    maxCompareColumns: { type: Number, default: 4 },
    /** lines of a line chart (free: 5) */
    maxTrendLines: { type: Number, default: 5 },
  },
  data(): Data {
    return {
      MAX_DIMENSIONS,
      saved: [],
      savedLoaded: false,
      saveBusy: false,
      saveError: '',
      catalog: null,
      state: null,
      rows: null,
      loading: false,
      error: '',
      loadError: '',
      filtersOpen: false,
      lastDefinition: '',
      periodVisits: null,
      elapsedMs: null,
      suggestedPeriod: null,
      runTimer: 0,
      abortController: null,
      guard: createRequestGuard(),
      others: null,
      othersState: 'idle',
      othersGuard: createRequestGuard(),
      queue: createRunQueue(),
      unmounted: false,
      columnErrors: {},
      trendRows: null,
      trendLoading: false,
      trendError: '',
      trendErrors: {},
      trendKey: '',
      trendSequence: 0,
      trendAbort: null,
      fullRows: null,
      fullRowsKey: '',
      fullRowsAbort: null,
    };
  },
  created() {
    this.loadSavedList(true);
    loadCatalog().then((catalog) => {
      this.catalog = catalog;
      this.readState();
    }).catch((e) => {
      this.loadError = errorText(e);
    });
    // warnings only: without them the page works as before
    loadPeriodVisits().then((periodVisits) => {
      this.periodVisits = periodVisits;
      this.offerSmallerPeriod(false);
    }).catch(() => {
      this.offerSmallerPeriod(true);
    });
  },
  beforeUnmount() {
    this.unmounted = true;
    window.clearTimeout(this.runTimer);
    if (this.abortController) {
      this.abortController.abort();
    }
    if (this.trendAbort) {
      this.trendAbort.abort();
    }
  },
  computed: {
    /** "n of 10" (more metrics, 2026-11-24) */
    metricsCount(): string {
      const chosen = this.state ? this.state.metrics.length : 0;
      return translate('FeinwerkDataExploration_MetricsCount', String(chosen), String(MAX_METRICS));
    },
    /** the table was cut: the row limit is reached or a number filter removed rows */
    tableCut(): boolean {
      if (!this.state || !this.rows) {
        return false;
      }
      const shown = this.rows.filter((row) => !row.fwde_total).length;
      return shown >= this.state.limit
        || this.state.filters.some((filter) => NUMBER_OPERATORS.includes(filter.operator));
    },
    hashState(): string {
      const hash = MatomoUrl.hashParsed.value;
      return HASH_KEYS.map((key) => String(hash[key] ?? '')).join('|');
    },
    /** Matomo's comparison controls: the table's columns (the own one first) */
    comparison(): { columns: ComparisonColumn[], cut: boolean } {
      return comparisonColumns(
        ComparisonsStoreInstance.getSegmentComparisons(),
        ComparisonsStoreInstance.getPeriodComparisons(),
        this.maxCompareColumns,
        translate('General_Unknown'),
      );
    },
    comparing(): boolean {
      return isComparing(this.comparison.columns);
    },
    /** a pivot applies (rows and pivot, 2026-11-24): chosen and Matomo does not compare */
    pivoting(): boolean {
      return !!this.state && !!this.state.pivot && !this.comparing;
    },
    /** the exploration's metrics with the names the table shows: a KPI card picks one of them */
    saveMetrics(): { id: string, name: string }[] {
      const { catalog, state } = this;
      if (!catalog || !state) {
        return [];
      }
      return state.metrics.map((id) => {
        const metric = catalog.metrics.find((m) => m.id === id);
        const name = metric && this.actionScope && metric.nameInActionScope
          ? metric.nameInActionScope
          : metric?.name || id;
        return { id, name };
      });
    },
    /** the pivot's values of the result, for the chart */
    pivotValues(): PivotValue[] {
      return this.pivoting && this.rows ? parsePivotResult(this.rows).values : [];
    },
    compareLimitText(): string {
      return translate('FeinwerkDataExploration_CompareLimit', String(this.maxCompareColumns));
    },
    /** the rows without the total row */
    ownRows(): ResultRow[]|null {
      return this.rows ? this.rows.filter((row) => !row.fwde_total) : null;
    },
    actionScope(): boolean {
      const { catalog, state } = this;
      if (!catalog || !state) {
        return false;
      }
      return catalog.dimensions.some((d) => state.dimensions.includes(d.id) && d.scope === 'action')
        || catalog.metrics.some((m) => state.metrics.includes(m.id) && m.scope === 'action');
    },
    /** an empty result says what to do: with filters set, edit them (UI review 2026-11-23) */
    empty(): { message: string, editFilters: boolean } {
      return emptyState(this.state ? this.state.filters.length : 0);
    },
    /** period and segment of the result (UI item 5); none while comparing */
    chips(): { period: string, segment: string } | null {
      return viewChips(
        ComparisonsStoreInstance.getSegmentComparisons(),
        ComparisonsStoreInstance.getPeriodComparisons(),
        translate('General_Unknown'),
      );
    },
    /** the applied filters for the left column (UI item 4; groups 2026-11-23) */
    filterLines(): SummaryLine[] {
      if (!this.state || !this.catalog) {
        return [];
      }
      return filterSummaryLines(this.state, this.catalog, this.actionScope, translate);
    },
    view(): ChartView {
      const rowCount = this.ownRows ? this.ownRows.length : 0;
      return this.state ? effectiveView(this.state, rowCount) : 'table';
    },
    canChart(): boolean {
      return this.state ? canChart(this.ownRows ? this.ownRows.length : 0, this.state) : false;
    },
    chartType(): ChartType {
      return this.state
        ? effectiveChartType(
          this.state,
          this.ownRows ? this.ownRows.length : 0,
          this.donutReason,
          this.pivoting,
        )
        : 'bar';
    },
    /**
     * "Bars | Trend | Donut": two rows or more where a trend is possible; with a time dimension the
     * chart is already a line over time (one row: its trend)
     */
    showTypeSwitch(): boolean {
      return !!this.state && canTrend(this.state) && !!this.ownRows && this.ownRows.length >= 2;
    },
    /** why the donut cannot show the chart metric (a lang key), null when it can (2026-11-20) */
    donutReason(): string|null {
      return this.state && this.catalog
        ? donutBlocker(this.state, this.catalog, this.chartMetric, this.comparing)
        : 'FeinwerkDataExploration_DonutNeedsCount';
    },
    /** the donut of a cut table needs the exact rest: asked automatically while the donut shows */
    donutNeedsOthers(): boolean {
      return donutNeedsOthers({
        chartType: this.chartType,
        view: this.view,
        cut: this.tableCut,
        othersState: this.othersState,
        loading: this.loading,
      });
    },
    /** the exact rest of the chart metric for the donut, null until calculated */
    donutOthers(): number|null {
      if (!this.tableCut || !this.others || this.othersState !== 'done') {
        return null;
      }
      const value = this.others[this.chartMetric];
      return typeof value === 'number' ? value : null;
    },
    /** why the donut's "Others" lacks the cut rows, '' when it is complete */
    donutOthersNote(): string {
      if (!this.tableCut || this.othersState === 'done') {
        return '';
      }
      return this.othersState === 'failed'
        ? 'FeinwerkDataExploration_OthersFailed'
        : 'FeinwerkDataExploration_OthersLoading';
    },
    showsTrend(): boolean {
      return !this.error && !!this.ownRows && this.ownRows.length > 0 && this.view !== 'table'
        && this.chartType === 'trend';
    },
    /** the comparison columns for the chart: lines of a time dimension, grouped bars otherwise */
    chartCompareColumns(): ComparisonColumn[] {
      return this.comparing ? this.comparison.columns : [];
    },
    /** the hint above a compared chart; none for grouped bars (they show every column) */
    compareHint(): string {
      const { state, chartType, chartCompareColumns } = this;
      return state ? compareChartHint(state, chartType, chartCompareColumns.length) : '';
    },
    /** the setup the visible trend needs, '' while none is visible or the table still loads */
    trendRequestKey(): string {
      return this.showsTrend && !this.loading ? this.lastDefinition : '';
    },
    /** the definition whose every row the chart's time lines need ('' none) */
    fullRowsRequestKey(): string {
      if (!this.state || this.loading || this.error || !this.ownRows || this.view === 'table') {
        return '';
      }
      const { length } = this.ownRows;
      return needsFullTimeRows(this.state, length, this.maxRows, this.comparing, this.pivoting)
        ? this.lastDefinition
        : '';
    },
    chartMetric(): string {
      return this.state ? chartMetricFor(this.state) : '';
    },
    largePeriodHint(): string {
      return largePeriodHint(this.periodVisits);
    },
    slowHint(): string {
      return slowHint(this.elapsedMs, this.periodVisits, this.loading, this.error);
    },
    suggestedPeriodLabel(): string {
      return suggestedPeriodLabel(this.suggestedPeriod?.days || 0);
    },
    canRetry(): boolean {
      return retryAllowed(this.error);
    },
    noSmallerPeriodKey(): string {
      return noSmallerPeriodKeyForUser();
    },
    visitsLogEnabled(): boolean {
      const { piwik } = window as unknown as WindowWithLive;
      return !!(piwik && piwik.visitorLogEnabled);
    },
    title(): string {
      return this.currentSaved ? this.currentSaved.name : this.resultName;
    },
    resultName(): string {
      return this.catalog && this.state ? resultTitle(this.state, this.catalog) : '';
    },
    currentSaved(): SavedExploration|null {
      // a KPI card opens as its exploration on this page (2026-11-24)
      return findSaved(this.saved, String(MatomoUrl.parsed.value.fwdeSaved || ''), ['table', 'kpi']);
    },
    /** The page shows another setup than the open saved exploration (changed, or an older link). */
    savedChanged(): boolean {
      if (!this.currentSaved || !this.state || !this.catalog) {
        return false;
      }
      const limits = { defaultRows: this.defaultRows, maxRows: this.maxRows };
      const segment = String(MatomoUrl.parsed.value.segment || '');
      return differsFromSaved(
        this.currentSaved,
        toHashParams(this.state),
        segment,
        this.catalog,
        limits,
      );
    },
    login(): string {
      const { piwik } = window as unknown as WindowWithLive;
      return String((piwik && piwik.userLogin) || '');
    },
    periodDays(): string[]|null {
      const parsed = MatomoUrl.parsed.value;
      const today = new Date().toISOString().substring(0, 10);
      return daysOfPeriod(String(parsed.period), String(parsed.date), today);
    },
    rawDataRange(): string|null {
      return rawDataRange(this.rawDataFrom);
    },
    showRawDataWarning(): boolean {
      return showsRawDataWarning(this.rawDataFrom);
    },
    /** the archived exploration's report hint for a period it cannot cover (archived reports) */
    archivedHint(): ''|'open'|'place' {
      return archivedReportHint(this.currentSaved);
    },
    /**
     * the first day a changed archived exploration rebuilds (archive_history_months or the raw
     * data's start): a change of what shapes its archive starts its history again (the panel warns)
     */
    historyLostFrom(): string {
      const hash = MatomoUrl.hashParsed.value as Record<string, unknown>;
      const from = historyLostFrom(this.currentSaved, hash, String(hash.segment || ''), this.archiveHistoryFrom);
      return from ? formatLongDate(from, Matomo.language) : '';
    },
  },
  watch: {
    donutNeedsOthers(needed: boolean) {
      if (needed) {
        this.calculateOthers();
      }
    },
    hashState() {
      // back/forward or a pasted link changed the exploration parameters
      this.readState();
    },
    trendRequestKey(key: string) {
      if (key !== '' && key !== this.trendKey) {
        this.loadTrend(key);
      }
    },
    fullRowsRequestKey(key: string) {
      if (key !== '' && key !== this.fullRowsKey) {
        this.loadFullRows(key);
      }
    },
  },
  methods: {
    translate,
    readState() {
      if (!this.catalog) {
        return;
      }
      const limits = { defaultRows: this.defaultRows, maxRows: this.maxRows };
      const state = parseState(MatomoUrl.parsed.value, this.catalog, limits);
      this.state = state;
      // "Explore this row" from a report: open the second-dimension picker once
      if (MatomoUrl.parsed.value.fwdeBreakdown === '1') {
        this.openNextDimension();
        MatomoUrl.updateHash({ ...MatomoUrl.hashParsed.value, fwdeBreakdown: '' });
      }
      const definition = JSON.stringify([
        toDefinition(state, this.comparing),
        this.comparing ? this.comparison.columns : [],
      ]);
      if (definition !== this.lastDefinition) {
        const first = this.lastDefinition === '';
        this.lastDefinition = definition;
        // a trend of the old setup is void: its open and further requests stop
        if (this.trendAbort) {
          this.trendAbort.abort();
        }
        // so are the chart's full rows
        if (this.fullRowsAbort) {
          this.fullRowsAbort.abort();
        }
        this.fullRows = null;
        this.fullRowsKey = '';
        // a short pause, so a few quick changes start one request; the table is dimmed meanwhile
        window.clearTimeout(this.runTimer);
        if (first) {
          this.run();
        } else {
          this.loading = true;
          this.runTimer = window.setTimeout(() => this.run(), RUN_DELAY_MS);
        }
      }
    },
    /** a change of the exploration: validated, then into the URL hash (which runs it) */
    update(change: Partial<ExplorationState>) {
      if (!this.state || !this.catalog) {
        return;
      }
      const next = { ...this.state, ...change };
      const limits = { defaultRows: this.defaultRows, maxRows: this.maxRows };
      // re-validate: e.g. a removed dimension makes filters and the sort column on it invalid
      const valid = parseState(toHashParams(next), this.catalog, limits);
      MatomoUrl.updateHash({
        ...MatomoUrl.hashParsed.value, ...toHashParams(valid), fwdeBreakdown: '',
      });
    },
    /** Asks for the most recent days that fit when the period is too large or too long. */
    offerSmallerPeriod(countFailed: boolean) {
      if (!needsSmallerPeriod(this.periodVisits, countFailed)) {
        return;
      }
      loadSuggestedPeriod().then((suggestion) => {
        this.suggestedPeriod = suggestion || {};
      }).catch(() => undefined);
    },
    /** The archived exploration's own menu page, for the same period (archived reports). */
    openArchivedReport() {
      const saved = this.currentSaved;
      if (saved && saved.menu) {
        MatomoUrl.updateHash({
          ...MatomoUrl.hashParsed.value,
          category: saved.menu,
          subcategory: `fwdeSaved${saved.id}`,
        });
      }
    },
    /** Another period for the same exploration: a range of days (the page reloads its widget). */
    showFrom(range: string) {
      MatomoUrl.updateHash({ ...MatomoUrl.hashParsed.value, period: 'range', date: range });
    },
    /**
     * The saved explorations the user may open; without them the page works as before.
     *
     * @param openFromLink a link that names only a saved exploration (a bookmark) opens its setup
     */
    loadSavedList(openFromLink = false): Promise<void> {
      return loadSaved().then((list) => {
        this.saved = list;
        this.savedLoaded = true;
        const linked = this.currentSaved;
        if (openFromLink && linked && linkNamesOnlySaved(MatomoUrl.hashParsed.value)) {
          this.openSaved(linked);
        }
      }).catch(() => undefined);
    },
    /**
     * A delete in the manage panel: the list again, and the open exploration, when it was the one,
     * stays on screen as a new one, as after a delete in the save panel (deleteCurrent).
     */
    savedDeleted(id: number) {
      this.loadSavedList().then(() => {
        const hash = MatomoUrl.hashParsed.value;
        if (String(hash.fwdeSaved || '') === String(id)) {
          MatomoUrl.updateHash({ ...hash, fwdeSaved: '' });
        }
      });
    },
    /** Opens a saved exploration (its state and segment), or a new one with the default state. */
    openSaved(saved: SavedExploration|null) {
      const hash = { ...MatomoUrl.hashParsed.value };
      if (saved) {
        MatomoUrl.updateHash({ ...hash, ...savedHashParams(saved) });
        return;
      }
      STATE_KEYS.forEach((key) => {
        hash[key] = '';
      });
      MatomoUrl.updateHash({ ...hash, fwdeSaved: '' });
    },
    saveExploration(event: {
      name: string, shared: boolean, asNew: boolean, menu: string, card?: CardSettings,
    }) {
      if (!this.state) {
        return;
      }
      const explored = stateToSave(toHashParams(this.state));
      const fields = {
        name: event.name,
        // the menu place goes into the saved state, never into the link (saved reports); a KPI card
        // keeps its settings instead and has no menu place (2026-11-24)
        state: event.card ? withCard(explored, event.card) : withMenu(explored, event.menu),
        segment: String(MatomoUrl.parsed.value.segment || ''),
        shared: event.shared,
      };
      const current = this.currentSaved;
      // a saved funnel is never overwritten by a table, a table never by a card (and back)
      const request = current && current.canEdit && !event.asNew
        && savedKind(current) === (event.card ? 'kpi' : 'table')
        ? updateSaved(current.id, fields).then(() => current.id)
        : addSaved(fields).then((result) => Number(result.id));
      this.saveBusy = true;
      this.saveError = '';
      request.then((id) => this.loadSavedList().then(() => {
        MatomoUrl.updateHash({ ...MatomoUrl.hashParsed.value, fwdeSaved: String(id) });
      })).catch((e) => {
        this.saveError = errorText(e);
      }).finally(() => {
        this.saveBusy = false;
      });
    },
    deleteCurrent() {
      const current = this.currentSaved;
      if (!current) {
        return;
      }
      this.saveBusy = true;
      this.saveError = '';
      deleteSaved(current.id)
        .then(() => this.loadSavedList())
        .then(() => {
          MatomoUrl.updateHash({ ...MatomoUrl.hashParsed.value, fwdeSaved: '' });
        })
        .catch((e) => {
          this.saveError = errorText(e);
        })
        .finally(() => {
          this.saveBusy = false;
        });
    },
    filterToRow(row: ResultRow) {
      if (this.state) {
        this.update(rowFilterState(this.state, row));
      }
    },
    breakDownRow(row: ResultRow) {
      this.filterToRow(row);
      this.openNextDimension();
    },
    /** the dimension picker's next empty slot ("break down", "Explore this row") */
    openNextDimension() {
      this.$nextTick(() => {
        const picker = this.$refs.dimensionPicker as PickerRef|undefined;
        if (picker) {
          const chosen = this.state ? this.state.dimensions.length : 1;
          picker.openSlot = Math.min(MAX_DIMENSIONS - 1, chosen);
          if (picker.$el) {
            picker.$el.scrollIntoView({ block: 'nearest' });
          }
        }
      });
    },
    /** Matomo's segmented visits log: the row's visits within the page's segment. */
    showRowVisits(row: ResultRow) {
      const segment = combinedSegment(
        String(MatomoUrl.parsed.value.segment || ''),
        row.segment ? String(row.segment) : null,
      );
      const log = (window as unknown as WindowWithLive).SegmentedVisitorLog;
      if (segment && log) {
        log.show('FeinwerkDataExploration.getExploration', segment, {});
      }
    },
    sortBy(column: string) {
      if (!this.state) {
        return;
      }
      const isDescending = this.state.sort === column && this.state.sortOrder === 'desc';
      const sortOrder = isDescending ? 'asc' : 'desc';
      this.update({ sort: column, sortOrder });
    },
    run() {
      if (!this.state) {
        return;
      }
      this.loading = true;
      this.error = '';
      // one exploration at a time: a change while one runs starts when it ends (see runQueue)
      if (this.queue.request()) {
        this.start();
      }
    },
    /**
     * The comparison table, column by column (comparison design 5): first the own view with its
     * total and shares (asked as its own column 1), then each comparison in its own request; a
     * column that fails gets a hint, the others stay.
     */
    runCompared(
      state: ExplorationState,
      latest: () => boolean,
      abortController: AbortController,
    ): Promise<void> {
      const { columns } = this.comparison;
      return runComparison(state, columns[0], abortController).then((first) => {
        if (!latest()) {
          return undefined;
        }
        this.rows = withoutComparisonColumns(first);
        this.columnErrors = {};
        return requestColumns(
          remainingColumns(this.rows, columns),
          (n) => runComparison(state, columns[n], abortController),
          () => latest() && !abortController.signal.aborted,
          (n, rows) => {
            if (this.rows) {
              this.rows = mergeColumn(this.rows, rows, n);
            }
          },
          (n, e) => {
            this.columnErrors = { ...this.columnErrors, [n]: errorText(e) };
          },
        );
      });
    },
    /**
     * The trend (trend design 3): only while it is visible, after the table; without comparing one
     * request for the first rows, otherwise one per comparison column. A newer setup makes older
     * answers void; a failing column keeps the other lines.
     */
    loadTrend(key: string) {
      if (!this.state) {
        return;
      }
      if (this.trendAbort) {
        this.trendAbort.abort();
      }
      const abortController = new AbortController();
      this.trendAbort = abortController;
      this.trendSequence += 1;
      const sequence = this.trendSequence;
      const latest = () => !this.unmounted && trendIsCurrent({ sequence, key }, {
        sequence: this.trendSequence,
        definition: this.lastDefinition,
        aborted: abortController.signal.aborted,
      });
      const { state } = this;
      const { columns } = this.comparison;
      this.trendKey = key;
      this.trendRows = null;
      this.trendError = '';
      this.trendErrors = {};
      this.trendLoading = true;
      let lines: TrendRow[] = [];
      requestColumns(
        trendColumns(columns),
        (n) => runTrend(state, n === 0 ? null : columns[n], abortController),
        latest,
        (n, rows) => {
          lines = mergeTrendLines(lines, rows as TrendRow[], n);
          this.trendRows = lines;
        },
        (n, e) => {
          if (n === 0) {
            this.trendError = errorText(e);
            // the same setup may be asked again (switch away and back)
            this.trendKey = '';
          } else {
            this.trendErrors = { ...this.trendErrors, [n]: errorText(e) };
          }
        },
      ).then(() => {
        // every comparison failed: the own line still shows
        if (!latest() || !needsOwnLine(lines, columns)) {
          return undefined;
        }
        return runTrend(state, columns[0], abortController).then((rows) => {
          if (latest()) {
            lines = withOwnLine(lines, rows as TrendRow[]);
            this.trendRows = lines;
          }
        }).catch(() => undefined);
      }).finally(() => {
        if (latest()) {
          this.trendLoading = false;
        }
      });
    },
    /**
     * Every row of the exploration (the largest row limit) for the chart's time lines: the table
     * keeps its limit. On failure the chart stays with the table's rows and its hint.
     */
    loadFullRows(key: string) {
      if (!this.state) {
        return;
      }
      if (this.fullRowsAbort) {
        this.fullRowsAbort.abort();
      }
      const abortController = new AbortController();
      this.fullRowsAbort = abortController;
      this.fullRowsKey = key;
      this.fullRows = null;
      runExploration({ ...this.state, limit: this.maxRows }, abortController).then((rows) => {
        if (!this.unmounted && !abortController.signal.aborted && this.lastDefinition === key) {
          this.fullRows = rows;
        }
      }).catch(() => {
        if (this.lastDefinition === key) {
          // the same setup may be asked again later
          this.fullRowsKey = '';
        }
      });
    },
    compareExportUrl(format: 'CSV'|'TSV'): string {
      return this.state ? exportUrl(this.state, format, this.comparison.columns) : '';
    },
    /** the exact rest of the shown table, asked by "Calculate" (design 2026-11-20: on demand) */
    calculateOthers() {
      if (!this.state) {
        return;
      }
      const id = this.othersGuard.start();
      this.othersState = 'loading';
      runOthers(this.state).then((others) => {
        if (this.othersGuard.isLatest(id)) {
          ({ others: this.others, othersState: this.othersState } = othersAnswered(others));
        }
      }).catch(() => {
        if (this.othersGuard.isLatest(id)) {
          this.othersState = 'failed';
        }
      });
    },
    start() {
      if (!this.state || this.unmounted) {
        this.queue.finish();
        return;
      }
      // a new table: an earlier rest no longer belongs to it, and a pending one is dropped
      this.othersGuard.start();
      this.others = null;
      this.othersState = 'idle';
      const abortController = new AbortController();
      this.abortController = abortController;
      const id = this.guard.start();
      const started = Date.now();
      this.elapsedMs = null;
      const latest = () => this.guard.isLatest(id) && !this.queue.isPending();
      const request = this.comparing
        ? this.runCompared(this.state, latest, abortController)
        : runExploration(this.state, abortController).then((rows) => {
          if (latest()) {
            this.rows = rows;
          }
        });
      request.then(() => {
        if (latest()) {
          this.elapsedMs = Date.now() - started;
        }
      }).catch((e) => {
        if (this.guard.isLatest(id) && !this.queue.isPending() && !abortController.signal.aborted) {
          this.error = errorText(e);
        }
      }).finally(() => {
        if (this.queue.finish()) {
          this.start();
        } else if (this.guard.isLatest(id)) {
          this.loading = false;
        }
      });
    },
  },
});
</script>
