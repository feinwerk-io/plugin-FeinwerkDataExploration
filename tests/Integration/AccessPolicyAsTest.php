<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Plugins\FeinwerkDataExploration\Engine\AccessPolicy;
use Piwik\Plugins\FeinwerkDataExploration\SystemSettings;
use Piwik\Plugins\UsersManager\API as UsersManagerApi;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Saved reports (2026-11-24): whether another user (a scheduled report's owner) may use explorations on a website,
 * by the plugin's minimum role.
 *
 * @group FeinwerkDataExploration
 */
class AccessPolicyAsTest extends IntegrationTestCase
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
        $users = UsersManagerApi::getInstance();
        foreach (['viewer' => 'view', 'writer' => 'write', 'manager' => 'admin'] as $login => $role) {
            $users->addUser($login, 'secret-password-1', $login . '@example.com');
            $users->setUserAccess($login, $role, [1]);
        }
        $users->addUser('boss', 'secret-password-1', 'boss@example.com');
        // the model directly: the API asks for the current user's password
        (new \Piwik\Plugins\UsersManager\Model())->setSuperUserAccess('boss', true);
    }

    public function testRolesAgainstTheSetting(): void
    {
        $expected = [
            'view' => ['viewer' => true, 'writer' => true, 'manager' => true, 'boss' => true, 'nobody' => false, 'anonymous' => false],
            'write' => ['viewer' => false, 'writer' => true, 'manager' => true, 'boss' => true],
            'admin' => ['viewer' => false, 'writer' => false, 'manager' => true, 'boss' => true],
            'superuser' => ['viewer' => false, 'manager' => false, 'boss' => true],
        ];
        foreach ($expected as $role => $users) {
            $settings = new SystemSettings();
            $settings->minimumRole->setValue($role);
            $settings->save();
            foreach ($users as $login => $allowed) {
                $this->assertSame($allowed, AccessPolicy::canUseAs($login, 1), $role . ': ' . $login);
            }
        }
        $this->assertFalse(AccessPolicy::canUseAs('viewer', 2), 'no access to website 2');
    }
}
