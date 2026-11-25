<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fwde-toolbar">
    <SegmentedSwitch
      v-if="state"
      class="fwde-toolbar__view"
      :options="viewSwitch"
      :model-value="view"
      :label="translate('FeinwerkDataExploration_View')"
      @update:model-value="$emit('view', $event)"
    />
    <label
      v-if="state"
      class="fwde-toolbar__limit"
    >
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
      v-if="state || exportUrlFor"
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
            :href="exportHref(format)"
            target="_blank"
            rel="noreferrer noopener"
            @click="exportOpen = false"
          >{{ translate('FeinwerkDataExploration_ExportAs', format) }}</a>
        </li>
      </ul>
    </div>
    <div
      ref="copyBox"
      class="fwde-copybox"
    >
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
      <!-- the browser refused to copy (no clipboard over http, a denied permission):
           the link to copy by hand -->
      <div
        v-if="manualLink"
        class="fwde-menu__list fwde-copylink"
      >
        <label class="fwde-copylink__label">
          <span>{{ translate('FeinwerkDataExploration_CopyLinkByHand') }}</span>
          <input
            ref="manualInput"
            class="browser-default fwde-input"
            type="text"
            readonly
            :value="manualLink"
            @focus="selectAll"
            @keydown.escape.stop="closeManual"
          />
        </label>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import { ChartView, ExplorationState } from './types';
import { LIMIT_OPTIONS } from './state';
import { exportUrl } from './exportLink';
import SegmentedSwitch from './SegmentedSwitch.vue';

export default defineComponent({
  components: { SegmentedSwitch },
  props: {
    /** the free-form table; null for pages without view switch and row limit (funnel) */
    state: { type: Object as PropType<ExplorationState|null>, default: null },
    maxRows: { type: Number, default: 0 },
    view: { type: String as PropType<ChartView>, default: 'table' },
    canChart: { type: Boolean, default: false },
    /** the export link of a page without state (funnel) */
    exportUrlFor: {
      type: Function as PropType<(format: 'CSV'|'TSV') => string>,
      default: undefined,
    },
  },
  emits: ['limit', 'view'],
  data() {
    return {
      copied: false,
      /** the link shown for copying by hand when the browser refuses ('' = hidden) */
      manualLink: '',
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
    /** Chart and Both need rows: disabled with the reason as tooltip */
    viewSwitch(): { value: string, label: string, disabled: boolean, title?: string }[] {
      return this.viewOptions.map((option) => {
        const needsRows = option.view !== 'table' && !this.canChart;
        return {
          value: option.view,
          label: option.label,
          disabled: needsRows,
          title: needsRows ? translate('FeinwerkDataExploration_ChartNeedsRows') : undefined,
        };
      });
    },
    copyText(): string {
      return translate(this.copied
        ? 'FeinwerkDataExploration_LinkCopied'
        : 'FeinwerkDataExploration_CopyLink');
    },
  },
  methods: {
    translate,
    exportHref(format: 'CSV'|'TSV'): string {
      if (this.exportUrlFor) {
        return this.exportUrlFor(format);
      }
      return this.state ? exportUrl(this.state, format) : '';
    },
    onLimit(event: Event) {
      this.$emit('limit', Number((event.target as HTMLSelectElement).value));
    },
    onDocumentClick(event: MouseEvent) {
      const copyBox = this.$refs.copyBox as HTMLElement|undefined;
      if (this.manualLink && copyBox && !copyBox.contains(event.target as Node)) {
        this.manualLink = '';
      }
      const menu = this.$refs.exportMenu as HTMLElement|undefined;
      if (this.exportOpen && menu && !menu.contains(event.target as Node)) {
        this.exportOpen = false;
      }
    },
    onKeydown(event: KeyboardEvent) {
      if (event.key === 'Escape') {
        this.exportOpen = false;
        this.manualLink = '';
      }
    },
    /**
     * The clipboard first; without it (Matomo over http) or when the browser refuses, the older
     * copy command; when that fails too, the link selected in a field to copy by hand (UI review
     * 2026-11-23: never a silent failure, the user would paste an old clipboard).
     */
    copyLink() {
      const url = window.location.href;
      this.manualLink = '';
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url)
          .then(() => this.copiedNow())
          .catch(() => this.copyWithCommand(url));
        return;
      }
      this.copyWithCommand(url);
    },
    copyWithCommand(url: string) {
      let copied = false;
      if (typeof document.execCommand === 'function') {
        const field = document.createElement('textarea');
        field.value = url;
        field.setAttribute('readonly', '');
        field.style.position = 'fixed';
        field.style.top = '-1000px';
        document.body.appendChild(field);
        field.select();
        try {
          copied = document.execCommand('copy');
        } catch (e) {
          copied = false;
        }
        document.body.removeChild(field);
      }
      if (copied) {
        this.copiedNow();
        return;
      }
      this.manualLink = url;
      this.$nextTick(() => {
        const input = this.$refs.manualInput as HTMLInputElement|undefined;
        if (input) {
          input.focus();
          input.select();
        }
      });
    },
    copiedNow() {
      this.copied = true;
      window.clearTimeout(this.timer);
      this.timer = window.setTimeout(() => { this.copied = false; }, 2500);
    },
    selectAll(event: FocusEvent) {
      (event.target as HTMLInputElement).select();
    },
    closeManual() {
      this.manualLink = '';
      const button = (this.$el as HTMLElement).querySelector<HTMLElement>('.fwde-toolbar__copy');
      if (button) {
        button.focus();
      }
    },
  },
});
</script>
