<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later

  The Feinwerk Plugins logo of the promotion kit (templates/_feinwerkLogo.twig, kit 2c5b91f): the
  store's round mark as inline SVG and the word mark as text, nothing loaded, hidden from screen
  readers; colours in stylesheets/feinwerklogo.css. Used by the tip line and the premium banner.
-->
<template>
  <span
    class="fwlogo"
    aria-hidden="true"
  ><svg
    class="fwlogo__mark"
    viewBox="0 0 48 48"
    width="18"
    height="18"
    focusable="false"
  ><circle
    class="fwlogo__core"
    cx="24"
    cy="24"
    r="5.4"
  /><g
    v-for="ring in LOGO_RINGS"
    :key="ring.opacity"
    class="fwlogo__dots"
    :opacity="ring.opacity"
  ><circle
    v-for="([cx, cy], n) in ring.dots"
    :key="n"
    :cx="cx"
    :cy="cy"
    :r="ring.r"
  /></g></svg><span class="fwlogo__word">feinwerk<span
    class="fwlogo__sub"
  > plugins</span></span></span>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

/** the dots of the store's feinwerk-plugins-mark.svg, as in the kit's _feinwerkLogo.twig */
const LOGO_RINGS = [
  {
    opacity: '0.9', r: '2.9', dots: [[16, 24], [32, 24], [24, 16], [24, 32]],
  },
  {
    opacity: '0.55', r: '2.5', dots: [[16, 16], [32, 16], [16, 32], [32, 32]],
  },
  {
    opacity: '0.35', r: '2.1', dots: [[8, 24], [40, 24], [24, 8], [24, 40]],
  },
];

export default defineComponent({
  data() {
    return { LOGO_RINGS };
  },
});
</script>
