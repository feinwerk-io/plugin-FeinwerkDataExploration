<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later

  Managing saved explorations: the user's own ones, private and shared (Peter, 2026-11-25: "Das
  delete über den save button ist so überhaupt nicht praktisch"), and, for admins of the website,
  every one shared by others with its owner (design 2026-11-20). Each with its kind, last change,
  menu place and Delete after confirming ("for everyone" when shared). Others' private explorations
  never appear here (the API never lists them), nor shared ones the user may not change. The server
  checks every delete (API::deleteSavedExploration). The delete call comes in as a prop (SavedPicker
  passes it), so this component imports nothing from api.ts.
-->
<template>
  <div class="fwde-manage">
    <p
      v-if="!groups.length"
      class="fwde-manage__empty"
    >{{ translate('FeinwerkDataExploration_ManageEmpty') }}</p>
    <section
      v-for="group in groups"
      :key="group.key"
      :class="['fwde-manage__group', `fwde-manage__${group.key}`]"
    >
      <h3 class="fwde-manage__heading">{{ translate(group.heading) }}</h3>
      <table class="fwde-manage__table">
        <thead>
          <tr>
            <th>{{ translate('General_Name') }}</th>
            <th v-if="group.key === 'others'">
              {{ translate('FeinwerkDataExploration_ColumnOwner') }}
            </th>
            <th>{{ translate('FeinwerkDataExploration_ColumnChanged') }}</th>
            <th v-if="menuSections.length">
              {{ translate('FeinwerkDataExploration_ShowInMenu') }}
            </th>
            <th>
              <span class="fwde-visually-hidden">
                {{ translate('FeinwerkDataExploration_RowActions') }}
              </span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="item in group.items"
            :key="item.id"
            :data-id="item.id"
          >
            <td>
              <span class="fwde-manage__name">{{ item.name }}</span>
              <span class="fwde-manage__kind">{{ kindText(item, group.key) }}</span>
            </td>
            <td v-if="group.key === 'others'">{{ item.owner }}</td>
            <td>{{ item.updated.slice(0, 10) }}</td>
            <td v-if="menuSections.length">
              <select
                v-if="item.kind === 'table' && item.canPlace && place"
                class="browser-default fwde-select fwde-manage__menu"
                :aria-label="translate('FeinwerkDataExploration_ShowInMenu')"
                :value="item.menu || ''"
                :disabled="busy"
                @change="changePlace(item, $event)"
              >
                <option value="">{{ translate('FeinwerkDataExploration_MenuNone') }}</option>
                <option
                  v-for="section in menuSections"
                  :key="section.id"
                  :value="section.id"
                >{{ section.name }}</option>
              </select>
              <span v-else-if="item.kind === 'table'">{{ sectionName(item.menu) }}</span>
            </td>
            <td class="fwde-manage__actions">
              <template v-if="confirming === item.id">
                <span class="fwde-manage__confirm">
                  {{ translate(item.shared
                    ? 'FeinwerkDataExploration_ManageSharedConfirm'
                    : 'FeinwerkDataExploration_SavedDeleteConfirm', item.name) }}
                  <span
                    v-if="item.kind !== 'funnel'"
                    class="fwde-manage__deletehint"
                  >{{ translate('FeinwerkDataExploration_DeleteAlsoRemoves') }}</span>
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
    </section>
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
import { MenuSection, SavedExploration } from './api';

interface ManageGroup {
  key: 'mine'|'others';
  heading: string;
  items: SavedExploration[];
}

export default defineComponent({
  props: {
    saved: { type: Array as PropType<SavedExploration[]>, required: true },
    /** deletes one exploration; rejects with an Error whose message the dialog shows */
    remove: { type: Function as PropType<(id: number) => Promise<unknown>>, required: true },
    /** changes a shared exploration's menu place (Peter, 2026-11-24: admins may) */
    place: {
      type: Function as PropType<(id: number, menu: string) => Promise<unknown>>,
      default: null,
    },
    menuSections: { type: Array as PropType<MenuSection[]>, default: () => [] },
    /** the user's login: their own explorations come first, under "My explorations" */
    login: { type: String, default: '' },
  },
  emits: ['deleted', 'placed'],
  data() {
    return {
      confirming: null as number|null,
      busy: false,
      error: '',
    };
  },
  computed: {
    /** the sections with something in them: mine, then the shared ones of others I may change */
    groups(): ManageGroup[] {
      const mine = this.saved.filter((item) => item.owner === this.login);
      const others = this.saved.filter((item) => item.owner !== this.login && item.shared
        && (item.canDelete || item.canPlace));
      const groups: ManageGroup[] = [
        { key: 'mine', heading: 'FeinwerkDataExploration_MyExplorations', items: mine },
        { key: 'others', heading: 'FeinwerkDataExploration_SharedByOthers', items: others },
      ];
      return groups.filter((group) => group.items.length > 0);
    },
  },
  methods: {
    translate,
    /** the kind; among my own ones also whether it is shared (all of the others' ones are) */
    kindText(item: SavedExploration, group: string): string {
      const kind = translate(this.kindKey(item));
      return group === 'mine' && item.shared
        ? translate('FeinwerkDataExploration_SavedShared', kind)
        : kind;
    },
    kindKey(item: SavedExploration): string {
      if (item.kind === 'funnel') {
        return 'FeinwerkDataExploration_Funnel';
      }
      return item.kind === 'kpi'
        ? 'FeinwerkDataExploration_SaveAsKpiCard'
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
    sectionName(menu: string|null|undefined): string {
      const section = this.menuSections.find((s) => s.id === menu);
      return section ? section.name : translate('FeinwerkDataExploration_MenuNone');
    },
    changePlace(item: SavedExploration, event: Event) {
      const menu = (event.target as HTMLSelectElement).value;
      this.busy = true;
      this.error = '';
      this.place(item.id, menu).then(() => {
        this.$emit('placed', item.id);
      }).catch((e: unknown) => {
        this.error = e instanceof Error ? e.message : String(e);
      }).finally(() => {
        this.busy = false;
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
