<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fwde-saved">
    <label
      class="fwde-field__label"
      for="fwde-saved-select"
    >{{ translate('FeinwerkDataExploration_SavedExplorations') }}</label>
    <select
      id="fwde-saved-select"
      class="browser-default fwde-select fwde-saved__select"
      :value="current ? String(current.id) : ''"
      @change="onChange"
    >
      <option value="">{{ translate('FeinwerkDataExploration_NewExploration') }}</option>
      <optgroup
        v-if="groups.mine.length"
        :label="translate('FeinwerkDataExploration_MyExplorations')"
      >
        <option
          v-for="saved in groups.mine"
          :key="saved.id"
          :value="String(saved.id)"
        >{{ saved.shared
          ? translate('FeinwerkDataExploration_SavedShared', saved.name)
          : saved.name }}</option>
      </optgroup>
      <optgroup
        v-if="groups.shared.length"
        :label="translate('FeinwerkDataExploration_SharedByOthers')"
      >
        <option
          v-for="saved in groups.shared"
          :key="saved.id"
          :value="String(saved.id)"
        >{{ translate('FeinwerkDataExploration_SavedBy', saved.name, saved.owner) }}</option>
      </optgroup>
    </select>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import { SavedExploration } from './api';
import { groupSaved } from './saved';

export default defineComponent({
  props: {
    saved: { type: Array as PropType<SavedExploration[]>, required: true },
    current: { type: Object as PropType<SavedExploration|null>, default: null },
    login: { type: String, required: true },
  },
  emits: ['open'],
  computed: {
    groups(): { mine: SavedExploration[], shared: SavedExploration[] } {
      return groupSaved(this.saved, this.login);
    },
  },
  methods: {
    translate,
    onChange(event: Event) {
      const id = (event.target as HTMLSelectElement).value;
      this.$emit('open', this.saved.find((s) => String(s.id) === id) || null);
    },
  },
});
</script>
