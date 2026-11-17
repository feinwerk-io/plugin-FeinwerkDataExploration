<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Promo;

/**
 * Feinwerk promotion kit: the paid Feinwerk plugins a free plugin recommends when the Marketplace Connector cannot
 * (missing, older than its paidPluginRecommendations() helper, or not connected to the store). Peter, 2026-11-16:
 * "Wieso wird Werbung nur angezeigt für jene mit Marketplace bzw. Store-Verbindung? ... Deshalb veröffentlichen wir ja
 * kostenloses Plugins, um Werbereichweite zu bekommen!" (docs/promotions.md).
 *
 * The list is what the store sells today, in its order (plugins.feinwerk.io session, 2026-11-16); never parked plugins
 * (FeinwerkAdCostImport). Like the connector, it recommends only what this Matomo can install: per plugin the lowest
 * Matomo version of its stable build on the 5 and the 6 line ('' = no stable build on that line yet; store DB,
 * 2026-11-16) and the lowest PHP version of those builds (plugin.json require.php). Names and one-liners live in lang/*.json (keys PaidPlugin<Stem>Name / PaidPlugin<Stem>Text),
 * the store URLs only here (Peter, 2026-11-11, Matomo Marketplace). No prices (they change), no pictures, nothing
 * requested from outside. Refresh the list and the texts from the store with each release; when the connector is
 * connected its live list wins.
 */
final class PaidPluginCatalog
{
    /**
     * slug (technical name) => [stem of the translation keys, lowest Matomo 5 version, lowest Matomo 6 version, lowest
     * PHP version], in the store's order. A one-build plugin (>=5.0.0,<7.0.0-b1) has 6.0.0-b1 on the 6 line, so Matomo 6
     * betas and release candidates count.
     */
    public const PLUGINS = [
        'FeinwerkChannelGrouping' => ['ChannelGrouping', '5.3.0', '', '8.1.0'],
        'FeinwerkSearchReport' => ['SearchReport', '5.0.0', '', '8.1.0'],
        'FeinwerkConversionsApi' => ['ConversionsApi', '5.0.0', '', '8.1.0'],
        'FeinwerkTrackingSpamScore' => ['TrackingSpamScore', '5.0.0', '', '8.1.0'],
        'FeinwerkTrackingRouter' => ['TrackingRouter', '5.0.0', '', '7.4.0'],
        'FeinwerkSentinelPro' => ['SentinelPro', '5.0.0', '6.0.0-b1', '8.1.0'],
        'FeinwerkVisitJourney' => ['VisitJourney', '5.12.0', '', '8.1.0'],
        'FeinwerkEmailReportsPro' => ['EmailReportsPro', '5.0.0', '6.0.0-b1', '8.1.0'],
        'FeinwerkAuthSaml' => ['AuthSaml', '5.0.0', '', '8.1.0'],
    ];

    /** the store's public plugin pages: STORE . {de|en} . '/plugins/' . slug */
    public const STORE = 'https://plugins.feinwerk.io/';

    /** The public store page of a plugin in the viewer's language (de, else en), without parameters. */
    public static function storeUrl(string $slug, string $language): string
    {
        return self::STORE . (strpos(strtolower($language), 'de') === 0 ? 'de' : 'en') . '/plugins/' . rawurlencode($slug);
    }

    /**
     * Whether a plugin of the list has a stable build for this Matomo version (its line 5 or 6, at least its floor) and
     * this PHP version. The connector needs PHP 8.1, so below that the fallback list is all an admin ever sees.
     */
    public static function installableOn(string $slug, string $matomoVersion, string $phpVersion = PHP_VERSION): bool
    {
        if (!isset(self::PLUGINS[$slug])) {
            return false;
        }
        $major = (int) $matomoVersion;
        $floor = $major === 5 ? self::PLUGINS[$slug][1] : ($major === 6 ? self::PLUGINS[$slug][2] : '');

        return $floor !== '' && version_compare($matomoVersion, $floor, '>=')
            && version_compare($phpVersion, self::PLUGINS[$slug][3], '>=');
    }

    /**
     * The plugins of the list this Matomo does not have and can install, in the store's order, in the connector's entry
     * shape (name, description, '' for price and image, the public store page as storeUrl).
     *
     * @param string[]                 $present       the plugin folders of this Matomo (Matomo's plugin manager)
     * @param string                   $matomoVersion this Matomo (Piwik\Version::VERSION)
     * @param callable(string): string $translate     translation of a key
     * @param string                   $self          the recommending plugin, never recommended
     * @param string                   $phpVersion    this PHP (tests)
     * @return array<int, array<string, string>>
     */
    public static function notInstalled(
        array $present,
        string $matomoVersion,
        string $language,
        callable $translate,
        string $self,
        string $phpVersion = PHP_VERSION
    ): array {
        $out = [];
        foreach (self::PLUGINS as $slug => [$stem]) {
            if ($slug === $self || in_array($slug, $present, true) || !self::installableOn($slug, $matomoVersion, $phpVersion)) {
                continue;
            }
            $out[] = self::entry($slug, $stem, $language, $translate);
        }

        return $out;
    }

    /**
     * Demo mode (PromoLine::demo()): the whole list but the recommending plugin, installed or not, with or without a
     * build for this Matomo.
     *
     * @param callable(string): string $translate
     * @return array<int, array<string, string>>
     */
    public static function all(string $language, callable $translate, string $self): array
    {
        $out = [];
        foreach (self::PLUGINS as $slug => [$stem]) {
            if ($slug !== $self) {
                $out[] = self::entry($slug, $stem, $language, $translate);
            }
        }

        return $out;
    }

    /**
     * @param callable(string): string $translate
     * @return array<string, string>
     */
    private static function entry(string $slug, string $stem, string $language, callable $translate): array
    {
        return [
            'slug' => $slug,
            'name' => (string) $translate('FeinwerkDataExploration_PaidPlugin' . $stem . 'Name'),
            'description' => (string) $translate('FeinwerkDataExploration_PaidPlugin' . $stem . 'Text'),
            'price' => '',
            'image' => '',
            'storeUrl' => self::storeUrl($slug, $language),
        ];
    }
}
