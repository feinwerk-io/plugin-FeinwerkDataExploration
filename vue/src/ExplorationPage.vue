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
            @limit="update({ limit: $event })"
            @view="update({ view: $event })"
          />
          <SaveDialog
            v-if="savedLoaded"
            class="fwde-card__save"
            :current="currentSaved"
            :suggested-name="resultName"
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
          <div
            v-if="!loading && !error && rows && !rows.length"
            class="pk-emptyDataTable"
          >{{ translate('CoreHome_ThereIsNoDataForThisReport') }}</div>
          <ResultChart
            v-if="!error && rows && rows.length && view !== 'table'"
            :rows="rows"
            :state="state"
            :catalog="catalog"
            :metric="chartMetric"
            :action-scope="actionScope"
            :currency-symbol="currencySymbol"
            :hour-labels="hourLabels"
            :period-days="periodDays"
            :class="{ 'fwde-main__stale': loading }"
            @metric="update({ chartMetric: $event })"
          />
          <ResultTable
            v-if="!error && rows && rows.length && view !== 'chart'"
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
        </div>
      </section>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  ActivityIndicator, Alert, MatomoUrl, NumberFormatter, translate,
} from 'CoreHome';
import {
  RUN_DELAY_MS, canRetry, isLargePeriod, isSlow, needsSmallerPeriod, noSmallerPeriodKey,
} from './hints';
import DimensionPicker from './DimensionPicker.vue';
import MetricPicker from './MetricPicker.vue';
import FilterEditor from './FilterEditor.vue';
import ResultTable from './ResultTable.vue';
import ExplorationToolbar from './ExplorationToolbar.vue';
import SavedPicker from './SavedPicker.vue';
import SaveDialog from './SaveDialog.vue';
import {
  differsFromSaved, findSaved, linkNamesOnlySaved, savedHashParams, stateToSave, STATE_KEYS,
} from './saved';
import ResultChart from './ResultChart.vue';
import { Catalog, ChartView, ExplorationState } from './types';
import { parseState, toDefinition, toHashParams } from './state';
import {
  canChart, chartMetricFor, effectiveView, resultTitle,
} from './chart';
import { combinedSegment, filtersForRow } from './rows';
import { periodDays as daysOfPeriod, rangeFromDay, startsBefore } from './period';
import { createRequestGuard } from './requestGuard';
import { createRunQueue } from './runQueue';
import {
  addSaved, deleteSaved, errorText, Goal, loadCatalog, loadGoals, loadPeriodVisits, loadSaved,
  loadSuggestedPeriod, PeriodVisits, ResultRow, runExploration, SavedExploration, SuggestedPeriod,
  updateSaved,
} from './api';

const HASH_KEYS = [
  'fwdeDims', 'fwdeMetrics', 'fwdeGoal', 'fwdeSort', 'fwdeOrder', 'fwdeLimit', 'fwdeFilters',
  'fwdeView', 'fwdeChart',
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
    ResultChart,
    ExplorationToolbar,
    SavedPicker,
    SaveDialog,
  },
  props: {
    idSite: { type: Number, required: true },
    maxRows: { type: Number, required: true },
    defaultRows: { type: Number, required: true },
    maxDays: { type: Number, required: true },
    rawDataFrom: { type: String, default: null },
    currencySymbol: { type: String, default: '' },
    hourLabels: { type: Array as PropType<string[]>, default: () => [] },
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
  },
  computed: {
    hashState(): string {
      const hash = MatomoUrl.hashParsed.value;
      return HASH_KEYS.map((key) => String(hash[key] ?? '')).join('|');
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
      return this.state ? effectiveView(this.state, this.rows ? this.rows.length : 0) : 'table';
    },
    canChart(): boolean {
      return canChart(this.rows ? this.rows.length : 0);
    },
    chartMetric(): string {
      return this.state ? chartMetricFor(this.state) : '';
    },
    largePeriodHint(): string {
      const info = this.periodVisits;
      if (!info || !isLargePeriod(info.visits, info.maxVisits)) {
        return '';
      }
      const visits = NumberFormatter.formatNumber(info.visits, 0, 0);
      return info.timeoutSeconds > 0
        ? translate('FeinwerkDataExploration_LargePeriodHint', visits, String(info.timeoutSeconds))
        : translate('FeinwerkDataExploration_LargePeriodHintNoLimit', visits);
    },
    slowHint(): string {
      const timeout = this.periodVisits ? this.periodVisits.timeoutSeconds : 0;
      if (this.loading || this.error || this.elapsedMs === null) {
        return '';
      }
      if (!isSlow(this.elapsedMs, timeout)) {
        return '';
      }
      const seconds = NumberFormatter.formatNumber(this.elapsedMs / 1000, 0, 0);
      return timeout > 0
        ? translate('FeinwerkDataExploration_SlowQueryHint', seconds, String(timeout))
        : translate('FeinwerkDataExploration_SlowQueryHintNoLimit', seconds);
    },
    suggestedPeriodLabel(): string {
      const days = this.suggestedPeriod?.days || 0;
      return days === 1
        ? translate('FeinwerkDataExploration_LoadLastDay')
        : translate('FeinwerkDataExploration_LoadLastDays', String(days));
    },
    canRetry(): boolean {
      // both role variants of the time limit message start with the same sentence
      const timeout = translate('FeinwerkDataExploration_ErrorTimeout', '\u0000');
      return canRetry(
        this.error,
        translate('FeinwerkDataExploration_ErrorBusy'),
        timeout.includes('\u0000') ? timeout.slice(0, timeout.indexOf('\u0000')) : '',
      );
    },
    noSmallerPeriodKey(): string {
      const { piwik } = window as unknown as WindowWithLive;
      return noSmallerPeriodKey(!!(piwik && piwik.hasSuperUserAccess));
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
      return findSaved(this.saved, String(MatomoUrl.parsed.value.fwdeSaved || ''));
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
      if (!this.rawDataFrom) {
        return null;
      }
      const parsed = MatomoUrl.parsed.value;
      const today = new Date().toISOString().substring(0, 10);
      return rangeFromDay(String(parsed.period), String(parsed.date), this.rawDataFrom, today);
    },
    showRawDataWarning(): boolean {
      const parsed = MatomoUrl.parsed.value;
      const today = new Date().toISOString().substring(0, 10);
      return startsBefore(String(parsed.period), String(parsed.date), this.rawDataFrom, today);
    },
  },
  watch: {
    hashState() {
      // back/forward or a pasted link changed the exploration parameters
      this.readState();
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
      const definition = JSON.stringify(toDefinition(state));
      if (definition !== this.lastDefinition) {
        const first = this.lastDefinition === '';
        this.lastDefinition = definition;
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
      const request = current && current.canEdit && !event.asNew
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
      runExploration(this.state, abortController).then((rows) => {
        if (this.guard.isLatest(id) && !this.queue.isPending()) {
          this.rows = rows;
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
