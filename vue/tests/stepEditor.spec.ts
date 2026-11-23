/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import StepEditor from '../src/StepEditor.vue';
import { FunnelStepState } from '../src/types';

vi.mock('CoreHome', () => ({
  translate: (key: string, ...params: string[]) => [key, ...params].join(' '),
}));

const page = (value: string): FunnelStepState => ({
  type: 'pageUrl', operator: 'contains', value, idGoal: null,
});
const goals = [{ id: 1, name: 'Order' }, { id: 2, name: 'Newsletter' }];

function editor(steps: FunnelStepState[], withGoals = true) {
  return mount(StepEditor, { props: { steps, goals: withGoals ? goals : [], maxSteps: 3 } });
}

describe('step editor', () => {
  it('add button is disabled with the limit hint at maxSteps', () => {
    const full = editor([page('a'), page('b'), page('c')]);
    expect(full.find('.fwde-steps__add-button').attributes('disabled')).toBeDefined();
    expect(full.find('.fwde-steps__limit').text()).toBe('FeinwerkDataExploration_FunnelMaxSteps 3');

    const two = editor([page('a'), page('b')]);
    expect(two.find('.fwde-steps__add-button').attributes('disabled')).toBeUndefined();
    expect(two.find('.fwde-steps__limit').exists()).toBe(false);
  });

  it('adds an empty step and removes down to two only', async () => {
    const two = editor([page('a'), page('b')]);
    expect(two.findAll('.fwde-step__remove').every((b) => b.attributes('disabled') !== undefined)).toBe(true);
    await two.find('.fwde-steps__add-button').trigger('click');
    expect((two.emitted('update') as unknown[][])[0][0]).toHaveLength(3);

    const three = editor([page('a'), page('b'), page('c')]);
    await three.findAll('.fwde-step__remove')[1].trigger('click');
    expect((three.emitted('update') as unknown[][])[0][0]).toEqual([page('a'), page('c')]);
  });

  it('goal type only with goals; choosing a goal emits its id', async () => {
    const options = (wrapper: ReturnType<typeof editor>) => wrapper.find('.fwde-step__type')
      .findAll('option').map((o) => o.attributes('value'));
    expect(options(editor([page('a'), page('b')], false))).not.toContain('goal');
    expect(options(editor([page('a'), page('b')], false))).toContain('anyGoal');

    const wrapper = editor([page('a'), {
      type: 'goal', operator: '', value: '', idGoal: null,
    }]);
    await wrapper.find('.fwde-step__goal').setValue('2');
    const steps = (wrapper.emitted('update') as FunnelStepState[][][])[0][0];
    expect(steps[1]).toEqual({
      type: 'goal', operator: '', value: '', idGoal: 2,
    });
  });

  it('typing emits once after a pause, not per key', async () => {
    vi.useFakeTimers();
    try {
      const wrapper = editor([page('a'), page('')]);
      const input = wrapper.findAll('.fwde-step__value')[1];
      await input.setValue('/c');
      await input.setValue('/ca');
      await input.setValue('/cart');
      expect(wrapper.emitted('update')).toBeUndefined();
      expect((input.element as HTMLInputElement).value).toBe('/cart');
      vi.advanceTimersByTime(600);
      const updates = wrapper.emitted('update') as FunnelStepState[][][];
      expect(updates).toHaveLength(1);
      expect(updates[0][0][1].value).toBe('/cart');
    } finally {
      vi.useRealTimers();
    }
  });

  it('a new type keeps the text and an allowed operator', async () => {
    const wrapper = editor([{
      type: 'pageUrl', operator: 'startsWith', value: '/a', idGoal: null,
    }, page('b')]);
    await wrapper.find('.fwde-step__type').setValue('eventName');
    const steps = (wrapper.emitted('update') as FunnelStepState[][][])[0][0];
    expect(steps[0]).toEqual({
      type: 'eventName', operator: 'contains', value: '/a', idGoal: null,
    });
  });
});
