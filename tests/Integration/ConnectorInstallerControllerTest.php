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
use Piwik\Plugins\FeinwerkDataExploration\ConnectorInstaller\ConnectorInstaller;
use Piwik\Plugins\FeinwerkDataExploration\Controller;
use Piwik\Plugins\Login\PasswordVerifier;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Regressions from Campaign Score's review of the connector installer (2026-11-19; test shape from Conversions API):
 * the page opens without a website in the request (it ran setGeneralVariablesView(), which needs idSite), follows
 * Matomo's frame rule for admin pages, the click action sends a super user to the admin home when there is nothing to
 * offer, and it stops when Matomo's password check sends the user to its page.
 *
 * @group FeinwerkDataExploration
 */
class ConnectorInstallerControllerTest extends IntegrationTestCase
{
    /** @var array<string, mixed> */
    private $get = [];

    /** @var array<string, mixed> */
    private $general = [];

    public function provideContainerConfig()
    {
        return ['Piwik\\Access' => new FakeAccess()];
    }

    public function setUp(): void
    {
        parent::setUp();
        FakeAccess::$superUser = true;
        FakeAccess::$superUserLogin = 'superUserLogin';
        // a Matomo always has a website (its top menu needs one); the request names none
        Fixture::createWebsite('2026-01-01 00:00:00', 0, 'Shop', 'https://shop.example', 1, null, null, 'UTC');
        $this->get = $_GET;
        $this->general = Config::getInstance()->General;
        unset($_GET['idSite'], $_POST['idSite']);
        Config::getInstance()->FeinwerkDataExploration = [];
        Config::getInstance()->FeinwerkMarketplaceConnector = [];
    }

    public function tearDown(): void
    {
        $_GET = $this->get;
        unset($_GET['retry'], $_GET['nonce']);
        Config::getInstance()->General = $this->general;
        Config::getInstance()->FeinwerkDataExploration = [];
        Config::getInstance()->FeinwerkMarketplaceConnector = [];
        parent::tearDown();
    }

    public function test_the_page_opens_without_a_website(): void
    {
        $this->assertOffered();

        $html = (new Controller())->connectorInstaller();

        self::assertStringContainsString('class="card fwci"', $html);
    }

    public function test_the_page_follows_matomos_frame_rule_for_admin_pages(): void
    {
        $this->assertOffered();
        $general = Config::getInstance()->General;
        // report pages may be framed, settings pages not: the installer is a settings page
        $general['enable_framed_pages'] = 1;
        $general['enable_framed_settings'] = 0;
        Config::getInstance()->General = $general;

        $html = (new Controller())->connectorInstaller();

        self::assertStringContainsString('top.location = self.location', $html, "the frame guard of Matomo's admin pages");
    }

    public function test_the_click_leads_to_the_admin_home_when_there_is_nothing_to_offer(): void
    {
        Config::getInstance()->FeinwerkDataExploration = [ConnectorInstaller::SWITCH => '0'];
        self::assertSame(ConnectorInstaller::HIDDEN, ConnectorInstaller::decide(ConnectorInstaller::environment()));

        // in CLI mode Matomo throws instead of sending the redirect
        $this->expectExceptionMessage('index.php?module=CoreAdminHome&action=home');

        (new Controller())->connectorInstallerRun();
    }

    public function test_the_click_stops_when_the_password_check_sends_the_user_to_its_page(): void
    {
        $state = ConnectorInstaller::decide(ConnectorInstaller::environment());
        if ($state !== ConnectorInstaller::INSTALL && $state !== ConnectorInstaller::ACTIVATE) {
            self::markTestSkipped('this Matomo offers no one-click install (state ' . $state . ')');
        }
        $verifier = $this->createMock(PasswordVerifier::class);
        // Matomo has sent its password page; true would mean "verified recently"
        $verifier->method('requirePasswordVerifiedRecently')->willReturn(null);
        StaticContainer::getContainer()->set(PasswordVerifier::class, $verifier);

        self::assertSame('', (new Controller())->connectorInstallerRun());
    }

    /**
     * Regression (Auth SAML, 2026-11-23): right after Matomo's password step the old nonce can fail Matomo's referrer
     * check. Nothing is installed; the page comes back with a fresh button and a note instead of an error page.
     */
    public function test_a_stale_click_brings_the_page_back_with_a_fresh_button(): void
    {
        $state = ConnectorInstaller::decide(ConnectorInstaller::environment());
        if ($state !== ConnectorInstaller::INSTALL && $state !== ConnectorInstaller::ACTIVATE) {
            self::markTestSkipped('this Matomo offers no one-click install (state ' . $state . ')');
        }
        $verifier = $this->createMock(PasswordVerifier::class);
        $verifier->method('requirePasswordVerifiedRecently')->willReturn(true);
        StaticContainer::getContainer()->set(PasswordVerifier::class, $verifier);
        $_GET['nonce'] = 'not-the-nonce';

        $html = (new Controller())->connectorInstallerRun();

        self::assertStringContainsString('fwci__again', $html);
        self::assertStringContainsString('fwci__run', $html);
        self::assertStringNotContainsString('alert-danger fwci__error', $html);
    }

    /** A second failure in a row has a lasting cause (proxy, trusted host, origin): Matomo's reason, no loop. */
    public function test_a_second_stale_click_in_a_row_shows_matomos_reason(): void
    {
        $state = ConnectorInstaller::decide(ConnectorInstaller::environment());
        if ($state !== ConnectorInstaller::INSTALL && $state !== ConnectorInstaller::ACTIVATE) {
            self::markTestSkipped('this Matomo offers no one-click install (state ' . $state . ')');
        }
        $verifier = $this->createMock(PasswordVerifier::class);
        $verifier->method('requirePasswordVerifiedRecently')->willReturn(true);
        StaticContainer::getContainer()->set(PasswordVerifier::class, $verifier);
        $_GET['nonce'] = 'not-the-nonce';
        $_GET['retry'] = '1';

        $html = (new Controller())->connectorInstallerRun();

        self::assertStringContainsString('fwci__error', $html);
        self::assertStringNotContainsString('fwci__again', $html);
    }

    public function test_the_page_names_its_language(): void
    {
        $this->assertOffered();

        $html = (new Controller())->connectorInstaller();

        // preg_match: PHPUnit 8.5 (5.x line) has no assertMatchesRegularExpression
        self::assertSame(1, preg_match('/<html[^>]* lang="[a-z]{2}/', $html), 'html lang');
    }

    private function assertOffered(): void
    {
        self::assertNotSame(
            ConnectorInstaller::HIDDEN,
            ConnectorInstaller::decide(ConnectorInstaller::environment()),
            'precondition: a super user, the installer switched on, the connector not active'
        );
    }
}
