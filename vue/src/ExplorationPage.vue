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
            @open="openSaved"
          />
        </section>
        <section class="fwde-section">
          <h3 class="fwde-section__title">
            {{ translate('FeinwerkDataExploration_Dimensions') }}
          </h3>
          <DimensionPicker
            ref="dimensionPicker"
            :catalog="catalog"
            :state="state"
            @change="update({ dimensions: $event })"
          />
        </section>
        <section class="fwde-section">
          <h3 class="fwde-section__title">
            {{ translate('FeinwerkDataExploration_Metrics') }}
            <span class="fwde-section__count">
              {{ translate('FeinwerkDataExploration_MetricsCount', state.metrics.length, 5) }}
            </span>
          </h3>
          <MetricPicker
            :catalog="catalog"
            :state="state"
            :goals="goals"
            :action-scope="actionScope"
            @change="update({ metrics: $event })"
            @goal="update({ idGoal: $event === '' ? null : Number($event) })"
          />
        </section>
        <section class="fwde-section">
          <h3 class="fwde-section__title">{{ translate('FeinwerkDataExploration_Filters') }}</h3>
          <FilterEditor
            :key="filterEditorKey"
            :catalog="catalog"
            :state="state"
            :action-scope="actionScope"
            @change="update({ filters: $event }, true)"
          />
        </section>
      </aside>
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
            @open="saveError = ''"
            @save="saveExploration"
            @delete="deleteCurrent"
          />
        </header>
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
            class="pk-emptyDataTable"
          >{{ translate('CoreHome_ThereIsNoDataForThisReport') }}</div>
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
            :rows="ownRows"
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
          <ResultTable
            v-if="!comparing && !error && rows && rows.length && view !== 'chart'"
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
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  ActivityIndicator, Alert, ComparisonsStoreInstance, MatomoUrl, translate,
} from 'CoreHome';
import { RUN_DELAY_MS, needsSmallerPeriod } from './hints';
import {
  largePeriodHint, noSmallerPeriodKeyForUser, rawDataRange, retryAllowed, showsRawDataWarning,
  slowHint, suggestedPeriodLabel,
} from './pageHints';
import DimensionPicker from './DimensionPicker.vue';
import MetricPicker from './MetricPicker.vue';
import FilterEditor from './FilterEditor.vue';
import ResultTable from './ResultTable.vue';
import CompareTable from './CompareTable.vue';
import {
  comparisonColumns, isComparing, mergeColumn, remainingColumns, requestColumns,
  withoutComparisonColumns,
} from './comparison';
import { exportUrl } from './exportLink';
import ExplorationToolbar from './ExplorationToolbar.vue';
import SavedPicker from './SavedPicker.vue';
import SaveDialog from './SaveDialog.vue';
import FeedbackBar from './FeedbackBar.vue';
import PromoLine, { PromoVariables } from './PromoLine.vue';
import { FeedbackSettings } from './feedback';
import {
  differsFromSaved, findSaved, linkNamesOnlySaved, savedHashParams, savedKind, stateToSave,
  STATE_KEYS,
} from './saved';
import ResultChart from './ResultChart.vue';
import {
  Catalog, ChartType, ChartView, ComparisonColumn, ExplorationState,
} from './types';
import { parseState, toDefinition, toHashParams } from './state';
import {
  canChart, chartMetricFor, compareChartHint, effectiveView, resultTitle,
} from './chart';
import {
  canTrend, chartTypeFor, mergeTrendLines, needsOwnLine, trendColumns, trendIsCurrent, withOwnLine,
} from './trend';
import { combinedSegment, filtersForRow } from './rows';
import { periodDays as daysOfPeriod } from './period';
import { createRequestGuard } from './requestGuard';
import { createRunQueue } from './runQueue';
import {
  addSaved, deleteSaved, errorText, Goal, loadCatalog, loadGoals, loadPeriodVisits, loadSaved,
  loadSuggestedPeriod, PeriodVisits, ResultRow, runComparison, runExploration, runTrend,
  SavedExploration, SuggestedPeriod, TrendRow, updateSaved,
} from './api';

const HASH_KEYS = [
  'fwdeDims', 'fwdeMetrics', 'fwdeGoal', 'fwdeSort', 'fwdeOrder', 'fwdeLimit', 'fwdeFilters',
  'fwdeView', 'fwdeChart', 'fwdeChartType',
  // Matomo's comparison of segments and periods (comparison design)
  'compareSegments', 'comparePeriods', 'compareDates',
];

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
  saved: SavedExploration[];
  savedLoaded: boolean;
  saveBusy: boolean;
  saveError: string;
  catalog: Catalog|null;
  goals: Goal[];
  state: ExplorationState|null;
  rows: ResultRow[]|null;
  loading: boolean;
  error: string;
  loadError: string;
  filterEditorKey: number;
  /** the filters the editor emitted last, as JSON */
  editorFilters: string;
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
}

