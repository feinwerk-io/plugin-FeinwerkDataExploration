/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import SaveDialog from '../src/SaveDialog.vue';
import {
  cardDefaults, findSaved, savedHashParams, savedKind, withCard,
} from '../src/saved';
import { SavedExploration } from '../src/api';

// Matomo 5 runs the specs with Jest; CoreHome is Matomo's UMD global, so the mock is virtual
jest.mock('CoreHome', () => ({
  translate: (key: string) => key,
}), { virtual: true });

const card: SavedExploration = {
  id: 5,
  name: 'Bounce card',
  shared: false,
  owner: 'anna',
  canEdit: true,
  canDelete: true,
  kind: 'kpi',
  updated: '2026-11-24 08:30:00',
  state: {
    fwdeKind: 'kpi',
    fwdeDims: 'browser',
    fwdeMetrics: 'nb_visits,bounce_rate',
    fwdeView: 'chart',
    fwdeKpiMetric: 'bounce_rate',
    fwdeKpiCompare: 'previous',
    fwdeKpiLine: '7',
    fwdeKpiChange: 'absolute',
  },
  segment: '',
};
const table: SavedExploration = {
  ...card, id: 6, kind: 'table', state: { fwdeDims: 'browser', fwdeMetrics: 'nb_visits' },
};

describe('KPI cards in the saved state (2026-11-24)', () => {
  it('knows a card by its kind', () => {
    expect(savedKind(card)).toBe('kpi');
    expect(savedKind(table)).toBe('table');
  });

  it('saves a card with its settings and without a menu place', () => {
    expect(withCard(
      { fwdeDims: 'browser', fwdeMetrics: 'nb_visits', fwdeMenu: 'Goals_Goals' },
      {
        metric: 'nb_visits', compare: 'average4', line: 30, change: 'percent',
      },
    )).toEqual({
      fwdeDims: 'browser',
      fwdeMetrics: 'nb_visits',
      fwdeKind: 'kpi',
      fwdeKpiMetric: 'nb_visits',
      fwdeKpiCompare: 'average4',
      fwdeKpiLine: '30',
      fwdeKpiChange: 'percent',
    });
  });

  it('opens a card as its exploration on the table page, the card keys left out of the link', () => {
    const params = savedHashParams(card);
    expect(params.subcategory).toBe('FeinwerkDataExploration_FreeFormTable');
    expect([params.fwdeDims, params.fwdeView]).toEqual(['browser', 'chart']);
    expect([params.fwdeKind, params.fwdeKpiMetric, params.fwdeKpiCompare]).toEqual(['', '', '']);
  });

  // the browser check found it: the table page looked up tables only, so a card opened without its settings
  it('is found on the table page, not on the funnel page', () => {
    expect(findSaved([card, table], '5', ['table', 'kpi'])).toBe(card);
    expect(findSaved([card, table], '5', 'funnel')).toBeNull();
    expect(findSaved([card, table], '6', 'table')).toBe(table);
  });

  it('reads a card\'s settings for the save panel, the defaults for anything else', () => {
    expect(cardDefaults(card, ['nb_visits', 'bounce_rate'])).toEqual({
      saveAs: 'kpi',
      card: {
        metric: 'bounce_rate', compare: 'previous', line: 7, change: 'absolute',
      },
    });
    expect(cardDefaults(table, ['nb_visits', 'bounce_rate'])).toEqual({
      saveAs: 'table',
      card: {
        metric: 'nb_visits', compare: 'average4', line: 30, change: 'percent',
      },
    });
    // a card whose metric the exploration no longer has starts with the first one
    expect(cardDefaults(card, ['nb_visits']).card.metric).toBe('nb_visits');
  });
});

const mounted: Array<{ unmount: () => void }> = [];
const panel = () => document.querySelector<HTMLElement>('section.fwde-sidepanel');
const settle = async () => {
  await nextTick();
  await nextTick();
};
const METRICS = [{ id: 'nb_visits', name: 'Visits' }, { id: 'bounce_rate', name: 'Bounce rate' }];

function dialog(props: Record<string, unknown> = {}) {
  const wrapper = mount(SaveDialog, {
    props: {
      suggestedName: 'Browser',
      canShare: true,
      metrics: METRICS,
      menuSections: [{ id: 'Goals_Goals', name: 'Goals' }],
      ...props,
    },
    attachTo: document.body,
  });
  mounted.push(wrapper);
  return wrapper;
}

function chooseCard() {
  const options = Array.from(document.querySelectorAll<HTMLElement>(
    'section.fwde-sidepanel .fwde-savedialog__kind .fwde-segmented__option',
  ));
  options.find((o) => o.textContent?.includes('FeinwerkDataExploration_SaveAsKpiCard'))?.click();
}

