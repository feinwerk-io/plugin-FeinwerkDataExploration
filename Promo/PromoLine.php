<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Promo;

use Piwik\Cache;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\FeinwerkDataExploration\ConnectorInstaller\ConnectorInstaller;
use Piwik\Site;
use Piwik\Translation\Translator;

/**
 * The Feinwerk promotion kit (docs/promo-kit.md, house rule docs/promotions.md): the messages, their conditions and
 * off switches, and the tip line under the plugin's own report. The free placements (admin home banner, context boxes
 * on Matomo's reports) live in PromoPlacements and use the same messages. Everything is shown only to users with admin
 * access to at least one website and to super users, in Matomo's HTML interface only (not in Widgetize embeds, API
 * output, exports or email reports), never for a product that is already installed, and not at all when one of the off
 * switches says so (enabledBy()). Nothing is loaded from outside.
 *
 * Runs on PHP 7.4 and Matomo 5.0 to 6.x (the widest floor of the Feinwerk plugins).
 */
final class PromoLine
{
    public const PLUGIN = 'FeinwerkDataExploration';
    /** days a dismissed message stays hidden in that browser (localStorage fwpromo:<id>:dismissed, javascripts/promo.js) */
    public const DISMISS_DAYS = 30;
    /** the Marketplace Connector: its config section and its UI setting switch promotions off for all Feinwerk plugins */
    public const CONNECTOR = 'FeinwerkMarketplaceConnector';
    private const CONNECTOR_SWITCH = '\\Piwik\\Plugins\\FeinwerkMarketplaceConnector\\Promotions';

    /**
     * message id => what it says and where it leads. product: technical name of an advertised plugin (shown only while
     * it is not installed), null for a website. urls: landing page per language, en is the fallback. placement: where a
     * free plugin shows it besides the tip line (PromoPlacements::PLACEMENTS). ecommerce: only for websites with
     * ecommerce. Copy approved by Peter on 2026-11-10.
     */
    public const MESSAGES = [
        'campaigns' => [
            'title' => 'FeinwerkDataExploration_PromoCampaignsTitle',
            'text' => 'FeinwerkDataExploration_PromoCampaignsText',
            'link' => 'FeinwerkDataExploration_PromoCampaignsLink',
            'urls' => ['de' => 'https://campaigns.feinwerk.io/de/matomo', 'en' => 'https://campaigns.feinwerk.io/en/matomo'],
            'product' => null,
            'placement' => 'campaigns',
            'ecommerce' => false,
        ],
        'hosting' => [
            'title' => 'FeinwerkDataExploration_PromoHostingTitle',
            'text' => 'FeinwerkDataExploration_PromoHostingText',
            'link' => 'FeinwerkDataExploration_PromoHostingLink',
            'urls' => [
                'de' => 'https://feinwerk.io/de/kontakt?service=hosting',
                'en' => 'https://feinwerk.io/en/contact?service=hosting',
            ],
            'product' => null,
            'placement' => 'adminHome',
            'ecommerce' => false,
        ],
        'capi' => [
            'title' => 'FeinwerkDataExploration_PromoCapiTitle',
            'text' => 'FeinwerkDataExploration_PromoCapiText',
            'link' => 'FeinwerkDataExploration_PromoCapiLink',
            'urls' => [
                'de' => 'https://plugins.feinwerk.io/de/plugins/FeinwerkConversionsApi',
                'en' => 'https://plugins.feinwerk.io/en/plugins/FeinwerkConversionsApi',
            ],
            'product' => 'FeinwerkConversionsApi',
            'placement' => 'ecommerce',
            'ecommerce' => true,
        ],
        // the Feinwerk Marketplace Connector from the Matomo Marketplace (Peter, 2026-11-11, copy approved 2026-11-12,
        // started 2026-11-16, docs/promotions.md "Planned message connector"): tip line only, about every second time
        // (choose()), its link depends on the user (connectorUrl()), so it has no fixed urls
        'connector' => [
            'title' => 'FeinwerkDataExploration_PromoConnectorTitle',
            'text' => 'FeinwerkDataExploration_PromoConnectorText',
            'link' => 'FeinwerkDataExploration_PromoConnectorLink',
            // on a line too narrow for the full link (Peter, 2026-11-25: "Kurzer Linktext")
            'linkShort' => 'FeinwerkDataExploration_PromoConnectorLinkShort',
            'urls' => [],
            'product' => 'FeinwerkMarketplaceConnector',
            'placement' => 'line',
            'ecommerce' => false,
        ],
        'sentinel' => [
            'title' => 'FeinwerkDataExploration_PromoSentinelTitle',
            'text' => 'FeinwerkDataExploration_PromoSentinelText',
            'link' => 'FeinwerkDataExploration_PromoSentinelLink',
            'urls' => [
                'de' => 'https://plugins.feinwerk.io/de/plugins/FeinwerkSentinel',
                'en' => 'https://plugins.feinwerk.io/en/plugins/FeinwerkSentinel',
            ],
            'product' => 'FeinwerkSentinel',
            'placement' => 'goals',
            'ecommerce' => false,
        ],
    ];

