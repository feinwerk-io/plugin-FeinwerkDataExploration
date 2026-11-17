/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  canRun, emptyStep, isComplete, parseSteps, toFunnelDefinition, toStepsParam,
} from '../src/funnelState';
import { FunnelStepState } from '../src/types';

const page = (value: string, operator: FunnelStepState['operator'] = 'contains'): FunnelStepState => ({
  type: 'pageUrl', operator, value, idGoal: null,
});
const goal = (idGoal: number|null): FunnelStepState => ({
  type: 'goal', operator: '', value: '', idGoal,
});
const anyGoal: FunnelStepState = {
  type: 'anyGoal', operator: '', value: '', idGoal: null,
};

describe('funnel steps in the URL', () => {
  it('round trip, values with separators survive', () => {
    const steps = [page('/a;b~c%d', 'startsWith'), goal(1), anyGoal];
    expect(parseSteps(toStepsParam(steps), [1], 3)).toEqual(steps);
    expect(toStepsParam([page('/a'), goal(1)])).toBe('pageUrl~contains~%2Fa;goal~~1');
  });

  it('drops goal steps of unknown goals to an incomplete goal step', () => {
    expect(parseSteps('pageUrl~contains~%2Fa;goal~~9', [1], 3)).toEqual([page('/a'), goal(null)]);
  });

  it('drops invalid entries, cuts at maxSteps and pads to two steps', () => {
    expect(parseSteps('', [], 3)).toEqual([emptyStep(), emptyStep()]);
    expect(parseSteps('pageViews~contains~x;eventName~startsWith~x;pageUrl~contains~%2Fa', [], 3))
      .toEqual([page('/a'), emptyStep()]);
    const four = 'pageUrl~contains~a;pageUrl~contains~b;pageUrl~contains~c;pageUrl~contains~d';
    expect(parseSteps(four, [], 3).map((s) => s.value)).toEqual(['a', 'b', 'c']);
    expect(parseSteps(four, [], 4)).toHaveLength(4);
    expect(parseSteps('pageUrl~contains~%E0%A4%A', [], 3)).toEqual([emptyStep(), emptyStep()]);
  });

  it('keeps untrimmed text while typing; the definition trims', () => {
    const steps = [page('foo '), anyGoal];
    expect(parseSteps(toStepsParam(steps), [], 3)[0].value).toBe('foo ');
    expect(toFunnelDefinition(steps)).toEqual({
      steps: [{ type: 'pageUrl', operator: 'contains', value: 'foo' }, { type: 'anyGoal' }],
    });
  });

  it('runs only with two or more complete steps', () => {
    expect(isComplete(page(' '))).toBe(false);
    expect(isComplete(page('x'.repeat(1001)))).toBe(false);
    expect(isComplete(goal(null))).toBe(false);
    expect(isComplete(goal(2))).toBe(true);
    expect(isComplete(anyGoal)).toBe(true);
    expect(canRun([page('/a'), goal(2)])).toBe(true);
    expect(canRun([page('/a'), page('')])).toBe(false);
    expect(canRun([page('/a')])).toBe(false);
  });

  it('definition of goal steps', () => {
    expect(toFunnelDefinition([goal(3), anyGoal])).toEqual({
      steps: [{ type: 'goal', idGoal: 3 }, { type: 'anyGoal' }],
    });
  });
});
