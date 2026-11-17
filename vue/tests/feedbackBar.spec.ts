/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import FeedbackBar from '../src/FeedbackBar.vue';

// Matomo 5 runs the specs with Jest; CoreHome is Matomo's UMD global, so the mock is virtual
jest.mock('CoreHome', () => ({
  AjaxHelper: { post: () => Promise.resolve({}) },
  translate: (key: string) => key,
}), { virtual: true });

const question = 'Is this exploration helpful? A long question that may end with an ellipsis';

function bar(enabled: boolean) {
  return mount(FeedbackBar, {
    props: {
      idSite: 1,
      topic: 'free_form_table',
      question,
      feedback: { enabled, supportUrl: 'https://example.org/support', version: '6.0.0' },
    },
  });
}

describe('feedback row (footer variant A)', () => {
  it('question carries its full text as title', () => {
    expect(bar(true).find('.fwde-feedback__question').attributes('title')).toBe(question);
  });

  it('support link has the help icon and stays when feedback is off', () => {
    const wrapper = bar(false);
    expect(wrapper.find('.fwde-feedback__support svg').exists()).toBe(true);
    expect(wrapper.findAll('.fwde-feedback__thumb')).toHaveLength(0);
  });
});
