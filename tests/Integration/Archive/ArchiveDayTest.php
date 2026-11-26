<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration\Archive;

use Piwik\Cache;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchiveRun;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchiveSettings;
use Piwik\Plugins\FeinwerkDataExploration\Archive\StoredMetrics;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationWindow;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Explorer;
use Piwik\Plugins\FeinwerkDataExploration\Engine\LazySlot;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Plugins\LanguagesManager\API as LanguagesApi;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Plugins\UsersManager\API as UsersManagerApi;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * One day of an exploration for the archive (design 2026-11-25): the same rows as the live engine, rates stored as
 * their numerator and denominator, label conditions on coded dimensions resolved in the owner's language.
 *
 * @group FeinwerkDataExploration
 */
class ArchiveDayTest extends IntegrationTestCase
{
    private const DAY = '2026-03-27';
    private const MONDAY = '2026-03-23';
    private const FIREFOX = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0';
    private const CHROME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    private const CHROME_MOBILE = 'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36';

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
        Fixture::createSuperUser(false);
        IPAnonymizer::deactivate();
        FakeAccess::$superUser = true;
        Cache::getLazyCache()->flushAll();
        Fixture::createWebsite('2026-01-01 00:00:00', 1, 'Shop', 'https://example.com', 1, null, null, 'UTC');
        GoalsApi::getInstance()->addGoal(1, 'Newsletter', 'manually', '', 'contains');

        $visits = [
            // user agent, time, pageviews, goal revenue (null: no conversion)
            [self::FIREFOX, self::DAY . ' 10:00:00', 1, null],
            [self::FIREFOX, self::DAY . ' 11:00:00', 3, 10],
            [self::CHROME_MOBILE, self::DAY . ' 12:00:00', 2, null],
            [self::CHROME, self::DAY . ' 13:00:00', 1, 5],
            [self::CHROME, self::MONDAY . ' 09:00:00', 2, null],
        ];
        foreach ($visits as $n => [$userAgent, $time, $pageviews, $revenue]) {
            $t = Fixture::getTracker(1, $time, true, true);
            $t->setNewVisitorId();
            $t->setIp('198.51.100.' . ($n + 1));
            $t->setUserAgent($userAgent);
            for ($p = 0; $p < $pageviews; $p++) {
                $t->setForceVisitDateTime(date('Y-m-d H:i:s', strtotime($time) + 60 * $p));
                $t->setUrl('https://example.com/page' . $p);
                Fixture::checkResponse($t->doTrackPageView('Page ' . $p));
            }
            if ($revenue !== null) {
                Fixture::checkResponse($t->doTrackGoal(1, $revenue));
            }
        }
    }

    public function testDayEqualsLive(): void
    {
        $metrics = ['nb_visits', 'bounce_rate', 'avg_time_on_site', 'conversion_rate', 'revenue', 'max_actions', 'nb_uniq_visitors'];
        $definition = $this->definition(['dimensions' => ['browser', 'deviceType'], 'metrics' => $metrics]);
        $explorer = $this->explorer();

        $archived = $explorer->archiveDay(1, self::DAY, $definition, '', ArchiveRun::forCli(new ArchiveSettings(), new Limits()));
        $live = $this->liveRows($explorer, $definition, self::DAY);

        $this->assertCount(count($live), $archived);
        $this->assertGreaterThanOrEqual(3, count($archived));
        foreach ($archived as $row) {
            $key = $row['fwde_k1'] . '|' . $row['fwde_k2'];
            $this->assertArrayHasKey($key, $live, $key);
            foreach ($metrics as $metric) {
                $this->assertEquals((float) $live[$key][$metric], (float) StoredMetrics::value($metric, $row, true), $key . ' ' . $metric);
            }
            $this->assertArrayHasKey('bounce_rate__n', $row);
            $this->assertArrayNotHasKey('bounce_rate', $row);
        }
    }

    public function testOwnersLanguage(): void
    {
        UsersManagerApi::getInstance()->addUser('anna', 'secret-password-1', 'anna@example.com');
        LanguagesApi::getInstance()->setLanguageForUser('anna', 'de');
        $definition = $this->definition(['dimensions' => ['weekday'], 'metrics' => ['nb_visits'],
            'filters' => [['target' => 'label', 'operator' => 'equals', 'value' => 'Montag']]]);
        $run = ArchiveRun::forCli(new ArchiveSettings(), new Limits());

        $rows = $run->withLanguage('anna', fn () => $this->explorer()->archiveDay(1, self::MONDAY, $definition, '', $run));
        $this->assertCount(1, $rows);
        $this->assertSame(1, (int) $rows[0]['nb_visits']);
        // the same condition read in English matches nothing: the owner's language decides
        $this->assertSame([], $this->explorer()->archiveDay(1, self::MONDAY, $definition, '', $run));
    }

    /** @return array<string, array<string, mixed>> the live engine's raw rows of a day by "key1|key2" */
    private function liveRows(Explorer $explorer, ExplorationDefinition $definition, string $day): array
    {
        $limits = new Limits();
        $dimensions = array_map(fn (string $id) => (new DimensionCatalog())->get(1, $id), $definition->dimensionIds);
        $slot = new LazySlot($limits);
        try {
            $rows = $explorer->fetchRows(1, ExplorationWindow::for(1, 'day', $day, $limits), $definition, $dimensions, '', $slot);
        } finally {
            $slot->release();
        }
        $byKey = [];
        foreach ($rows as $row) {
            $byKey[$row['fwde_k1'] . '|' . $row['fwde_k2']] = $row;
        }
        return $byKey;
    }

    private function explorer(): Explorer
    {
        return new Explorer(new DimensionCatalog(), new MetricCatalog(), new Limits());
    }

    private function definition(array $json): ExplorationDefinition
    {
        return ExplorationDefinition::fromJson((string) json_encode($json + ['limit' => 1000]), 1, new DimensionCatalog(), new MetricCatalog(), new Limits());
    }
}
