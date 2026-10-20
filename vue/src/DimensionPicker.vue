<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fwde-picker">
    <div
      v-for="slot in [0, 1]"
      :key="slot"
      class="fwde-picker__slot"
    >
      <div class="fwde-field__label">
        {{ translate(slot === 0
          ? 'FeinwerkDataExploration_Dimension'
          : 'FeinwerkDataExploration_SecondDimension') }}
      </div>
      <button
        type="button"
        class="fwde-picker__current"
        :aria-expanded="openSlot === slot ? 'true' : 'false'"
        @click="openSlot = openSlot === slot ? null : slot"
      >
        <span>{{ currentName(slot) }}</span>
        <span
          class="icon-chevron-down"
          aria-hidden="true"
        />
      </button>
      <div
        v-if="openSlot === slot"
        class="fwde-picker__panel"
      >
        <button
          v-if="slot === 1 && dimensions.length === 2"
          type="button"
          class="btn-flat fwde-picker__none"
          @click="choose(1, null)"
        >{{ translate('FeinwerkDataExploration_NoSecondDimension') }}</button>
        <SearchableList
          :items="items(slot)"
          :selected="dimensions[slot] ? [dimensions[slot]] : []"
          :search-label="translate('FeinwerkDataExploration_SearchDimensions')"
          @toggle="choose(slot, $event)"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import SearchableList, { ListItem } from './SearchableList.vue';
import { Catalog, ExplorationState } from './types';
import { dimensionReasons } from './availability';

const GROUP_KEYS: Record<string, string> = {
  acquisition: 'FeinwerkDataExploration_GroupAcquisition',
  visitor: 'FeinwerkDataExploration_GroupVisitor',
  technology: 'FeinwerkDataExploration_GroupTechnology',
  time: 'FeinwerkDataExploration_GroupTime',
  visit: 'FeinwerkDataExploration_GroupVisit',
  behaviour: 'FeinwerkDataExploration_GroupBehaviour',
  custom: 'FeinwerkDataExploration_GroupCustom',
};

export default defineComponent({
  components: { SearchableList },
  props: {
    catalog: { type: Object as PropType<Catalog>, required: true },
    state: { type: Object as PropType<ExplorationState>, required: true },
  },
  emits: ['change'],
  data() {
    return { openSlot: null as number|null };
  },
  computed: {
    dimensions(): string[] {
      return this.state.dimensions;
    },
  },
  methods: {
    translate,
    currentName(slot: number): string {
      const id = this.dimensions[slot];
      const dimension = this.catalog.dimensions.find((d) => d.id === id);
      if (dimension) {
        return dimension.name;
      }
      return translate(slot === 1
        ? 'FeinwerkDataExploration_NoSecondDimension'
        : 'FeinwerkDataExploration_ChooseDimension');
    },
    items(slot: number): ListItem[] {
      const reasons = dimensionReasons(this.state, this.catalog, slot === 0 ? 0 : 1);
      return this.catalog.dimensions.map((d) => ({
        key: d.id,
        label: d.name,
        group: GROUP_KEYS[d.group] ? translate(GROUP_KEYS[d.group]) : d.group,
        reason: reasons[d.id],
      }));
    },
    choose(slot: number, id: string|null) {
      const dimensions = [...this.dimensions];
      if (id === null) {
        dimensions.splice(1);
      } else {
        dimensions[slot] = id;
      }
      this.openSlot = null;
      this.$emit('change', dimensions.filter((d) => !!d));
    },
  },
});
</script>
