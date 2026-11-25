<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Cache;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The conditions "is empty" and "is not empty" (Peter, 2026-11-25: "Es muss auch eine Bedingung 'nicht leer' geben ...
 * Zeilen filtern, deren Kampagnenname gesetzt (und nicht leer) ist", "2A umsetzen"): empty is what the table shows as
 * "... not defined" or "Unknown", a missing or empty value and a code whose label is the same.
 *
 * @group FeinwerkDataExploration
 */
class ApiEmptyFilterTest extends IntegrationTestCase
{
    private const DAY = '2026-03-27';

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

        $chrome = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        $visits = [
            [$chrome, 'https://example.com/?pk_campaign=autumn'],
            [$chrome, 'https://example.com/?pk_campaign=autumn'],
            [$chrome, 'https://example.com/'],
            ['Xyzzy/1.0', 'https://example.com/'],
        ];
        foreach ($visits as $i => [$userAgent, $url]) {
            $t = Fixture::getTracker(1, self::DAY . ' 1' . $i . ':00:00', true, true);
            $t->setNewVisitorId();
            $t->setIp('198.51.100.' . ($i + 1));
            $t->setUserAgent($userAgent);
            $t->setUrl($url);
            Fixture::checkResponse($t->doTrackPageView('Home'));
        }
    }

    public function testACampaignNameThatIsSet(): void
    {
        $all = $this->labels('campaignName', '');
        $this->assertContains('autumn', $all);
        $this->assertCount(2, $all);

        $this->assertSame(['autumn'], $this->labels('campaignName', '{"target": "label", "operator": "notEmpty"}'));
        $empty = $this->labels('campaignName', '{"target": "label", "operator": "empty"}');
        $this->assertCount(1, $empty);
        $this->assertNotContains('autumn', $empty);
    }

    /** a coded value whose label is the one of an empty value ("Unknown") counts as empty */
    public function testAnUnknownBrowserIsEmpty(): void
    {
        $this->assertSame(['Chrome'], $this->labels('browser', '{"target": "label", "operator": "notEmpty"}'));
        $this->assertSame(['Unknown'], $this->labels('browser', '{"target": "label", "operator": "empty"}'));
    }

    /** a value sent with them is ignored; on a metric they are refused naming the allowed operators */
    public function testNoValueAndNoMetric(): void
    {
        $this->assertSame(['autumn'], $this->labels('campaignName', '{"target": "label", "operator": "notEmpty", "value": "x"}'));
        try {
            $this->labels('campaignName', '{"target": "nb_visits", "operator": "notEmpty"}');
            $this->fail('expected a refusal');
        } catch (ExplorationException $e) {
            $this->assertSame(400, $e->getCode());
        }
    }

    /** release review 2026-11-25: a dimension compared in SQL (raw values), a date and a number key */
    public function testTextDateAndNumberKeys(): void
    {
        $this->assertSame(['autumn'], $this->labels('referrerName', '{"target": "label", "operator": "notEmpty"}'));
        $this->assertCount(1, $this->labels('referrerName', '{"target": "label", "operator": "empty"}'));
        // never empty, and no database error for a DATE or a number compared with an empty text (MySQL 8)
        $this->assertSame(['2026-03-27'], $this->labels('date', '{"target": "label", "operator": "notEmpty"}'));
        $this->assertSame([], $this->labels('date', '{"target": "label", "operator": "empty"}'));
        $this->assertSame([], $this->labels('visitCount', '{"target": "label", "operator": "empty"}'));
    }

    /** next to a number condition in groups with "any" (HAVING) */
    public function testInGroupsWithANumber(): void
    {
        $table = API::getInstance()->getExploration(1, 'day', self::DAY, '{"dimensions": ["campaignName"], "metrics": ["nb_visits"],'
            . ' "filterGroups": {"match": "any", "groups": ['
            . '{"filters": [{"target": "label", "operator": "notEmpty"}, {"target": "nb_visits", "operator": "gte", "value": 3}]},'
            . '{"filters": [{"target": "label", "operator": "empty"}]}]}}');
        $labels = $table->getColumn('label');
        $this->assertCount(1, $labels);
        $this->assertNotContains('autumn', $labels);
    }

    /** @return string[] the labels of one dimension's rows, with one condition (none for '') */
    private function labels(string $dimension, string $condition): array
    {
        $filters = $condition === '' ? '' : ', "filterGroups": {"groups": [{"filters": [' . $condition . ']}]}';
        $table = API::getInstance()->getExploration(1, 'day', self::DAY, '{"dimensions": ["' . $dimension . '"],'
            . ' "metrics": ["nb_visits"], "sort": "label", "sortOrder": "asc"' . $filters . '}');
        return $table->getColumn('label');
    }
}
