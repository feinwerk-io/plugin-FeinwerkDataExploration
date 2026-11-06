<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Feedback;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Feedback\PluginFeedback;

/**
 * Feedback thumbs under the exploration's result: what goes to Feinwerk (nothing personal) and where the support link
 * leads (Feinwerk feedback kit).
 *
 * @group FeinwerkDataExploration
 */
class PluginFeedbackTest extends TestCase
{
    public function testPayloadFollowsTheStoreContract(): void
    {
        $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36';
        $this->assertSame([
            'plugin' => 'FeinwerkDataExploration',
            'plugin_version' => '6.0.0-beta.1',
            'matomo_version' => '6.0.0-rc1',
            'topic' => 'free_form_table',
            'vote' => 'down',
            'browser' => 'Chrome 141',
            'language' => 'de',
        ], PluginFeedback::payload('free_form_table', 'down', '6.0.0-beta.1', '6.0.0-rc1', $ua, 'de'));
    }

    public function testUnknownBrowserAndLanguageAreNull(): void
    {
        $payload = PluginFeedback::payload('free_form_table', 'up', '5.0.0', '5.14.0', 'curl/8.5.0', '');
        $this->assertNull($payload['browser']);
        $this->assertNull($payload['language']);
    }

    public function testPluginNameAndTopicMatchTheStorePatterns(): void
    {
        $payload = PluginFeedback::payload('free_form_table', 'up', '1', '6', '', 'en');
        $this->assertSame(1, preg_match('/^Feinwerk[A-Za-z0-9]{3,52}$/', $payload['plugin']));
        $this->assertSame(1, preg_match('/^[a-z_]{1,30}$/', $payload['topic']));
    }

    public function testSendsNoBrowserDetailsInHeaders(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0) Chrome/141.0.0.0 Safari/537.36';
        $_SERVER['HTTP_VIA'] = '1.1 corporate-proxy.example';
        $seen = [];
        $transport = function (...$args) use (&$seen) {
            $seen = ['url' => $args[1], 'timeout' => $args[2], 'userAgent' => $args[3], 'via' => $_SERVER['HTTP_VIA'] ?? null,
                'body' => $args[14], 'headers' => $args[15]];
            return ['status' => 202, 'data' => '{"ok":true}'];
        };
        $payload = PluginFeedback::payload('free_form_table', 'up', '6.0.0-beta.1', '6.0.0-rc1', '', 'en');

