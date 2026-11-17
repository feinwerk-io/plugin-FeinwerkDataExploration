<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->
<template>
  <div class="fwde-funnel">
    <ol class="fwde-funnel__bars">
      <li
        v-for="row in rows"
        :key="row.step"
        class="fwde-funnel__step"
      >
        <div class="fwde-funnel__head">
          <span class="fwde-funnel__number">{{ row.step }}</span>
          <span class="fwde-funnel__label">{{ row.label }}</span>
          <span class="fwde-funnel__visits">{{ number(row.nb_visits) }}</span>
          <span class="fwde-funnel__share">{{ percent(row.share_of_start) }}</span>
        </div>
        <div
          class="fwde-funnel__track"
          aria-hidden="true"
        >
          <div
            class="fwde-funnel__bar"
            :style="{ width: `${barPercent(row)}%` }"
          />
        </div>
        <div
          v-if="row.step > 1 && row.nb_dropoffs > 0"
          class="fwde-funnel__dropoffs"
        >{{ translate('FeinwerkDataExploration_FunnelDropoffs', number(row.nb_dropoffs)) }}</div>
      </li>
    </ol>
    <div class="fwde-table">
      <div class="fwde-table__scroller">
        <table class="dataTable fwde-table__table fwde-funnel__table">
          <thead>
            <tr>
              <th class="fwde-table__number">
                {{ translate('FeinwerkDataExploration_FunnelColumnStep') }}
              </th>
              <th class="label">
                {{ translate('FeinwerkDataExploration_FunnelColumnCondition') }}
              </th>
              <th class="fwde-table__number">{{ translate('General_ColumnNbVisits') }}</th>
              <th class="fwde-table__number">
                {{ translate('FeinwerkDataExploration_FunnelColumnShareOfStart') }}
              </th>
              <th class="fwde-table__number">
                {{ translate('FeinwerkDataExploration_FunnelColumnShareOfPrevious') }}
              </th>
              <th class="fwde-table__number">
                {{ translate('FeinwerkDataExploration_FunnelColumnDropoffs') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="row in rows"
              :key="row.step"
            >
              <td class="fwde-table__number">{{ row.step }}</td>
              <td class="fwde-table__label">{{ row.label }}</td>
              <td class="fwde-table__number">{{ number(row.nb_visits) }}</td>
              <td class="fwde-table__number">{{ percent(row.share_of_start) }}</td>
              <td class="fwde-table__number">{{ percent(row.share_of_previous) }}</td>
              <td class="fwde-table__number">{{ number(row.nb_dropoffs) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <p
      v-if="total !== null"
      class="fwde-funnel__total"
    >{{ translate('FeinwerkDataExploration_FunnelTotal', percent(total), String(rows.length)) }}</p>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { NumberFormatter, translate } from 'CoreHome';
import { FunnelRow } from './types';
import { barPercent, totalRate } from './funnelResult';

export default defineComponent({
  props: {
    rows: { type: Array as PropType<FunnelRow[]>, required: true },
  },
  computed: {
    total(): number|null {
      return totalRate(this.rows);
    },
  },
  methods: {
    translate,
    barPercent,
    number(value: number): string {
      return NumberFormatter.formatNumber(value, 0, 0);
    },
    percent(value: number): string {
      return NumberFormatter.formatPercent(value, 1, 0);
    },
  },
});
</script>
