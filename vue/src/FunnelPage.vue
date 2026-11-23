<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->
<template>
  <div class="fwde-page">
    <ActivityIndicator
      v-if="!steps && !loadError"
      :loading="true"
    />
    <Alert
      v-if="loadError"
      severity="danger"
    >{{ loadError }}</Alert>
    <div
      v-if="steps"
      class="fwde-page__layout"
    >
      <aside class="fwde-card fwde-sidebar">
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
            @open="openSaved"
            @deleted="loadSavedList()"
          />
        </section>
        <section class="fwde-section">
          <h3 class="fwde-section__title">
            {{ translate('FeinwerkDataExploration_FunnelSteps') }}
          </h3>
          <StepEditor
            :steps="steps"
            :goals="goals"
            :max-steps="maxSteps"
            @update="update"
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
              :export-url-for="complete ? exportUrlFor : undefined"
            />
            <SaveDialog
              v-if="savedLoaded && complete"
              class="fwde-card__save"
              :current="currentSaved"
              :suggested-name="suggestedName"
              :can-share="canShare"
              :busy="saveBusy"
              :error="saveError"
              @open="saveError = ''"
              @save="saveFunnel"
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
            <div
              v-if="!complete"
              class="fwde-funnel__hint"
            >{{ translate('FeinwerkDataExploration_FunnelNoSteps') }}</div>
            <ActivityIndicator
              :loading="loading"
              :loading-message="translate('FeinwerkDataExploration_LoadingExploration')"
            />
            <div
              v-if="complete && !loading && !error && rows && isEmpty"
              class="pk-emptyDataTable"
            >{{ translate('CoreHome_ThereIsNoDataForThisReport') }}</div>
            <FunnelResult
              v-if="complete && !error && rows && !isEmpty"
              :rows="rows"
              :class="{ 'fwde-main__stale': loading }"
            />
            <FeedbackBar
              v-if="complete && !error && rows"
              :id-site="idSite"
              topic="funnel"
              :question="translate('FeinwerkDataExploration_FeedbackQuestion_funnel')"
              :feedback="feedback"
            />
            <PromoLine
              v-if="complete && !error && rows"
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
  ActivityIndicator, Alert, MatomoUrl, translate,
} from 'CoreHome';
import { RUN_DELAY_MS, needsSmallerPeriod } from './hints';
import {
  largePeriodHint, noSmallerPeriodKeyForUser, rawDataRange, retryAllowed, showsRawDataWarning,
  slowHint, suggestedPeriodLabel,
} from './pageHints';
import StepEditor from './StepEditor.vue';
import FunnelResult from './FunnelResult.vue';
import ExplorationToolbar from './ExplorationToolbar.vue';
import SavedPicker from './SavedPicker.vue';
import SaveDialog from './SaveDialog.vue';
import {
  findSaved, funnelDiffersFromSaved, linkNamesOnlySaved, savedHashParams, savedKind, stateToSave,
  STATE_KEYS,
} from './saved';
import FeedbackBar from './FeedbackBar.vue';
import PromoLine, { PromoVariables } from './PromoLine.vue';
import PremiumBanner from './PremiumBanner.vue';
import { PremiumProps } from './premium';
import { FeedbackSettings } from './feedback';
import {
  canRun, parseSteps, toFunnelDefinition, toStepsParam,
} from './funnelState';
import { funnelExportUrl } from './exportLink';
import { FunnelRow, FunnelStepState } from './types';
import { createRequestGuard } from './requestGuard';
import { createRunQueue } from './runQueue';
import {
  addSaved, deleteSaved, errorText, Goal, loadGoals, loadPeriodVisits, loadSaved,
  loadSuggestedPeriod, PeriodVisits, runFunnel, SavedExploration, SuggestedPeriod, updateSaved,
} from './api';

/** Globals Matomo sets for the page. */
interface WindowWithPiwik {
  piwik?: { userLogin?: string };
}

interface Data {
  saved: SavedExploration[];
  savedLoaded: boolean;
  saveBusy: boolean;
  saveError: string;
  goals: Goal[];
  steps: FunnelStepState[]|null;
  rows: FunnelRow[]|null;
  loading: boolean;
  error: string;
  loadError: string;
  /** the definition of the last request, as JSON */
  lastDefinition: string;
  periodVisits: PeriodVisits|null;
  elapsedMs: number|null;
  suggestedPeriod: SuggestedPeriod|null;
  runTimer: number;
  abortController: AbortController|null;
  guard: ReturnType<typeof createRequestGuard>;
  queue: ReturnType<typeof createRunQueue>;
  unmounted: boolean;
}

/**
 * The funnel page (spec 3): steps in the hash parameter fwdeSteps, so a link, the back button and
 * Matomo's period selector and segment editor drive it as they drive the free-form table.
 */
