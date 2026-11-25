/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// The premium banner together with the kit's javascripts/promocard.js (version 5, kit PageFilterReport d4ce520): the
// Vue group is a pausable place, so one x empties it for 30 days (Peter, 2026-11-24: "Ein × blendet alles 30 Tage
// aus"); demo mode keeps the plain rotation. In its own file: the script stays loaded in this page (its version guard
// and its listeners), so the other banner specs do not see it.
import * as fs from 'fs';
import * as path from 'path';
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import PremiumBanner from '../src/PremiumBanner.vue';
import { PremiumPlugin, PremiumProps } from '../src/premium';

const plugin = (slug: string): PremiumPlugin => ({
  id: `premium-${slug}`,
  slug,
  name: slug,
  description: `${slug} text`,
  price: '',
  image: '',
  url: `https://plugins.feinwerk.io/en/plugins/${slug}`,
  external: '1',
});
const plugins = [plugin('FeinwerkSentinelPro'), plugin('FeinwerkSearchReport')];

const mounted: Array<{ unmount: () => void }> = [];
const banner = (premium: PremiumProps) => {
  const wrapper = mount(PremiumBanner, {
    props: {
      premium, label: 'Recommended by Feinwerk', moreLabel: 'Learn more', dismissLabel: 'Hide',
    },
    attachTo: document.body,
  });
  mounted.push(wrapper);
  return wrapper;
};
const live = () => banner({
  plugins, days: 90, pauseDays: 30, demo: false, note: '',
});
const demo = () => banner({
  plugins, days: 0, pauseDays: 0, demo: true, note: '',
});

/** the MutationObserver of promocard.js runs after the DOM change */
const settle = async () => {
  await nextTick();
  await new Promise((resolve) => { setTimeout(resolve, 0); });
};
const shown = () => Array.from(document.querySelectorAll('.fwde-premium [data-fwpromo-ready]'))
  .map((item) => item.getAttribute('data-fwpromo-id'));

describe('premium banner with promocard.js 5 (pausable place)', () => {
  beforeAll(() => {
    const script = fs.readFileSync(path.resolve(__dirname, '../../javascripts/promocard.js'), 'utf8');
    // eslint-disable-next-line no-eval
    window.eval(script);
    expect((window as unknown as { fwpromoCardKit: number }).fwpromoCardKit).toBe(5);
  });

  afterEach(() => {
    mounted.splice(0).forEach((wrapper) => wrapper.unmount());
    window.localStorage.clear();
    window.sessionStorage.clear();
  });

  it('shows the first plugin of a live banner as a rotation group once the pause is checked', async () => {
    live();
    await settle();
    const group = document.querySelector('.fwde-premium') as HTMLElement;
    expect(group.getAttribute('data-fwpromo-rotation')).toBe('banner');
    expect(group.hasAttribute('data-fwpromo-pausable')).toBe(false);
    expect(shown()).toEqual(['premium-FeinwerkSentinelPro']);
  });

  it('one x empties the place: no next plugin, the pause and the closed plugin are kept', async () => {
    live();
    await settle();
    (document.querySelector('.fwde-premium [data-fwpromo-ready] [data-fwpromo-close]') as HTMLElement).click();
    await settle();

    expect(document.querySelector('.fwde-premium')).toBeNull();
    expect(Object.keys(window.localStorage).sort())
      .toEqual(['fwpromo:place:fwde-banner:paused', 'fwpromo:premium-FeinwerkSentinelPro:dismissed']);
    expect(() => mounted.splice(0).forEach((wrapper) => wrapper.unmount())).not.toThrow();

    // the banner built again during the pause (another exploration): the place stays empty. The end of the pause
    // needs a new page (the script also keeps the pause in the page itself): tests/js/run-promocard-check.sh checks it
    live();
    await settle();
    expect(document.querySelector('.fwde-premium')).toBeNull();
  });

  it('demo mode keeps the plain rotation: the x shows the next plugin and pauses nothing', async () => {
    demo();
    await settle();
    expect(shown()).toEqual(['premium-FeinwerkSentinelPro']);
    (document.querySelector('.fwde-premium [data-fwpromo-ready] [data-fwpromo-close]') as HTMLElement).click();
    await settle();

    expect(shown()).toEqual(['premium-FeinwerkSearchReport']);
    expect(Object.keys(window.localStorage)).toEqual([]);
    expect(Object.keys(window.sessionStorage)).toEqual(['fwpromo:premium-FeinwerkSentinelPro:dismissed']);
  });
});
