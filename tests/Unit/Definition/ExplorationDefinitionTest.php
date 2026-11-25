<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Definition;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\CustomDimensionSource;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\ColumnProbe;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\LabelFormatter;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;

/**
 * @group FeinwerkDataExploration
 */
class ExplorationDefinitionTest extends TestCase
{
    public function testParsesFullDefinition(): void
    {
        $definition = $this->parse('{"dimensions": ["browser", "pageUrl"], "metrics": ["nb_visits", "nb_pageviews"],'
            . ' "idGoal": 2, "sort": "nb_pageviews", "sortOrder": "asc", "limit": 250}');

        $this->assertSame(['browser', 'pageUrl'], $definition->dimensionIds);
        $this->assertSame(['nb_visits', 'nb_pageviews'], $definition->metricIds);
        $this->assertSame(2, $definition->idGoal);
        $this->assertSame('nb_pageviews', $definition->sortColumn);
        $this->assertSame('asc', $definition->sortOrder);
        $this->assertSame(250, $definition->limit);
    }

    public function testDefaultsApplied(): void
    {
        $definition = $this->parse('{"dimensions": ["browser"], "metrics": ["nb_actions", "nb_visits"]}');

        $this->assertNull($definition->idGoal);
        $this->assertSame('nb_actions', $definition->sortColumn);
        $this->assertSame('desc', $definition->sortOrder);
        $this->assertSame(100, $definition->limit);
    }

    public function testUnknownDimensionIsRejected(): void
    {
        $this->assertRejected('{"dimensions": ["browserEngine"], "metrics": ["nb_visits"]}', 'FeinwerkDataExploration_ErrorUnknownDimension', ['browserEngine']);
    }

    public function testSameDimensionTwiceIsRejected(): void
    {
        $this->assertRejected('{"dimensions": ["browser", "browser"], "metrics": ["nb_visits"]}', 'FeinwerkDataExploration_ErrorDuplicateDimension', ['browser']);
    }

    /** up to three since rows and pivot (2026-11-24) */
    public function testZeroOrFourDimensionsRejected(): void
    {
        $this->assertRejected('{"dimensions": [], "metrics": ["nb_visits"]}', 'FeinwerkDataExploration_ErrorDimensionCount');
        $this->assertRejected('{"dimensions": ["browser", "country", "city", "deviceType"], "metrics": ["nb_visits"]}', 'FeinwerkDataExploration_ErrorDimensionCount');
        $this->assertRejected('{"metrics": ["nb_visits"]}', 'FeinwerkDataExploration_ErrorDimensionCount');
    }

    public function testZeroOrSixMetricsRejected(): void
    {
        // up to ten since 2026-11-24 (testTenMetricsAcceptedElevenRefused)
        $this->assertRejected('{"dimensions": ["browser"], "metrics": []}', 'FeinwerkDataExploration_ErrorMetricCount', [10]);
        $this->assertCount(6, $this->parse(
            '{"dimensions": ["browser"], "metrics": ["nb_visits", "nb_actions", "bounce_rate", "revenue", "nb_events", "nb_outlinks"]}'
        )->metricIds);
    }

    public function testUnknownMetricRejected(): void
    {
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_hits"]}', 'FeinwerkDataExploration_ErrorUnknownMetric', ['nb_hits']);
    }

    public function testDuplicateMetricRejected(): void
    {
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits", "nb_visits"]}', 'FeinwerkDataExploration_ErrorDuplicateMetric', ['nb_visits']);
    }

