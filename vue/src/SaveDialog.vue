<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fwde-savedialog">
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
    <SidePanel
      :open="open"
      :title="translate('FeinwerkDataExploration_SaveExploration')"
      :close-label="translate('General_Close')"
      scope-class="fwde-page fwde-savepanel"
      @update:open="onPanel"
    >
      <form
        id="fwde-savedialog-form"
        class="fwde-savedialog__form"
        @submit.prevent="save(!canChange)"
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
        <div
          v-if="canChange"
          class="fwde-savedialog__delete"
        >
          <button
            v-if="!confirmDelete"
            ref="deleteAsk"
            type="button"
            class="fwde-button fwde-savedialog__deleteask"
            :disabled="busy"
            @click="askDelete(true)"
          >{{ translate('General_Delete') }}</button>
          <template v-else>
            <span>{{ translate('FeinwerkDataExploration_SavedDeleteConfirm', currentName) }}</span>
            <button
              ref="deleteYes"
              type="button"
              class="fwde-button fwde-savedialog__deleteyes"
              :disabled="busy"
              @click="$emit('delete')"
            >{{ translate('General_Delete') }}</button>
            <button
              type="button"
              class="fwde-button fwde-savedialog__deleteno"
              @click="askDelete(false)"
            >{{ translate('General_Cancel') }}</button>
          </template>
        </div>
      </form>
      <template #footer>
        <button
          type="button"
          class="fwde-button fwde-savedialog__cancel"
          @click="close()"
        >{{ translate('General_Cancel') }}</button>
        <template v-if="canChange">
          <button
            type="button"
            class="fwde-button fwde-savedialog__asnew"
            :disabled="busy"
            @click="save(true)"
          >{{ translate('FeinwerkDataExploration_SaveAsNew') }}</button>
          <button
            type="submit"
            form="fwde-savedialog-form"
            class="btn fwde-savedialog__changes"
            :disabled="busy"
          >{{ translate('FeinwerkDataExploration_SaveChanges') }}</button>
        </template>
        <button
          v-else
          type="submit"
          form="fwde-savedialog-form"
          class="btn fwde-savedialog__new"
          :disabled="busy"
        >{{ translate('General_Save') }}</button>
      </template>
    </SidePanel>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import { SavedExploration } from './api';
import { dialogDefaults } from './saved';
import SidePanel from './SidePanel.vue';

export default defineComponent({
  components: { SidePanel },
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
      this.$nextTick(() => {
        const input = this.$refs.name as HTMLInputElement|undefined;
        if (input) {
          input.focus();
          input.select();
        }
      });
    },
    /** closes the panel; focus back on Save (also where a click does not focus a button) */
    close() {
      this.open = false;
      const toggle = this.$refs.toggle as HTMLElement|undefined;
      if (toggle) {
        toggle.focus();
      }
    },
    /** Delete swaps for its confirmation and back; the focus goes along (it stays in the panel) */
    askDelete(ask: boolean) {
      this.confirmDelete = ask;
      this.$nextTick(() => {
        const target = this.$refs[ask ? 'deleteYes' : 'deleteAsk'] as HTMLElement|undefined;
        if (target) {
          target.focus();
        }
      });
    },
    /** the panel asks to close: Escape, the dimmed page or its close button */
    onPanel(isOpen: boolean) {
      if (!isOpen) {
        this.close();
      }
    },
    save(asNew: boolean) {
      if (this.name.trim() === '') {
        return;
      }
      this.$emit('save', { name: this.name.trim(), shared: this.shared, asNew });
    },
  },
});
</script>
