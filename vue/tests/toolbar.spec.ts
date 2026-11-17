/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import ExplorationToolbar from '../src/ExplorationToolbar.vue';

// Matomo 5 runs the specs with Jest; CoreHome is Matomo's UMD global, so the mock is virtual
jest.mock('CoreHome', () => ({
  translate: (key: string) => key,
  Matomo: {},
  MatomoUrl: { parsed: { value: {} }, stringify: () => '' },
}), { virtual: true });
// Matomo 5's CoreHome declarations for Jest lack `Matomo` (the build has it): the export links are not under test here
jest.mock('../src/exportLink', () => ({ exportUrl: () => '', funnelExportUrl: () => '' }));

describe('toolbar of a page without table state (funnel)', () => {
  it('offers export only with an export link, i.e. once the funnel can run', () => {
    expect(mount(ExplorationToolbar).find('.fwde-menu').exists()).toBe(false);
    const ready = mount(ExplorationToolbar, { props: { exportUrlFor: () => 'https://example.org/x' } });
    expect(ready.find('.fwde-menu').exists()).toBe(true);
    expect(ready.find('.fwde-toolbar__copy').exists()).toBe(true);
    expect(ready.find('.fwde-switch').exists()).toBe(false);
  });
});
