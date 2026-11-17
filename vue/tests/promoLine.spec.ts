/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import PromoLine from '../src/PromoLine.vue';

const promo = {
  id: 'campaigns',
  placement: 'line',
  title: 'Title',
  text: 'Consistent campaign names make your reports clearer.',
  link: 'Learn more',
  url: 'https://campaigns.feinwerk.io/en/matomo?mtm_campaign=plugin-promo',
  dismissDays: 30,
};

describe('tip line (Feinwerk promotion kit)', () => {
  it('renders nothing without a message', () => {
    expect(mount(PromoLine, { props: { promo: null, dismissLabel: 'Hide' } }).find('.fwpromo-line').exists()).toBe(false);
  });

  it('renders the kit markup with the sentence as title', () => {
    const line = mount(PromoLine, { props: { promo, dismissLabel: 'Hide this tip for 30 days' } });
    const root = line.find('.fwpromo-line');
    expect(root.attributes('data-fwpromo-id')).toBe('campaigns');
    expect(root.attributes('data-fwpromo-placement')).toBe('line');
    expect(root.attributes('data-fwpromo-days')).toBe('30');
    expect(line.find('.fwpromo-line__text').attributes('title')).toBe(promo.text);
    const link = line.find('a.fwpromo-line__link');
    expect([link.attributes('href'), link.attributes('target'), link.attributes('rel')])
      .toEqual([promo.url, '_blank', 'noopener']);
    expect(line.find('button.fwpromo-line__dismiss.fwpromo-dismiss').attributes('title')).toBe('Hide this tip for 30 days');
    expect(root.attributes('data-fwpromo-demo')).toBeUndefined();
  });

  it('starts with the Feinwerk Plugins logo, hidden from screen readers, nothing loaded (kit 2c5b91f)', () => {
    const line = mount(PromoLine, { props: { promo, dismissLabel: 'Hide' } });
    const first = line.find('.fwpromo-line').element.firstElementChild as Element;
    expect(first.classList.contains('fwlogo')).toBe(true);
    expect(first.getAttribute('aria-hidden')).toBe('true');
    expect(line.find('.fwlogo svg.fwlogo__mark circle.fwlogo__core').exists()).toBe(true);
    expect(line.findAll('.fwlogo g.fwlogo__dots circle')).toHaveLength(12);
    expect(line.find('.fwlogo__word').text()).toBe('feinwerk plugins');
    expect(line.find('.fwlogo__sub').text()).toBe('plugins');
    expect(line.find('.fwlogo img, .fwlogo image, .fwlogo use').exists()).toBe(false);
  });

  it('opens a link inside Matomo in the same tab and shows the admin note on the link', () => {
    const inside = mount(PromoLine, {
      props: {
        promo: {
          ...promo, id: 'connector', url: 'index.php?module=Marketplace&action=overview', external: false, note: '',
        },
        dismissLabel: 'Hide',
      },
    });
    const own = inside.find('a.fwpromo-line__link');
    expect([own.attributes('target'), own.attributes('rel'), own.attributes('title')])
      .toEqual([undefined, undefined, undefined]);

    const admin = mount(PromoLine, {
      props: {
        promo: {
          ...promo, id: 'connector', external: true, note: 'Your Matomo super user can install it.',
        },
        dismissLabel: 'Hide',
      },
    });
    const link = admin.find('a.fwpromo-line__link');
    expect([link.attributes('target'), link.attributes('title')])
      .toEqual(['_blank', 'Your Matomo super user can install it.']);
  });

  it('marks a demo notice, so the kit script hides it for the browser session only', () => {
    const line = mount(PromoLine, {
      props: { promo: { ...promo, dismissDays: 0, demo: true }, dismissLabel: 'Hide' },
    });
    const root = line.find('.fwpromo-line');
    expect(root.attributes('data-fwpromo-demo')).toBe('1');
    expect(root.attributes('data-fwpromo-days')).toBe('0');
  });
});
