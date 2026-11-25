<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fwde-picker">
    <div
      v-for="slot in slots"
      :key="slot"
      class="fwde-picker__slot"
    >
      <div class="fwde-field__label">
        {{ translate(SLOT_KEYS[slot]) }}
      </div>
      <button
        :ref="`current${slot}`"
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
          v-if="slot > 0 && dimensions.length > slot"
          type="button"
          class="btn-flat fwde-picker__none"
          @click="choose(slot, null)"
        >{{ translate('FeinwerkDataExploration_NoSecondDimension') }}</button>
        <SearchableList
          :items="items(slot)"
          :selected="dimensions[slot] ? [dimensions[slot]] : []"
          :search-label="translate('FeinwerkDataExploration_SearchDimensions')"
          @toggle="choose(slot, $event)"
          @close="close(slot)"
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
import { MAX_DIMENSIONS } from './state';
import { GROUP_KEYS } from './labels';

/** the label of each slot */
const SLOT_KEYS = [
  'FeinwerkDataExploration_Dimension',
  'FeinwerkDataExploration_SecondDimension',
  'FeinwerkDataExploration_ThirdDimension',
];

export default defineComponent({
  components: { SearchableList },
  props: {
    catalog: { type: Object as PropType<Catalog>, required: true },
    state: { type: Object as PropType<ExplorationState>, required: true },
  },
  emits: ['change'],
  data() {
    return { openSlot: null as number|null, SLOT_KEYS };
  },
  computed: {
    dimensions(): string[] {
      return this.state.dimensions;
    },
    /** the chosen slots and one empty slot after them (rows and pivot: up to three) */
    slots(): number[] {
      const count = Math.min(MAX_DIMENSIONS, Math.max(2, this.dimensions.length + 1));
      return Array.from({ length: count }, (_, i) => i);
    },
  },
  methods: {
    /** Escape in the list: close it and give the focus back to its button */
    close(slot: number) {
      this.openSlot = null;
      this.$nextTick(() => {
        const button = this.$refs[`current${slot}`] as HTMLElement|HTMLElement[]|undefined;
        const element = Array.isArray(button) ? button[0] : button;
        if (element) {
          element.focus();
        }
      });
    },
    translate,
    currentName(slot: number): string {
      const id = this.dimensions[slot];
      const dimension = this.catalog.dimensions.find((d) => d.id === id);
      if (dimension) {
        return dimension.name;
      }
      return translate(slot > 0
        ? 'FeinwerkDataExploration_NoSecondDimension'
        : 'FeinwerkDataExploration_ChooseDimension');
    },
    items(slot: number): ListItem[] {
      const reasons = dimensionReasons(this.state, this.catalog, slot);
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
        // "None" takes this slot and the ones after it away
        dimensions.splice(slot);
      } else {
        dimensions[slot] = id;
      }
      this.openSlot = null;
      this.$emit('change', dimensions.filter((d) => !!d));
    },
  },
});
</script>
