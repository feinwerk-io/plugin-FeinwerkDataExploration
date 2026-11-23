<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\ColumnProbe;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\CustomDimensionSource;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\LabelFormatter;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Additivity;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;

/**
 * When the rows of a table add up to its total (design 2026-11-20): every counted unit of every metric in exactly one
 * row. The same cases as vue/tests/additivity.spec.ts.
 */
class AdditivityTest extends TestCase
{
    /**
     * @dataProvider cases
     */
    public function test_the_unit_that_counts_in_several_rows(string $dimensions, string $metrics, ?string $unit): void
    {
        $definition = ExplorationDefinition::fromJson(
            '{"dimensions": ' . $dimensions . ', "metrics": ' . $metrics . '}',
            1,
            $this->dimensions(),
            new MetricCatalog(),
            new Limits()
        );

        $this->assertSame($unit, Additivity::overcountUnit($definition, $this->dimensions(), new MetricCatalog(), 1));
    }

    public static function cases(): array
    {
        return [
            'a visit with several pages' => ['["pageUrl"]', '["nb_visits"]', 'visit'],
            'page views per page' => ['["pageUrl"]', '["nb_pageviews"]', null],
            'hours of actions with a visit metric' => ['["hour"]', '["nb_visits", "nb_pageviews"]', 'visit'],
            'hours of visits' => ['["hour"]', '["nb_visits"]', null],
            'unique visitors' => ['["browser"]', '["nb_uniq_visitors"]', 'visitor'],
            'conversions per country' => ['["country"]', '["nb_conversions"]', null],
            'conversions per page' => ['["pageUrl"]', '["nb_conversions"]', 'visit'],
            'unique page views per page' => ['["pageUrl"]', '["nb_uniq_pageviews"]', 'visit'],
            'action metrics under a visit dimension' => ['["browser"]', '["nb_pageviews", "nb_events"]', null],
        ];
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
