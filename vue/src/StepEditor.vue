<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->
<template>
  <div class="fwde-steps">
    <ol class="fwde-steps__list">
      <li
        v-for="(step, i) in local"
        :key="i"
        class="fwde-step"
      >
        <span
          class="fwde-step__number"
          aria-hidden="true"
        >{{ i + 1 }}</span>
        <div class="fwde-step__fields">
          <select
            class="browser-default fwde-select fwde-step__type"
            :value="step.type"
            :aria-label="translate('FeinwerkDataExploration_FunnelStepType', String(i + 1))"
            @change="setType(i, $event)"
          >
            <option
              v-for="type in types"
              :key="type"
              :value="type"
            >{{ translate(typeKey(type)) }}</option>
          </select>
          <select
            v-if="!isGoalType(step.type)"
            class="browser-default fwde-select fwde-step__operator"
            :value="step.operator"
            :aria-label="translate('FeinwerkDataExploration_FilterCondition')"
            @change="setOperator(i, $event)"
          >
            <option
              v-for="operator in operators(step)"
              :key="operator"
              :value="operator"
            >{{ translate(operatorKey(operator)) }}</option>
          </select>
          <input
            v-if="!isGoalType(step.type)"
            type="text"
            class="browser-default fwde-input fwde-step__value"
            :value="step.value"
            :maxlength="maxText"
            :aria-label="translate('FeinwerkDataExploration_FilterValue')"
            @input="setValue(i, $event)"
          >
          <select
            v-if="step.type === 'goal'"
            class="browser-default fwde-select fwde-step__goal"
            :value="step.idGoal === null ? '' : String(step.idGoal)"
            :aria-label="translate('FeinwerkDataExploration_FunnelTypeGoal')"
            @change="setGoal(i, $event)"
          >
            <option value="">{{ translate('FeinwerkDataExploration_FunnelChooseGoal') }}</option>
            <option
              v-for="goal in goals"
              :key="goal.id"
              :value="String(goal.id)"
            >{{ goal.name }}</option>
          </select>
        </div>
        <button
          type="button"
          class="fwde-icon-button fwde-step__remove"
          :disabled="local.length <= 2"
          :title="translate('FeinwerkDataExploration_FunnelRemoveStep')"
          :aria-label="translate('FeinwerkDataExploration_FunnelRemoveStep')"
          @click="remove(i)"
        >
          <span
            class="icon-close"
            aria-hidden="true"
          />
        </button>
      </li>
    </ol>
    <div class="fwde-steps__add">
      <button
        type="button"
        class="fwde-button fwde-steps__add-button"
        :disabled="local.length >= maxSteps"
        @click="add"
      >
        <span
          class="icon-add"
          aria-hidden="true"
        />
        {{ translate('FeinwerkDataExploration_FunnelAddStep') }}
      </button>
      <span
        v-if="local.length >= maxSteps"
        class="fwde-steps__limit"
      >{{ translate('FeinwerkDataExploration_FunnelMaxSteps', String(maxSteps)) }}</span>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import { Goal } from './api';
import {
  emptyStep, isGoalType, MAX_STEP_TEXT, OPERATORS_BY_TYPE, STEP_TYPES, withType,
} from './funnelState';
import { FunnelStepState, StepOperator, StepType } from './types';

/** As the filter editor: the page follows the typed text after this pause. */
const TYPING_PAUSE_MS = 600;

/** Translation keys, written out so the translations test finds them. */
const TYPE_KEYS: Record<StepType, string> = {
  pageUrl: 'FeinwerkDataExploration_FunnelTypePageUrl',
  pageTitle: 'FeinwerkDataExploration_FunnelTypePageTitle',
  eventCategory: 'FeinwerkDataExploration_FunnelTypeEventCategory',
  eventAction: 'FeinwerkDataExploration_FunnelTypeEventAction',
  eventName: 'FeinwerkDataExploration_FunnelTypeEventName',
  siteSearch: 'FeinwerkDataExploration_FunnelTypeSiteSearch',
  download: 'FeinwerkDataExploration_FunnelTypeDownload',
  outlink: 'FeinwerkDataExploration_FunnelTypeOutlink',
  goal: 'FeinwerkDataExploration_FunnelTypeGoal',
  anyGoal: 'FeinwerkDataExploration_FunnelTypeAnyGoal',
};
const OPERATOR_KEYS: Record<string, string> = {
  contains: 'FeinwerkDataExploration_OperatorContains',
  equals: 'FeinwerkDataExploration_OperatorEquals',
  startsWith: 'FeinwerkDataExploration_OperatorStartsWith',
};

export default defineComponent({
  props: {
    steps: { type: Array as PropType<FunnelStepState[]>, required: true },
    goals: { type: Array as PropType<Goal[]>, default: () => [] },
    maxSteps: { type: Number, required: true },
  },
  emits: ['update'],
  data() {
    return {
      maxText: MAX_STEP_TEXT,
      /** the steps as edited: typing changes them at once and the page after a pause */
      local: this.steps.map((s) => ({ ...s })) as FunnelStepState[],
      timer: 0,
      typing: false,
    };
  },
  watch: {
    steps(steps: FunnelStepState[]) {
      // a link, the back button or a saved funnel; while typing the field keeps what is typed
      if (!this.typing) {
        this.local = steps.map((s) => ({ ...s }));
      }
    },
  },
  beforeUnmount() {
    window.clearTimeout(this.timer);
  },
  computed: {
    /** goal steps only where the website has goals */
    types(): StepType[] {
      return STEP_TYPES.filter((type) => type !== 'goal' || this.goals.length > 0);
    },
  },
  methods: {
    translate,
    isGoalType,
    typeKey(type: StepType): string {
      return TYPE_KEYS[type];
    },
    operatorKey(operator: string): string {
      return OPERATOR_KEYS[operator];
    },
    operators(step: FunnelStepState): StepOperator[] {
      return OPERATORS_BY_TYPE[step.type] || [];
    },
    /** a choice or a button: the page gets the steps at once (with any text still pending) */
    emitWith(index: number, step: FunnelStepState) {
      this.emitNow(this.local.map((s, i) => (i === index ? step : s)));
    },
    emitNow(steps: FunnelStepState[]) {
      window.clearTimeout(this.timer);
      this.typing = false;
      this.local = steps;
      this.$emit('update', steps);
    },
    setType(index: number, event: Event) {
      const type = (event.target as HTMLSelectElement).value as StepType;
      this.emitWith(index, withType(this.local[index], type));
    },
    setOperator(index: number, event: Event) {
      const operator = (event.target as HTMLSelectElement).value as StepOperator;
      this.emitWith(index, { ...this.local[index], operator });
    },
    /** typing: one update after a pause, so a key is neither a history entry nor a query */
    setValue(index: number, event: Event) {
      const { value } = event.target as HTMLInputElement;
      this.local = this.local.map((s, i) => (i === index ? { ...s, value } : s));
      this.typing = true;
      window.clearTimeout(this.timer);
      this.timer = window.setTimeout(() => this.emitNow(this.local), TYPING_PAUSE_MS);
    },
    setGoal(index: number, event: Event) {
      const { value } = event.target as HTMLSelectElement;
      this.emitWith(index, { ...this.local[index], idGoal: value === '' ? null : Number(value) });
    },
    add() {
      if (this.local.length < this.maxSteps) {
        this.emitNow([...this.local, emptyStep()]);
      }
    },
    remove(index: number) {
      if (this.local.length > 2) {
        this.emitNow(this.local.filter((s, i) => i !== index));
      }
    },
  },
});
</script>
