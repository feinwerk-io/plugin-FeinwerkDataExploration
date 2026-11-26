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
        @submit.prevent="save(!canChange || !!historyLostFrom)"
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
        <div
          v-if="metrics.length"
          class="fwde-savedialog__kind"
        >
          <div class="fwde-field__label">{{ translate('FeinwerkDataExploration_SaveAs') }}</div>
          <SegmentedSwitch
            :options="kindOptions"
            :model-value="saveAs"
            :label="translate('FeinwerkDataExploration_SaveAs')"
            @update:model-value="saveAs = $event"
          />
        </div>
        <div
          v-if="saveAs === 'kpi'"
          class="fwde-savedialog__card"
        >
          <label
            class="fwde-field__label"
            for="fwde-savedialog-kpimetric"
          >{{ translate('FeinwerkDataExploration_KpiMetric') }}</label>
          <select
            id="fwde-savedialog-kpimetric"
            v-model="card.metric"
            class="browser-default fwde-select"
          >
            <option
              v-for="metric in metrics"
              :key="metric.id"
              :value="metric.id"
            >{{ metric.name }}</option>
          </select>
          <label
            class="fwde-field__label"
            for="fwde-savedialog-kpicompare"
          >{{ translate('FeinwerkDataExploration_KpiCompare') }}</label>
          <select
            id="fwde-savedialog-kpicompare"
            v-model="card.compare"
            class="browser-default fwde-select"
          >
            <option
              v-for="option in compareOptions"
              :key="option.value"
              :value="option.value"
            >{{ option.label }}</option>
          </select>
          <div class="fwde-field__label">{{ translate('FeinwerkDataExploration_KpiLine') }}</div>
          <SegmentedSwitch
            :options="lineOptions"
            :model-value="String(card.line)"
            :label="translate('FeinwerkDataExploration_KpiLine')"
            @update:model-value="card.line = Number($event)"
          />
          <div class="fwde-field__label">{{ translate('FeinwerkDataExploration_KpiChange') }}</div>
          <SegmentedSwitch
            :options="changeOptions"
            :model-value="card.change"
            :label="translate('FeinwerkDataExploration_KpiChange')"
            @update:model-value="card.change = $event"
          />
        </div>
        <div
          v-if="menuSections.length && saveAs !== 'kpi'"
          class="fwde-savedialog__place"
        >
          <label
            class="fwde-field__label"
            for="fwde-savedialog-menu"
          >{{ translate('FeinwerkDataExploration_ShowInMenu') }}</label>
          <select
            id="fwde-savedialog-menu"
            v-model="menu"
            class="browser-default fwde-select fwde-savedialog__menu"
          >
            <option value="">{{ translate('FeinwerkDataExploration_MenuNone') }}</option>
            <option
              v-for="section in menuSections"
              :key="section.id"
              :value="section.id"
            >{{ section.name }}</option>
          </select>
        </div>
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
            <span
              v-if="current && savedKind(current) !== 'funnel'"
              class="fwde-savedialog__deletehint"
            >{{ translate('FeinwerkDataExploration_DeleteAlsoRemoves') }}</span>
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
        <div
          v-if="canChange && historyLostFrom"
          class="alert alert-warning fwde-savedialog__history"
          role="note"
        >{{ translate('FeinwerkDataExploration_ArchiveHistoryLost', historyLostFrom) }}</div>
      </form>
      <template #footer>
        <button
          type="button"
          class="fwde-button fwde-savedialog__cancel"
          @click="close()"
        >{{ translate('General_Cancel') }}</button>
        <template v-if="canChange">
          <!-- a change that restarts the history: "Save as new" is the default
               (UI review 2026-11-26 F1) -->
          <button
            :type="historyLostFrom ? 'submit' : 'button'"
            :form="historyLostFrom ? 'fwde-savedialog-form' : undefined"
            :class="historyLostFrom ? 'btn' : 'fwde-button'"
            class="fwde-savedialog__asnew"
            :disabled="busy"
            @click="historyLostFrom ? undefined : save(true)"
          >{{ translate('FeinwerkDataExploration_SaveAsNew') }}</button>
          <button
            :type="historyLostFrom ? 'button' : 'submit'"
            :form="historyLostFrom ? undefined : 'fwde-savedialog-form'"
            :class="historyLostFrom ? 'fwde-button' : 'btn'"
            class="fwde-savedialog__changes"
            :disabled="busy"
            @click="historyLostFrom ? save(false) : undefined"
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
import { MenuSection, SavedExploration } from './api';
import {
  cardDefaults, CardSettings, dialogDefaults, savedKind,
} from './saved';
import SidePanel from './SidePanel.vue';
import SegmentedSwitch, { SegmentedOption } from './SegmentedSwitch.vue';

