<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later

  The horizontal scroller of a result table with a shadow on each side that hides columns (UI
  review 2026-11-23: a wide table was cut at the card's edge with no cue, a cut number read as a
  whole one). The shadows follow the scrolling, the box's size and the table's width. It reports
  whether the table fits and the box's width ("layout", once per change), so the table can choose
  how wide its first dimension is.
-->
<template>
  <div
    class="fwde-table__frame"
    :class="{
      'fwde-table__frame--more-left': edges.left,
      'fwde-table__frame--more-right': edges.right,
    }"
  >
    <div
      ref="scroller"
      class="fwde-table__scroller"
      @scroll.passive="measure"
    >
      <slot />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ScrollEdges, scrollEdges } from './scrollEdges';

export default defineComponent({
  emits: ['layout'],
  data() {
    return {
      edges: { left: false, right: false } as ScrollEdges,
      /** the last layout reported ("overflow|width") */
      reported: '',
      observer: null as ResizeObserver|null,
    };
  },
  mounted() {
    window.addEventListener('resize', this.measure);
    if (typeof ResizeObserver !== 'undefined') {
      this.observer = new ResizeObserver(() => this.measure());
      const scroller = this.$refs.scroller as HTMLElement;
      this.observer.observe(scroller);
      if (scroller.firstElementChild) {
        this.observer.observe(scroller.firstElementChild);
      }
    }
    this.measure();
  },
  updated() {
    this.measure();
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.measure);
    if (this.observer) {
      this.observer.disconnect();
    }
  },
  methods: {
    measure() {
      const scroller = this.$refs.scroller as HTMLElement|undefined;
      if (!scroller) {
        return;
      }
      const next = scrollEdges(scroller.scrollLeft, scroller.clientWidth, scroller.scrollWidth);
      if (next.left !== this.edges.left || next.right !== this.edges.right) {
        this.edges = next;
      }
      const layout = {
        overflow: scroller.scrollWidth - scroller.clientWidth > 1,
        width: scroller.clientWidth,
      };
      const key = `${layout.overflow}|${layout.width}`;
      if (key !== this.reported) {
        this.reported = key;
        this.$emit('layout', layout);
      }
    },
  },
});
</script>
