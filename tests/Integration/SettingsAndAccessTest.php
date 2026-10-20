<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Cache;
use Piwik\Date;
use Piwik\NoAccessException;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\LimitsProvider;
use Piwik\Plugins\FeinwerkDataExploration\Engine\RawDataRetention;
use Piwik\Plugins\FeinwerkDataExploration\SystemSettings;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Common;
use Piwik\Db;
use Piwik\Site;
use Piwik\Plugins\FeinwerkDataExploration\Engine\AccessPolicy;
use Piwik\Piwik;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group FeinwerkDataExploration
 */
class SettingsAndAccessTest extends IntegrationTestCase
{
    private const DEFINITION = '{"dimensions": ["browser"], "metrics": ["nb_visits"]}';

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->extraTestEnvVars['loadRealTranslations'] = true;
    }

    public function provideContainerConfig()
    {
        return ['Piwik\\Access' => new FakeAccess()];
    }

    public function setUp(): void
    {
        parent::setUp();
        Fixture::loadAllTranslations();
        FakeAccess::$superUser = true;
        Cache::getLazyCache()->flushAll();
        Fixture::createWebsite('2026-01-01 00:00:00', 0, 'Shop', 'https://example.com', 1, null, null, 'UTC');
    }

    public function testRollUpSitesHaveNoExplorations(): void
    {
        // a roll-up site has no raw data of its own (RollUpReporting)
        Db::query('UPDATE ' . Common::prefixTable('site') . " SET type = 'rollup' WHERE idsite = 1");
        Site::clearCache();
        Cache::getTransientCache()->flushAll();

        $this->assertFalse(AccessPolicy::canUse(1));
        try {
            API::getInstance()->getExploration(1, 'day', '2026-03-27', self::DEFINITION);
            $this->fail('a roll-up site must be refused');
        } catch (ExplorationException $e) {
            $this->assertSame('FeinwerkDataExploration_ErrorRollUp', $e->getTranslationKey());
        }
    }

    public function testComparisonModeIsOffOnTheExplorationPage(): void
    {
        $pages = \Piwik\Plugins\API\API::getInstance()->getPagesComparisonsDisabledFor();

        $this->assertContains('FeinwerkDataExploration_Explorations.FeinwerkDataExploration_FreeFormTable', $pages);
    }

    public function testGeneralSettingsShowTheNameWithSpaces(): void
    {
        // the section heading in Administration > General settings, which texts refer to
        $this->assertSame('Feinwerk Data Exploration', (new SystemSettings())->getTitle());
    }

    public function testSettingsExplainLimitsAndTheMissingReplica(): void
    {
        $settings = new SystemSettings();

        // the harness has no [database_reader]
        $this->assertFalse(Db::hasReaderConfigured());
        $this->assertSame(Piwik::translate('FeinwerkDataExploration_SettingUseReaderNoReaderHelp'), $settings->useReader->configureField()->inlineHelp);
        $this->assertSame(Piwik::translate('FeinwerkDataExploration_SettingsLimitsIntro'), $settings->maxDays->configureField()->introduction);
        $this->assertSame(Piwik::translate('FeinwerkDataExploration_SettingsDatabaseIntro'), $settings->useReader->configureField()->introduction);
    }

    public function testMinimumRoleWriteRefusesViewUser(): void
    {
        $this->setSetting('minimumRole', 'write');
        FakeAccess::clearAccess(false, [], [1], 'viewer');

        $this->expectException(NoAccessException::class);
        API::getInstance()->getExploration(1, 'day', '2026-03-27', self::DEFINITION);
    }

    public function testMinimumRoleWriteAllowsWriteUser(): void
    {
        $this->setSetting('minimumRole', 'write');
        FakeAccess::clearAccess(false, [], [1], 'writer', [1]);

        $this->assertSame(0, API::getInstance()->getExploration(1, 'day', '2026-03-27', self::DEFINITION)->getRowsCount());
    }

    public function testSuperuserRoleRefusesAdmin(): void
    {
        $this->setSetting('minimumRole', 'superuser');
        FakeAccess::clearAccess(false, [1], [1], 'siteadmin', [1]);

        $this->expectException(NoAccessException::class);
        API::getInstance()->getAvailableMetrics(1);
    }

    public function testMaxDaysSettingLimitsPeriod(): void
    {
        $this->setSetting('maxDays', 7);

        try {
            API::getInstance()->getExploration(1, 'range', '2026-03-01,2026-03-08', self::DEFINITION);
            $this->fail('8 days with a limit of 7');
        } catch (ExplorationException $e) {
            $this->assertSame('FeinwerkDataExploration_ErrorPeriodTooLong', $e->getTranslationKey());
            $this->assertSame([7], $e->getParams());
        }
        $this->assertSame(0, API::getInstance()->getExploration(1, 'range', '2026-03-01,2026-03-07', self::DEFINITION)->getRowsCount());
    }

    public function testMaxDaysAbove31IsRejectedBySetting(): void
    {
        $this->expectException(\Exception::class);
        $this->setSetting('maxDays', 32);
    }

    public function testTimeoutSettingReachesLimits(): void
    {
        $this->setSetting('timeoutSeconds', 7);
        $this->setSetting('maxRows', 250);
        $this->setSetting('useReader', false);

        $limits = (new LimitsProvider())->get();

        $this->assertSame(7, $limits->timeoutSeconds);
        $this->assertSame(250, $limits->maxRows);
        $this->assertSame(100, $limits->defaultRows);
        $this->assertFalse($limits->useReader);
    }

    public function testDefaultRowsFollowSmallMaxRows(): void
    {
        $this->setSetting('maxRows', 50);

        $this->assertSame(50, (new LimitsProvider())->get()->defaultRows);
    }

    public function testRawDataRetention(): void
    {
        $this->assertNull(RawDataRetention::earliestCompleteDay(1));

        // the API asks for the password again on Matomo 6; the settings are stored the same way
        PrivacyManager::savePurgeDataSettings(['delete_logs_enable' => 1, 'delete_logs_older_than' => 180]);

        $this->assertSame(Date::factory('today')->subDay(179)->toString(), RawDataRetention::earliestCompleteDay(1));
    }

    private function setSetting(string $name, $value): void
    {
        $wasSuperUser = FakeAccess::$superUser;
        FakeAccess::$superUser = true;
        try {
            $settings = new SystemSettings();
            $settings->$name->setValue($value);
            $settings->save();
        } finally {
            FakeAccess::$superUser = $wasSuperUser;
        }
    }
}
