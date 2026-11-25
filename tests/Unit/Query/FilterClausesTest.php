<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Query;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\ColumnProbe;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\CustomDimensionSource;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\LabelFormatter;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\FeinwerkDataExploration\Query\FilterClauses;

/**
 * The clauses of the top-level AND (filter groups, 2026-11-23): text-only clauses run before the grouping, clauses
 * with a number condition on the grouped rows.
 *
 * @group FeinwerkDataExploration
 */
class FilterClausesTest extends TestCase
{
    private const TEXT = '{"target": "label", "operator": "contains", "value": "a"}';
    private const NUMBER = '{"target": "nb_visits", "operator": "gt", "value": 3}';

    public function testAnAllGroupGivesOneClausePerCondition(): void
    {
        $clauses = $this->clauses('{"groups": [{"filters": [' . self::TEXT . ', ' . self::NUMBER . ']}]}');

        $this->assertSame([
            ['groups' => [['join' => 'AND', 'indexes' => [0]]], 'number' => false],
            ['groups' => [['join' => 'AND', 'indexes' => [1]]], 'number' => true],
        ], $clauses);
    }

    public function testAnAnyGroupIsOneClause(): void
    {
        $clauses = $this->clauses('{"match": "any", "groups": [{"match": "any", "filters": [' . self::TEXT . ', ' . self::NUMBER . ']}]}');

        $this->assertSame([['groups' => [['join' => 'OR', 'indexes' => [0, 1]]], 'number' => true]], $clauses);
    }

    public function testOuterAllSplitsAllGroupsAndKeepsAnyGroups(): void
    {
        $clauses = $this->clauses('{"groups": [{"match": "any", "filters": [' . self::TEXT . ', ' . self::TEXT . ']},'
            . '{"filters": [' . self::TEXT . ', ' . self::NUMBER . ']}]}');

        $this->assertSame([
            ['groups' => [['join' => 'OR', 'indexes' => [0, 1]]], 'number' => false],
            ['groups' => [['join' => 'AND', 'indexes' => [2]]], 'number' => false],
            ['groups' => [['join' => 'AND', 'indexes' => [3]]], 'number' => true],
        ], $clauses);
    }

    public function testOuterAnyWithTwoGroupsIsOneClause(): void
    {
        $text = $this->clauses('{"match": "any", "groups": [{"filters": [' . self::TEXT . ', ' . self::TEXT . ']}, {"filters": [' . self::TEXT . ']}]}');
        $mixed = $this->clauses('{"match": "any", "groups": [{"filters": [' . self::TEXT . ']}, {"match": "any", "filters": [' . self::NUMBER . ']}]}');

        $this->assertSame([['groups' => [['join' => 'AND', 'indexes' => [0, 1]], ['join' => 'AND', 'indexes' => [2]]], 'number' => false]], $text);
        $this->assertSame([['groups' => [['join' => 'AND', 'indexes' => [0]], ['join' => 'OR', 'indexes' => [1]]], 'number' => true]], $mixed);
    }

    public function testNoFiltersNoClauses(): void
    {
        $this->assertSame([], $this->clauses('{"groups": []}'));
    }

    private function clauses(string $filterGroups): array
    {
        $noCustomDimensions = new class implements CustomDimensionSource {
            public function activeFor(int $idSite): array
            {
                return [];
            }
        };
        $dimensions = new DimensionCatalog(fn (string $coreId) => $coreId, $noCustomDimensions, new LabelFormatter(fn (string $key, array $params = []) => $key), new ColumnProbe(fn () => []));
        $definition = ExplorationDefinition::fromJson('{"dimensions": ["browser"], "metrics": ["nb_visits"], "filterGroups": ' . $filterGroups . '}',
            1, $dimensions, new MetricCatalog(), new Limits());
        return FilterClauses::of($definition);
    }
}