    public function testLimitAboveMaxRejected(): void
    {
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "limit": 1001}', 'FeinwerkDataExploration_ErrorLimit', [1000]);
    }

    public function testLimitBelowOneRejected(): void
    {
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "limit": 0}', 'FeinwerkDataExploration_ErrorLimit', [1000]);
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "limit": "ten"}', 'FeinwerkDataExploration_ErrorLimit', [1000]);
    }

    public function testSortMustBeSelectedMetricOrLabel(): void
    {
        $this->assertSame('label', $this->parse('{"dimensions": ["browser"], "metrics": ["nb_visits"], "sort": "label"}')->sortColumn);
        $this->assertSame(
            'secondary_label',
            $this->parse('{"dimensions": ["browser", "country"], "metrics": ["nb_visits"], "sort": "secondary_label"}')->sortColumn
        );
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "sort": "nb_actions"}', 'FeinwerkDataExploration_ErrorSort');
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "sort": "secondary_label"}', 'FeinwerkDataExploration_ErrorSort');
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "sortOrder": "up"}', 'FeinwerkDataExploration_ErrorSort');
    }

    public function testGoalMustBePositive(): void
    {
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_conversions"], "idGoal": 0}', 'FeinwerkDataExploration_ErrorGoal');
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_conversions"], "idGoal": "x"}', 'FeinwerkDataExploration_ErrorGoal');
    }

    public function testEscapedQuotesAreDecoded(): void
    {
        $definition = $this->parse('{&quot;dimensions&quot;:[&quot;browser&quot;],&quot;metrics&quot;:[&quot;nb_visits&quot;]}');

        $this->assertSame(['browser'], $definition->dimensionIds);
    }

    public function testInvalidJsonRejected(): void
    {
        $this->assertRejected('{"dimensions": ["browser"', 'FeinwerkDataExploration_ErrorInvalidDefinition');
        $this->assertRejected('"browser"', 'FeinwerkDataExploration_ErrorInvalidDefinition');
        $this->assertRejected('{"dimensions": "browser", "metrics": ["nb_visits"]}', 'FeinwerkDataExploration_ErrorDimensionCount');
    }

    public function testScopeIsActionWhenAnyActionDimensionOrMetric(): void
    {
        $this->assertSame('action', $this->parse('{"dimensions": ["browser"], "metrics": ["nb_pageviews"]}')->scope($this->dimensions(), new MetricCatalog(), 1));
        $this->assertSame('action', $this->parse('{"dimensions": ["browser", "pageUrl"], "metrics": ["nb_visits"]}')->scope($this->dimensions(), new MetricCatalog(), 1));
        $this->assertSame('visit', $this->parse('{"dimensions": ["browser"], "metrics": ["nb_visits", "revenue"]}')->scope($this->dimensions(), new MetricCatalog(), 1));
    }

    public function testNormalizedIsStableAcrossKeyOrder(): void
    {
        $a = $this->parse('{"dimensions": ["browser"], "metrics": ["nb_visits"], "limit": 50, "sortOrder": "asc"}');
        $b = $this->parse('{"sortOrder": "asc", "limit": 50, "metrics": ["nb_visits"], "dimensions": ["browser"]}');

        $this->assertSame($a->normalized(), $b->normalized());
        $this->assertSame(['dimensions', 'metrics', 'idGoal', 'sort', 'sortOrder', 'limit', 'filterGroups'], array_keys($a->normalized()));
        $this->assertNull($a->normalized()['filterGroups']);
    }

    public function testFiltersParsed(): void
    {
        $definition = $this->parse('{"dimensions": ["pageUrl"], "metrics": ["nb_visits"], "filters": ['
            . '{"target": "label", "operator": "contains", "value": " blog "},'
            . '{"target": "nb_visits", "operator": "gt", "value": "10"}]}');

        $this->assertSame([
            ['target' => 'label', 'operator' => 'contains', 'value' => 'blog', 'group' => 0],
            ['target' => 'nb_visits', 'operator' => 'gt', 'value' => 10.0, 'group' => 0],
        ], $definition->filters);
        $this->assertSame(['match' => 'all', 'groups' => [['match' => 'all', 'filters' => [
            ['target' => 'label', 'operator' => 'contains', 'value' => 'blog'],
            ['target' => 'nb_visits', 'operator' => 'gt', 'value' => 10.0],
        ]]]], $definition->normalized()['filterGroups']);
        $this->assertSame([], $this->parse('{"dimensions": ["browser"], "metrics": ["nb_visits"]}')->filters);
    }

    public function testIsNotStartsWithAndEndsWithParsed(): void
    {
        $definition = $this->parse('{"dimensions": ["pageUrl"], "metrics": ["nb_visits"], "filters": ['
            . '{"target": "label", "operator": "notEquals", "value": "/"},'
            . '{"target": "label", "operator": "startsWith", "value": "/blog"},'
            . '{"target": "label", "operator": "endsWith", "value": ".pdf"}]}');

        $this->assertSame(['notEquals', 'startsWith', 'endsWith'], array_column($definition->filters, 'operator'));
        $this->assertRejectedAt('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [{"target": "nb_visits", "operator": "endsWith", "value": "1"}]}',
            'FeinwerkDataExploration_ErrorFilterCondition', 'filters[0]');
    }

    public function testMatchParsedWithAllAsDefault(): void
    {
        $this->assertSame('all', $this->parse('{"dimensions": ["browser"], "metrics": ["nb_visits"]}')->match);
        // the legacy "match" is the switch of the one group; without filters there is no group
        $any = $this->parse('{"dimensions": ["browser"], "metrics": ["nb_visits"], "match": "any"}');
        $this->assertSame('all', $any->match);
        $this->assertSame([], $any->groupMatches);
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "match": "some"}',
            'FeinwerkDataExploration_ErrorFilterMatchValue', ['match']);
    }

    public function testTextOperatorNeedsLabelTarget(): void
    {
        $this->assertRejectedAt('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [{"target": "nb_visits", "operator": "contains", "value": "x"}]}',
            'FeinwerkDataExploration_ErrorFilterCondition', 'filters[0]');
        $this->assertRejectedAt('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [{"target": "label", "operator": "like", "value": "x"}]}',
            'FeinwerkDataExploration_ErrorFilterCondition', 'filters[0]');
    }

    public function testMetricFilterNeedsSelectedMetricAndNumber(): void
    {
        $this->assertRejectedAt('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [{"target": "nb_actions", "operator": "gt", "value": 1}]}',
            'FeinwerkDataExploration_ErrorFilterCondition', 'filters[0]');
        $this->assertRejectedAt('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [{"target": "label", "operator": "contains", "value": "a"},'
            . '{"target": "nb_visits", "operator": "lt", "value": "abc"}]}', 'FeinwerkDataExploration_ErrorFilterCondition', 'filters[1]');
        $this->assertRejectedAt('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [{"target": "label", "operator": "gt", "value": 1}]}',
            'FeinwerkDataExploration_ErrorFilterCondition', 'filters[0]');
    }

    public function testSecondaryLabelFilterNeedsTwoDimensions(): void
    {
        $filter = '"filters": [{"target": "secondary_label", "operator": "equals", "value": "x"}]';
        $this->assertRejectedAt('{"dimensions": ["browser"], "metrics": ["nb_visits"], ' . $filter . '}', 'FeinwerkDataExploration_ErrorFilterCondition', 'filters[0]');
        $this->assertCount(1, $this->parse('{"dimensions": ["browser", "country"], "metrics": ["nb_visits"], ' . $filter . '}')->filters);
    }

    public function testElevenFiltersRejected(): void
    {
        $filters = implode(',', array_fill(0, 11, '{"target": "label", "operator": "contains", "value": "a"}'));
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [' . $filters . ']}', 'FeinwerkDataExploration_ErrorFilterCount', [10]);
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": "a"}', 'FeinwerkDataExploration_ErrorFilterCount', [10]);
        $ten = implode(',', array_fill(0, 10, '{"target": "label", "operator": "contains", "value": "a"}'));
        $this->assertCount(10, $this->parse('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [' . $ten . ']}')->filters);
    }

    public function testFilterValueTrimmedAndLimitedTo1000(): void
    {
        // long enough for the stored URL of a row ("filter to this value")
        $long = str_repeat('a', 1001);
        $this->assertRejectedAt('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [{"target": "label", "operator": "contains", "value": "' . $long . '"}]}',
            'FeinwerkDataExploration_ErrorFilterCondition', 'filters[0]');
        $this->assertRejectedAt('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [{"target": "label", "operator": "contains", "value": "   "}]}',
            'FeinwerkDataExploration_ErrorFilterCondition', 'filters[0]');
        $hundred = str_repeat('b', 1000);
        $definition = $this->parse('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [{"target": "label", "operator": "equals", "value": "' . $hundred . '"}]}');
        $this->assertSame($hundred, $definition->filters[0]['value']);
    }

    /** More metrics (2026-11-24): a goal's metrics of the website, up to ten metrics */
    public function testPerGoalMetricOfTheWebsiteAccepted(): void
    {
        $metrics = new MetricCatalog(fn (int $idSite) => [3 => 'Newsletter'], fn (int $idSite) => false);
        $parse = fn (string $json) => ExplorationDefinition::fromJson($json, 1, $this->dimensions(), $metrics, new Limits());

        $this->assertSame(['nb_visits', 'goal_3_revenue'], $parse('{"dimensions": ["browser"], "metrics": ["nb_visits", "goal_3_revenue"]}')->metricIds);
        try {
            $parse('{"dimensions": ["browser"], "metrics": ["goal_4_revenue"]}');
            $this->fail('a metric of a goal the website lacks was accepted');
        } catch (ExplorationException $e) {
            $this->assertSame('FeinwerkDataExploration_ErrorUnknownMetric', $e->getTranslationKey());
        }
    }

    public function testTenMetricsAcceptedElevenRefused(): void
    {
        $ten = '"nb_visits", "nb_uniq_visitors", "nb_actions", "bounce_rate", "avg_time_on_site", "nb_actions_per_visit",'
            . ' "max_actions", "sum_visit_length", "nb_conversions", "conversion_rate"';
        $this->assertCount(10, $this->parse('{"dimensions": ["browser"], "metrics": [' . $ten . ']}')->metricIds);
        $this->assertRejected('{"dimensions": ["browser"], "metrics": [' . $ten . ', "revenue"]}', 'FeinwerkDataExploration_ErrorMetricCount', [10]);
    }

    /** rows and pivot (2026-11-24): up to three row dimensions */
    public function testThreeDimensionsAcceptedFourRefused(): void
    {
        $three = $this->parse('{"dimensions": ["browser", "deviceType", "pageTitle"], "metrics": ["nb_visits"],'
            . ' "sort": "tertiary_label", "filters": [{"target": "tertiary_label", "operator": "contains", "value": "shop"}]}');
        $this->assertSame(['browser', 'deviceType', 'pageTitle'], $three->dimensionIds);
        $this->assertSame('tertiary_label', $three->sortColumn);
        $this->assertSame(['label', 'secondary_label', 'tertiary_label'], ExplorationDefinition::labelColumns(3));
        $this->assertRejected('{"dimensions": ["browser", "deviceType", "pageTitle", "country"], "metrics": ["nb_visits"]}',
            'FeinwerkDataExploration_ErrorDimensionCount');
        $this->assertRejected('{"dimensions": ["browser", "deviceType"], "metrics": ["nb_visits"], "sort": "tertiary_label"}',
            'FeinwerkDataExploration_ErrorSort');
    }

    public function testPivotParsedWithItsLimit(): void
    {
        $plain = $this->parse('{"dimensions": ["browser"], "metrics": ["nb_visits"]}');
        $pivoted = $this->parse('{"dimensions": ["browser"], "metrics": ["nb_visits"], "pivot": {"dimension": "deviceType"}}');
        $this->assertNull($plain->pivot);
        $this->assertSame(['dimension' => 'deviceType', 'limit' => 5], $pivoted->pivot);
        $this->assertSame(['dimension' => 'weekday', 'limit' => 10],
            $this->parse('{"dimensions": ["browser"], "metrics": ["nb_visits"], "pivot": {"dimension": "weekday", "limit": 10}}')->pivot);
        $this->assertNotSame($plain->normalized(), $pivoted->normalized());
        $this->assertArrayNotHasKey('pivot', $plain->normalized(), 'definitions without a pivot keep their cache keys');
    }

    public function testPivotRefusals(): void
    {
        $base = '{"dimensions": ["browser"], "metrics": ["nb_visits"], "pivot": ';
        foreach (['browser', 'date', 'hour', 'nope'] as $id) {
            $this->assertRejected($base . '{"dimension": "' . $id . '"}}', 'FeinwerkDataExploration_ErrorPivotDimension', [$id]);
        }
        $this->assertRejected($base . '{"dimension": "deviceType", "limit": 7}}', 'FeinwerkDataExploration_ErrorPivotLimit', ['3, 5, 10']);
        $this->assertRejected($base . '"deviceType"}', 'FeinwerkDataExploration_ErrorPivotDimension', ['']);
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits", "nb_actions", "bounce_rate", "revenue",'
            . ' "nb_conversions", "avg_time_on_site", "max_actions"], "pivot": {"dimension": "deviceType", "limit": 10}}',
            'FeinwerkDataExploration_ErrorPivotCells', [60]);
        // six metrics by ten values fit exactly
        $this->assertSame(10, $this->parse('{"dimensions": ["browser"], "metrics": ["nb_visits", "nb_actions", "bounce_rate",'
            . ' "revenue", "nb_conversions", "avg_time_on_site"], "pivot": {"dimension": "deviceType", "limit": 10}}')->pivot['limit']);
    }

    public function testFilterGroupsParsed(): void
    {
        $definition = $this->parse('{"dimensions": ["browser", "country"], "metrics": ["nb_visits"], "filterGroups": {"match": "any", "groups": ['
            . '{"filters": [{"target": "label", "operator": "contains", "value": "chrome"}, {"target": "secondary_label", "operator": "equals", "value": "de"}]},'
            . '{"match": "any", "filters": [{"target": "nb_visits", "operator": "gte", "value": 10}, {"target": "label", "operator": "matches", "value": "^fire"}]}]}}');

        $this->assertSame('any', $definition->match);
        $this->assertSame(['all', 'any'], $definition->groupMatches);
        $this->assertSame([0, 0, 1, 1], array_column($definition->filters, 'group'));
        $this->assertSame(['contains', 'equals', 'gte', 'matches'], array_column($definition->filters, 'operator'));
        $this->assertSame(10.0, $definition->filters[2]['value']);
        $this->assertSame('any', $definition->normalized()['filterGroups']['match']);
        $this->assertSame(['all', 'any'], array_column($definition->normalized()['filterGroups']['groups'], 'match'));
    }

    public function testLegacyFiltersAreOneGroup(): void
    {
        $legacy = $this->parse('{"dimensions": ["pageUrl"], "metrics": ["nb_visits"], "match": "any", "filters": ['
            . '{"target": "label", "operator": "contains", "value": "blog"}, {"target": "nb_visits", "operator": "gt", "value": 3}]}');
        $groups = $this->parse('{"dimensions": ["pageUrl"], "metrics": ["nb_visits"], "filterGroups": {"groups": [{"match": "any", "filters": ['
            . '{"target": "label", "operator": "contains", "value": "blog"}, {"target": "nb_visits", "operator": "gt", "value": 3}]}]}}');

        $this->assertSame('all', $legacy->match);
        $this->assertSame(['any'], $legacy->groupMatches);
        $this->assertSame($groups->normalized(), $legacy->normalized());
    }

    public function testFiltersAndFilterGroupsTogetherRejected(): void
    {
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [], "filterGroups": {"groups": []}}',
            'FeinwerkDataExploration_ErrorFilterGroupsBoth', []);
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "match": "any", "filterGroups": {"groups": []}}',
            'FeinwerkDataExploration_ErrorFilterGroupsBoth', []);
    }

    public function testElevenConditionsOrSixGroupsOrAnEmptyGroupRejected(): void
    {
        $condition = '{"target": "label", "operator": "contains", "value": "a"}';
        $group = fn (int $n) => '{"filters": [' . implode(',', array_fill(0, $n, $condition)) . ']}';
        $groups = fn (array $sizes) => '{"dimensions": ["browser"], "metrics": ["nb_visits"], "filterGroups": {"groups": ['
            . implode(',', array_map($group, $sizes)) . ']}}';

        $this->assertCount(10, $this->parse($groups([5, 5]))->filters);
        $this->assertSame([], $this->parse($groups([]))->filters);
        $this->assertRejected($groups([6, 5]), 'FeinwerkDataExploration_ErrorFilterGroups', [5, 10]);
        $this->assertRejected($groups([1, 1, 1, 1, 1, 1]), 'FeinwerkDataExploration_ErrorFilterGroups', [5, 10]);
        $this->assertRejected($groups([1, 0]), 'FeinwerkDataExploration_ErrorFilterGroups', [5, 10]);
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filterGroups": "x"}', 'FeinwerkDataExploration_ErrorFilterGroups', [5, 10]);
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filterGroups": {"match": "both", "groups": []}}',
            'FeinwerkDataExploration_ErrorFilterMatchValue', ['filterGroups.match']);
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filterGroups": {"groups": [{"match": "x", "filters": [' . $condition . ']}]}}',
            'FeinwerkDataExploration_ErrorFilterMatchValue', ['filterGroups.groups[0].match']);
    }

    public function testNewOperatorsParsed(): void
    {
        $definition = $this->parse('{"dimensions": ["pageUrl"], "metrics": ["nb_visits", "bounce_rate"], "filters": ['
            . '{"target": "label", "operator": "notStartsWith", "value": "/shop"},'
            . '{"target": "label", "operator": "notEndsWith", "value": ".pdf"},'
            . '{"target": "label", "operator": "matches", "value": "^/(blog|news)/[0-9]+"},'
            . '{"target": "nb_visits", "operator": "gte", "value": 2},'
            . '{"target": "bounce_rate", "operator": "lte", "value": 0.25}]}');

        $this->assertSame(['notStartsWith', 'notEndsWith', 'matches', 'gte', 'lte'], array_column($definition->filters, 'operator'));
        $this->assertSame(0.25, $definition->filters[4]['value']);
    }

    public function testInvalidPatternRejected(): void
    {
        $this->assertRejectedAt('{"dimensions": ["pageUrl"], "metrics": ["nb_visits"], "filters": [{"target": "label", "operator": "matches", "value": "(ab"}]}',
            'FeinwerkDataExploration_ErrorFilterPattern', 'filters[0]');
        $long = str_repeat('a', 201);
        $this->assertRejectedAt('{"dimensions": ["pageUrl"], "metrics": ["nb_visits"], "filters": [{"target": "label", "operator": "matches", "value": "' . $long . '"}]}',
            'FeinwerkDataExploration_ErrorFilterPattern', 'filters[0]');
        // a delimiter or modifier inside the pattern is part of the pattern
        $this->assertCount(1, $this->parse('{"dimensions": ["pageUrl"], "metrics": ["nb_visits"], "filters": [{"target": "label", "operator": "matches", "value": "a/b~c#d"}]}')->filters);
    }

    public function testErrorsNameTheField(): void
    {
        try {
            $this->parse('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filterGroups": {"groups": ['
                . '{"filters": [{"target": "label", "operator": "contains", "value": "a"}]},'
                . '{"filters": [{"target": "label", "operator": "between", "value": "a"}]}]}}');
            $this->fail('expected an ExplorationException');
        } catch (ExplorationException $e) {
            $this->assertSame('FeinwerkDataExploration_ErrorFilterCondition', $e->getTranslationKey());
            $this->assertSame('filterGroups.groups[1].filters[0]', $e->getParams()[0]);
            $this->assertStringContainsString('notStartsWith', $e->getParams()[1]);
            $this->assertStringContainsString('matches', $e->getParams()[1]);
            $this->assertStringContainsString('gte', $e->getParams()[2]);
        }
    }

    private function assertRejectedAt(string $json, string $key, string $path): void
    {
        try {
            $this->parse($json);
            $this->fail('expected an ExplorationException for ' . $json);
        } catch (ExplorationException $e) {
            $this->assertSame($key, $e->getTranslationKey(), $json);
            $this->assertSame($path, $e->getParams()[0] ?? null, $json);
        }
    }

    private function parse(string $json): ExplorationDefinition
    {
        return ExplorationDefinition::fromJson($json, 1, $this->dimensions(), new MetricCatalog(), new Limits());
    }

    private function assertRejected(string $json, string $key, ?array $params = null): void
    {
        try {
            $this->parse($json);
            $this->fail('expected an ExplorationException for ' . $json);
        } catch (ExplorationException $e) {
            $this->assertSame($key, $e->getTranslationKey(), $json);
            if ($params !== null) {
                $this->assertSame($params, $e->getParams(), $json);
            }
        }
    }

    private function dimensions(): DimensionCatalog
    {
        $noCustomDimensions = new class implements CustomDimensionSource {
            public function activeFor(int $idSite): array
            {
                return [];
            }
        };
        return new DimensionCatalog(fn (string $coreId) => $coreId, $noCustomDimensions, new LabelFormatter(fn (string $key, array $params = []) => $key), new ColumnProbe(fn () => []));
    }
}
