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
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import SearchableList, { ListItem } from './SearchableList.vue';
import { Catalog, ExplorationState } from './types';
import { metricReasons } from './availability';

const GROUP_KEYS: Record<string, string> = {
  visits: 'FeinwerkDataExploration_MetricGroupVisits',
  goals: 'FeinwerkDataExploration_MetricGroupGoals',
  ecommerce: 'Goals_Ecommerce',
  actions: 'FeinwerkDataExploration_MetricGroupActions',
};
const SCOPE_GROUPS: Record<string, string> = { visit: 'visits', goal: 'goals', action: 'actions' };
/** the list's order (more metrics, 2026-11-24): each goal's own heading follows "Goals" */
const GROUP_ORDER = ['visits', 'goals', 'goal', 'ecommerce', 'actions'];
/** the metrics of all goals together: the list says so */
const ALL_GOALS_METRICS = ['nb_conversions', 'conversion_rate', 'revenue'];

export default defineComponent({
  components: { SearchableList },
  props: {
    catalog: { type: Object as PropType<Catalog>, required: true },
    state: { type: Object as PropType<ExplorationState>, required: true },
    actionScope: { type: Boolean, required: true },
  },
  emits: ['change'],
  computed: {
    items(): ListItem[] {
      const reasons = metricReasons(this.state, this.catalog);
      const entries = this.catalog.metrics.map((m, index) => {
        const group = m.group || SCOPE_GROUPS[m.scope];
        let label = this.actionScope && m.nameInActionScope ? m.nameInActionScope : m.name;
        let heading = translate(GROUP_KEYS[group]);
        let rank = GROUP_ORDER.indexOf(group);
        if (m.idGoal) {
          // a goal's metric is named "<goal name>: <column>" (MetricDefinition::label): the goal
          // becomes the heading, the search finds it there too
          const cut = m.name.lastIndexOf(': ');
          heading = translate('FeinwerkDataExploration_MetricGroupGoal', m.name.substring(0, cut));
          label = m.name.substring(cut + 2);
          rank = GROUP_ORDER.indexOf('goal');
        } else if (ALL_GOALS_METRICS.includes(m.id)) {
          label = translate('FeinwerkDataExploration_MetricAllGoals', label);
        }
        const item: ListItem = {
          key: m.id,
          label,
          group: heading,
          reason: reasons[m.id],
          description: m.description,
        };
        return { rank, index, item };
      });
      return entries.sort((a, b) => a.rank - b.rank || a.index - b.index).map((e) => e.item);
    },
  },
  methods: {
    translate,
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
