<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fwde-page fwde-kpi-widget">
    <ActivityIndicator :loading="loading" />
    <Alert
      v-if="error"
      severity="warning"
    >{{ error }}</Alert>
    <KpiCardView
      v-if="card"
      :card="card"
      :id-site="idSite"
      :explore-url="exploreUrl"
      :currency-symbol="currencySymbol"
      :feedback="feedback"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { ActivityIndicator, Alert } from 'CoreHome';
import KpiCardView from './KpiCardView.vue';
import { FeedbackSettings } from './feedback';
import { KpiCardData } from './kpi';
import { errorText, loadKpiCard } from './api';

/**
 * A KPI card's dashboard widget (2026-11-24): asks FeinwerkDataExploration.getKpiCard for the
 * dashboard's period and segment and shows the card, or why it cannot.
 */
export default defineComponent({
  components: { ActivityIndicator, Alert, KpiCardView },
  props: {
    idSite: { type: Number, required: true },
    idSaved: { type: Number, required: true },
    exploreUrl: { type: String, required: true },
    currencySymbol: { type: String, default: '' },
    feedback: {
      type: Object as PropType<FeedbackSettings>,
      default: () => ({ enabled: false, supportUrl: '', version: '' }),
    },
  },
  data() {
    return { card: null as KpiCardData|null, loading: true, error: '' };
  },
  mounted() {
    loadKpiCard(this.idSaved)
      .then((card) => {
        this.card = card;
      })
      .catch((e) => {
        this.error = errorText(e);
      })
      .finally(() => {
        this.loading = false;
      });
  },
});
</script>
