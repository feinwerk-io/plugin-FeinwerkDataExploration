<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later

  The premium banner below the result (Peter, 2026-11-17: "ein randloses Banner unter dem
  Hauptfenster mit Premium-Plugin Rotation"): paid Feinwerk plugins this Matomo does not have, one
  at a time, in the markup of the promotion kit's templates/_premiumBanner.twig (Page Filter
  Report 99c4100) with this plugin's classes. The list comes from
  Promo\PremiumPromo::bannerProps() as the widget prop `premium` (null for users without admin
  access, in embeds, with promotions off). The kit's javascripts/promocard.js does the rest: it
  shows the first plugin not hidden, its x hides it for `days` (demo: this session) and the next
  follows, and it removes the group when none is left. So Vue renders the group once (v-once) and
  never patches it again; it carries no data-fwpromo-widget, which would hide the whole
  exploration page (one Matomo widget) with the last plugin.
-->
<template>
  <div
    v-if="premium && premium.plugins.length"
    class="fwde-premium-slot"
  >
    <div
      v-once
      class="fwde-premium"
      data-fwpromo-rotation="banner"
    >
      <section
        v-for="plugin in premium.plugins"
        :key="plugin.id"
        class="fwde-premium__item"
        :data-fwpromo-id="plugin.id"
        :data-fwpromo-days="premium.days"
        :data-fwpromo-demo="premium.demo ? '1' : undefined"
        :aria-label="label"
      >
        <div class="fwde-premium__head">
          <span
            v-if="plugin.image"
            class="fwde-premium__art"
          ><img
            :src="plugin.image"
            alt=""
            loading="lazy"
          ></span>
          <div class="fwde-premium__intro">
            <div class="fwde-premium__label">{{ label }}</div>
            <div class="fwde-premium__name">{{ plugin.name }}</div>
          </div>
        </div>
        <div
          v-if="plugin.description"
          class="fwde-premium__text"
          :title="plugin.description"
        >{{ plugin.description }}</div>
        <div
          v-if="plugin.external === '1' && premium.note"
          class="fwde-premium__note"
        >{{ premium.note }}</div>
        <div class="fwde-premium__actions">
          <a
            class="fwde-premium__cta"
            :href="plugin.url"
            :target="plugin.external === '1' ? '_blank' : undefined"
            :rel="plugin.external === '1' ? 'noopener' : undefined"
          >{{ moreLabel }} &rarr;</a>
          <span
            v-if="plugin.price"
            class="fwde-premium__price"
          >{{ plugin.price }}</span>
          <FeinwerkLogo />
        </div>
        <button
          type="button"
          class="fwde-premium__close"
          data-fwpromo-close
          :title="dismissLabel"
          :aria-label="dismissLabel"
        ><span aria-hidden="true">&times;</span></button>
      </section>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import FeinwerkLogo from './FeinwerkLogo.vue';
import { PremiumProps } from './premium';

export default defineComponent({
  components: { FeinwerkLogo },
  props: {
    premium: {
      type: Object as PropType<PremiumProps|null>,
      default: null,
    },
    label: { type: String, required: true },
    moreLabel: { type: String, required: true },
    dismissLabel: { type: String, required: true },
  },
});
</script>
