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
    <div class="fwde-saved__row">
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
            ? translate('FeinwerkDataExploration_SavedShared', displayName(saved))
            : displayName(saved) }}</option>
        </optgroup>
        <optgroup
          v-if="groups.shared.length"
          :label="translate('FeinwerkDataExploration_SharedByOthers')"
        >
          <option
            v-for="saved in groups.shared"
            :key="saved.id"
            :value="String(saved.id)"
          >{{ ownedBy(saved) }}</option>
        </optgroup>
      </select>
      <div
        v-if="canCopy && copy && copyStore && current"
        class="fwde-saved__copy"
      >
        <component
          :is="copy.action"
          :action-form-data="{ idSaved: current.id }"
          :modal-store="copyStore"
          :is-action-visible="true"
          :is-action-enabled="true"
        />
        <component
          :is="copy.modal"
          :modal-store="copyStore"
        />
      </div>
    </div>
    <button
      v-if="canManage"
      type="button"
      class="fwde-saved__manage"
      aria-haspopup="dialog"
      :aria-expanded="managing ? 'true' : 'false'"
      @click="managing = true"
    >{{ translate('FeinwerkDataExploration_ManageShared') }}</button>
    <SidePanel
      v-if="canManage"
      :open="managing"
      :title="translate('FeinwerkDataExploration_ManageShared')"
      :close-label="translate('General_Close')"
      scope-class="fwde-page fwde-managepanel"
      @update:open="managing = $event"
    >
      <ManageSavedDialog
        :saved="saved"
        :remove="removeSaved"
        @deleted="$emit('deleted', $event)"
      />
    </SidePanel>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import * as CoreHome from 'CoreHome';
import { deleteSaved, errorText, SavedExploration } from './api';
import ManageSavedDialog from './ManageSavedDialog.vue';
import SidePanel from './SidePanel.vue';
import {
  CopySupport, copySupport, groupSaved, savedKind,
} from './saved';

const { translate } = CoreHome;

export default defineComponent({
  components: { ManageSavedDialog, SidePanel },
  props: {
    saved: { type: Array as PropType<SavedExploration[]>, required: true },
    current: { type: Object as PropType<SavedExploration|null>, default: null },
    login: { type: String, required: true },
    /** write access on some website: Matomo's copy dialog lists only those */
    canCopy: { type: Boolean, default: false },
    /** admin access to the website: manage its shared explorations (design 2026-11-20) */
    canManage: { type: Boolean, default: false },
  },
  emits: ['open', 'deleted'],
  data() {
    return { managing: false };
  },
  computed: {
    groups(): { mine: SavedExploration[], shared: SavedExploration[] } {
      return groupSaved(this.saved, this.login);
    },
    /** null on Matomo before 5.14: no copy action */
    copy(): CopySupport | null {
      return copySupport(CoreHome as unknown as Record<string, unknown>);
    },
    copyStore(): unknown {
      return this.copy ? this.copy.buildStore(
        translate('FeinwerkDataExploration_SavedExplorationEntity'),
        { method: 'FeinwerkDataExploration.copySavedExploration' },
      ) : null;
    },
  },
  methods: {
    /** the manage dialog's delete, with Matomo's error text as the message */
    removeSaved(id: number): Promise<unknown> {
      return deleteSaved(id).catch((e) => {
        throw new Error(errorText(e));
      });
    },
    translate,
    ownedBy(saved: SavedExploration): string {
      return translate('FeinwerkDataExploration_SavedBy', this.displayName(saved), saved.owner);
    },
    /** funnels are named as such; tables by their name */
    displayName(saved: SavedExploration): string {
      return savedKind(saved) === 'funnel'
        ? translate('FeinwerkDataExploration_SavedFunnel', saved.name)
        : saved.name;
    },
    onChange(event: Event) {
      const id = (event.target as HTMLSelectElement).value;
      this.$emit('open', this.saved.find((s) => String(s.id) === id) || null);
    },
  },
});
</script>
