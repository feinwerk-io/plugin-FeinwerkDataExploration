<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Archive;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Archive\FilterSplit;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\ColumnProbe;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\CustomDimensionSource;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\LabelFormatter;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;

/**
 * Which conditions an archive applies and which wait for the added-up period (design 2026-11-25): a condition on a
 * dimension holds per row on every day, a condition on a metric only for the period's total.
 */
class FilterSplitTest extends TestCase
{
    public function testDimensionsOnlyAllArchived(): void
    {
        [$archive, $read] = $this->split(['match' => 'all', 'groups' => [
            ['match' => 'all', 'filters' => [['target' => 'label', 'operator' => 'contains', 'value' => 'Fire']]],
            ['match' => 'any', 'filters' => [['target' => 'secondary_label', 'operator' => 'equals', 'value' => 'Desktop'],
                ['target' => 'secondary_label', 'operator' => 'equals', 'value' => 'Tablet']]],
        ]]);
        $this->assertCount(3, $archive->filters);
        $this->assertNull($read);
    }

    public function testMetricGroupUnderAllMovesWhole(): void
    {
        [$archive, $read] = $this->split(['match' => 'all', 'groups' => [
            ['match' => 'all', 'filters' => [['target' => 'label', 'operator' => 'contains', 'value' => 'Fire']]],
            ['match' => 'any', 'filters' => [['target' => 'nb_visits', 'operator' => 'gt', 'value' => 100],
                ['target' => 'secondary_label', 'operator' => 'equals', 'value' => 'Desktop']]],
        ]]);
        $this->assertSame([['target' => 'label', 'operator' => 'contains', 'value' => 'Fire', 'group' => 0]], $archive->filters);
        $this->assertSame('all', $read['match']);
        $this->assertCount(1, $read['groups']);
        $this->assertSame('any', $read['groups'][0]['match']);
        $this->assertCount(2, $read['groups'][0]['filters']);
    }

    /** final review 2026-11-26: the usual filter, one "all" group, keeps its dimension conditions in the archive */
    public function testAllGroupSplitsItsConditions(): void
    {
        [$archive, $read] = $this->split(['match' => 'all', 'groups' => [
            ['match' => 'all', 'filters' => [['target' => 'secondary_label', 'operator' => 'equals', 'value' => 'Desktop'],
                ['target' => 'nb_visits', 'operator' => 'gt', 'value' => 10]]],
        ]]);
        $this->assertSame([['target' => 'secondary_label', 'operator' => 'equals', 'value' => 'Desktop', 'group' => 0]], $archive->filters);
        $this->assertSame(['match' => 'all', 'groups' => [['match' => 'all', 'filters' => [
            ['target' => 'nb_visits', 'operator' => 'gt', 'value' => 10.0]]]]], $read);
    }

    public function testAnyWithAMetricMovesEverything(): void
    {
        [$archive, $read] = $this->split(['match' => 'any', 'groups' => [
            ['match' => 'all', 'filters' => [['target' => 'label', 'operator' => 'contains', 'value' => 'Fire']]],
            ['match' => 'all', 'filters' => [['target' => 'nb_visits', 'operator' => 'gt', 'value' => 100]]],
        ]]);
        $this->assertSame([], $archive->filters);
        $this->assertSame('any', $read['match']);
        $this->assertCount(2, $read['groups']);
    }

    public function testAnyOfDimensionsStaysArchived(): void
    {
        [$archive, $read] = $this->split(['match' => 'any', 'groups' => [
            ['match' => 'all', 'filters' => [['target' => 'label', 'operator' => 'contains', 'value' => 'Fire']]],
            ['match' => 'all', 'filters' => [['target' => 'label', 'operator' => 'contains', 'value' => 'Chrome']]],
        ]]);
        $this->assertCount(2, $archive->filters);
        $this->assertSame('any', $archive->match);
        $this->assertNull($read);
    }

    public function testLegacyFiltersShape(): void
    {
        $definition = $this->definition(['filters' => [['target' => 'nb_visits', 'operator' => 'gt', 'value' => 5],
            ['target' => 'label', 'operator' => 'contains', 'value' => 'Fire']], 'match' => 'all']);
        [$archive, $read] = FilterSplit::split($definition, fn (string $t): bool => !in_array($t, ExplorationDefinition::LABEL_COLUMNS, true));
        // the old form is one "all" group: its text condition stays in the archive, the metric one is read
        $this->assertCount(1, $archive->filters);
        $this->assertSame('label', $archive->filters[0]['target']);
        $this->assertCount(1, $read['groups'][0]['filters']);
        $this->assertSame('nb_visits', $read['groups'][0]['filters'][0]['target']);
    }

    private function split(array $filterGroups): array
    {
        return FilterSplit::split($this->definition(['filterGroups' => $filterGroups]),
            fn (string $target): bool => !in_array($target, ExplorationDefinition::LABEL_COLUMNS, true));
    }

    private function definition(array $filters): ExplorationDefinition
    {
        $noCustomDimensions = new class implements CustomDimensionSource {
            public function activeFor(int $idSite): array
            {
                return [];
            }
        };
        $dimensions = new DimensionCatalog(fn (string $coreId) => $coreId, $noCustomDimensions,
            new LabelFormatter(fn (string $key, array $params = []) => $key), new ColumnProbe(fn () => []));
        $metrics = new MetricCatalog(fn (int $idSite) => [], fn (int $idSite) => false);
        return ExplorationDefinition::fromJson((string) json_encode(['dimensions' => ['browser', 'deviceType'],
            'metrics' => ['nb_visits']] + $filters), 1, $dimensions, $metrics, new Limits());
    }
}