export default defineComponent({
  inheritAttrs: false,
  components: {
    ActivityIndicator,
    Alert,
    DimensionPicker,
    MetricPicker,
    FilterEditor,
    ResultTable,
    CompareTable,
    ResultChart,
    ExplorationToolbar,
    SavedPicker,
    SaveDialog,
    FeedbackBar,
    PromoLine,
  },
  props: {
    idSite: { type: Number, required: true },
    maxRows: { type: Number, required: true },
    defaultRows: { type: Number, required: true },
    /** at least write access to the site: may share saved explorations */
    canShare: { type: Boolean, default: false },
    canCopy: { type: Boolean, default: false },
    promo: {
      type: Object as PropType<PromoVariables|null>,
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
      saved: [],
      savedLoaded: false,
      saveBusy: false,
      saveError: '',
      catalog: null,
      goals: [],
      state: null,
      rows: null,
      loading: false,
      error: '',
      loadError: '',
      filterEditorKey: 1,
      editorFilters: '',
      lastDefinition: '',
      periodVisits: null,
      elapsedMs: null,
      suggestedPeriod: null,
      runTimer: 0,
      abortController: null,
      guard: createRequestGuard(),
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
    };
  },
  created() {
    this.loadSavedList(true);
    Promise.all([loadCatalog(), loadGoals()]).then(([catalog, goals]) => {
      this.catalog = catalog;
      this.goals = goals;
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
    view(): ChartView {
      const rowCount = this.ownRows ? this.ownRows.length : 0;
      return this.state ? effectiveView(this.state, rowCount) : 'table';
    },
    canChart(): boolean {
      return this.state ? canChart(this.ownRows ? this.ownRows.length : 0, this.state) : false;
    },
    chartType(): ChartType {
      return this.state ? chartTypeFor(this.state, this.ownRows ? this.ownRows.length : 0) : 'bar';
    },
    /** "Bars | Trend": a trend is possible and there are two rows or more (one row: the trend) */
    showTypeSwitch(): boolean {
      return !!this.state && canTrend(this.state) && !!this.ownRows && this.ownRows.length >= 2;
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
      return findSaved(this.saved, String(MatomoUrl.parsed.value.fwdeSaved || ''), 'table');
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
  },
  watch: {
    hashState() {
      // back/forward or a pasted link changed the exploration parameters
      this.readState();
    },
    trendRequestKey(key: string) {
      if (key !== '' && key !== this.trendKey) {
        this.loadTrend(key);
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
      // rebuild the editor only for filters that changed elsewhere (link, back button, a removed
      // dimension); after its own input it keeps its rows and the focus
      const filters = JSON.stringify(state.filters);
      if (filters !== this.editorFilters) {
        this.editorFilters = filters;
        this.filterEditorKey += 1;
      }
      this.state = state;
      // "Explore this row" from a report: open the second-dimension picker once
      if (MatomoUrl.parsed.value.fwdeBreakdown === '1') {
        this.openSecondDimension();
        MatomoUrl.updateHash({ ...MatomoUrl.hashParsed.value, fwdeBreakdown: '' });
      }
      const definition = JSON.stringify([
        toDefinition(state),
        this.comparing ? this.comparison.columns : [],
      ]);
      if (definition !== this.lastDefinition) {
        const first = this.lastDefinition === '';
        this.lastDefinition = definition;
        // a trend of the old setup is void: its open and further requests stop
        if (this.trendAbort) {
          this.trendAbort.abort();
        }
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
    /**
     * @param fromEditor the filter editor's own change: it keeps its rows and focus, every other
     *                   filter change rebuilds it
     */
    update(change: Partial<ExplorationState>, fromEditor = false) {
      if (!this.state || !this.catalog) {
        return;
      }
      const next = { ...this.state, ...change };
      const limits = { defaultRows: this.defaultRows, maxRows: this.maxRows };
      // re-validate: e.g. a removed dimension makes filters and the sort column on it invalid
      const valid = parseState(toHashParams(next), this.catalog, limits);
      if (change.filters && fromEditor) {
        this.editorFilters = JSON.stringify(valid.filters);
      }
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
    saveExploration(event: { name: string, shared: boolean, asNew: boolean }) {
      if (!this.state) {
        return;
      }
      const fields = {
        name: event.name,
        state: stateToSave(toHashParams(this.state)),
        segment: String(MatomoUrl.parsed.value.segment || ''),
        shared: event.shared,
      };
      const current = this.currentSaved;
      // a saved funnel is never overwritten by a table (its page saves it)
      const request = current && current.canEdit && !event.asNew && savedKind(current) === 'table'
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
        this.update({ filters: filtersForRow(this.state, row) });
      }
    },
    breakDownRow(row: ResultRow) {
      this.filterToRow(row);
      this.openSecondDimension();
    },
    openSecondDimension() {
      this.$nextTick(() => {
        const picker = this.$refs.dimensionPicker as PickerRef|undefined;
        if (picker) {
          picker.openSlot = 1;
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
    compareExportUrl(format: 'CSV'|'TSV'): string {
      return this.state ? exportUrl(this.state, format, this.comparison.columns) : '';
    },
    start() {
      if (!this.state || this.unmounted) {
        this.queue.finish();
        return;
      }
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