export default defineComponent({
  components: { SidePanel, SegmentedSwitch },
  props: {
    current: { type: Object as PropType<SavedExploration|null>, default: null },
    /** the name a new exploration gets suggested (the result title) */
    suggestedName: { type: String, required: true },
    /** at least write access: may share with the website's users */
    canShare: { type: Boolean, default: false },
    busy: { type: Boolean, default: false },
    error: { type: String, default: '' },
    /** Matomo's sections a saved exploration can show in as a report; none: no choice (funnels) */
    menuSections: { type: Array as PropType<MenuSection[]>, default: () => [] },
    /** the exploration's metrics with their names: with them it can be saved as a KPI card too */
    metrics: { type: Array as PropType<{ id: string, name: string }[]>, default: () => [] },
    /**
     * an archived exploration whose change starts its history again, where raw data is deleted:
     * the first day that can be rebuilt (archived saved explorations, 2026-11-25); empty otherwise
     */
    historyLostFrom: { type: String, default: '' },
  },
  emits: ['save', 'delete', 'open'],
  data() {
    return {
      open: false,
      name: '',
      shared: false,
      menu: '',
      confirmDelete: false,
      /** an exploration or a KPI card (2026-11-24) */
      saveAs: 'table' as 'table'|'kpi',
      card: {
        metric: '', compare: 'average4', line: 30, change: 'percent',
      } as CardSettings,
      /**
       * the name the panel suggested last; while the name is still it, it follows the kind and
       * the card's metric
       */
      autoName: '',
    };
  },
  computed: {
    currentName(): string {
      return this.current ? this.current.name : '';
    },
    /** "Save changes" keeps the kind: a table never turns into a card or back (then: a new save) */
    canChange(): boolean {
      if (!this.current || !this.current.canEdit) {
        return false;
      }
      return !this.metrics.length || savedKind(this.current) === this.saveAs;
    },
    kindOptions(): SegmentedOption[] {
      return [
        { value: 'table', label: translate('FeinwerkDataExploration_SaveAsExploration') },
        { value: 'kpi', label: translate('FeinwerkDataExploration_SaveAsKpiCard') },
      ];
    },
    compareOptions(): { value: string, label: string }[] {
      return [
        { value: 'average4', label: translate('FeinwerkDataExploration_KpiCompareAverage4') },
        { value: 'weekday', label: translate('FeinwerkDataExploration_KpiCompareWeekday') },
        { value: 'previous', label: translate('FeinwerkDataExploration_KpiComparePrevious') },
        { value: 'none', label: translate('FeinwerkDataExploration_KpiCompareNone') },
      ];
    },
    lineOptions(): SegmentedOption[] {
      return [
        { value: '0', label: translate('FeinwerkDataExploration_KpiLineOff') },
        { value: '7', label: translate('FeinwerkDataExploration_KpiLine7') },
        { value: '30', label: translate('FeinwerkDataExploration_KpiLine30') },
      ];
    },
    changeOptions(): SegmentedOption[] {
      return [
        { value: 'percent', label: translate('FeinwerkDataExploration_KpiChangePercent') },
        { value: 'absolute', label: translate('FeinwerkDataExploration_KpiChangeAbsolute') },
      ];
    },
  },
  watch: {
    // UI review 2026-11-24 (F6): a new card is named after its metric, a name the user typed stays
    saveAs() {
      this.suggestName();
    },
    'card.metric': function cardMetric() {
      this.suggestName();
    },
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
      ({ name: this.name, shared: this.shared, menu: this.menu } = dialogDefaults(
        this.current,
        this.suggestedName,
        this.canShare,
      ));
      ({ saveAs: this.saveAs, card: this.card } = cardDefaults(
        this.current,
        this.metrics.map((metric) => metric.id),
      ));
      // a saved exploration keeps its name; a new one starts with the suggestion
      this.autoName = this.current ? '' : this.name;
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
    savedKind,
    /**
     * while the name is still the panel's suggestion: the card's metric for a KPI card, else the
     * result title
     */
    suggestName() {
      if (!this.open || this.name !== this.autoName) {
        return;
      }
      const metric = this.metrics.find((m) => m.id === this.card.metric);
      this.name = this.saveAs === 'kpi' && metric ? metric.name : this.suggestedName;
      this.autoName = this.name;
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
      const event: Record<string, unknown> = {
        name: this.name.trim(),
        shared: this.shared,
        asNew,
        menu: this.menuSections.length && this.saveAs !== 'kpi' ? this.menu : '',
      };
      if (this.saveAs === 'kpi') {
        event.card = { ...this.card };
      }
      this.$emit('save', event);
    },
  },
});
</script>