export default defineComponent({
  inheritAttrs: false,
  components: {
    ActivityIndicator,
    Alert,
    StepEditor,
    FunnelResult,
    ExplorationToolbar,
    SavedPicker,
    SaveDialog,
    FeedbackBar,
    PromoLine,
    PremiumBanner,
  },
  props: {
    idSite: { type: Number, required: true },
    maxSteps: { type: Number, required: true },
    maxDays: { type: Number, required: true },
    rawDataFrom: { type: String, default: null },
    canShare: { type: Boolean, default: false },
    canCopy: { type: Boolean, default: false },
    /** admin access to the website: manage its shared explorations */
    canManage: { type: Boolean, default: false },
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
  },
  data(): Data {
    return {
      saved: [],
      savedLoaded: false,
      saveBusy: false,
      saveError: '',
      goals: [],
      steps: null,
      rows: null,
      loading: false,
      error: '',
      loadError: '',
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
    loadGoals().then((goals) => {
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
      return String(MatomoUrl.hashParsed.value.fwdeSteps ?? '');
    },
    complete(): boolean {
      return !!this.steps && canRun(this.steps);
    },
    isEmpty(): boolean {
      return !this.rows || !this.rows.length || this.rows[0].nb_visits === 0;
    },
    currentSaved(): SavedExploration|null {
      return findSaved(this.saved, String(MatomoUrl.parsed.value.fwdeSaved || ''), 'funnel');
    },
    title(): string {
      return this.currentSaved
        ? this.currentSaved.name
        : translate('FeinwerkDataExploration_Funnel');
    },
    /** The page shows other steps or another segment than the open saved funnel. */
    savedChanged(): boolean {
      const current = this.currentSaved;
      if (!current || !this.steps || savedKind(current) !== 'funnel') {
        return false;
      }
      return funnelDiffersFromSaved(
        current,
        toStepsParam(this.steps),
        String(MatomoUrl.parsed.value.segment || ''),
        this.goals.map((g) => g.id),
        this.maxSteps,
      );
    },
    /** e.g. "/products > /cart > Order", the name a new saved funnel is offered */
    suggestedName(): string {
      const short = (step: FunnelStepState): string => {
        if (step.type === 'anyGoal') {
          return translate('FeinwerkDataExploration_FunnelTypeAnyGoal');
        }
        if (step.type === 'goal') {
          const goal = this.goals.find((g) => g.id === step.idGoal);
          return goal ? goal.name : '';
        }
        return step.value.trim();
      };
      return (this.steps || []).map(short).join(' > ').slice(0, 100);
    },
    login(): string {
      const { piwik } = window as unknown as WindowWithPiwik;
      return String((piwik && piwik.userLogin) || '');
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
    rawDataRange(): string|null {
      return rawDataRange(this.rawDataFrom);
    },
    showRawDataWarning(): boolean {
      return showsRawDataWarning(this.rawDataFrom);
    },
  },
  watch: {
    hashState() {
      // back/forward or a pasted link changed the steps
      this.readState();
    },
  },
  methods: {
    translate,
    exportUrlFor(format: 'CSV'|'TSV'): string {
      return funnelExportUrl(this.steps || [], format);
    },
    readState() {
      const goalIds = this.goals.map((g) => g.id);
      const steps = parseSteps(MatomoUrl.parsed.value.fwdeSteps, goalIds, this.maxSteps);
      this.steps = steps;
      if (!canRun(steps)) {
        window.clearTimeout(this.runTimer);
        this.lastDefinition = '';
        this.loading = false;
        return;
      }
      const definition = JSON.stringify(toFunnelDefinition(steps));
      if (definition !== this.lastDefinition) {
        const first = this.lastDefinition === '' && this.rows === null;
        this.lastDefinition = definition;
        // a short pause, so typing starts one request; the result is dimmed meanwhile
        window.clearTimeout(this.runTimer);
        if (first) {
          this.run();
        } else {
          this.loading = true;
          this.runTimer = window.setTimeout(() => this.run(), RUN_DELAY_MS);
        }
      }
    },
    update(steps: FunnelStepState[]) {
      MatomoUrl.updateHash({ ...MatomoUrl.hashParsed.value, fwdeSteps: toStepsParam(steps) });
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
    showFrom(range: string) {
      MatomoUrl.updateHash({ ...MatomoUrl.hashParsed.value, period: 'range', date: range });
    },
    /**
     * The saved explorations of both kinds; without them the page works as before.
     *
     * @param openFromLink a link that names only a saved funnel (a bookmark) opens its steps
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
    /** Opens a saved exploration on its page (a table opens the table page), or a new funnel. */
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
    saveFunnel(event: { name: string, shared: boolean, asNew: boolean }) {
      if (!this.steps) {
        return;
      }
      const fields = {
        name: event.name,
        state: stateToSave({ fwdeSteps: toStepsParam(this.steps) }, 'funnel'),
        segment: String(MatomoUrl.parsed.value.segment || ''),
        shared: event.shared,
      };
      const current = this.currentSaved;
      const request = current && current.canEdit && !event.asNew && savedKind(current) === 'funnel'
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
    run() {
      if (!this.steps || !canRun(this.steps)) {
        return;
      }
      this.loading = true;
      this.error = '';
      // one funnel at a time: a change while one runs starts when it ends (see runQueue)
      if (this.queue.request()) {
        this.start();
      }
    },
    start() {
      if (!this.steps || this.unmounted) {
        this.queue.finish();
        return;
      }
      const abortController = new AbortController();
      this.abortController = abortController;
      const id = this.guard.start();
      const started = Date.now();
      this.elapsedMs = null;
      runFunnel(this.steps, abortController).then((rows) => {
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
