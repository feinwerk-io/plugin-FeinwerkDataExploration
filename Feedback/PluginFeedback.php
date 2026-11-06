<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Feedback;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Http;
use Piwik\SettingsPiwik;
use Psr\Log\LoggerInterface;

/**
 * Feedback thumbs under the exploration's result ("Is this exploration helpful?") and the support link next to them,
 * the server side of the Feinwerk feedback kit. The vote goes from the Matomo server to Feinwerk's store
 * (POST /api/v1/feedback): plugin, versions, question, vote, browser family and UI language, nothing about the user or
 * the website. config.ini [FeinwerkDataExploration] feedback = 0 switches it off.
 */
class PluginFeedback
{
    public const PLUGIN = 'FeinwerkDataExploration';
    /** the questions (store "topic") this plugin asks; the API accepts only these */
    public const TOPICS = ['free_form_table'];
    public const DEFAULT_STORE = 'https://plugins.feinwerk.io';
    private const TIMEOUT = 3; // seconds; the same in every plugin (feedback kit)
    /**
     * Sent instead of the visitor's browser string: without it Matomo's HTTP client forwards the browser's full
     * User-Agent (and puts it into the Via header), which the privacy line under the thumbs does not cover.
     */
    public const USER_AGENT = 'Feinwerk-Feedback/1.0';
    /** per user and question: time of the last vote (one vote every 10 seconds); deleted with the user and on uninstall */
    public const THROTTLE_OPTION_PREFIX = 'fw_dataexploration.feedback.';

    /** all throttle options of one user start with this */
    public static function userOptionPrefix(string $login): string
    {
        return self::THROTTLE_OPTION_PREFIX . md5($login) . '.';
    }

    public static function throttleOption(string $login, string $topic): string
    {
        return self::userOptionPrefix($login) . $topic;
    }

    /** Option::deleteLike pattern for a deleted user: all its throttles (kit pattern, also older key formats) */
    public static function userOptionPattern(string $login): string
    {
        return self::THROTTLE_OPTION_PREFIX . md5($login) . '%';
    }

    /** votes this Matomo sends to the store per minute at most, all users and questions together */
    public const SERVER_CAP = 20;
    public const SERVER_OPTION = self::THROTTLE_OPTION_PREFIX . 'server';

    /**
     * Counts a vote against the server's cap. $stored is the SERVER_OPTION value ("minute:count").
     *
     * @return string|null the new value to store, null when the cap of this minute is reached
     */
    public static function countVote(?string $stored, int $now, int $cap = self::SERVER_CAP): ?string
    {
        $minute = intdiv($now, 60);
        [$was, $count] = array_map('intval', explode(':', (string) $stored) + [0, 0]);
        if ($was !== $minute) {
            $count = 0;
        }
        return $count < $cap ? $minute . ':' . ($count + 1) : null;
    }

    /** @var callable Http::sendHttpRequestBy or a test double with the same arguments */
    private $transport;
    /** @var bool|null null: Matomo's PIWIK_TEST_MODE decides */
    private $testMode;

    /**
     * @param bool|null $testMode only tests with a recording transport pass false; null detects PIWIK_TEST_MODE
     */
    public function __construct(?callable $transport = null, ?bool $testMode = null)
    {
        $this->transport = $transport ?? [Http::class, 'sendHttpRequestBy'];
        $this->testMode = $testMode;
    }

    /** Whether to ask and send: the config switch, Matomo's internet features and never from a test run. */
    public function isEnabled(): bool
    {
        return self::enabledBy(self::configSection(), SettingsPiwik::isInternetEnabled(), $this->testMode ?? self::inTestMode());
    }

    /** @return array<string, ?string> the request body of the store contract (unknown browser or language: null) */
    public static function payload(string $topic, string $vote, string $pluginVersion, string $matomoVersion, string $userAgent, string $language): array
    {
        if (!in_array($topic, self::TOPICS, true)) {
            throw new \InvalidArgumentException('topic');
        }
        if (!in_array($vote, ['up', 'down'], true)) {
            throw new \InvalidArgumentException('vote');
        }
        $browser = self::browser($userAgent);
        $language = mb_substr((string) preg_replace('/[^A-Za-z_-]/', '', $language), 0, 10);
        return [
            'plugin' => self::PLUGIN,
            'plugin_version' => mb_substr($pluginVersion, 0, 30),
            'matomo_version' => mb_substr($matomoVersion, 0, 30),
            'topic' => $topic,
            'vote' => $vote,
            'browser' => $browser !== '' ? $browser : null,
            'language' => $language !== '' ? $language : null,
        ];
    }

