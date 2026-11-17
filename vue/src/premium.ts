/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// The widget prop of the premium banner (PremiumBanner.vue), in its own module so the specs
// can use the types on Matomo 5 too (its Jest shim gives a .vue file only a default export).

/** one paid plugin, as Promo\PremiumPromo::valid() gives it */
export interface PremiumPlugin {
  /** premium-<slug>: the key of the 30-day hiding */
  id: string;
  slug: string;
  name: string;
  description: string;
  price: string;
  /** only through the connector's image proxy inside Matomo (index.php?...), else '' */
  image: string;
  url: string;
  /** '1': the public store page (new tab); '': the connector's plugin window inside Matomo */
  external: string;
}

/** the widget prop `premium` (Promo\PremiumPromo::forBanner()) */
export interface PremiumProps {
  plugins: PremiumPlugin[];
  /** days the x hides a plugin; 0 in demo mode (this browser session) */
  days: number;
  demo: boolean;
  /** for admins under a store link: who can install the plugin; '' for super users */
  note: string;
}
