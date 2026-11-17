/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { FunnelStepState, StepOperator, StepType } from './types';

/** As FunnelStep::OPERATORS in PHP: the conditions of the text steps. */
export const OPERATORS_BY_TYPE: Record<string, StepOperator[]> = {
  pageUrl: ['contains', 'equals', 'startsWith'],
  pageTitle: ['contains', 'equals', 'startsWith'],
  eventCategory: ['contains', 'equals'],
  eventAction: ['contains', 'equals'],
  eventName: ['contains', 'equals'],
  siteSearch: ['contains', 'equals'],
  download: ['contains', 'equals'],
  outlink: ['contains', 'equals'],
};

export const STEP_TYPES: StepType[] = [
  'pageUrl', 'pageTitle', 'eventCategory', 'eventAction', 'eventName', 'siteSearch', 'download',
  'outlink', 'goal', 'anyGoal',
];

export const MAX_STEP_TEXT = 1000;

export function isGoalType(type: StepType): boolean {
  return type === 'goal' || type === 'anyGoal';
}

export function emptyStep(): FunnelStepState {
  return {
    type: 'pageUrl', operator: 'contains', value: '', idGoal: null,
  };
}

function goalStep(type: 'goal'|'anyGoal', idGoal: number|null): FunnelStepState {
  return {
    type, operator: '', value: '', idGoal,
  };
}

/**
 * The steps of the hash parameter fwdeSteps: "type~operator~value;…" with the value URI-encoded
 * (only the first two "~" separate), goals "goal~~3" and "anyGoal~~". Invalid entries are left
 * out; a goal the website does not have becomes a goal step still to choose; more than maxSteps
 * are cut and fewer than two padded with empty steps. Texts stay as typed (not trimmed).
 */
export function parseSteps(value: unknown, goalIds: number[], maxSteps: number): FunnelStepState[] {
  const steps: FunnelStepState[] = [];
  String(value || '').split(';').forEach((entry) => {
    const first = entry.indexOf('~');
    const second = first < 0 ? -1 : entry.indexOf('~', first + 1);
    if (second < 0 || steps.length >= maxSteps) {
      return;
    }
    const type = entry.substring(0, first);
    const operator = entry.substring(first + 1, second);
    let text: string;
    try {
      text = decodeURIComponent(entry.substring(second + 1));
    } catch (e) {
      return;
    }
    if (type === 'anyGoal') {
      steps.push(goalStep('anyGoal', null));
    } else if (type === 'goal') {
      const id = Number(text);
      steps.push(goalStep('goal', Number.isInteger(id) && goalIds.includes(id) ? id : null));
    } else if ((OPERATORS_BY_TYPE[type] || []).includes(operator as StepOperator)) {
      steps.push({
        type: type as StepType, operator: operator as StepOperator, value: text, idGoal: null,
      });
    }
  });
  while (steps.length < 2) {
    steps.push(emptyStep());
  }
  return steps;
}

export function toStepsParam(steps: FunnelStepState[]): string {
  return steps.map((step) => {
    if (step.type === 'goal') {
      return `goal~~${step.idGoal === null ? '' : step.idGoal}`;
    }
    if (step.type === 'anyGoal') {
      return 'anyGoal~~';
    }
    return `${step.type}~${step.operator}~${encodeURIComponent(step.value)}`;
  }).join(';');
}

export function isComplete(step: FunnelStepState): boolean {
  if (step.type === 'goal') {
    return step.idGoal !== null;
  }
  if (step.type === 'anyGoal') {
    return true;
  }
  const text = step.value.trim();
  return text !== '' && text.length <= MAX_STEP_TEXT;
}

export function canRun(steps: FunnelStepState[]): boolean {
  return steps.length >= 2 && steps.every(isComplete);
}

/** The definition for FeinwerkDataExploration.getFunnel. */
export function toFunnelDefinition(steps: FunnelStepState[]): { steps: Record<string, unknown>[] } {
  return {
    steps: steps.map((step) => {
      if (step.type === 'goal') {
        return { type: 'goal', idGoal: step.idGoal };
      }
      if (step.type === 'anyGoal') {
        return { type: 'anyGoal' };
      }
      return { type: step.type, operator: step.operator, value: step.value.trim() };
    }),
  };
}

/**
 * The step after its type was changed: text kept between text types, the operator kept where the
 * new type allows it, goal steps without operator and text.
 */
export function withType(step: FunnelStepState, type: StepType): FunnelStepState {
  if (type === 'goal' || type === 'anyGoal') {
    return goalStep(type, null);
  }
  const operators = OPERATORS_BY_TYPE[type];
  return {
    type,
    operator: operators.includes(step.operator) ? step.operator : 'contains',
    value: isGoalType(step.type) ? '' : step.value,
    idGoal: null,
  };
}
