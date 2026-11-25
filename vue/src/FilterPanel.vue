<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later

  The filters in the side panel (UI item 4; Peter, 2026-11-21: "Filter mit Apply ja"): the editor
  works on a draft of the applied filters and their match; Apply (or Enter in a field) hands both
  over at once, so the exploration runs once; Cancel, Escape and the dimmed page drop the draft. A
  filter the user changed but left without a value keeps the panel open and says what is missing.
-->
<template>
  <SidePanel
    :open="open"
    :title="translate('FeinwerkDataExploration_Filters')"
    :close-label="translate('General_Close')"
    scope-class="fwde-page fwde-filterpanel"
    @update:open="onPanel"
  >
    <!-- the editor checks the filters itself (decimals included): no browser validation -->
    <form
      id="fwde-filterpanel-form"
      class="fwde-filterpanel__form"
      novalidate
      @submit.prevent="apply"
    >
      <FilterEditor
        v-if="open"
        ref="editor"
        :key="session"
        :catalog="catalog"
        :state="draftState"
        :action-scope="actionScope"
        :start-with-row="true"
        @change="draft.filters = $event"
        @match="draft.match = $event"
        @group-match="draft.groupMatch = $event"
      />
    </form>
    <template #footer>
      <button
        type="button"
        class="fwde-button fwde-filters__cancel"
        @click="cancel"
      >{{ translate('General_Cancel') }}</button>
      <button
        type="submit"
        form="fwde-filterpanel-form"
        class="btn fwde-filters__apply"
      >{{ translate('General_Apply') }}</button>
    </template>
  </SidePanel>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import {
  Catalog, ExplorationState, FilterMatch, RowFilter,
} from './types';
import FilterEditor from './FilterEditor.vue';
import SidePanel from './SidePanel.vue';

export default defineComponent({
  components: { FilterEditor, SidePanel },
  props: {
    open: { type: Boolean, required: true },
    catalog: { type: Object as PropType<Catalog>, required: true },
    /** the applied exploration: the draft starts from its filters and match */
    state: { type: Object as PropType<ExplorationState>, required: true },
    actionScope: { type: Boolean, required: true },
  },
  emits: ['update:open', 'apply'],
  data() {
    return {
      /** a new editor (rows from the applied filters) every time the panel opens */
      session: 0,
      draft: {
        filters: [] as RowFilter[], match: 'all' as FilterMatch, groupMatch: [] as FilterMatch[],
      },
    };
  },
  computed: {
    draftState(): ExplorationState {
      return {
        ...this.state,
        filters: this.draft.filters,
        match: this.draft.match,
        groupMatch: this.draft.groupMatch,
      };
    },
  },
  watch: {
    open(isOpen: boolean) {
      if (isOpen) {
        this.reset();
      }
    },
  },
  created() {
    if (this.open) {
      this.reset();
    }
  },
  methods: {
    translate,
    reset() {
      this.draft = {
        filters: this.state.filters.map((filter) => ({ ...filter })),
        match: this.state.match,
        groupMatch: this.state.groupMatch.slice(),
      };
      this.session += 1;
    },
    apply() {
      const editor = this.$refs.editor as {
        flush?: () => void,
        checkComplete?: () => boolean,
      } | undefined;
      // a filter left half done stays in the panel with what is missing (UI review 2026-11-23)
      if (editor && editor.checkComplete && !editor.checkComplete()) {
        return;
      }
      if (editor && editor.flush) {
        editor.flush();
      }
      // the switch between the groups matters from two groups on
      const groupCount = this.draft.groupMatch.length;
      this.$emit('apply', {
        filters: this.draft.filters,
        groupMatch: this.draft.groupMatch,
        match: groupCount > 1 ? this.draft.match : 'all',
      });
      this.$emit('update:open', false);
    },
    cancel() {
      this.$emit('update:open', false);
    },
    onPanel(isOpen: boolean) {
      if (!isOpen) {
        this.cancel();
      }
    },
  },
});
</script>
