<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fwde-list">
    <SearchInput
      v-model="query"
      :placeholder="searchLabel"
      :show-clear="true"
    />
    <div class="fwde-list__groups">
      <div
        v-for="group in groups"
        :key="group.name"
        class="fwde-list__group"
      >
        <div class="fwde-list__group-name">{{ group.name }}</div>
        <ul>
          <li
            v-for="item in group.items"
            :key="item.key"
          >
            <label
              :class="{
                'fwde-list__item': true,
                'fwde-list__item--selected': selected.includes(item.key),
                'fwde-list__item--disabled': isDisabled(item),
              }"
              :title="itemTitle(item)"
            >
              <input
                :type="multiple ? 'checkbox' : 'radio'"
                class="fwde-list__input"
                :name="inputName"
                :checked="selected.includes(item.key)"
                :disabled="isDisabled(item)"
                @change="$emit('toggle', item.key)"
              />
              <span class="fwde-list__label">{{ item.label }}</span>
              <span
                v-if="item.reason"
                class="fwde-list__reason"
              >{{ translate(item.reason) }}</span>
            </label>
          </li>
        </ul>
      </div>
      <p
        v-if="!groups.length"
        class="fwde-list__empty"
      >{{ translate('FeinwerkDataExploration_NoMatches') }}</p>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { SearchInput, translate } from 'CoreHome';

export interface ListItem {
  key: string;
  label: string;
  group: string;
  /** translation key of the reason why the item cannot be chosen, or null */
  reason: string|null;
  /** what the item means, shown as its tooltip */
  description?: string;
}

let instances = 0;

export default defineComponent({
  components: { SearchInput },
  props: {
    items: { type: Array as PropType<ListItem[]>, required: true },
    selected: { type: Array as PropType<string[]>, required: true },
    multiple: { type: Boolean, default: false },
    searchLabel: { type: String, required: true },
  },
  emits: ['toggle'],
  data() {
    instances += 1;
    return { query: '', inputName: `fwde-list-${instances}` };
  },
  computed: {
    groups(): { name: string, items: ListItem[] }[] {
      const needle = this.query.trim().toLowerCase();
      const groups: { name: string, items: ListItem[] }[] = [];
      this.items.forEach((item) => {
        if (needle && !`${item.label} ${item.group}`.toLowerCase().includes(needle)) {
          return;
        }
        let group = groups.find((g) => g.name === item.group);
        if (!group) {
          group = { name: item.group, items: [] };
          groups.push(group);
        }
        group.items.push(item);
      });
      return groups;
    },
  },
  methods: {
    itemTitle(item: ListItem): string {
      if (item.reason) {
        return `${item.label}: ${translate(item.reason)}`;
      }
      return item.description ? `${item.label}: ${item.description}` : item.label;
    },
    translate,
    isDisabled(item: ListItem): boolean {
      return item.reason !== null && !this.selected.includes(item.key);
    },
  },
});
</script>