describe('saving a KPI card in the side panel (2026-11-24)', () => {
  afterEach(() => {
    mounted.splice(0).forEach((wrapper) => wrapper.unmount());
    while (document.body.firstChild) {
      document.body.removeChild(document.body.firstChild);
    }
  });

  it('shows the card settings with their defaults instead of the menu place', async () => {
    const wrapper = dialog();
    await wrapper.find('.fwde-savedialog__toggle').trigger('click');
    await settle();
    expect(panel()?.querySelector('#fwde-savedialog-kpimetric')).toBeNull();
    expect(panel()?.querySelector('#fwde-savedialog-menu')).not.toBeNull();

    chooseCard();
    await settle();
    expect(panel()?.querySelector('#fwde-savedialog-menu')).toBeNull();
    const metric = panel()?.querySelector<HTMLSelectElement>('#fwde-savedialog-kpimetric');
    expect(Array.from(metric?.options || []).map((o) => o.value)).toEqual(['nb_visits', 'bounce_rate']);
    expect(metric?.value).toBe('nb_visits');
    expect(panel()?.querySelector<HTMLSelectElement>('#fwde-savedialog-kpicompare')?.value).toBe('average4');
  });

  // UI review 2026-11-24 (F6): a new card is named after its metric while the name is still a suggestion
  it('suggests the metric as the name of a new card', async () => {
    const wrapper = dialog();
    await wrapper.find('.fwde-savedialog__toggle').trigger('click');
    await settle();
    const name = () => panel()?.querySelector<HTMLInputElement>('#fwde-savedialog-name');
    chooseCard();
    await settle();
    expect(name()?.value).toBe('Visits');
    const metric = panel()?.querySelector<HTMLSelectElement>('#fwde-savedialog-kpimetric');
    if (metric) {
      metric.value = 'bounce_rate';
      metric.dispatchEvent(new Event('change'));
    }
    await settle();
    expect(name()?.value).toBe('Bounce rate');
    // back to an exploration: its own suggestion again
    Array.from(document.querySelectorAll<HTMLElement>(
      'section.fwde-sidepanel .fwde-savedialog__kind .fwde-segmented__option',
    )).find((o) => o.textContent?.includes('FeinwerkDataExploration_SaveAsExploration'))?.click();
    await settle();
    expect(name()?.value).toBe('Browser');
  });

  it('keeps a name the user typed when the card or its metric changes', async () => {
    const wrapper = dialog();
    await wrapper.find('.fwde-savedialog__toggle').trigger('click');
    await settle();
    const name = panel()?.querySelector<HTMLInputElement>('#fwde-savedialog-name');
    if (name) {
      name.value = 'Daily check';
      name.dispatchEvent(new Event('input'));
    }
    await settle();
    chooseCard();
    await settle();
    expect(panel()?.querySelector<HTMLInputElement>('#fwde-savedialog-name')?.value).toBe('Daily check');
  });

  it('saves the card with its settings', async () => {
    const wrapper = dialog();
    await wrapper.find('.fwde-savedialog__toggle').trigger('click');
    await settle();
    chooseCard();
    await settle();
    const compare = panel()?.querySelector<HTMLSelectElement>('#fwde-savedialog-kpicompare');
    if (compare) {
      compare.value = 'previous';
      compare.dispatchEvent(new Event('change'));
    }
    await settle();
    (panel()?.querySelector('.fwde-savedialog__new') as HTMLElement).click();
    await settle();
    expect(wrapper.emitted('save')).toEqual([[{
      name: 'Visits',
      shared: false,
      asNew: true,
      menu: '',
      card: {
        metric: 'nb_visits', compare: 'previous', line: 30, change: 'percent',
      },
    }]]);
  });

  it('opens a saved card with its settings; saving changes keeps it a card', async () => {
    const wrapper = dialog({ current: card });
    await wrapper.find('.fwde-savedialog__toggle').trigger('click');
    await settle();
    expect(panel()?.querySelector<HTMLSelectElement>('#fwde-savedialog-kpimetric')?.value).toBe('bounce_rate');
    expect(panel()?.querySelector<HTMLSelectElement>('#fwde-savedialog-kpicompare')?.value).toBe('previous');
    (panel()?.querySelector('.fwde-savedialog__changes') as HTMLElement).click();
    await settle();
    const [[event]] = wrapper.emitted('save') as [[Record<string, unknown>]];
    expect(event.asNew).toBe(false);
    expect(event.card).toEqual({
      metric: 'bounce_rate', compare: 'previous', line: 7, change: 'absolute',
    });
  });
});
