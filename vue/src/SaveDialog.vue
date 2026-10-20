<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    ref="root"
    class="fwde-savedialog"
  >
    <button
      ref="toggle"
      type="button"
      class="fwde-button fwde-savedialog__toggle"
      aria-haspopup="dialog"
      :aria-expanded="open ? 'true' : 'false'"
      @click="toggle"
    >
      <span
        class="icon-save"
        aria-hidden="true"
      />
      {{ translate('General_Save') }}
    </button>
    <form
      v-if="open"
      class="fwde-savedialog__panel"
      role="dialog"
      :aria-label="translate('FeinwerkDataExploration_SaveExploration')"
      @submit.prevent="save(!canChange)"
      @keydown.esc.prevent="close()"
    >
      <p
        v-if="current && !current.canEdit"
        class="fwde-savedialog__note"
      >{{ translate('FeinwerkDataExploration_SavedNotYours', current.owner) }}</p>
      <label
        class="fwde-field__label"
        for="fwde-savedialog-name"
      >{{ translate('FeinwerkDataExploration_SavedName') }}</label>
      <input
        id="fwde-savedialog-name"
        ref="name"
        v-model="name"
        type="text"
        class="browser-default fwde-input"
        maxlength="100"
        required
      >
      <fieldset class="fwde-savedialog__visibility">
        <legend class="fwde-field__label">
          {{ translate('FeinwerkDataExploration_SavedVisibility') }}
        </legend>
        <label class="fwde-savedialog__option">
          <input
            v-model="shared"
            type="radio"
            name="fwde-savedialog-shared"
            :value="false"
          >
          <span class="fwde-list__label">{{
            translate('FeinwerkDataExploration_VisibilityPrivate') }}</span>
        </label>
        <label
          class="fwde-savedialog__option"
          :class="{ 'fwde-savedialog__option--disabled': !canShare }"
        >
          <input
            v-model="shared"
            type="radio"
            name="fwde-savedialog-shared"
            :value="true"
            :disabled="!canShare"
          >
          <span class="fwde-list__label">{{
            translate('FeinwerkDataExploration_VisibilityShared') }}</span>
        </label>
        <p
          v-if="!canShare"
          class="fwde-savedialog__note"
        >{{ translate('FeinwerkDataExploration_SavedShareNeedsWrite') }}</p>
      </fieldset>
      <p
        v-if="error"
        class="fwde-savedialog__error"
        role="alert"
      >{{ error }}</p>
      <div class="fwde-savedialog__buttons">
        <template v-if="canChange">
          <button
            type="submit"
            class="btn fwde-savedialog__changes"
            :disabled="busy"
          >{{ translate('FeinwerkDataExploration_SaveChanges') }}</button>
          <button
            type="button"
            class="fwde-button fwde-savedialog__asnew"
            :disabled="busy"
            @click="save(true)"
          >{{ translate('FeinwerkDataExploration_SaveAsNew') }}</button>
        </template>
        <button
          v-else
          type="submit"
          class="btn fwde-savedialog__new"
          :disabled="busy"
        >{{ translate('General_Save') }}</button>
        <button
          type="button"
          class="fwde-button"
          @click="close()"
        >{{ translate('General_Cancel') }}</button>
      </div>
      <div
        v-if="canChange"
        class="fwde-savedialog__delete"
      >
        <button
          v-if="!confirmDelete"
          type="button"
          class="fwde-button fwde-savedialog__deleteask"
          :disabled="busy"
          @click="confirmDelete = true"
        >{{ translate('General_Delete') }}</button>
        <template v-else>
          <span>{{ translate('FeinwerkDataExploration_SavedDeleteConfirm', currentName) }}</span>
          <button
            type="button"
            class="fwde-button fwde-savedialog__deleteyes"
            :disabled="busy"
            @click="$emit('delete')"
          >{{ translate('General_Delete') }}</button>
          <button
            type="button"
            class="fwde-button"
            @click="confirmDelete = false"
          >{{ translate('General_Cancel') }}</button>
        </template>
      </div>
    </form>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import { SavedExploration } from './api';
import { dialogDefaults } from './saved';

export default defineComponent({
  props: {
    current: { type: Object as PropType<SavedExploration|null>, default: null },
    /** the name a new exploration gets suggested (the result title) */
    suggestedName: { type: String, required: true },
    /** at least write access: may share with the website's users */
    canShare: { type: Boolean, default: false },
    busy: { type: Boolean, default: false },
    error: { type: String, default: '' },
  },
  emits: ['save', 'delete', 'open'],
  data() {
    return {
      open: false, name: '', shared: false, confirmDelete: false,
    };
  },
  computed: {
    currentName(): string {
      return this.current ? this.current.name : '';
    },
    canChange(): boolean {
      return !!(this.current && this.current.canEdit);
    },
  },
  watch: {
    // a finished save or delete closes the dialog
    busy(isBusy: boolean, wasBusy: boolean) {
      if (wasBusy && !isBusy && !this.error) {
        this.close();
      }
    },
  },
  beforeUnmount() {
    document.removeEventListener('click', this.onDocumentClick);
  },
  methods: {
    translate,
    toggle() {
      if (this.open) {
        this.close();
        return;
      }
      ({ name: this.name, shared: this.shared } = dialogDefaults(
        this.current,
        this.suggestedName,
        this.canShare,
      ));
      this.confirmDelete = false;
      this.open = true;
      this.$emit('open');
      document.addEventListener('click', this.onDocumentClick);
      this.$nextTick(() => {
        const input = this.$refs.name as HTMLInputElement|undefined;
        if (input) {
          input.focus();
          input.select();
        }
      });
    },
    /** @param returnFocus back to the button (not when the user clicked somewhere else) */
    close(returnFocus = true) {
      this.open = false;
      document.removeEventListener('click', this.onDocumentClick);
      const toggle = this.$refs.toggle as HTMLElement|undefined;
      if (returnFocus && toggle) {
        toggle.focus();
      }
    },
    save(asNew: boolean) {
      if (this.name.trim() === '') {
        return;
      }
      this.$emit('save', { name: this.name.trim(), shared: this.shared, asNew });
    },
    onDocumentClick(event: MouseEvent) {
      const root = this.$refs.root as HTMLElement|undefined;
      // the path of the click, not contains(): "Delete" swaps itself for the confirmation, so the
      // clicked button is gone from the page when the click reaches the document
      if (this.open && root && !event.composedPath().includes(root)) {
        this.close(false);
      }
    },
  },
});
</script>
