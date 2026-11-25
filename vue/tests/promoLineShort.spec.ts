/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// The tip line together with the kit's javascripts/promo.js (version 6, kit PageFilterReport f7a8448; Peter, 2026-11-25:
// "Kurzer Linktext"): a bare line still wider than its place gets fwpromo-line--short, which shows the connector link's
// short form, but only where the link has one. jsdom has no layout, so the widths are set by hand; the real widths in
// five languages are measured by tests/js/run-promo-check.sh with the kit's markup, which PromoLine.vue renders (see
// promoLine.spec.ts). In its own file: the script stays loaded in this page (its version guard and its listeners).
import * as fs from 'fs';
import * as path from 'path';
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import PromoLine from '../src/PromoLine.vue';

const promo = {
  id: 'connector',
  placement: 'line',
  title: 'Feinwerk Connector',
  text: 'Exclusive Plugins for Matomo by Feinwerk, free, freemium and premium:',
  link: 'Install the Feinwerk Connector from the Matomo Marketplace',
  linkShort: 'Install the connector',
  url: 'index.php?module=FeinwerkDataExploration&action=connectorInstaller',
  external: false,
  note: '',
  dismissDays: 30,
};

/** the widths promo.js reads: the line's content (scrollWidth) against its place (clientWidth); the sentence gets 0 */
let lineContent = 0;
const PLACE = 390;
const width = (content: boolean) => function measured(this: Element): number {
  if (!this.classList.contains('fwpromo-line')) {
    return 0;
  }
  return content ? lineContent : PLACE;
};

const mounted: Array<{ unmount: () => void }> = [];
const line = async (props: typeof promo | Omit<typeof promo, 'linkShort'>) => {
  mounted.push(mount(PromoLine, { props: { promo: props, dismissLabel: 'Hide' }, attachTo: document.body }));
  // the MutationObserver of promo.js waits for an animation frame
  await nextTick();
  await new Promise((resolve) => { setTimeout(resolve, 50); });
  return document.querySelector('.fwpromo-line') as HTMLElement;
};

describe('tip line with promo.js 6 (short connector link on narrow lines)', () => {
  beforeAll(() => {
    jest.spyOn(Element.prototype, 'scrollWidth', 'get').mockImplementation(width(true));
    jest.spyOn(Element.prototype, 'clientWidth', 'get').mockImplementation(width(false));
    const script = fs.readFileSync(path.resolve(__dirname, '../../javascripts/promo.js'), 'utf8');
    // eslint-disable-next-line no-eval
    window.eval(script);
    expect((window as unknown as { fwpromoKit: number }).fwpromoKit).toBe(6);
  });

  afterAll(() => {
    jest.restoreAllMocks();
  });

  afterEach(() => {
    mounted.splice(0).forEach((wrapper) => wrapper.unmount());
    window.localStorage.clear();
  });

  it('shows the short link where the line is wider than its place even without the sentence', async () => {
    lineContent = 438;
    const shown = await line(promo);
    expect(shown.getAttribute('data-fwpromo-ready')).toBe('1');
    expect(Array.from(shown.classList))
      .toEqual(['fwpromo-line', 'fwpromo-line--compact', 'fwpromo-line--bare', 'fwpromo-line--short']);
    expect(shown.querySelector('.fwpromo-line__link')?.getAttribute('title')).toBe(promo.link);
  });

  it('keeps the full link where it fits', async () => {
    lineContent = 300;
    const shown = await line(promo);
    expect(shown.getAttribute('data-fwpromo-ready')).toBe('1');
    expect(shown.classList.contains('fwpromo-line--short')).toBe(false);
  });

  it('never marks a link without a short form', async () => {
    lineContent = 438;
    const { linkShort, ...campaigns } = promo;
    expect(linkShort).not.toBe('');
    const shown = await line({ ...campaigns, id: 'campaigns' });
    expect(shown.getAttribute('data-fwpromo-ready')).toBe('1');
    expect(shown.classList.contains('fwpromo-line--bare')).toBe(true);
    expect(shown.classList.contains('fwpromo-line--short')).toBe(false);
  });
});