        $this->assertTrue((new PluginFeedback($transport))->send($payload));
        $this->assertSame(PluginFeedback::USER_AGENT, $seen['userAgent']);
        $this->assertNull($seen['via'], 'the incoming proxy chain must not go into the Via header');
        $this->assertSame('1.1 corporate-proxy.example', $_SERVER['HTTP_VIA'], 'restored after the request');
        $this->assertSame(3, $seen['timeout']);
        $this->assertStringEndsWith('/api/v1/feedback', $seen['url']);
        $this->assertSame($payload, json_decode($seen['body'], true));
        $this->assertContains('Content-Type: application/json', $seen['headers']);
        unset($_SERVER['HTTP_USER_AGENT'], $_SERVER['HTTP_VIA']);
    }

    public function testOnlyUpOrDown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PluginFeedback::payload('free_form_table', 'maybe', '1.0.0', '6.0.0', '', 'en');
    }

    public function testOnlyTheQuestionThePluginAsks(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PluginFeedback::payload('preview', 'up', '1.0.0', '6.0.0', '', 'en');
    }

    public function testBrowserIsFamilyAndMajorVersionOnly(): void
    {
        $this->assertSame('Edge 140', PluginFeedback::browser('Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.3485.54'));
        $this->assertSame('Firefox 143', PluginFeedback::browser('Mozilla/5.0 (X11; Linux x86_64; rv:143.0) Gecko/20100101 Firefox/143.0'));
        $this->assertSame('Safari 18', PluginFeedback::browser('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Safari/605.1.15'));
        $this->assertSame('Opera 122', PluginFeedback::browser('Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 OPR/122.0.0.0'));
        $this->assertSame('', PluginFeedback::browser('curl/8.5.0'));
    }

    public function testSupportOpensTheContactFormWithThePluginsTopic(): void
    {
        $this->assertSame(
            'https://feinwerk.io/de/kontakt?service=plugins&mtm_campaign=plugin-support&mtm_source=FeinwerkDataExploration',
            PluginFeedback::supportUrl('de')
        );
        $this->assertStringStartsWith('https://feinwerk.io/en/contact?service=plugins', PluginFeedback::supportUrl('en'));
        $this->assertStringStartsWith('https://feinwerk.io/en/contact?service=plugins', PluginFeedback::supportUrl('fr'));
    }

    public function testFeedbackCanBeSwitchedOffInTheConfigAndByMatomosInternetSwitch(): void
    {
        $this->assertTrue(PluginFeedback::enabledBy([]));
        $this->assertTrue(PluginFeedback::enabledBy(['feedback' => '1']));
        $this->assertFalse(PluginFeedback::enabledBy(['feedback' => '0']));
        // [General] enable_internet_features = 0: Matomo's HTTP client does not check it, so the plugin must
        $this->assertFalse(PluginFeedback::enabledBy([], false));
        $this->assertFalse(PluginFeedback::enabledBy(['feedback' => '1'], false));
    }

    public function testEveryWayOfWritingOffSwitchesItOff(): void
    {
        // Matomo's INI reader turns off, no and false into PHP false, and (string) false is ''
        foreach ([false, null, '0', '', 'off', 'no', 'false', 'OFF', ' 0 '] as $off) {
            $this->assertFalse(PluginFeedback::enabledBy(['feedback' => $off]), var_export($off, true));
        }
        foreach ([true, '1', 'on', 'yes'] as $on) {
            $this->assertTrue(PluginFeedback::enabledBy(['feedback' => $on]), var_export($on, true));
        }
    }

    public function testNothingIsSentInMatomosTestMode(): void
    {
        $this->assertFalse(PluginFeedback::enabledBy([], true, true));
        $this->assertFalse(PluginFeedback::enabledBy(['feedback' => '1'], true, true));
    }

    public function testAServerSendsAtMostTwentyVotesAMinute(): void
    {
        $stored = null;
        for ($i = 0; $i < 20; $i++) {
            $stored = PluginFeedback::countVote($stored, 960 + $i);
            $this->assertNotNull($stored, "vote $i");
        }
        $this->assertSame('16:20', $stored);
        $this->assertNull(PluginFeedback::countVote($stored, 1019), 'the 21st vote in the same minute');
        $this->assertSame('17:1', PluginFeedback::countVote($stored, 1020), 'the next minute');
        $this->assertSame('17:1', PluginFeedback::countVote('garbage', 1020));
    }

    public function testTheServerCountIsNoUsersThrottle(): void
    {
        $like = '/^' . str_replace('%', '.*', preg_quote(PluginFeedback::userOptionPattern('anna'), '/')) . '$/';
        $this->assertSame(0, preg_match($like, PluginFeedback::SERVER_OPTION));
        $this->assertStringStartsWith(PluginFeedback::THROTTLE_OPTION_PREFIX, PluginFeedback::SERVER_OPTION, 'removed on uninstall');
    }

    public function testDeletingAUserCatchesItsThrottlesOnly(): void
    {
        // the LIKE pattern FeinwerkDataExploration::deleteUser() deletes with
        $like = '/^' . str_replace('%', '.*', preg_quote(PluginFeedback::userOptionPattern('anna'), '/')) . '$/';
        $this->assertSame(1, preg_match($like, PluginFeedback::throttleOption('anna', 'free_form_table')));
        $this->assertSame(0, preg_match($like, PluginFeedback::throttleOption('bert', 'free_form_table')));
    }

    public function testEachUserAndQuestionHasItsOwnThrottle(): void
    {
        $anna = PluginFeedback::throttleOption('anna', 'free_form_table');
        $this->assertNotSame($anna, PluginFeedback::throttleOption('bert', 'free_form_table'));
        $this->assertStringStartsWith(PluginFeedback::userOptionPrefix('anna'), $anna, 'deleted with the user');
        $this->assertStringStartsWith('fw_dataexploration.feedback.', $anna);
    }

    public function testThePageGetsTheSwitchTheSupportLinkAndTheVersion(): void
    {
        $this->assertSame([
            'enabled' => true,
            'supportUrl' => PluginFeedback::supportUrl('de'),
            'version' => '5.0.0',
        ], PluginFeedback::clientProps(true, 'de', '5.0.0'));
        // switched off: no question, but the support link stays
        $this->assertSame(['enabled' => false, 'supportUrl' => PluginFeedback::supportUrl('en'), 'version' => '5.0.0'],
            PluginFeedback::clientProps(false, 'en', '5.0.0'));
    }

    public function testAFailingStoreIsNotAnError(): void
    {
        $refused = new PluginFeedback(fn (...$args) => ['status' => 422, 'data' => '{}']);
        $this->assertFalse($refused->send(['plugin' => 'FeinwerkDataExploration']));
    }
}
