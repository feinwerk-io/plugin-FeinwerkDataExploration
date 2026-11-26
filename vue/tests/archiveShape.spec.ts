/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { archiveShape, archivedReportHint, historyLostFrom } from '../src/archiveShape';
import { SavedExploration } from '../src/api';

const saved = (fields: Partial<SavedExploration>): SavedExploration => ({
  id: 4,
  name: 'Pages',
  shared: true,
  owner: 'anna',
  canEdit: true,
  canDelete: true,
  kind: 'table',
  updated: '2026-11-19 08:30:00',
  state: {},
  segment: '',
  ...fields,
});

// archived saved explorations (design 2026-11-25): what shapes the archived data, mirroring the server's version
describe('the archived shape of an exploration', () => {
  const base = { fwdeDims: 'browser', fwdeMetrics: 'nb_visits' };

  it('stays for how it is shown', () => {
    const shape = archiveShape(base, '');
    [{ fwdeSort: 'nb_visits' }, { fwdeOrder: 'asc' }, { fwdeLimit: '10' }, { fwdeView: 'chart' },
      { fwdeChartType: 'donut' }, { fwdeMenu: 'Goals_Goals' }, { fwdePivotLimit: '10' }].forEach((change) => {
      expect(archiveShape({ ...base, ...change }, '')).toBe(shape);
    });
  });

  it('changes with the data', () => {
    const shape = archiveShape(base, '');
    [{ fwdeDims: 'deviceType' }, { fwdeMetrics: 'nb_visits,nb_actions' }, { fwdeFilters: 'label~contains~Fire' },
      { fwdePivot: 'deviceType' }].forEach((change) => {
      expect(archiveShape({ ...base, ...change }, '')).not.toBe(shape);
    });
    expect(archiveShape(base, 'browserCode==FF')).not.toBe(shape);
  });

  it('offers the report of an archived exploration for longer periods', () => {
    expect(archivedReportHint(null)).toBe('');
    expect(archivedReportHint(saved({ archived: false, menu: 'Goals_Goals' }))).toBe('');
    expect(archivedReportHint(saved({ archived: true, menu: 'Goals_Goals' }))).toBe('open');
    expect(archivedReportHint(saved({ archived: true, menu: null }))).toBe('place');
    expect(archivedReportHint(saved({ archived: true, kind: 'kpi', menu: null }))).toBe('');
  });

  // final review 2026-11-26: every change of the shape loses the days before the rebuild's first day
  it('warns about the history a change loses', () => {
    const archived = saved({ archived: true, state: base });
    expect(historyLostFrom(archived, { ...base, fwdeSort: 'nb_visits' }, '', '2026-05-26')).toBe('');
    expect(historyLostFrom(archived, { ...base, fwdeDims: 'deviceType' }, '', '2026-05-26')).toBe('2026-05-26');
    expect(historyLostFrom(archived, base, 'browserCode==FF', '2026-05-26')).toBe('2026-05-26');
    // nothing lost: a young website, archiving off, or an exploration that is not archived
    expect(historyLostFrom(archived, { ...base, fwdeDims: 'deviceType' }, '', null)).toBe('');
    expect(historyLostFrom(saved({ archived: false, state: base }), { fwdeDims: 'deviceType' }, '', '2026-05-26')).toBe('');
    expect(historyLostFrom(null, base, '', '2026-05-26')).toBe('');
  });
});
