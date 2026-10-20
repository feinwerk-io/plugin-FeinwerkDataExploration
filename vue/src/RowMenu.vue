<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    ref="root"
    class="fwde-rowmenu"
  >
    <button
      ref="toggle"
      type="button"
      class="fwde-icon-button fwde-rowmenu__toggle"
      aria-haspopup="true"
      :aria-expanded="open ? 'true' : 'false'"
      :aria-label="translate('FeinwerkDataExploration_RowMenu', label)"
      :title="translate('FeinwerkDataExploration_RowMenu', label)"
      @click="toggle"
    >
      <span
        class="icon-more-verti"
        aria-hidden="true"
      />
    </button>
    <ul
      v-if="open"
      class="fwde-menu__list fwde-rowmenu__list"
      :style="menuStyle"
    >
      <li>
        <button
          type="button"
          class="fwde-menu__item fwde-rowmenu__filter"
          :disabled="!!filterReason"
          :title="filterReason"
          @click="choose('filter')"
        >{{ translate('FeinwerkDataExploration_RowFilter') }}</button>
      </li>
      <li v-if="canBreakDown">
        <button
          type="button"
          class="fwde-menu__item fwde-rowmenu__breakdown"
          :disabled="!!filterReason"
          :title="filterReason"
          @click="choose('breakdown')"
        >{{ translate('FeinwerkDataExploration_RowBreakDown') }}</button>
      </li>
      <li v-if="filterReason">
        <span class="fwde-rowmenu__reason">{{ filterReason }}</span>
      </li>
      <li>
        <button
          type="button"
          class="fwde-menu__item fwde-rowmenu__visits"
          :disabled="!!visitsReason"
          :title="visitsReason"
          @click="choose('visits')"
        >{{ translate('FeinwerkDataExploration_RowShowVisits') }}</button>
        <span
          v-if="visitsReason"
          class="fwde-rowmenu__reason"
        >{{ visitsReason }}</span>
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate } from 'CoreHome';

export default defineComponent({
  props: {
    label: { type: String, required: true },
    canBreakDown: { type: Boolean, required: true },
    /** why the visits cannot be shown, empty when they can */
    visitsReason: { type: String, default: '' },
    /** why the row cannot be filtered to (or broken down), empty when it can */
    filterReason: { type: String, default: '' },
  },
  emits: ['filter', 'breakdown', 'visits'],
  data() {
    return { open: false, menuStyle: {} as Record<string, string> };
  },
  watch: {
    // listeners only while open: a table has up to 1,000 rows, each with a menu
    open(isOpen: boolean) {
      if (isOpen) {
        document.addEventListener('click', this.onDocumentClick);
        document.addEventListener('keydown', this.onKeydown);
        // the menu is placed over the page (the table scrolls sideways and would cut it off)
        window.addEventListener('scroll', this.close, true);
      } else {
        this.removeListeners();
      }
    },
  },
  beforeUnmount() {
    this.removeListeners();
  },
  methods: {
    translate,
    toggle(event: MouseEvent) {
      if (this.open) {
        this.open = false;
        return;
      }
      const box = (event.currentTarget as HTMLElement).getBoundingClientRect();
      this.menuStyle = {
        position: 'fixed',
        top: `${Math.round(box.bottom + 4)}px`,
        left: `${Math.max(8, Math.round(box.right - 240))}px`,
        right: 'auto',
      };
      this.open = true;
    },
    close() {
      this.open = false;
    },
    removeListeners() {
      document.removeEventListener('click', this.onDocumentClick);
      document.removeEventListener('keydown', this.onKeydown);
      window.removeEventListener('scroll', this.close, true);
    },
    choose(action: 'filter'|'breakdown'|'visits') {
      this.open = false;
      this.$emit(action);
    },
    onDocumentClick(event: MouseEvent) {
      const root = this.$refs.root as HTMLElement|undefined;
      if (this.open && root && !root.contains(event.target as Node)) {
        this.open = false;
      }
    },
    onKeydown(event: KeyboardEvent) {
      if (event.key === 'Escape') {
        this.open = false;
        const toggle = this.$refs.toggle as HTMLElement|undefined;
        if (toggle) {
          toggle.focus();
        }
      }
    },
  },
});
</script>
