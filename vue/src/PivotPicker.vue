<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fwde-picker fwde-pivot-picker">
    <div class="fwde-picker__slot">
      <div class="fwde-field__label">{{ translate('FeinwerkDataExploration_Pivot') }}</div>
      <button
        ref="current"
        type="button"
        class="fwde-picker__current"
        :disabled="comparing"
        :aria-expanded="open ? 'true' : 'false'"
        @click="open = !open"
      >
        <span>{{ currentName }}</span>
        <span
          class="icon-chevron-down"
          aria-hidden="true"
        />
      </button>
      <p
        v-if="comparing"
        class="fwde-pivot-picker__paused"
      >{{ translate('FeinwerkDataExploration_PivotPaused') }}</p>
      <div
        v-if="open && !comparing"
        class="fwde-picker__panel"
      >
        <button
          v-if="state.pivot"
          type="button"
          class="btn-flat fwde-picker__none"
          @click="choose(null)"
        >{{ translate('FeinwerkDataExploration_NoSecondDimension') }}</button>
        <SearchableList
          :items="items"
          :selected="state.pivot ? [state.pivot] : []"
          :search-label="translate('FeinwerkDataExploration_SearchDimensions')"
          @toggle="choose($event)"
          @close="close"
        />
      </div>
    </div>
    <div
      v-if="state.pivot && state.pivot !== PIVOT_WEEKDAY && !comparing"
      class="fwde-pivot-picker__limit"
    >
      <SegmentedSwitch
        :options="limitOptions"
        :model-value="String(state.pivotLimit)"
        :label="translate('FeinwerkDataExploration_PivotValues')"
        @update:model-value="$emit('change', { pivot: state.pivot, pivotLimit: Number($event) })"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import SearchableList, { ListItem } from './SearchableList.vue';
import SegmentedSwitch, { SegmentedOption } from './SegmentedSwitch.vue';
import { Catalog, ExplorationState } from './types';
import {
  PIVOT_DEFAULT_LIMIT, PIVOT_LIMITS, PIVOT_WEEKDAY, pivotLimitReasons, pivotReasons,
} from './pivot';
import { GROUP_KEYS } from './labels';

/**
 * "Columns (pivot)" (rows and pivot, 2026-11-24): a dimension whose top values become column
 * groups, and how many (3, 5, 10). Off while Matomo compares: the comparison wins, the pivot stays
 * in the link and comes back when the comparison ends.
 */
export default defineComponent({
  components: { SearchableList, SegmentedSwitch },
  props: {
    catalog: { type: Object as PropType<Catalog>, required: true },
    state: { type: Object as PropType<ExplorationState>, required: true },
    /** Matomo compares segments or periods */
    comparing: { type: Boolean, default: false },
  },
  emits: ['change'],
  data() {
    return { open: false, PIVOT_WEEKDAY };
  },
  computed: {
    currentName(): string {
      const dimension = this.catalog.dimensions.find((d) => d.id === this.state.pivot);
      return dimension ? dimension.name : translate('FeinwerkDataExploration_NoSecondDimension');
    },
    items(): ListItem[] {
      const reasons = pivotReasons(this.state, this.catalog);
      return this.catalog.dimensions.map((d) => ({
        key: d.id,
        label: d.name,
        group: GROUP_KEYS[d.group] ? translate(GROUP_KEYS[d.group]) : d.group,
        reason: reasons[d.id],
      }));
    },
    limitOptions(): SegmentedOption[] {
      const reasons = pivotLimitReasons(this.state);
      return PIVOT_LIMITS.map((limit) => ({
        value: String(limit),
        label: String(limit),
        disabled: reasons[limit] !== null,
        title: reasons[limit] ? translate(reasons[limit] as string) : undefined,
      }));
    },
  },
  methods: {
    translate,
    choose(id: string|null) {
      this.open = false;
      this.$emit('change', {
        pivot: id,
        pivotLimit: id === this.state.pivot ? this.state.pivotLimit : PIVOT_DEFAULT_LIMIT,
      });
    },
    /** Escape in the list: close it and give the focus back to its button */
    close() {
      this.open = false;
      this.$nextTick(() => {
        const button = this.$refs.current as HTMLElement|undefined;
        if (button) {
          button.focus();
        }
      });
    },
  },
});
</script>
