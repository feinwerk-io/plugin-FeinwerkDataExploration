/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
declare module '*.vue' {
  import { DefineComponent } from 'vue';

  // eslint-disable-next-line @typescript-eslint/ban-types, @typescript-eslint/no-explicit-any
  const component: DefineComponent<{}, {}, any>;
  export default component;
}
