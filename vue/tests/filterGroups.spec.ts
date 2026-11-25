/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  filterGroupsDefinition, filterParam, groupsOf, isValidFilter, matchParam, parseFilterParam,
} from '../src/filterGroups';
import { parseState } from '../src/state';
import { CATALOG, LIMITS } from './fixtures';

const METRICS = ['nb_visits', 'bounce_rate'];

describe('filter groups in the link (2026-11-23)', () => {
  it('writes and reads "(A and B) or C"', () => {
    const filters = [
      { target: 'label', operator: 'contains' as const, value: 'chr', group: 0 },
      { target: 'secondary_label', operator: 'equals' as const, value: 'Desktop', group: 0 },
      { target: 'nb_visits', operator: 'gt' as const, value: '100', group: 1 },
    ];
    const param = filterParam(filters, ['all', 'all']);
    expect(param).toBe('label~contains~chr;secondary_label~equals~Desktop|nb_visits~gt~100');
    expect(matchParam(filters, 'any')).toBe('any');
    expect(parseFilterParam(param, 'any', 2, METRICS)).toEqual({
      filters, groupMatch: ['all', 'all'], match: 'any',
    });
  });

  it('keeps values with | ; ~ : and a leading "any:" as they are', () => {
    const filters = [
      { target: 'label', operator: 'contains' as const, value: 'a|b;c~d:e', group: 0 },
      { target: 'label', operator: 'equals' as const, value: 'any:x', group: 1 },
    ];
    const parsed = parseFilterParam(filterParam(filters, ['any', 'all']), 'all', 1, METRICS);
    expect(parsed.filters).toEqual(filters);
    expect(parsed.groupMatch).toEqual(['any', 'all']);
  });

  it('reads an old link\'s "match any" as the switch of its one group, and writes it back new', () => {
    const parsed = parseFilterParam('label~contains~a;label~contains~e', 'any', 1, METRICS);
    expect(parsed).toEqual({
      filters: [
        { target: 'label', operator: 'contains', value: 'a', group: 0 },
        { target: 'label', operator: 'contains', value: 'e', group: 0 },
      ],
      groupMatch: ['any'],
      match: 'all',
    });
    expect(filterParam(parsed.filters, parsed.groupMatch)).toBe('any:label~contains~a;label~contains~e');
    expect(matchParam(parsed.filters, parsed.match)).toBe('');
  });

  it('drops broken conditions, the groups they empty and what passes the limits', () => {
    const parsed = parseFilterParam('label~bogus~x|any:label~contains~y;nb_visits~gt~abc', 'any', 1, METRICS);
    expect(parsed.filters).toEqual([{
      target: 'label', operator: 'contains', value: 'y', group: 0,
    }]);
    expect(parsed.groupMatch).toEqual(['any']);
    expect(parsed.match).toBe('all');

    const six = Array(6).fill('label~contains~a').join('|');
    expect(parseFilterParam(six, 'any', 1, METRICS).groupMatch).toHaveLength(5);
    const twelve = Array(12).fill('label~contains~a').join(';');
    expect(parseFilterParam(twelve, '', 1, METRICS).filters).toHaveLength(10);
  });

  it('takes the third label as a target only with three dimensions (rows and pivot, 2026-11-24)', () => {
    const param = 'tertiary_label~contains~shop';
    expect(parseFilterParam(param, '', 3, METRICS).filters).toHaveLength(1);
    expect(parseFilterParam(param, '', 2, METRICS).filters).toHaveLength(0);
  });

  it('reads the new operators, a pattern up to 200 characters', () => {
    const parsed = parseFilterParam(
      `label~notStartsWith~a;label~notEndsWith~b;label~matches~${encodeURIComponent('^x(y|z)')}`
        + `;label~matches~${'p'.repeat(201)};nb_visits~gte~2;bounce_rate~lte~25`,
      '',
      1,
      METRICS,
    );
    expect(parsed.filters.map((f) => f.operator)).toEqual(['notStartsWith', 'notEndsWith', 'matches', 'gte', 'lte']);
    expect(parsed.filters[2].value).toBe('^x(y|z)');
  });

  // Peter, 2026-11-25: "Es muss auch eine Bedingung 'nicht leer' geben" (2A)
  it('reads and writes "is empty" and "is not empty" without a value', () => {
    const parsed = parseFilterParam('label~notEmpty~;label~empty~x;nb_visits~notEmpty~', '', 1, METRICS);
    expect(parsed.filters.map((f) => [f.target, f.operator, f.value])).toEqual([
      ['label', 'notEmpty', ''],
      ['label', 'empty', ''],
    ]);
    expect(filterParam(parsed.filters, parsed.groupMatch)).toBe('label~notEmpty~;label~empty~');
    expect(isValidFilter({ target: 'label', operator: 'notEmpty', value: '' }, 1, METRICS)).toBe(true);
    expect(isValidFilter({ target: 'nb_visits', operator: 'notEmpty', value: '' }, 1, METRICS)).toBe(false);
  });

  it('builds the API\'s filterGroups, rates as fractions', () => {
    const state = parseState({
      fwdeDims: 'browser',
      fwdeMetrics: 'nb_visits,bounce_rate',
      fwdeFilters: 'label~contains~chr|any:bounce_rate~gte~25;nb_visits~lt~3',
      fwdeMatch: 'any',
    }, CATALOG, LIMITS);
    expect(filterGroupsDefinition(state)).toEqual({
      match: 'any',
      groups: [
        { match: 'all', filters: [{ target: 'label', operator: 'contains', value: 'chr' }] },
        {
          match: 'any',
          filters: [
            { target: 'bounce_rate', operator: 'gte', value: 0.25 },
            { target: 'nb_visits', operator: 'lt', value: 3 },
          ],
        },
      ],
    });
    expect(filterGroupsDefinition(parseState({ fwdeDims: 'browser' }, CATALOG, LIMITS))).toBeNull();
  });

  it('lists the groups in order with their switch', () => {
    const groups = groupsOf([
      { target: 'label', operator: 'contains', value: 'a', group: 1 },
      { target: 'label', operator: 'contains', value: 'b', group: 0 },
    ], ['any', 'all']);
    expect(groups.map((g) => [g.match, g.filters.map((f) => f.value)])).toEqual([['any', ['b']], ['all', ['a']]]);
  });
});
