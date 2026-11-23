<!--
  Matomo - free/libre analytics platform
  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later

  One joined control (UI item 1, 2026-11-21): Live Dashboard's segmented switch (main 9ee49d7) with
  the fwde- prefix, plus a disabled flag and a tooltip per option. Self-contained: no plugin
  imports, texts come in as props, styles in stylesheets/fwde-ui.css.
-->
<template>
  <div
    class="fwde-segmented"
    role="radiogroup"
    tabindex="-1"
    :aria-label="label"
    @keydown="onKey"
  >
    <button
      v-for="(option, i) in options"
      :key="option.value"
      type="button"
      role="radio"
      class="fwde-segmented__option"
      :class="{
        'fwde-segmented__option--active': option.value === modelValue,
        'fwde-segmented__option--disabled': !!option.disabled,
      }"
      :aria-checked="option.value === modelValue ? 'true' : 'false'"
      :tabindex="i === activeIndex ? 0 : -1"
      :disabled="!!option.disabled"
      :title="option.title || option.label"
      @click="choose(i)"
    >{{ option.label }}</button>
  </div>
</template>

<script lang="ts">
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { defineComponent, PropType } from 'vue';

export interface SegmentedOption {
  value: string;
  label: string;
  /** cannot be chosen now; the arrow keys skip it */
  disabled?: boolean;
  /** the tooltip, e.g. why the option is disabled; else the label */
  title?: string;
}

/**
 * A radio group, the chosen option filled. Tab reaches the chosen option; the arrow keys (around
 * the ends), Home and End choose another one that is not disabled.
 */
export default defineComponent({
  props: {
    options: {
      type: Array as PropType<SegmentedOption[]>,
      required: true,
    },
    modelValue: {
      type: String,
      required: true,
    },
    label: {
      type: String,
      required: true,
    },
  },
  emits: ['update:modelValue'],
  computed: {
    /** the chosen option, else the first that can be chosen (so Tab still reaches the group) */
    activeIndex(): number {
      const chosen = this.options.findIndex((o) => o.value === this.modelValue);
      if (chosen >= 0) {
        return chosen;
      }
      return Math.max(0, this.options.findIndex((o) => !o.disabled));
    },
  },
  methods: {
    choose(index: number) {
      const option = this.options[index];
      // the chosen one again is no change (an arrow key with every other option disabled)
      if (option && !option.disabled && option.value !== this.modelValue) {
        this.$emit('update:modelValue', option.value);
      }
    },
    /** the next option to choose from index in direction step (1 or -1), around the ends */
    nextEnabled(index: number, step: number): number {
      const count = this.options.length;
      for (let n = 1; n <= count; n += 1) {
        const i = (index + step * n + count * n) % count;
        if (!this.options[i].disabled) {
          return i;
        }
      }
      return index;
    },
    onKey(event: KeyboardEvent) {
      const forward = this.nextEnabled(this.activeIndex, 1);
      const back = this.nextEnabled(this.activeIndex, -1);
      const moves: Record<string, number> = {
        ArrowRight: forward,
        ArrowDown: forward,
        ArrowLeft: back,
        ArrowUp: back,
        Home: this.nextEnabled(this.options.length - 1, 1),
        End: this.nextEnabled(0, -1),
      };
      const next = moves[event.key];
      if (next === undefined) {
        return;
      }
      event.preventDefault();
      this.choose(next);
      // from the DOM: Vue does not promise the order of refs collected in a v-for
      const group = event.currentTarget as HTMLElement | null;
      const option = group ? group.querySelectorAll('button')[next] : undefined;
      if (option) {
        option.focus();
      }
    },
  },
});
</script>