    /** Browser family and major version ("Chrome 141"), empty when unknown. Order matters: Edge and Opera also say Chrome. */
    public static function browser(string $userAgent): string
    {
        $patterns = [
            'Edge' => '/\bEdg(?:e|A|iOS)?\/(\d+)/',
            'Opera' => '/\bOPR\/(\d+)/',
            'Firefox' => '/\bFirefox\/(\d+)/',
            'Chrome' => '/\b(?:Chrome|CriOS)\/(\d+)/',
            'Safari' => '/\bVersion\/(\d+)[.\d]* (?:Mobile\/\S+ )?Safari\//',
        ];
        foreach ($patterns as $family => $pattern) {
            if (preg_match($pattern, $userAgent, $m)) {
                return $family . ' ' . $m[1];
            }
        }
        return '';
    }

    /** The Feinwerk contact form with the topic "Plugins" preselected. */
    public static function supportUrl(string $language): string
    {
        $page = $language === 'de' ? 'de/kontakt' : 'en/contact';
        return 'https://feinwerk.io/' . $page . '?service=plugins&mtm_campaign=plugin-support&mtm_source=' . self::PLUGIN;
    }

    /**
     * What the feedback bar under the result needs: whether to ask (off: only the support link), the support link in the
     * user's language and the plugin version (the browser remembers a vote per version).
     *
     * @return array{enabled: bool, supportUrl: string, version: string}
     */
    public static function clientProps(bool $enabled, string $language, string $version): array
    {
        return ['enabled' => $enabled, 'supportUrl' => self::supportUrl($language), 'version' => $version];
    }

    public static function enabled(): bool
    {
        return self::enabledBy(self::configSection(), SettingsPiwik::isInternetEnabled(), self::inTestMode());
    }

    /**
     * @param array<string, mixed> $section the plugin's config.ini section
     * @param bool $internetEnabled Matomo's [General] enable_internet_features: its HTTP client does not check it, so
     *                              with it off nothing may be sent
     * @param bool $testMode Matomo's PIWIK_TEST_MODE: test runs (sandboxes, CI) never reach the store
     */
    public static function enabledBy(array $section, bool $internetEnabled = true, bool $testMode = false): bool
    {
        if (!$internetEnabled || $testMode) {
            return false;
        }
        if (!array_key_exists('feedback', $section)) {
            return true;
        }
        // Matomo's INI reader turns off, no and false into PHP false; an empty value counts as off as well
        $value = $section['feedback'];
        return $value !== false && $value !== null
            && !in_array(strtolower(trim((string) $value)), ['0', '', 'off', 'no', 'false'], true);
    }

    /** @return array<string, mixed> */
    private static function configSection(): array
    {
        $section = Config::getInstance()->{self::PLUGIN};
        return is_array($section) ? $section : [];
    }

    private static function inTestMode(): bool
    {
        return defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE;
    }

    /** The store the Marketplace Connector talks to (a test store too), else Feinwerk's store. */
    public static function endpoint(): string
    {
        $factory = '\Piwik\Plugins\FeinwerkMarketplaceConnector\MarketplaceClientFactory';
        $base = '';
        try {
            if (class_exists($factory) && method_exists($factory, 'url')) {
                $base = (string) $factory::url();
            }
        } catch (\Throwable $e) {
            $base = '';
        }
        return rtrim($base !== '' ? $base : self::DEFAULT_STORE, '/') . '/api/v1/feedback';
    }

    /** Sends the vote; failures (store unreachable, endpoint missing) are logged and never shown to the user. */
    public function send(array $payload): bool
    {
        // Matomo's client also copies an incoming proxy chain (HTTP_VIA) into its Via header: leave it out.
        $via = $_SERVER['HTTP_VIA'] ?? null;
        unset($_SERVER['HTTP_VIA']);
        try {
            $response = ($this->transport)(
                Http::getTransportMethod(),
                self::endpoint(),
                self::TIMEOUT,
                self::USER_AGENT,
                null,
                null,
                0,
                false,
                false,
                false,
                true,
                'POST',
                null,
                null,
                json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ['Content-Type: application/json', 'Accept: application/json']
            );
        } catch (\Exception $e) {
            self::log('feedback not sent: {message}', ['message' => $e->getMessage()]);
            return false;
        } finally {
            if ($via !== null) {
                $_SERVER['HTTP_VIA'] = $via;
            }
        }
        $status = (int) ($response['status'] ?? 0);
        if ($status < 200 || $status >= 300) {
            self::log('feedback not accepted (HTTP {status})', ['status' => $status]);
            return false;
        }
        return true;
    }

    /** Info only: a vote that did not arrive is no error for the user. */
    private static function log(string $message, array $context): void
    {
        try {
            StaticContainer::get(LoggerInterface::class)->info(self::PLUGIN . ': ' . $message, $context);
        } catch (\Throwable $e) {
            // no container (unit tests) or no logger: nothing to report to
        }
    }
}