    /**
     * Demo mode for Feinwerk's test instances (Peter, 2026-11-16: "kannst du einen demo modus einführen, damit stats6
     * auch wieder die werbung zeigt?", docs/promotions.md "Demo mode for test instances"): `promotions_demo = 1` in
     * [FeinwerkMarketplaceConnector] of config.ini.php, also without the connector. Every off switch still wins and the
     * audience stays admins and super users; demo only lifts the conditions (installed products, build floors) and
     * keeps a hidden notice away for the browser session instead of 30 days.
     */
    public const DEMO = 'promotions_demo';

    /** @var bool|null demo mode of this request */
    private static $demo = null;

    /** the message for the Feinwerk Marketplace Connector (shown about every second time while it is not installed) */
    public const CONNECTOR_MESSAGE = 'connector';

    /** paid plugins prefer this message for their tip line (docs/promotions.md); free plugins rotate (null) */
    public const PREFERRED = null;

    /**
     * The campaigns tip leads to the free Feinwerk Campaign Manager in three steps (Peter's decision 8 of 2026-11-23 in
     * Campaign Manager's design, relayed by its session: "Tip line `campaigns` in the other plugins becomes a three-step
     * funnel"; texts approved by Peter on 2026-11-25, his choice: "Texte so übernehmen (Recommended)"). campaigns():
     * not installed: install it (once the store lists it, else today's tip); installed and not connected: its
     * campaign overview; connected: no campaigns tip. The message id stays 'campaigns', so one x hides every step.
     */
    public const CAMPAIGNS_MESSAGE = 'campaigns';
    public const CAMPAIGN_MANAGER = 'FeinwerkCampaignManager';
    /** Campaign Manager's shared interface: isConnected(): bool, overviewUrl(int $idSite, string $period, string $date) */
    public const CAMPAIGN_MANAGER_CONNECTION = 'Piwik\\Plugins\\FeinwerkCampaignManager\\Platform\\Connection';
    /**
     * true once plugins.feinwerk.io lists Campaign Manager publicly: listed on 2026-11-25 (Peter, question dialog: "Erst
     * nach dem Listing (Recommended)"; his listing go in the store session: "Ja, (a) und (b)")
     */
    public const CAMPAIGN_MANAGER_LISTED = true;
    public const CAMPAIGNS_PLATFORM = 'platform';
    public const CAMPAIGNS_INSTALL = 'install';
    public const CAMPAIGNS_OPEN = 'open';
    public const CAMPAIGNS_NONE = 'none';
    /** the connector's helper for a plugin's window on its Feinwerk Plugins screen (asked for on 2026-11-25) */
    private const CONNECTOR_INSTALL_URL = 'pluginInstallUrl';
    private const CAMPAIGNS_CACHE = 'fwpromo.campaigns.';

    /**
     * The off switches, in this order (docs/promo-kit.md, "Off switches"): promotions = 0 in the plugin's own config
     * section, promotions = 0 in [FeinwerkMarketplaceConnector] (all Feinwerk plugins, read even without the
     * connector), then the connector's setting in Matomo's UI. A failing connector switch counts as on.
     *
     * @param array<string, mixed> $section the plugin's config section
     * @param array<string, mixed> $connectorSection [FeinwerkMarketplaceConnector] of config.ini.php
     * @param callable|null $connectorSwitch the connector's Promotions::areEnabled, null when there is none
     */
    public static function enabledBy(array $section, array $connectorSection = [], ?callable $connectorSwitch = null): bool
    {
        if (!self::switchOn($section) || !self::switchOn($connectorSection)) {
            return false;
        }
        if ($connectorSwitch === null) {
            return true;
        }
        try {
            return (bool) $connectorSwitch();
        } catch (\Throwable $e) {
            return true;
        }
    }

