<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fwde-picker">
    <SearchableList
      :items="items"
      :selected="state.metrics"
      :multiple="true"
      :search-label="translate('FeinwerkDataExploration_SearchMetrics')"
      @toggle="toggle"
    />
    <div
      v-if="hasGoalMetric && goals.length"
      class="fwde-picker__goal"
    >
      <label
        class="fwde-field__label"
        :for="goalSelectId"
      >{{ translate('FeinwerkDataExploration_Goal') }}</label>
      <select
        :id="goalSelectId"
        class="browser-default fwde-select"
        :value="state.idGoal === null ? '' : String(state.idGoal)"
        @change="onGoalChange"
      >
        <option value="">{{ translate('FeinwerkDataExploration_AllGoals') }}</option>
        <option
          v-for="goal in goals"
          :key="goal.id"
          :value="String(goal.id)"
        >{{ goal.name }}</option>
      </select>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import SearchableList, { ListItem } from './SearchableList.vue';
import { Catalog, ExplorationState } from './types';
import { metricReasons } from './availability';
import { Goal } from './api';

const SCOPE_KEYS: Record<string, string> = {
  visit: 'FeinwerkDataExploration_MetricGroupVisits',
  goal: 'FeinwerkDataExploration_MetricGroupGoals',
  action: 'FeinwerkDataExploration_MetricGroupActions',
};

export default defineComponent({
  components: { SearchableList },
  props: {
    catalog: { type: Object as PropType<Catalog>, required: true },
    state: { type: Object as PropType<ExplorationState>, required: true },
    goals: { type: Array as PropType<Goal[]>, required: true },
    actionScope: { type: Boolean, required: true },
  },
  emits: ['change', 'goal'],
  data() {
    return { goalSelectId: `fwde-goal-${Math.random().toString(36).substring(2, 8)}` };
  },
  computed: {
    items(): ListItem[] {
      const reasons = metricReasons(this.state, this.catalog);
      return this.catalog.metrics.map((m) => ({
        key: m.id,
        label: this.actionScope && m.nameInActionScope ? m.nameInActionScope : m.name,
        group: translate(SCOPE_KEYS[m.scope]),
        reason: reasons[m.id],
        description: m.description,
      }));
    },
    hasGoalMetric(): boolean {
      return this.catalog.metrics
        .some((m) => m.scope === 'goal' && this.state.metrics.includes(m.id));
    },
  },
  methods: {
    translate,
    onGoalChange(event: Event) {
      this.$emit('goal', (event.target as HTMLSelectElement).value);
    },
    toggle(id: string) {
      const metrics = this.state.metrics.includes(id)
        ? this.state.metrics.filter((m) => m !== id)
        : [...this.state.metrics, id];
      if (metrics.length) {
        this.$emit('change', metrics);
      }
    },
  },
});
</script>
