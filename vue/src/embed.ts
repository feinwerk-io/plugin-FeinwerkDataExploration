/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Whether the page itself is an embed (Widgetize, disableLink=1), seen by the website's own
 * viewers: then no feedback question and no link into Matomo (house rule "Feedback under every
 * report"). The server checks its request too (Widgets/WidgetProps::isEmbedded), but Matomo loads
 * the widgets of an embedded container or dashboard with their own module, so only the page's
 * address tells (release review 2026-11-25).
 */
export function embeddedPage(search: string): boolean {
  const params = new URLSearchParams(search);
  return (params.get('module') || '').trim() === 'Widgetize' || params.get('disableLink') === '1';
}