    /**
     * The demo switch, read like the connector's Promotions::demo(): 1, on, yes and true (any case, Matomo's INI reader
     * may also hand over PHP true or 1) are on, everything else is off.
     *
     * @param array<string, mixed> $connectorSection [FeinwerkMarketplaceConnector] of config.ini.php
     */
    public static function demoBy(array $connectorSection): bool
    {
        $value = $connectorSection[self::DEMO] ?? null;
        if ($value === true || $value === 1) {
            return true;
        }
        if (!is_string($value) && !is_int($value)) {
            return false;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'on', 'yes', 'true'], true);
    }

    /** Demo mode for this request, read once. Only asked behind enabled(): an off switch beats it. */
    public static function demo(): bool
    {
        if (self::$demo === null) {
            try {
                $connector = Config::getInstance()->{self::CONNECTOR};
                self::$demo = self::demoBy(is_array($connector) ? $connector : []);
            } catch (\Throwable $e) {
                self::$demo = false;
            }
        }

        return self::$demo;
    }

    /**
     * How long a hidden notice stays away: 30 days, in demo 0 (the kit's scripts then keep it away for the browser
     * session only, and older copies of them on the page only).
     */
    public static function dismissDays(bool $demo): int
    {
        return $demo ? 0 : self::DISMISS_DAYS;
    }

    /**
     * Whether this Matomo runs inside WordPress (Matomo for WordPress: the WordPress constant ABSPATH plus the Matomo
     * plugin's matomo_add_plugin(), as in PluginLead's test report of 2026-11-17).
     */
    public static function isWordPressBy(bool $hasAbsPath, bool $hasMatomoAddPlugin): bool
    {
        return $hasAbsPath && $hasMatomoAddPlugin;
    }

    public static function isWordPress(): bool
    {
        return self::isWordPressBy(defined('ABSPATH'), function_exists('matomo_add_plugin'));
    }

    public static function enabled(): bool
    {
        $config = Config::getInstance();
        $own = $config->{self::PLUGIN};
        $connector = $config->{self::CONNECTOR};

        return self::enabledBy(is_array($own) ? $own : [], is_array($connector) ? $connector : [], self::connectorSwitch());
    }

    /**
     * The connector's setting "Show notes on other Feinwerk offers", when the connector is activated and has it (from
     * its release with Promotions::areEnabled); null otherwise.
     *
     * @param callable(): bool|null $isActivated whether the connector is activated (null: ask Matomo)
     */
    public static function connectorSwitch(string $class = self::CONNECTOR_SWITCH, ?callable $isActivated = null): ?callable
    {
        try {
            $isActivated = $isActivated ?? static function (): bool {
                return PluginManager::getInstance()->isPluginActivated(self::CONNECTOR);
            };
            if (!class_exists($class) || !method_exists($class, 'areEnabled') || !$isActivated()) {
                return null;
            }
            return static function () use ($class): bool {
                return (bool) $class::areEnabled();
            };
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * One config section's promotions switch. Matomo's INI reader turns off, no and false into PHP false; an empty
     * value counts as off as well, like the feedback switch.
     *
     * @param array<string, mixed> $section
     */
    private static function switchOn(array $section): bool
    {
        if (!array_key_exists('promotions', $section)) {
            return true;
        }
        $value = $section['promotions'];

        return $value !== false && $value !== null
            && !in_array(strtolower(trim((string) $value)), ['0', '', 'off', 'no', 'false'], true);
    }

    /** Only admins of at least one website and super users, never anonymous users. */
    public static function visibleTo(bool $isAnonymous, bool $hasSomeAdminAccess): bool
    {
        return !$isAnonymous && $hasSomeAdminAccess;
    }

    /** A report embedded with Widgetize (module=Widgetize or disableLink=1) is seen by the customer's own viewers. */
    public static function isEmbedded(string $module, int $disableLink): bool
    {
        return $module === 'Widgetize' || $disableLink === 1;
    }

    /**
     * Messages whose condition holds: the advertised product is not installed, and a message for ecommerce only on a
     * website with ecommerce.
     *
     * @param callable(string): bool $isInstalled
     * @param string $campaignsStage the campaigns tip's step (campaigns()): none leaves the campaigns message out
     * @return string[] message ids in the order of MESSAGES
     */
    public static function candidates(
        callable $isInstalled,
        bool $isEcommerceSite = false,
        bool $demo = false,
        bool $wordpress = false,
        string $campaignsStage = self::CAMPAIGNS_PLATFORM
    ): array {
        // Matomo for WordPress (Peter, 2026-11-17: "Dort nicht werben"): the connector can neither install nor update
        // there yet, so no connector message, in demo too
        $skip = $wordpress ? [self::CONNECTOR_MESSAGE] : [];
        if ($demo) {
            // demo: the whole rotation, whatever is installed or tracked
            return array_values(array_diff(array_keys(self::MESSAGES), $skip));
        }
        $ids = [];
        foreach (self::MESSAGES as $id => $message) {
            if (in_array($id, $skip, true)) {
                continue;
            }
            if (!empty($message['ecommerce']) && !$isEcommerceSite) {
                continue;
            }
            if ($id === self::CAMPAIGNS_MESSAGE && $campaignsStage === self::CAMPAIGNS_NONE) {
                continue;
            }
            if ($message['product'] === null || !$isInstalled($message['product'])) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    /**
     * The preferred message when there is one and it is a candidate, otherwise one per day in turn; null when none is
     * left.
     *
     * @param string[] $candidates
     */
    public static function choose(array $candidates, int $day): ?string
    {
        if ($candidates === []) {
            return null;
        }
        if (self::PREFERRED !== null && in_array(self::PREFERRED, $candidates, true)) {
            return self::PREFERRED;
        }
        // the connector message about every second time (Peter: "vermehrt"), the others in turn on the other days
        if (in_array(self::CONNECTOR_MESSAGE, $candidates, true)) {
            $others = array_values(array_diff($candidates, [self::CONNECTOR_MESSAGE]));
            if ($day % 2 === 0 || $others === []) {
                return self::CONNECTOR_MESSAGE;
            }

            return $others[intdiv($day, 2) % count($others)];
        }

        return array_values($candidates)[$day % count($candidates)];
    }

    /**
     * Where the connector message leads (connector session, checked on Matomo 5.14.2 and 6.0.0-rc1):
     * - a super user whose Matomo may install from its Marketplace: the connector's page there, inside Matomo (no
     *   campaign parameters; the popover hash works from Matomo 5.0 to 6);
     * - an admin: the connector's page on plugins.matomo.org, with a note that the super user can install it.
     * A super user without a usable Marketplace gets no connector message at all (lineFor()).
     *
     * @return array{0: string, 1: bool} url, external
     */
    public static function connectorUrl(bool $isSuperUser, bool $marketplaceUsable, bool $installerOffered = false): array
    {
        // where this plugin's installer page is offered, the tip leads there (Peter, 2026-11-23: "Auf den Installer")
        if ($isSuperUser && $installerOffered) {
            return [ConnectorInstaller::PAGE_URL, false];
        }
        if ($isSuperUser && $marketplaceUsable) {
            return ['index.php?module=Marketplace&action=overview#?popover=browsePluginDetail%243AFeinwerkMarketplaceConnector', false];
        }
        return ['https://plugins.matomo.org/FeinwerkMarketplaceConnector?mtm_campaign=plugin-promo&mtm_source=' . self::PLUGIN
            . '&mtm_content=' . self::CONNECTOR_MESSAGE, true];
    }

    /**
     * Whether this Matomo lets a super user install from its Marketplace (kit PFR 0bbee14): Marketplace on, internet
     * features on, plugin administration on and automatic updates possible (all four exist at Matomo 5.0). False on any
     * doubt.
     */
    public static function marketplaceUsable(): bool
    {
        try {
            return self::marketplaceUsableBy([
                'marketplace' => class_exists('Piwik\\Plugins\\Marketplace\\Marketplace')
                    && \Piwik\Plugins\Marketplace\Marketplace::isMarketplaceEnabled(),
                'internet' => \Piwik\SettingsPiwik::isInternetEnabled(),
                'pluginsAdmin' => \Piwik\Plugins\CorePluginsAdmin\CorePluginsAdmin::isPluginsAdminEnabled(),
                'autoUpdate' => \Piwik\SettingsPiwik::isAutoUpdatePossible(),
            ]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Matomo's own rule for its one-click Install button (else it offers a ZIP download): Marketplace, internet
     * features, plugin administration and automatic updates possible (`SettingsPiwik::isAutoUpdatePossible()`: not
     * `enable_auto_update = 0`, not `multi_server_environment`). The same four as ConnectorInstaller::decide(). Pure.
     *
     * @param array{marketplace: bool, internet: bool, pluginsAdmin: bool, autoUpdate: bool} $switches
     */
    public static function marketplaceUsableBy(array $switches): bool
    {
        return $switches['marketplace'] && $switches['internet'] && $switches['pluginsAdmin'] && $switches['autoUpdate'];
    }

    /** Landing page in the user's language (German or English) with the campaign parameters for Feinwerk's Matomo. */
    public static function url(string $id, string $language): string
    {
        $urls = self::MESSAGES[$id]['urls'];
        $base = $urls[$language === 'de' ? 'de' : 'en'] ?? $urls['en'];

        return $base . (strpos($base, '?') !== false ? '&' : '?')
            . 'mtm_campaign=plugin-promo&mtm_source=' . self::PLUGIN . '&mtm_content=' . rawurlencode($id);
    }

    /**
     * Variables of templates/_promo.twig and templates/_promoBox.twig. Pure.
     *
     * @param callable(string): string $translate
     * @param array{superUser: bool, marketplace: bool}|null $user for the connector message: is the user a super user,
     *                                                         may this Matomo install from its Marketplace (null: neither)
     * @param array{stage: string, openUrl: string, installUrl: string}|null $campaigns for the campaigns message: its step
     *                                                         (campaigns()); null or platform: today's tip
     * @return array{id: string, placement: string, title: string, text: string, link: string, linkShort: string, url: string, external: bool, note: string, dismissDays: int, demo: bool}
     */
    public static function variables(
        string $id,
        string $language,
        callable $translate,
        string $placement = 'line',
        bool $demo = false,
        ?array $user = null,
        ?array $campaigns = null
    ): array {
        if (!isset(self::MESSAGES[$id])) {
            throw new \InvalidArgumentException('message');
        }
        $message = self::MESSAGES[$id];
        $step = $id === self::CAMPAIGNS_MESSAGE && $campaigns !== null ? self::campaignsStep($campaigns, $language) : null;
        if ($step !== null) {
            return [
                'id' => $id,
                'placement' => $placement,
                'title' => $translate($step['title']),
                'text' => $translate($step['text']),
                'link' => $translate($step['link']),
                'linkShort' => '',
                'url' => $step['url'],
                'external' => $step['external'],
                'note' => '',
                'dismissDays' => self::dismissDays($demo),
                'demo' => $demo,
            ];
        }
        $url = $id === self::CONNECTOR_MESSAGE ? null : self::url($id, $language);
        $external = true;
        $note = '';
        if ($url === null) {
            $superUser = (bool) ($user['superUser'] ?? false);
            [$url, $external] = self::connectorUrl(
                $superUser,
                (bool) ($user['marketplace'] ?? false),
                (bool) ($user['installer'] ?? false)
            );
            // an admin cannot install plugins: who can
            $note = $superUser ? '' : (string) $translate('FeinwerkDataExploration_PremiumAskSuperUser');
        }

        return [
            'id' => $id,
            'placement' => $placement,
            'title' => $translate($message['title']),
            'text' => $translate($message['text']),
            'link' => $translate($message['link']),
            'linkShort' => isset($message['linkShort']) ? (string) $translate($message['linkShort']) : '',
            'url' => $url,
            'external' => $external,
            'note' => $note,
            'dismissDays' => self::dismissDays($demo),
            'demo' => $demo,
        ];
    }

    /**
     * The campaigns tip's step. Campaign Manager active: open its overview, or none once it is connected (demo: open);
     * installed but switched off: none (the admin chose so; demo: today's tip); not installed: install once the store
     * lists it, else today's tip (platform). Pure.
     */
    public static function campaignsStage(bool $installed, bool $active, bool $connected, bool $listed, bool $demo = false): string
    {
        if ($active) {
            return $connected && !$demo ? self::CAMPAIGNS_NONE : self::CAMPAIGNS_OPEN;
        }
        if ($installed) {
            return $demo ? self::CAMPAIGNS_PLATFORM : self::CAMPAIGNS_NONE;
        }

        return $listed ? self::CAMPAIGNS_INSTALL : self::CAMPAIGNS_PLATFORM;
    }

    /**
     * Texts and link of a campaigns step; null for today's tip. open: Campaign Manager's overview in the same tab;
     * install: the connector's window for it where the connector offers one (super users), else its store page in a
     * new tab. Pure.
     *
     * @param array{stage: string, openUrl: string, installUrl: string} $campaigns
     * @return array{title: string, text: string, link: string, url: string, external: bool}|null
     */
    public static function campaignsStep(array $campaigns, string $language): ?array
    {
        $key = 'FeinwerkDataExploration_PromoCampaigns';
        if (($campaigns['stage'] ?? '') === self::CAMPAIGNS_OPEN && ($campaigns['openUrl'] ?? '') !== '') {
            return ['title' => $key . 'OpenTitle', 'text' => $key . 'OpenText', 'link' => $key . 'OpenLink',
                'url' => $campaigns['openUrl'], 'external' => false];
        }
        if (($campaigns['stage'] ?? '') !== self::CAMPAIGNS_INSTALL) {
            return null;
        }
        if (($campaigns['installUrl'] ?? '') !== '') {
            return ['title' => $key . 'Title', 'text' => $key . 'InstallText', 'link' => $key . 'InstallLink',
                'url' => $campaigns['installUrl'], 'external' => false];
        }

        return ['title' => $key . 'Title', 'text' => $key . 'InstallText', 'link' => $key . 'StoreLink',
            'url' => 'https://plugins.feinwerk.io/' . ($language === 'de' ? 'de' : 'en') . '/plugins/' . self::CAMPAIGN_MANAGER
                . '?mtm_campaign=plugin-promo&mtm_source=' . self::PLUGIN . '&mtm_content=' . self::CAMPAIGNS_MESSAGE,
            'external' => true];
    }

    /**
     * The campaigns tip's step for this website and request, read once per request (Campaign Manager's interface
     * through class_exists and isPluginActivated, as its session asks). Today's tip on any error. Never throws.
     *
     * @return array{stage: string, openUrl: string, installUrl: string}
     */
    public static function campaigns(int $idSite): array
    {
        $platform = ['stage' => self::CAMPAIGNS_PLATFORM, 'openUrl' => '', 'installUrl' => ''];
        try {
            $cache = Cache::getTransientCache();
            $cacheKey = self::CAMPAIGNS_CACHE . $idSite;
            if ($cache->contains($cacheKey)) {
                return $cache->fetch($cacheKey);
            }
            $manager = PluginManager::getInstance();
            $installed = $manager->isPluginInstalled(self::CAMPAIGN_MANAGER);
            $class = self::CAMPAIGN_MANAGER_CONNECTION;
            $active = $installed && $manager->isPluginActivated(self::CAMPAIGN_MANAGER) && class_exists($class);
            $connected = $active && (bool) $class::isConnected();
            $demo = self::demo();
            $stage = self::campaignsStage($installed, $active, $connected, self::CAMPAIGN_MANAGER_LISTED, $demo);
            $openUrl = '';
            if ($stage === self::CAMPAIGNS_OPEN) {
                $openUrl = $idSite > 0 ? (string) $class::overviewUrl($idSite, self::requestPeriod(), self::requestDate()) : '';
                if (strpos($openUrl, 'index.php?') !== 0) {
                    // no website to open (or an unexpected answer): no campaigns tip here
                    $openUrl = '';
                    $stage = $demo ? self::CAMPAIGNS_PLATFORM : self::CAMPAIGNS_NONE;
                }
            }
            $info = [
                'stage' => $stage,
                'openUrl' => $openUrl,
                'installUrl' => $stage === self::CAMPAIGNS_INSTALL ? self::connectorInstallUrl(self::CAMPAIGN_MANAGER) : '',
            ];
            $cache->save($cacheKey, $info);

            return $info;
        } catch (\Throwable $e) {
            return $platform;
        }
    }

    /** The request's period for a link into Matomo: one of Matomo's periods, else day. */
    private static function requestPeriod(): string
    {
        $period = (string) Common::getRequestVar('period', 'day', 'string');

        return in_array($period, ['day', 'week', 'month', 'year', 'range'], true) ? $period : 'day';
    }

    /** The request's date for a link into Matomo: a date, a range or a keyword, else yesterday. */
    private static function requestDate(): string
    {
        $date = (string) Common::getRequestVar('date', 'yesterday', 'string');

        return preg_match('/^[0-9A-Za-z,\-]{1,40}$/', $date) ? $date : 'yesterday';
    }

    /**
     * The connector's link to a plugin's window inside Matomo (connector helper pluginInstallUrl(), super users, once
     * it offers one); '' without it.
     */
    private static function connectorInstallUrl(string $plugin): string
    {
        try {
            $helper = ltrim(self::CONNECTOR_SWITCH, '\\');
            if (!PluginManager::getInstance()->isPluginActivated(self::CONNECTOR) || !class_exists($helper)
                || !method_exists($helper, self::CONNECTOR_INSTALL_URL)
            ) {
                return '';
            }
            $url = (string) call_user_func([$helper, self::CONNECTOR_INSTALL_URL], $plugin);

            return strpos($url, 'index.php?') === 0 ? $url : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /** Whether the current user may see promotions at all: switches on, admin of a website or super user. */
    public static function shownToCurrentUser(): bool
    {
        return self::enabled() && self::visibleTo(Piwik::isUserIsAnonymous(), Piwik::isUserHasSomeAdminAccess());
    }

    /** Whether a plugin is installed (the advertised product of a message). */
    public static function isInstalled(string $plugin): bool
    {
        return PluginManager::getInstance()->isPluginInstalled($plugin);
    }

    public static function language(): string
    {
        return (string) StaticContainer::get(Translator::class)->getCurrentLanguage();
    }

    /** The line for the current user and website, null when nothing is shown. */
    public static function current(int $idSite): ?array
    {
        if (!self::shownToCurrentUser()) {
            return null;
        }
        $superUser = Piwik::hasUserSuperUserAccess();

        return self::lineFor(
            [
                'day' => intdiv(time(), 86400),
                'ecommerce' => $idSite > 0 && Site::isEcommerceEnabledFor($idSite),
                'demo' => self::demo(),
                'wordpress' => self::isWordPress(),
                'superUser' => $superUser,
                'marketplace' => $superUser && self::marketplaceUsable(),
                'installer' => $superUser
                    && ConnectorInstaller::decide(ConnectorInstaller::environment()) !== ConnectorInstaller::HIDDEN,
                'campaigns' => self::campaigns($idSite),
            ],
            [self::class, 'isInstalled'],
            [Piwik::class, 'translate'],
            self::language()
        );
    }

    /**
     * The tip line for one user and website, null when none is left. Pure, so current()'s decisions are tested:
     * - Matomo for WordPress: no connector message (Peter, 2026-11-17, "Dort nicht werben");
     * - a super user whose Matomo cannot install from its Marketplace gets no connector message either (Peter,
     *   2026-11-17, "Dann keine Nachricht": a ZIP download would promise what the instance cannot do);
     * - an admin gets the note who can install it;
     * - the campaigns tip follows its step (campaigns(): none leaves it out).
     *
     * @param array{day: int, ecommerce: bool, demo: bool, wordpress: bool, superUser: bool, marketplace: bool} $env
     * @param callable(string): bool $isInstalled
     * @param callable $translate like Piwik::translate(string $key, array $args = [])
     */
    public static function lineFor(array $env, callable $isInstalled, callable $translate, string $language): ?array
    {
        $campaigns = $env['campaigns'] ?? null;
        $candidates = self::candidates(
            $isInstalled,
            $env['ecommerce'],
            $env['demo'],
            $env['wordpress'],
            (string) ($campaigns['stage'] ?? self::CAMPAIGNS_PLATFORM)
        );
        if ($env['superUser'] && !$env['marketplace']) {
            $candidates = array_values(array_diff($candidates, [self::CONNECTOR_MESSAGE]));
        }
        $id = self::choose($candidates, $env['day']);
        if ($id === null) {
            return null;
        }
        $user = $id === self::CONNECTOR_MESSAGE
            ? ['superUser' => $env['superUser'], 'marketplace' => $env['marketplace'], 'installer' => $env['installer'] ?? false]
            : null;

        return self::variables($id, $language, $translate, 'line', $env['demo'], $user, $campaigns);
    }
}
