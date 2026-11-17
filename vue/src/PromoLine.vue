<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later

  Tip line of the Feinwerk promotion kit (PageFilterReport docs/promo-kit.md, footer
  variant A): one sentence, its link and the hide control, in the kit's markup and
  classes. The shared javascripts/promo.js (loaded by Matomo's asset manager) finds it
  through its MutationObserver, applies the 30-day hiding and the one-notice-per-page
  rule and only then shows it (data-fwpromo-ready). The message comes from
  Promo\PromoLine::current() as a widget prop: null for users without admin access, in
  embeds and when promotions are switched off. The line starts with the Feinwerk Plugins
  logo of the kit's templates/_feinwerkLogo.twig (kit 2c5b91f; Peter, 2026-11-17:
  "feinwerk logo immer dabei"): inline SVG and text, nothing loaded, colours in
  stylesheets/feinwerklogo.css, only the round mark on a narrow line (promo.css, promo.js).
-->
<template>
  <div
    v-if="promo"
    class="fwpromo-line"
    :data-fwpromo-id="promo.id"
    data-fwpromo-placement="line"
    :data-fwpromo-days="promo.dismissDays"
    :data-fwpromo-demo="promo.demo ? '1' : undefined"
  >
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
    <span
      class="fwpromo-line__text"
      :title="promo.text"
    >{{ promo.text }}</span>
    <a
      class="fwpromo-line__link"
      :href="promo.url"
      :target="promo.external === false ? undefined : '_blank'"
      :rel="promo.external === false ? undefined : 'noopener'"
      :title="promo.note || undefined"
    >{{ promo.link }}</a>
    <button
      type="button"
      class="fwpromo-line__dismiss fwpromo-dismiss"
      :title="dismissLabel"
      :aria-label="dismissLabel"
    ><span aria-hidden="true">&times;</span></button>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';

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

export interface PromoVariables {
  id: string;
  placement: string;
  title: string;
  text: string;
  link: string;
  url: string;
  dismissDays: number;
  /** demo mode of a test instance: the kit script hides the notice for the browser session only */
  demo?: boolean;
  /** false: a link inside Matomo (the connector in the instance's own Marketplace), same tab */
  external?: boolean;
  /** the connector message for an admin: who can install it (the link's title) */
  note?: string;
}

export default defineComponent({
  props: {
    promo: {
      type: Object as PropType<PromoVariables|null>,
      default: null,
    },
    dismissLabel: { type: String, required: true },
  },
  data() {
    return { LOGO_RINGS };
  },
});
</script>
