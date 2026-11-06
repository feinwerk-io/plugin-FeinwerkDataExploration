<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Config;
use Piwik\NoAccessException;
use Piwik\Option;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Feedback\PluginFeedback;
use Piwik\Plugins\UsersManager\API as UsersManagerApi;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Version;

/**
 * The feedback vote under the result: sent by the Matomo server through the plugin's API, only the contract fields,
 * at most one vote per user and question every 10 seconds, switched off with feedback = 0. Votes go to a recording
 * transport here, never to the store.
 *
 * @group FeinwerkDataExploration
 */
class ApiFeedbackTest extends IntegrationTestCase
{
    /** @var array<int, array> request bodies the "store" received */
    public static array $sent = [];

    public function provideContainerConfig()
    {
        return [
            'Piwik\\Access' => new FakeAccess(),
            // test mode off for this recording instance only: every other PluginFeedback stays off in tests
            PluginFeedback::class => new PluginFeedback(function (...$args) {
                self::$sent[] = json_decode((string) $args[14], true);
                return ['status' => 202, 'data' => '{"ok":true}'];
            }, false),
        ];
    }

    public function setUp(): void
    {
        parent::setUp();
        self::$sent = [];
        FakeAccess::$superUser = true;
        Fixture::createWebsite('2026-01-01 00:00:00');
        Fixture::createWebsite('2026-01-01 00:00:00');
        FakeAccess::clearAccess(false, [], [1], 'anna');
    }

    public function testAVoteIsSentWithTheContractFieldsOnly(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; Linux x86_64; rv:143.0) Gecko/20100101 Firefox/143.0';

        $this->assertSame(['sent' => true], API::getInstance()->sendFeedback(1, 'free_form_table', 'down'));

        $this->assertCount(1, self::$sent);
        $vote = self::$sent[0];
        $this->assertSame(['plugin', 'plugin_version', 'matomo_version', 'topic', 'vote', 'browser', 'language'], array_keys($vote));
        $this->assertSame('FeinwerkDataExploration', $vote['plugin']);
        $this->assertSame(Version::VERSION, $vote['matomo_version']);
        $this->assertSame('free_form_table', $vote['topic']);
        $this->assertSame('down', $vote['vote']);
        $this->assertSame('Firefox 143', $vote['browser']);
        unset($_SERVER['HTTP_USER_AGENT']);
    }

    public function testASecondVoteWithinTenSecondsIsNotSent(): void
    {
        API::getInstance()->sendFeedback(1, 'free_form_table', 'up');

        $this->assertSame(['sent' => false], API::getInstance()->sendFeedback(1, 'free_form_table', 'down'));
        $this->assertCount(1, self::$sent);
    }

    public function testNothingIsSentWhenSwitchedOff(): void
    {
        // off, no and false arrive from config.ini.php as PHP false
        foreach (['0', false, 'off', ''] as $off) {
            Config::getInstance()->FeinwerkDataExploration = ['feedback' => $off];
            $this->assertSame(['sent' => false], API::getInstance()->sendFeedback(1, 'free_form_table', 'up'), var_export($off, true));
        }
        $this->assertSame([], self::$sent);
    }

    public function testNothingIsSentFromATestRunByDefault(): void
    {
        $this->assertFalse((new PluginFeedback())->isEnabled(), 'PIWIK_TEST_MODE');
        $this->assertFalse((new PluginFeedback(fn (...$args) => ['status' => 202]))->isEnabled());
    }

    public function testAVoteCountsAgainstTheServersCap(): void
    {
        $this->awayFromAMinuteBoundary();
        API::getInstance()->sendFeedback(1, 'free_form_table', 'up');

        $this->assertSame(intdiv(time(), 60) . ':1', Option::get(PluginFeedback::SERVER_OPTION));
    }

    public function testTheTwentyFirstVoteOfAMinuteIsNotSent(): void
    {
        $this->awayFromAMinuteBoundary();
        Option::set(PluginFeedback::SERVER_OPTION, intdiv(time(), 60) . ':20');

        $this->assertSame(['sent' => false], API::getInstance()->sendFeedback(1, 'free_form_table', 'up'));
        $this->assertSame([], self::$sent);
        $this->assertFalse(Option::get(PluginFeedback::throttleOption('anna', 'free_form_table')), 'nothing stored without a send');
    }

    private function awayFromAMinuteBoundary(): void
    {
        if (time() % 60 >= 57) {
            sleep(4);
        }
    }

    public function testOnlyTheQuestionsAndVotesOfThePlugin(): void
    {
        try {
            API::getInstance()->sendFeedback(1, 'pricing', 'up');
            $this->fail('an unknown topic was accepted');
        } catch (ExplorationException $e) {
            $this->assertSame('FeinwerkDataExploration_ErrorFeedbackValue', $e->getTranslationKey());
        }
        $this->assertSame([], self::$sent);
    }

    public function testAnonymousUsersCannotVote(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'anonymous');
        $this->expectException(NoAccessException::class);
        API::getInstance()->sendFeedback(1, 'free_form_table', 'up');
    }

    public function testUsersWithoutAccessToTheSiteCannotVote(): void
    {
        $this->expectException(NoAccessException::class);
        API::getInstance()->sendFeedback(2, 'free_form_table', 'up');
    }

    public function testDeletingAUserRemovesTheirVoteTimes(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'feedbackvoter');
        API::getInstance()->sendFeedback(1, 'free_form_table', 'up');
        $option = PluginFeedback::throttleOption('feedbackvoter', 'free_form_table');
        $this->assertNotFalse(Option::get($option));

        FakeAccess::$superUser = true;
        // Matomo 6 counts the signed-in login as existing: act as another user
        FakeAccess::$identity = 'superUserLogin';
        // Matomo deletes a user only while a super user is left
        Fixture::createSuperUser(false);
        UsersManagerApi::getInstance()->addUser('feedbackvoter', 'secret-password-1', 'voter@example.org');
        UsersManagerApi::getInstance()->deleteUser('feedbackvoter');

        $this->assertFalse(Option::get($option));
    }
}
