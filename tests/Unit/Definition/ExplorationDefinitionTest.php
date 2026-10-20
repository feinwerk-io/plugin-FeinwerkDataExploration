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
        $this->assertRejected('{"dimensions": ["browserVersion"], "metrics": ["nb_visits"]}', 'FeinwerkDataExploration_ErrorUnknownDimension', ['browserVersion']);
    }

    public function testSameDimensionTwiceIsRejected(): void
    {
        $this->assertRejected('{"dimensions": ["browser", "browser"], "metrics": ["nb_visits"]}', 'FeinwerkDataExploration_ErrorDuplicateDimension', ['browser']);
    }

    public function testZeroOrThreeDimensionsRejected(): void
    {
        $this->assertRejected('{"dimensions": [], "metrics": ["nb_visits"]}', 'FeinwerkDataExploration_ErrorDimensionCount');
        $this->assertRejected('{"dimensions": ["browser", "country", "city"], "metrics": ["nb_visits"]}', 'FeinwerkDataExploration_ErrorDimensionCount');
        $this->assertRejected('{"metrics": ["nb_visits"]}', 'FeinwerkDataExploration_ErrorDimensionCount');
    }

    public function testZeroOrSixMetricsRejected(): void
    {
        $this->assertRejected('{"dimensions": ["browser"], "metrics": []}', 'FeinwerkDataExploration_ErrorMetricCount', [5]);
        $this->assertRejected(
            '{"dimensions": ["browser"], "metrics": ["nb_visits", "nb_actions", "bounce_rate", "revenue", "nb_events", "nb_outlinks"]}',
            'FeinwerkDataExploration_ErrorMetricCount',
            [5]
        );
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
        $this->assertSame(['dimensions', 'metrics', 'idGoal', 'sort', 'sortOrder', 'limit', 'filters'], array_keys($a->normalized()));
    }

    public function testFiltersParsed(): void
    {
        $definition = $this->parse('{"dimensions": ["pageUrl"], "metrics": ["nb_visits"], "filters": ['
            . '{"target": "label", "operator": "contains", "value": " blog "},'
            . '{"target": "nb_visits", "operator": "gt", "value": "10"}]}');

        $this->assertSame([
            ['target' => 'label', 'operator' => 'contains', 'value' => 'blog'],
            ['target' => 'nb_visits', 'operator' => 'gt', 'value' => 10.0],
        ], $definition->filters);
        $this->assertSame($definition->filters, $definition->normalized()['filters']);
        $this->assertSame([], $this->parse('{"dimensions": ["browser"], "metrics": ["nb_visits"]}')->filters);
    }

    public function testTextOperatorNeedsLabelTarget(): void
    {
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [{"target": "nb_visits", "operator": "contains", "value": "x"}]}',
            'FeinwerkDataExploration_ErrorFilter', [1]);
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [{"target": "label", "operator": "like", "value": "x"}]}',
            'FeinwerkDataExploration_ErrorFilter', [1]);
    }

    public function testMetricFilterNeedsSelectedMetricAndNumber(): void
    {
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [{"target": "nb_actions", "operator": "gt", "value": 1}]}',
            'FeinwerkDataExploration_ErrorFilter', [1]);
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [{"target": "label", "operator": "contains", "value": "a"},'
            . '{"target": "nb_visits", "operator": "lt", "value": "abc"}]}', 'FeinwerkDataExploration_ErrorFilter', [2]);
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [{"target": "label", "operator": "gt", "value": 1}]}',
            'FeinwerkDataExploration_ErrorFilter', [1]);
    }

    public function testSecondaryLabelFilterNeedsTwoDimensions(): void
    {
        $filter = '"filters": [{"target": "secondary_label", "operator": "equals", "value": "x"}]';
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], ' . $filter . '}', 'FeinwerkDataExploration_ErrorFilter', [1]);
        $this->assertCount(1, $this->parse('{"dimensions": ["browser", "country"], "metrics": ["nb_visits"], ' . $filter . '}')->filters);
    }

    public function testSixFiltersRejected(): void
    {
        $filters = implode(',', array_fill(0, 6, '{"target": "label", "operator": "contains", "value": "a"}'));
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [' . $filters . ']}', 'FeinwerkDataExploration_ErrorFilterCount', [5]);
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": "a"}', 'FeinwerkDataExploration_ErrorFilterCount', [5]);
    }

    public function testFilterValueTrimmedAndLimitedTo1000(): void
    {
        // long enough for the stored URL of a row ("filter to this value")
        $long = str_repeat('a', 1001);
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [{"target": "label", "operator": "contains", "value": "' . $long . '"}]}',
            'FeinwerkDataExploration_ErrorFilter', [1]);
        $this->assertRejected('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [{"target": "label", "operator": "contains", "value": "   "}]}',
            'FeinwerkDataExploration_ErrorFilter', [1]);
        $hundred = str_repeat('b', 1000);
        $definition = $this->parse('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filters": [{"target": "label", "operator": "equals", "value": "' . $hundred . '"}]}');
        $this->assertSame($hundred, $definition->filters[0]['value']);
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
