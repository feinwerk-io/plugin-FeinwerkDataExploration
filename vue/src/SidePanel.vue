<!--
  Matomo - free/libre analytics platform
  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later

  A side panel that slides in from the right (UI item 2, 2026-11-21): a modal dialog with a title,
  a close button, a scrolling body and a footer that stays at the bottom; about two thirds of the
  page wide, the full width on a phone. Focus stays inside (Tab and Shift+Tab wrap), Escape, the
  dimmed page and the close button ask to close, the focus goes back to where it was, the page
  behind does not scroll. Self-contained: no plugin imports, texts come in as props, styles in
  stylesheets/fwde-ui.css. Rendered at the end of <body>, so a page's own styles reach it only
  through scopeClass.
-->
<template>
  <Teleport to="body">
    <!-- the keyboard closes the panel with Escape; the dimmed page is a mouse shortcut -->
    <!-- eslint-disable-next-line vuejs-accessibility/click-events-have-key-events -->
    <div
      v-if="open"
      class="fwde-sidepanel__backdrop"
      :class="scopeClass"
      @click="requestClose"
    />
    <Transition name="fwde-sidepanel">
      <section
        v-if="open"
        ref="panel"
        class="fwde-sidepanel"
        :class="scopeClass"
        role="dialog"
        aria-modal="true"
        tabindex="-1"
        :aria-labelledby="titleId"
        @keydown="onKeydown"
      >
        <header class="fwde-sidepanel__header">
          <h2
            :id="titleId"
            class="fwde-sidepanel__title"
          >{{ title }}</h2>
          <button
            type="button"
            class="fwde-sidepanel__close"
            :aria-label="closeLabel"
            :title="closeLabel"
            @click="requestClose"
          >
            <span aria-hidden="true">×</span>
          </button>
        </header>
        <div class="fwde-sidepanel__body">
          <slot />
        </div>
        <footer
          v-if="$slots.footer"
          class="fwde-sidepanel__footer"
        >
          <slot name="footer" />
        </footer>
      </section>
    </Transition>
  </Teleport>
</template>

<script lang="ts">
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { defineComponent } from 'vue';

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), '
  + 'select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
/** the class on <html> that locks the page scroll while any panel is open */
const OPEN_CLASS = 'fwde-sidepanel-open';

let openPanels = 0;
let lastId = 0;

export default defineComponent({
  props: {
    open: { type: Boolean, required: true },
    title: { type: String, required: true },
    /** the close button's label (aria-label and tooltip) */
    closeLabel: { type: String, required: true },
    /** classes for the panel and the backdrop, e.g. the page's scope for its own styles */
    scopeClass: { type: String, default: '' },
  },
  emits: ['update:open'],
  data() {
    lastId += 1;
    return {
      titleId: `fwde-sidepanel-title-${lastId}`,
      opener: null as HTMLElement | null,
      locked: false,
    };
  },
  watch: {
    open(isOpen: boolean) {
      if (isOpen) {
        this.onOpen();
      } else {
        this.onClose();
      }
    },
  },
  mounted() {
    if (this.open) {
      this.onOpen();
    }
  },
  beforeUnmount() {
    if (this.open) {
      this.onClose();
    }
  },
  methods: {
    requestClose() {
      this.$emit('update:open', false);
    },
    onOpen() {
      const active = document.activeElement;
      this.opener = active instanceof HTMLElement && active !== document.body ? active : null;
      this.lock();
      document.addEventListener('keydown', this.onDocumentKeydown, true);
      this.$nextTick(() => {
        const panel = this.$refs.panel as HTMLElement | undefined;
        const first = this.focusables(true)[0];
        if (first) {
          first.focus();
        } else if (panel) {
          panel.focus();
        }
      });
    },
    onClose() {
      this.unlock();
      document.removeEventListener('keydown', this.onDocumentKeydown, true);
      const { opener } = this;
      this.opener = null;
      if (opener && document.contains(opener)) {
        opener.focus();
      }
    },
    lock() {
      if (!this.locked) {
        this.locked = true;
        openPanels += 1;
        document.documentElement.classList.add(OPEN_CLASS);
      }
    },
    unlock() {
      if (this.locked) {
        this.locked = false;
        openPanels = Math.max(0, openPanels - 1);
        if (openPanels === 0) {
          document.documentElement.classList.remove(OPEN_CLASS);
        }
      }
    },
    /** the panel's focusable elements; bodyFirst: those of the body, else all (the close button) */
    focusables(bodyFirst: boolean): HTMLElement[] {
      const panel = this.$refs.panel as HTMLElement | undefined;
      if (!panel) {
        return [];
      }
      // tab stops only: not a switch's other options (tabindex -1), nothing hidden
      const all = Array.from(panel.querySelectorAll<HTMLElement>(FOCUSABLE))
        .filter((el) => el.tabIndex >= 0 && !el.closest('[hidden]'));
      if (!bodyFirst) {
        return all;
      }
      const body = all.filter((el) => el.closest('.fwde-sidepanel__body'));
      return body.length ? body : all;
    },
    /**
     * Keys while the focus is outside the panel, e.g. on <body> after the focused control was
     * removed (a filter row, a button swapped for its confirmation): Escape still closes, Tab comes
     * back into the panel.
     */
    onDocumentKeydown(event: KeyboardEvent) {
      const panel = this.$refs.panel as HTMLElement | undefined;
      if (!panel || panel.contains(event.target as Node)) {
        return;
      }
      if (event.key === 'Escape') {
        event.preventDefault();
        event.stopPropagation();
        this.requestClose();
      } else if (event.key === 'Tab') {
        event.preventDefault();
        const items = this.focusables(false);
        const target = event.shiftKey ? items[items.length - 1] : items[0];
        (target || panel).focus();
      }
    },
    onKeydown(event: KeyboardEvent) {
      if (event.key === 'Escape') {
        event.preventDefault();
        event.stopPropagation();
        this.requestClose();
        return;
      }
      if (event.key !== 'Tab') {
        return;
      }
      const items = this.focusables(false);
      if (!items.length) {
        event.preventDefault();
        return;
      }
      const first = items[0];
      const last = items[items.length - 1];
      const active = document.activeElement;
      const panel = this.$refs.panel as HTMLElement | undefined;
      if (event.shiftKey && (active === first || active === panel)) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && active === last) {
        event.preventDefault();
        first.focus();
      }
    },
  },
});
</script>
