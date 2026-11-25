<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    v-if="!embedded"
    class="fwde-page fwde-saved-footer"
  >
    <a
      class="fwde-saved-footer__open"
      :href="exploreUrl"
    >{{ translate('FeinwerkDataExploration_OpenInExploration') }}</a>
    <FeedbackBar
      :id-site="idSite"
      topic="saved_report"
      :question="translate('FeinwerkDataExploration_FeedbackQuestion_saved_report')"
      :feedback="feedback"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import FeedbackBar from './FeedbackBar.vue';
import { FeedbackSettings } from './feedback';
import { embeddedPage } from './embed';

/**
 * The footer under a saved exploration's report (saved reports, 2026-11-24), on its menu page and
 * in its dashboard widget: "Open in exploration" and the feedback line; nothing in an embed.
 */
export default defineComponent({
  components: { FeedbackBar },
  props: {
    idSite: { type: Number, required: true },
    /** the free-form table with the saved exploration, same website and period */
    exploreUrl: { type: String, required: true },
    feedback: {
      type: Object as PropType<FeedbackSettings>,
      default: () => ({ enabled: false, supportUrl: '', version: '' }),
    },
  },
  data() {
    // an embed (Widgetize) shows no footer: its request does not say so, the page's address does
    return { embedded: embeddedPage(window.location.search) };
  },
  methods: { translate },
});
</script>
