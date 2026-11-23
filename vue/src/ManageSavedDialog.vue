<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later

  Managing the website's shared explorations (design 2026-11-20): admins of the website see every
  shared exploration with its owner, kind and last change and may delete one for everyone after
  confirming. Private explorations never appear here (the API never lists another user's private
  ones). The server checks every delete (API::deleteSavedExploration). The delete call comes in as a
  prop (SavedPicker passes it), so this component imports nothing from api.ts.
-->
<template>
  <div class="fwde-manage">
    <p
      v-if="!shared.length"
      class="fwde-manage__empty"
    >{{ translate('FeinwerkDataExploration_ManageSharedEmpty') }}</p>
    <table
      v-else
      class="fwde-manage__table"
    >
      <thead>
        <tr>
          <th>{{ translate('General_Name') }}</th>
          <th>{{ translate('FeinwerkDataExploration_ColumnOwner') }}</th>
          <th>{{ translate('FeinwerkDataExploration_ColumnChanged') }}</th>
          <th>
            <span class="fwde-visually-hidden">
              {{ translate('FeinwerkDataExploration_RowActions') }}
            </span>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="item in shared"
          :key="item.id"
          :data-id="item.id"
        >
          <td>
            <span class="fwde-manage__name">{{ item.name }}</span>
            <span class="fwde-manage__kind">{{ translate(kindKey(item)) }}</span>
          </td>
          <td>{{ item.owner }}</td>
          <td>{{ item.updated.slice(0, 10) }}</td>
          <td class="fwde-manage__actions">
            <template v-if="confirming === item.id">
              <span class="fwde-manage__confirm">
                {{ translate('FeinwerkDataExploration_ManageSharedConfirm', item.name) }}
              </span>
              <button
                type="button"
                class="fwde-button fwde-manage__confirm-yes"
                :disabled="busy"
                @click="confirmDelete(item)"
              >{{ translate('General_Delete') }}</button>
              <button
                type="button"
                class="fwde-button fwde-manage__confirm-no"
                :disabled="busy"
                @click="ask(null, item.id)"
              >{{ translate('General_Cancel') }}</button>
            </template>
            <button
              v-else-if="item.canDelete"
              type="button"
              class="fwde-button fwde-manage__delete"
              @click="ask(item.id, item.id)"
            >{{ translate('General_Delete') }}</button>
          </td>
        </tr>
      </tbody>
    </table>
    <p
      v-if="error"
      class="fwde-manage__error"
      role="alert"
    >{{ error }}</p>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import { SavedExploration } from './api';

export default defineComponent({
  props: {
    saved: { type: Array as PropType<SavedExploration[]>, required: true },
    /** deletes one exploration; rejects with an Error whose message the dialog shows */
    remove: { type: Function as PropType<(id: number) => Promise<unknown>>, required: true },
  },
  emits: ['deleted'],
  data() {
    return {
      confirming: null as number|null,
      busy: false,
      error: '',
    };
  },
  computed: {
    shared(): SavedExploration[] {
      return this.saved.filter((item) => item.shared);
    },
  },
  methods: {
    translate,
    kindKey(item: SavedExploration): string {
      return item.kind === 'funnel'
        ? 'FeinwerkDataExploration_Funnel'
        : 'FeinwerkDataExploration_FreeFormTable';
    },
    /**
     * Shows or hides the confirmation of one row; the focus goes along (to "Delete" in the
     * confirmation, or back to the row's "Delete"), so it never falls out of the panel.
     */
    ask(confirming: number|null, id: number) {
      this.confirming = confirming;
      this.$nextTick(() => {
        const row = (this.$el as HTMLElement).querySelector(`tr[data-id="${id}"]`);
        const target = row ? row.querySelector<HTMLElement>(confirming === null
          ? '.fwde-manage__delete'
          : '.fwde-manage__confirm-yes') : null;
        if (target) {
          target.focus();
        }
      });
    },
    confirmDelete(item: SavedExploration) {
      this.busy = true;
      this.error = '';
      this.remove(item.id).then(() => {
        this.confirming = null;
        this.$emit('deleted', item.id);
      }).catch((e: unknown) => {
        this.error = e instanceof Error ? e.message : String(e);
      }).finally(() => {
        this.busy = false;
      });
    },
  },
});
</script>
