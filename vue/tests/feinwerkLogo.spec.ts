/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// The Feinwerk logo of the tip line and the banner is the kit's templates/_feinwerkLogo.twig rendered by Vue. The kit's
// promo check (tests/js/run-promo-check.sh) measures the tip line with that markup from tests/js/fixtures/
// feinwerkLogo.html, so the component must render exactly it.
import fs from 'node:fs';
import path from 'node:path';
import { mount } from '@vue/test-utils';
import FeinwerkLogo from '../src/FeinwerkLogo.vue';

/** the element tree without Vue's empty fragment anchors */
const tree = (html: string): Element => {
  const holder = document.createElement('div');
  holder.innerHTML = html;
  holder.normalize();
  return holder.firstElementChild as Element;
};

describe('Feinwerk logo', () => {
  it('renders the markup of the kit check fixture', () => {
    const fixture = fs.readFileSync(path.resolve(__dirname, '../../tests/js/fixtures/feinwerkLogo.html'), 'utf8').trim();
    const rendered = mount(FeinwerkLogo).element as Element;
    const own = rendered.cloneNode(true) as Element;
    own.normalize();

    expect(own.isEqualNode(tree(fixture))).toBe(true);
    expect(own.querySelectorAll('circle')).toHaveLength(13);
  });
});
