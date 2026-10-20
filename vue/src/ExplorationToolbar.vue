<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fwde-toolbar">
    <div
      class="fwde-switch"
      role="group"
      :aria-label="translate('FeinwerkDataExploration_View')"
    >
      <button
        v-for="option in viewOptions"
        :key="option.view"
        type="button"
        :class="{
          'fwde-switch__button': true,
          'fwde-switch__button--active': view === option.view,
        }"
        :aria-pressed="view === option.view ? 'true' : 'false'"
        :disabled="option.view !== 'table' && !canChart"
        :title="option.view !== 'table' && !canChart
          ? translate('FeinwerkDataExploration_ChartNeedsRows')
          : option.label"
        @click="$emit('view', option.view)"
      >{{ option.label }}</button>
    </div>
    <label class="fwde-toolbar__limit">
      <span>{{ translate('FeinwerkDataExploration_Rows') }}</span>
      <select
        class="browser-default fwde-select"
        :value="String(state.limit)"
        @change="onLimit"
      >
        <option
          v-for="option in limitOptions"
          :key="option"
          :value="String(option)"
        >{{ option }}</option>
      </select>
    </label>
    <div
      ref="exportMenu"
      class="fwde-menu"
    >
      <button
        type="button"
        class="fwde-button"
        aria-haspopup="true"
        :aria-expanded="exportOpen ? 'true' : 'false'"
        @click="exportOpen = !exportOpen"
      >
        <span
          class="icon-export"
          aria-hidden="true"
        />
        {{ translate('General_Export') }}
        <span
          class="icon-chevron-down"
          aria-hidden="true"
        />
      </button>
      <ul
        v-if="exportOpen"
        class="fwde-menu__list"
      >
        <li
          v-for="format in formats"
          :key="format"
        >
          <a
            class="fwde-menu__item"
            :href="exportUrl(state, format)"
            target="_blank"
            rel="noreferrer noopener"
            @click="exportOpen = false"
          >{{ translate('FeinwerkDataExploration_ExportAs', format) }}</a>
        </li>
      </ul>
    </div>
    <button
      type="button"
      class="fwde-icon-button fwde-toolbar__copy"
      :title="copyText"
      :aria-label="copyText"
      @click="copyLink"
    >
      <span
        :class="copied ? 'icon-ok' : 'icon-content-copy'"
        aria-hidden="true"
      />
    </button>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import { ChartView, ExplorationState } from './types';
import { LIMIT_OPTIONS } from './state';
import { exportUrl } from './exportLink';

export default defineComponent({
  props: {
    state: { type: Object as PropType<ExplorationState>, required: true },
    maxRows: { type: Number, required: true },
    view: { type: String as PropType<ChartView>, required: true },
    canChart: { type: Boolean, required: true },
  },
  emits: ['limit', 'view'],
  data() {
    return {
      copied: false,
      timer: 0,
      exportOpen: false,
      formats: ['CSV', 'TSV'] as ('CSV'|'TSV')[],
    };
  },
  mounted() {
    document.addEventListener('click', this.onDocumentClick);
    document.addEventListener('keydown', this.onKeydown);
  },
  beforeUnmount() {
    window.clearTimeout(this.timer);
    document.removeEventListener('click', this.onDocumentClick);
    document.removeEventListener('keydown', this.onKeydown);
  },
  computed: {
    limitOptions(): number[] {
      return LIMIT_OPTIONS.filter((option) => option <= this.maxRows);
    },
    viewOptions(): { view: ChartView, label: string }[] {
      return [
        { view: 'table', label: translate('FeinwerkDataExploration_ViewTable') },
        { view: 'chart', label: translate('FeinwerkDataExploration_ViewChart') },
        { view: 'both', label: translate('FeinwerkDataExploration_ViewBoth') },
      ];
    },
    copyText(): string {
      return translate(this.copied
        ? 'FeinwerkDataExploration_LinkCopied'
        : 'FeinwerkDataExploration_CopyLink');
    },
  },
  methods: {
    translate,
    exportUrl,
    onLimit(event: Event) {
      this.$emit('limit', Number((event.target as HTMLSelectElement).value));
    },
    onDocumentClick(event: MouseEvent) {
      const menu = this.$refs.exportMenu as HTMLElement|undefined;
      if (this.exportOpen && menu && !menu.contains(event.target as Node)) {
        this.exportOpen = false;
      }
    },
    onKeydown(event: KeyboardEvent) {
      if (event.key === 'Escape') {
        this.exportOpen = false;
      }
    },
    copyLink() {
      const done = () => {
        this.copied = true;
        window.clearTimeout(this.timer);
        this.timer = window.setTimeout(() => { this.copied = false; }, 2500);
      };
      if (navigator.clipboard) {
        navigator.clipboard.writeText(window.location.href).then(done).catch(() => undefined);
      }
    },
  },
});
</script>
