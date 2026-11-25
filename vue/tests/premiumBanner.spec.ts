/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import PremiumBanner from '../src/PremiumBanner.vue';
import { PremiumPlugin, PremiumProps } from '../src/premium';

const inside: PremiumPlugin = {
  id: 'premium-FeinwerkSentinelPro',
  slug: 'FeinwerkSentinelPro',
  name: 'Sentinel Pro',
  description: 'Watches your tracking every day.',
  price: 'from 59,00 € / month',
  image: 'index.php?module=FeinwerkMarketplaceConnector&action=image&src=a.png',
  url: 'index.php?module=FeinwerkMarketplaceConnector&action=index&fwmc_open=FeinwerkSentinelPro',
  external: '',
};
const store: PremiumPlugin = {
  id: 'premium-FeinwerkSearchReport',
  slug: 'FeinwerkSearchReport',
  name: 'Search Report',
  description: 'Shows what your visitors search for.',
  price: '',
  image: '',
  url: 'https://plugins.feinwerk.io/en/plugins/FeinwerkSearchReport?mtm_campaign=plugin-promo&mtm_source=FeinwerkDataExploration&mtm_content=rec-FeinwerkSearchReport',
  external: '1',
};
const props = (premium: PremiumProps|null) => ({
  premium, label: 'Recommended by Feinwerk', moreLabel: 'Learn more', dismissLabel: 'Hide this recommendation for 30 days',
});
const banner = (overrides: Partial<PremiumProps> = {}) => mount(PremiumBanner, {
  props: props({
    plugins: [inside, store],
    days: 90,
    pauseDays: 30,
    demo: false,
    note: 'Your Matomo super user can install it.',
    ...overrides,
  }),
  attachTo: document.body,
});

describe('premium banner (promotion kit, a pausable place for promocard.js)', () => {
  afterEach(() => {
    while (document.body.firstChild) {
      document.body.removeChild(document.body.firstChild);
    }
  });

  it('renders nothing without plugins', () => {
    expect(mount(PremiumBanner, { props: props(null) }).find('.fwde-premium').exists()).toBe(false);
    expect(mount(PremiumBanner, {
      props: props({
        plugins: [], days: 90, pauseDays: 30, demo: false, note: '',
      }),
    }).find('.fwde-premium').exists()).toBe(false);
  });

  // Peter, 2026-11-24: "Ein × blendet alles 30 Tage aus" (kit PageFilterReport d4ce520, "Pausable places"): any x
  // empties the place for pauseDays, the closed plugin stays away for its days; demo mode keeps the plain rotation
  it('is a pausable place of the kind banner, never a rotation group older kit copies would take', () => {
    const group = banner().find('div.fwde-premium');
    expect([
      group.attributes('data-fwpromo-pausable'),
      group.attributes('data-fwpromo-pause'),
      group.attributes('data-fwpromo-pause-days'),
    ]).toEqual(['banner', 'fwde-banner', '30']);
    // promocard.js 5 turns it into a rotation group once the pause is checked; up to version 4 never see it
    expect(group.attributes('data-fwpromo-rotation')).toBeUndefined();
    // the whole exploration page is one Matomo widget: promocard.js would hide it with this attribute
    expect(group.attributes('data-fwpromo-widget')).toBeUndefined();
  });

  it('keeps the plain rotation in demo mode: no pause, days 0, the demo flag', () => {
    const group = banner({ days: 0, pauseDays: 0, demo: true }).find('div.fwde-premium');
    expect(group.attributes('data-fwpromo-rotation')).toBe('banner');
    expect(Object.keys(group.attributes()).filter((name) => name.startsWith('data-fwpromo-pause'))).toEqual([]);
    expect(group.attributes('data-fwpromo-widget')).toBeUndefined();
    expect(group.findAll('section.fwde-premium__item')
      .map((item) => [item.attributes('data-fwpromo-days'), item.attributes('data-fwpromo-demo')]))
      .toEqual([['0', '1'], ['0', '1']]);
  });

  it('has one item per plugin with its dismissal id and its own days, the demo flag only in demo', () => {
    const items = banner().findAll('section.fwde-premium__item');
    expect(items.map((item) => item.attributes('data-fwpromo-id')))
      .toEqual(['premium-FeinwerkSentinelPro', 'premium-FeinwerkSearchReport']);
    expect(items.map((item) => item.attributes('data-fwpromo-days'))).toEqual(['90', '90']);
    expect(items[0].attributes('data-fwpromo-demo')).toBeUndefined();
    expect(items[0].attributes('aria-label')).toBe('Recommended by Feinwerk');
  });

  it('opens a store page in a new tab with the admin note, a page inside Matomo in the same tab', () => {
    const [own, shop] = banner().findAll('section.fwde-premium__item');
    const ownLink = own.find('a.fwde-premium__cta');
    expect([ownLink.attributes('href'), ownLink.attributes('target'), ownLink.attributes('rel')])
      .toEqual([inside.url, undefined, undefined]);
    expect(own.find('.fwde-premium__note').exists()).toBe(false);

    const shopLink = shop.find('a.fwde-premium__cta');
    expect([shopLink.attributes('href'), shopLink.attributes('target'), shopLink.attributes('rel')])
      .toEqual([store.url, '_blank', 'noopener']);
    expect(shop.find('.fwde-premium__note').text()).toBe('Your Matomo super user can install it.');
    expect(shopLink.text()).toContain('Learn more');

    const superUser = banner({ note: '' }).findAll('section.fwde-premium__item')[1];
    expect(superUser.find('.fwde-premium__note').exists()).toBe(false);
  });

  it('shows the picture and the price only when the plugin has them', () => {
    const [own, shop] = banner().findAll('section.fwde-premium__item');
    expect(own.find('.fwde-premium__art img').attributes('src')).toBe(inside.image);
    expect(own.find('.fwde-premium__art img').attributes('alt')).toBe('');
    expect(own.find('.fwde-premium__price').text()).toBe(inside.price);
    expect(shop.find('.fwde-premium__art').exists()).toBe(false);
    expect(shop.find('.fwde-premium__price').exists()).toBe(false);
    expect(own.find('.fwde-premium__name').text()).toBe('Sentinel Pro');
    expect(own.find('.fwde-premium__text').attributes('title')).toBe(inside.description);
  });

  it('closes each item with the Feinwerk logo and an x the script handles', () => {
    banner().findAll('section.fwde-premium__item').forEach((item) => {
      expect(item.find('.fwde-premium__actions .fwlogo').attributes('aria-hidden')).toBe('true');
      const close = item.find('button.fwde-premium__close');
      expect(close.attributes('data-fwpromo-close')).toBeDefined();
      expect([close.attributes('title'), close.attributes('aria-label'), close.attributes('type')])
        .toEqual(['Hide this recommendation for 30 days', 'Hide this recommendation for 30 days', 'button']);
    });
  });

  it('leaves the group to promocard.js: no rebuild after the script removed items, no error when it goes', async () => {
    const wrapper = banner();
    const group = wrapper.find('div.fwde-premium').element;
    // what promocard.js does: hidden items go, then the empty group itself
    group.querySelectorAll('section').forEach((item) => item.remove());
    await wrapper.setProps(props({
      plugins: [store], days: 90, pauseDays: 30, demo: false, note: '',
    }));
    expect(group.querySelectorAll('section')).toHaveLength(0);
    group.remove();
    await wrapper.setProps(props({
      plugins: [inside], days: 90, pauseDays: 30, demo: false, note: '',
    }));
    expect(() => wrapper.unmount()).not.toThrow();
  });
});
