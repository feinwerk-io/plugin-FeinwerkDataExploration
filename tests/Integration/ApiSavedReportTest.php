<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Cache;
use Piwik\Piwik;
use Piwik\DataTable;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedReportUnavailable;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Explorer;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ReportLevels;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache as TrackerCache;

/**
 * Saved explorations as reports (2026-11-24) on the database. One website, one day; titles "Shoes" and "shoes" are one
 * group under the database's collation, and each combination carries one spelling only (Chrome "shoes", Firefox
 * "Shoes"). V1 Chrome: Home, goal 1; V2 Chrome: shoes; V3 Firefox: Home; V4 Firefox: Shoes.
 *
 * @group FeinwerkDataExploration
 */
class ApiSavedReportTest extends IntegrationTestCase
{
    private const DAY = '2026-03-27';
    private const CHROME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    private const FIREFOX = 'Mozilla/5.0 (X11; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0';
    private const VISITS = [
        [self::CHROME, [['/home', 'Home'], ['goal', 1]]],
        [self::CHROME, [['/shoes-2', 'shoes']]],
        [self::FIREFOX, [['/home', 'Home']]],
        [self::FIREFOX, [['/shoes', 'Shoes']]],
    ];

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
        Fixture::createWebsite('2026-01-01 00:00:00', 0, 'Shop', 'https://example.com', 1, null, null, 'UTC');
        GoalsApi::getInstance()->addGoal(1, 'Newsletter', 'manually', '', 'contains');
        // real users: a report's owner is checked against the database (AccessPolicy::canUseAs)
        foreach (['anna', 'bert'] as $login) {
            \Piwik\Plugins\UsersManager\API::getInstance()->addUser($login, 'secret-password-1', $login . '@example.com');
            \Piwik\Plugins\UsersManager\API::getInstance()->setUserAccess($login, 'write', [1]);
        }
        TrackerCache::deleteCacheWebsiteAttributes(1);
        TrackerCache::clearCacheGeneral();
        foreach (self::VISITS as $n => [$userAgent, $actions]) {
            $t = Fixture::getTracker(1, self::DAY . ' 1' . $n . ':00:00', true, true);
            $t->setVisitorId(sprintf('b%015x', $n + 1));
            $t->setIp('198.51.100.' . ($n + 1));
            $t->setUserAgent($userAgent);
            foreach ($actions as $i => [$path, $title]) {
                $t->setForceVisitDateTime(self::DAY . ' 1' . $n . ':0' . $i . ':00');
                if ($path === 'goal') {
                    Fixture::checkResponse($t->doTrackGoal($title, 5));
                } else {
                    $t->setUrl('https://example.com' . $path);
                    Fixture::checkResponse($t->doTrackPageView($title));
                }
            }
        }
    }

    public function testFirstLevelEqualsTheFirstDimensionAlone(): void
    {
        $tree = $this->tree('{"dimensions": ["pageTitle", "browser"], "metrics": ["nb_visits", "nb_pageviews"], "limit": 100}');
        $alone = $this->explorer()->explore(1, 'day', self::DAY, $this->definition('{"dimensions": ["pageTitle"],'
            . ' "metrics": ["nb_visits", "nb_pageviews"], "limit": 100}'), '');

        $expected = [];
        foreach ($alone->getRows() as $row) {
            $expected[strtolower($row->getColumn('label'))] = [$row->getColumn('nb_visits'), $row->getColumn('nb_pageviews')];
        }
        $first = [];
        foreach ($tree as $entry) {
            $first[strtolower($entry['label'])] = [$entry['columns']['nb_visits'], $entry['columns']['nb_pageviews']];
        }
        ksort($expected);
        ksort($first);
        $this->assertSame(['home' => [2, 2], 'shoes' => [2, 2]], $expected);
        $this->assertSame($expected, $first);
    }

    /** review focus 2: "Shoes" and "shoes" are one first value, every combination of it under it */
    public function testCaseVariantsOfAFirstValueStayTogether(): void
    {
        $tree = $this->tree('{"dimensions": ["pageTitle", "browser"], "metrics": ["nb_visits"], "limit": 100}');

        $this->assertCount(2, $tree);
        $rowCount = 0;
        foreach ($tree as $entry) {
            $this->assertNotNull($entry['columns']['nb_visits'], $entry['label']);
            $rowCount += count($entry['children']);
        }
        $this->assertSame(4, $rowCount, 'every combination under one first value');
        $shoes = array_values(array_filter($tree, fn ($entry) => strtolower($entry['label']) === 'shoes'))[0];
        $this->assertSame(['Chrome', 'Firefox'], self::labels($shoes['children']));
    }

    public function testFirstLevelKeepsASecondDimensionTextFilter(): void
    {
        $tree = $this->tree('{"dimensions": ["pageTitle", "browser"], "metrics": ["nb_visits"], "limit": 100,'
            . ' "filters": [{"target": "secondary_label", "operator": "contains", "value": "fire"}]}');

        $first = [];
        foreach ($tree as $entry) {
            $first[strtolower($entry['label'])] = $entry['columns']['nb_visits'];
        }
        ksort($first);
        $this->assertSame(['home' => 1, 'shoes' => 1], $first);
    }

    public function testOneDimensionEqualsTheExploration(): void
    {
        $id = $this->save('anna', ['fwdeDims' => 'pageTitle', 'fwdeMetrics' => 'nb_visits,nb_pageviews', 'fwdeLimit' => '100']);
        $exploration = API::getInstance()->getExploration(1, 'day', self::DAY, '{"dimensions": ["pageTitle"], "metrics":'
            . ' ["nb_visits", "nb_pageviews"], "limit": 100}');

        $this->assertSame($this->dump($exploration), $this->dump(API::getInstance()->getSavedReport(1, 'day', self::DAY, $id)));
    }

    public function testTwoDimensionsInTwoLevels(): void
    {
        $id = $this->save('anna', ['fwdeDims' => 'pageTitle,browser', 'fwdeMetrics' => 'nb_visits']);
        $top = API::getInstance()->getSavedReport(1, 'day', self::DAY, $id);

        $browsers = [];
        foreach ($top->getRows() as $row) {
            $this->assertArrayNotHasKey('secondary_label', $row->getColumns());
            $sub = API::getInstance()->getSavedReport(1, 'day', self::DAY, $id, '', (int) $row->getIdSubDataTable());
            $labels = array_map(fn ($subRow) => $subRow->getColumn('label'), $sub->getRows());
            sort($labels);
            $browsers[strtolower($row->getColumn('label'))] = [$row->getColumn('nb_visits'), $labels];
        }
        ksort($browsers);
        // review focus 2: "shoes" (Chrome) and "Shoes" (Firefox) are one first value with both combinations
        $this->assertSame(['home' => [2, ['Chrome', 'Firefox']], 'shoes' => [2, ['Chrome', 'Firefox']]], $browsers);
        $this->assertSame(0, API::getInstance()->getSavedReport(1, 'day', self::DAY, $id, '', 9)->getRowsCount());
    }

    /** final review: a sub table id names its first value, not its position, so a recalculated order keeps it */
    public function testSubtableIdsFollowTheFirstValue(): void
    {
        $asc = $this->save('anna', ['fwdeDims' => 'pageTitle,browser', 'fwdeMetrics' => 'nb_visits', 'fwdeSort' => 'label', 'fwdeOrder' => 'asc']);
        $desc = $this->save('anna', ['fwdeDims' => 'pageTitle,browser', 'fwdeMetrics' => 'nb_visits', 'fwdeSort' => 'label', 'fwdeOrder' => 'desc']);
        $idOf = function (int $idSaved): int {
            foreach (API::getInstance()->getSavedReport(1, 'day', self::DAY, $idSaved)->getRows() as $row) {
                if (strtolower($row->getColumn('label')) === 'home') {
                    return (int) $row->getIdSubDataTable();
                }
            }
            return 0;
        };

        $this->assertSame($idOf($asc), $idOf($desc));
        $labels = array_map(fn ($row) => $row->getColumn('label'), API::getInstance()->getSavedReport(1, 'day', self::DAY, $desc, '', $idOf($asc))->getRows());
        sort($labels);
        $this->assertSame(['Chrome', 'Firefox'], $labels);
    }

    /** rows and pivot (2026-11-24): three dimensions in three levels, each level with its totals */
    public function testThreeDimensionsInThreeLevels(): void
    {
        $id = $this->save('anna', ['fwdeDims' => 'pageTitle,browser,deviceType', 'fwdeMetrics' => 'nb_visits']);

        $desktop = ['Desktop' => 1];
        $this->assertSame([
            'home' => [2, ['Chrome' => [1, $desktop], 'Firefox' => [1, $desktop]]],
            'shoes' => [2, ['Chrome' => [1, $desktop], 'Firefox' => [1, $desktop]]],
        ], $this->walk($id, 0, 3));
    }

    public function testUpperLevelsKeepADeeperTextFilter(): void
    {
        $id = $this->save('anna', ['fwdeDims' => 'pageTitle,browser,deviceType', 'fwdeMetrics' => 'nb_visits',
            'fwdeFilters' => 'secondary_label~contains~fire']);

        $this->assertSame([
            'home' => [1, ['Firefox' => [1, ['Desktop' => 1]]]],
            'shoes' => [1, ['Firefox' => [1, ['Desktop' => 1]]]],
        ], $this->walk($id, 0, 3));
    }

    /** P2: the pivot as the last level: the exploration's top values under every row, then the exact rest */
    public function testPivotAsTheLastLevel(): void
    {
        $t = Fixture::getTracker(1, self::DAY . ' 20:00:00', true, true);
        $t->setVisitorId(sprintf('b%015x', 9));
        $t->setIp('198.51.100.9');
        $t->setUserAgent(self::FIREFOX);
        foreach (['/hats' => 'Hats', '/socks' => 'Socks'] as $path => $title) {
            $t->setUrl('https://example.com' . $path);
            Fixture::checkResponse($t->doTrackPageView($title));
        }
        $state = ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_pageviews', 'fwdePivot' => 'pageUrl', 'fwdePivotLimit' => '3'];
        $id = $this->save('anna', $state);
        $page = API::getInstance()->getExploration(1, 'day', self::DAY, '{"dimensions": ["browser"], "metrics": ["nb_pageviews"],'
            . ' "pivot": {"dimension": "pageUrl", "limit": 3}}');
        $values = array_column($page->getFirstRow()->getMetadata('fwde_pivot')['values'], 'label');
        $this->assertTrue($page->getFirstRow()->getMetadata('fwde_pivot')['others'], 'precondition: five pages, three shown');

        $expected = [];
        foreach ($page->getRows() as $row) {
            if ($row->getMetadata('fwde_total')) {
                continue;
            }
            $cells = [];
            foreach ($values as $n => $value) {
                $cells[$value] = $row->getColumn('nb_pageviews__p' . ($n + 1));
            }
            $cells[Piwik::translate('General_Others')] = $row->getColumn('nb_pageviews__p_others');
            ksort($cells);
            $expected[strtolower($row->getColumn('label'))] = [$row->getColumn('nb_pageviews'), $cells];
        }
        ksort($expected);
        $this->assertSame($expected, $this->walk($id, 0, 2));
    }

    /** a rest that is not exact for every metric gets no row (Matomo shows a missing number as 0) */
    public function testPivotLevelWithoutAnExactRest(): void
    {
        $id = $this->save('anna', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_pageviews,nb_visits', 'fwdePivot' => 'pageUrl', 'fwdePivotLimit' => '3']);
        $chrome = $this->walk($id, 0, 2)['chrome'] ?? null;
        $this->assertNotNull($chrome);
        $this->assertCount(3, $chrome[1], 'three pages of the fixture, nothing cut');

        $t = Fixture::getTracker(1, self::DAY . ' 20:00:00', true, true);
        $t->setVisitorId(sprintf('b%015x', 9));
        $t->setIp('198.51.100.9');
        $t->setUserAgent(self::CHROME);
        foreach (['/hats' => 'Hats', '/socks' => 'Socks'] as $path => $title) {
            $t->setUrl('https://example.com' . $path);
            Fixture::checkResponse($t->doTrackPageView($title));
        }
        Cache::getLazyCache()->flushAll();
        $this->assertNotContains(Piwik::translate('General_Others'), array_keys($this->walk($id, 0, 2)['chrome'][1]), 'visits do not add up across pages');
    }

    /** a sub table id names its value path, so a recalculated order keeps it at every depth */
    public function testSubtableIdsFollowTheValuePath(): void
    {
        $asc = $this->save('anna', ['fwdeDims' => 'pageTitle,browser,deviceType', 'fwdeMetrics' => 'nb_visits', 'fwdeSort' => 'label', 'fwdeOrder' => 'asc']);
        $desc = $this->save('anna', ['fwdeDims' => 'pageTitle,browser,deviceType', 'fwdeMetrics' => 'nb_visits', 'fwdeSort' => 'label', 'fwdeOrder' => 'desc']);
        $idOf = function (int $idSaved): int {
            foreach (API::getInstance()->getSavedReport(1, 'day', self::DAY, $idSaved)->getRows() as $row) {
                if (strtolower($row->getColumn('label')) === 'home') {
                    foreach (API::getInstance()->getSavedReport(1, 'day', self::DAY, $idSaved, '', (int) $row->getIdSubDataTable())->getRows() as $sub) {
                        if ($sub->getColumn('label') === 'Firefox') {
                            return (int) $sub->getIdSubDataTable();
                        }
                    }
                }
            }
            return 0;
        };

        $this->assertNotSame(0, $idOf($asc));
        $this->assertSame($idOf($asc), $idOf($desc));
        $this->assertSame(['Desktop'], self::labels(array_map(fn ($row) => ['label' => $row->getColumn('label')],
            API::getInstance()->getSavedReport(1, 'day', self::DAY, $desc, '', $idOf($asc))->getRows())));
    }

    /** final review: a row's visitor log keeps the exploration's own segment */
    public function testRowSegmentsKeepTheSavedSegment(): void
    {
        $id = $this->save('anna', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits'], 'visitCount>=1');

        $segment = (string) API::getInstance()->getSavedReport(1, 'day', self::DAY, $id)->getFirstRow()->getMetadata('segment');
        $this->assertStringStartsWith('visitCount>=1;', $segment);
    }

    /** review focus 5: the saved OR segment and the request's segment, joined so that both apply */
    public function testSavedSegmentAndRequestSegmentBothApply(): void
    {
        $id = $this->save('anna', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits'], 'browserCode==CH,browserCode==FF');

        $labels = array_map(fn ($row) => $row->getColumn('label'),
            API::getInstance()->getSavedReport(1, 'day', self::DAY, $id, 'browserCode==FF')->getRows());
        $this->assertSame(['Firefox'], $labels);
    }

    public function testSeveralPeriodsAndTooLongRangesAreRefused(): void
    {
        $id = $this->save('anna', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits']);

        $this->assertUnavailable('FeinwerkDataExploration_ErrorPeriodNotSupported', fn () => API::getInstance()->getSavedReport(1, 'day', 'last7', $id));
        // archived (2026-11-25): a long range answers; not archived, it is refused as such
        $this->assertGreaterThan(0, API::getInstance()->getSavedReport(1, 'range', '2026-02-01,2026-03-27', $id)->getRowsCount());
        \Piwik\Config::getInstance()->FeinwerkDataExploration = ['archive_reports' => 0];
        try {
            $this->assertUnavailable('FeinwerkDataExploration_ArchiveNotArchived', fn () => API::getInstance()->getSavedReport(1, 'range', '2026-02-01,2026-03-27', $id));
        } finally {
            \Piwik\Config::getInstance()->FeinwerkDataExploration = [];
        }
    }

    public function testAnotherUsersPrivateExplorationIsNotFound(): void
    {
        $private = $this->save('bert', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits']);

        FakeAccess::clearAccess(false, [], [1], 'anna');
        try {
            API::getInstance()->getSavedReport(1, 'day', self::DAY, $private);
            $this->fail('another user\'s private exploration was shown');
        } catch (ExplorationException $e) {
            $this->assertSame(['FeinwerkDataExploration_ErrorSavedNotFound', 404], [$e->getTranslationKey(), $e->getCode()]);
        }

        // Email Reports Pro names the owner: a super user caller gets the owner's private one, never another's
        FakeAccess::clearAccess(true, [], [], 'root');
        $this->assertSame(2, API::getInstance()->getSavedReport(1, 'day', self::DAY, $private, '', 0, 'bert')->getRowsCount());
        $this->expectException(ExplorationException::class);
        API::getInstance()->getSavedReport(1, 'day', self::DAY, $private, '', 0, 'anna');
    }

    public function testADeletedGoalsMetricIsLeftOut(): void
    {
        $id = $this->save('anna', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits,goal_9_revenue']);

        $this->assertSame(['label', 'nb_visits'], array_keys(API::getInstance()->getSavedReport(1, 'day', self::DAY, $id)->getFirstRow()->getColumns()));
    }

    /** Matomo's e-mail cuts tables into an "Others" row and sums totals: only what adds up is summed */
    public function testColumnsDeclareHowTheyCombine(): void
    {
        $byBrowser = $this->save('anna', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits,bounce_rate']);
        $byUrl = $this->save('anna', ['fwdeDims' => 'pageUrl', 'fwdeMetrics' => 'nb_visits,nb_pageviews']);

        $ops = API::getInstance()->getSavedReport(1, 'day', self::DAY, $byBrowser)->getMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME);
        $this->assertSame(['sum', 'skip'], [$ops['nb_visits'], $ops['bounce_rate']]);
        // a visit can count in several page rows: nothing is summed
        $ops = API::getInstance()->getSavedReport(1, 'day', self::DAY, $byUrl)->getMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME);
        $this->assertSame(['skip', 'skip'], [$ops['nb_visits'], $ops['nb_pageviews']]);
    }

    /** final review (rows and pivot): the pivot is a level too, so visits under a page pivot are never summed */
    public function testColumnsDeclareHowTheyCombineAcrossThePivotLevel(): void
    {
        $id = $this->save('anna', ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits,nb_pageviews', 'fwdePivot' => 'pageUrl']);

        $ops = API::getInstance()->getSavedReport(1, 'day', self::DAY, $id)->getMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME);
        $this->assertSame(['skip', 'sum'], [$ops['nb_visits'], $ops['nb_pageviews']]);
    }

    /** a KPI card as a report: one row, its number */
    public function testCardReportIsItsNumber(): void
    {
        $id = $this->save('anna', ['fwdeKind' => 'kpi', 'fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits,bounce_rate', 'fwdeKpiMetric' => 'nb_visits']);
        $table = API::getInstance()->getSavedReport(1, 'day', self::DAY, $id);

        $this->assertSame(1, $table->getRowsCount());
        $this->assertSame(['nb_visits' => 4], $table->getFirstRow()->getColumns());
        $this->assertSame(4, API::getInstance()->getKpiCard(1, 'day', self::DAY, $id)['value']);
    }

    /** Matomo's daily e-mail graph and Email Reports Pro's KPI line ask runs of days */
    public function testCardReportAnswersARunOfDays(): void
    {
        $id = $this->save('anna', ['fwdeKind' => 'kpi', 'fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits']);
        $map = API::getInstance()->getSavedReport(1, 'day', '2026-03-20,' . self::DAY, $id);

        $this->assertInstanceOf(DataTable\Map::class, $map);
        $values = [];
        foreach ($map->getDataTables() as $day => $table) {
            $values[$day] = $table->getFirstRow()->getColumn('nb_visits');
        }
        $this->assertCount(8, $values);
        $this->assertSame(4, $values[self::DAY]);
        $this->assertSame(0, $values['2026-03-20']);

        $this->assertUnavailable('FeinwerkDataExploration_ErrorPeriodTooLong', fn () => API::getInstance()->getSavedReport(1, 'day', 'last40', $id));
    }

    private function save(string $login, array $state, string $segment = ''): int
    {
        FakeAccess::clearAccess(false, [], [1], $login, [1]);
        $id = API::getInstance()->addSavedExploration(1, 'Report of ' . $login, json_encode($state), $segment)['id'];
        FakeAccess::clearAccess(false, [], [1], $login);
        return (int) $id;
    }

    private function assertUnavailable(string $key, callable $call): void
    {
        try {
            $call();
            $this->fail('expected ' . $key);
        } catch (SavedReportUnavailable $e) {
            $this->assertSame([$key, 400], [$e->getTranslationKey(), $e->getCode()]);
        }
    }

    private function dump(DataTable $table): array
    {
        return array_map(fn ($row) => [$row->getColumn('label'), $row->getColumn('nb_visits'), $row->getColumn('nb_pageviews')], $table->getRows());
    }

    private static function labels(array $nodes): array
    {
        $labels = array_column($nodes, 'label');
        sort($labels);
        return $labels;
    }

    private function tree(string $json): array
    {
        return (new ReportLevels(new DimensionCatalog(), new MetricCatalog(), new Limits()))->tree(1, 'day', self::DAY, $this->definition($json), '');
    }

    /**
     * A saved report's levels through the API, as Matomo opens them: label (lowercase on the first level) => the first
     * metric, or [first metric, the next level] above the last level.
     */
    private function walk(int $idSaved, int $idSubtable, int $levels): array
    {
        $out = [];
        foreach (API::getInstance()->getSavedReport(1, 'day', self::DAY, $idSaved, '', $idSubtable)->getRows() as $row) {
            $label = $idSubtable === 0 ? strtolower($row->getColumn('label')) : $row->getColumn('label');
            $value = $row->getColumns();
            unset($value['label']);
            $value = reset($value);
            if ($levels > 1) {
                $this->assertGreaterThan(0, (int) $row->getIdSubDataTable(), $label);
                $out[$label] = [$value, $this->walk($idSaved, (int) $row->getIdSubDataTable(), $levels - 1)];
            } else {
                $this->assertNull($row->getIdSubDataTable(), $label);
                $out[$label] = $value;
            }
        }
        ksort($out);
        return $out;
    }

    private function explorer(): Explorer
    {
        return new Explorer(new DimensionCatalog(), new MetricCatalog(), new Limits());
    }

    private function definition(string $json): ExplorationDefinition
    {
        return ExplorationDefinition::fromJson($json, 1, new DimensionCatalog(), new MetricCatalog(), new Limits());
    }
}
