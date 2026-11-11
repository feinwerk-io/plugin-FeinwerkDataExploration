<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Feedback\PluginFeedback;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * No vote ever leaves a test run (PIWIK_TEST_MODE: CI, sandboxes, these tests): the class itself refuses, whatever
 * the caller does. Unlike ApiFeedbackTest, nothing here replaces the container's instance.
 *
 * @group FeinwerkDataExploration
 */
class FeedbackTestModeTest extends IntegrationTestCase
{
    public function provideContainerConfig()
    {
        return ['Piwik\\Access' => new FakeAccess()];
    }

    public function setUp(): void
    {
        parent::setUp();
        FakeAccess::$superUser = true;
        Fixture::createWebsite('2026-01-01 00:00:00');
        FakeAccess::clearAccess(false, [], [1], 'anna');
    }

    public function testTheContainersInstanceStaysOffInTestMode(): void
    {
        $this->assertFalse(StaticContainer::get(PluginFeedback::class)->isEnabled());
        $this->assertSame(['sent' => false], API::getInstance()->sendFeedback(1, 'free_form_table', 'up'));
    }

    public function testTestModeIsOnlySwitchedOffTogetherWithARecordingTransport(): void
    {
        $this->assertFalse((new PluginFeedback(null, false))->isEnabled(), 'the real HTTP client stays off');
        $this->assertTrue((new PluginFeedback(fn (...$args) => ['status' => 202], false))->isEnabled());
    }

    public function testTheRealHttpClientNeverRunsInTestMode(): void
    {
        // A local socket stands in as Matomo's HTTP proxy: a request that tried to leave would connect here (and fail)
        // instead of reaching the store.
        $proxy = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        $this->assertNotFalse($proxy, $errstr);
        $port = substr((string) strrchr((string) stream_socket_get_name($proxy, false), ':'), 1);
        Config::getInstance()->proxy = ['host' => '127.0.0.1', 'port' => $port, 'username' => '', 'password' => '', 'exclude' => ''];
        $payload = PluginFeedback::payload('free_form_table', 'up', '6.0.0', '6.0.0-rc1', '', 'en');

        $this->assertFalse((new PluginFeedback())->send($payload));
        $this->assertFalse(@stream_socket_accept($proxy, 0), 'the vote tried to leave the test run');
        fclose($proxy);
    }
}
