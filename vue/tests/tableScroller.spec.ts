/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import TableScroller from '../src/TableScroller.vue';
import { scrollEdges } from '../src/scrollEdges';

describe('which sides of a wide table hide columns (UI review 2026-11-23, F1)', () => {
  it('a table that fits hides nothing', () => {
    expect(scrollEdges(0, 600, 600)).toEqual({ left: false, right: false });
    expect(scrollEdges(0, 600, 601)).toEqual({ left: false, right: false });
  });

  it('at the start only the right side hides columns, at the end only the left', () => {
    expect(scrollEdges(0, 300, 900)).toEqual({ left: false, right: true });
    expect(scrollEdges(600, 300, 900)).toEqual({ left: true, right: false });
    expect(scrollEdges(599.5, 300, 900)).toEqual({ left: true, right: false });
  });

  it('in between both sides do', () => {
    expect(scrollEdges(200, 300, 900)).toEqual({ left: true, right: true });
  });
});

describe('table scroller', () => {
  const sized = (el: HTMLElement, clientWidth: number, scrollWidth: number) => {
    Object.defineProperty(el, 'clientWidth', { value: clientWidth, configurable: true });
    Object.defineProperty(el, 'scrollWidth', { value: scrollWidth, configurable: true });
  };

  it('shows a shadow on the side where columns are hidden and follows the scrolling', async () => {
    const wrapper = mount(TableScroller, {
      slots: { default: '<table><tbody><tr><td>wide</td></tr></tbody></table>' },
      attachTo: document.body,
    });
    const scroller = wrapper.find('.fwde-table__scroller').element as HTMLElement;
    expect(wrapper.find('.fwde-table__scroller table').exists()).toBe(true);
    sized(scroller, 300, 900);
    scroller.scrollLeft = 0;
    scroller.dispatchEvent(new Event('scroll'));
    await nextTick();
    const frame = () => wrapper.find('.fwde-table__frame').classes();
    expect(frame()).toContain('fwde-table__frame--more-right');
    expect(frame()).not.toContain('fwde-table__frame--more-left');
    scroller.scrollLeft = 600;
    scroller.dispatchEvent(new Event('scroll'));
    await nextTick();
    expect(frame()).toContain('fwde-table__frame--more-left');
    expect(frame()).not.toContain('fwde-table__frame--more-right');
    sized(scroller, 900, 900);
    scroller.scrollLeft = 0;
    window.dispatchEvent(new Event('resize'));
    await nextTick();
    expect(frame()).not.toContain('fwde-table__frame--more-left');
    expect(frame()).not.toContain('fwde-table__frame--more-right');
    wrapper.unmount();
  });

  it('tells the table whether it fits and how wide the box is, once per change', async () => {
    const wrapper = mount(TableScroller, {
      slots: { default: '<table><tbody><tr><td>wide</td></tr></tbody></table>' },
      attachTo: document.body,
    });
    const scroller = wrapper.find('.fwde-table__scroller').element as HTMLElement;
    sized(scroller, 300, 900);
    scroller.dispatchEvent(new Event('scroll'));
    await nextTick();
    sized(scroller, 300, 300);
    window.dispatchEvent(new Event('resize'));
    window.dispatchEvent(new Event('resize'));
    await nextTick();
    sized(scroller, 500, 500);
    window.dispatchEvent(new Event('resize'));
    await nextTick();
    expect(wrapper.emitted('layout')?.slice(-3)).toEqual([
      [{ overflow: true, width: 300 }],
      [{ overflow: false, width: 300 }],
      [{ overflow: false, width: 500 }],
    ]);
    wrapper.unmount();
  });
});
