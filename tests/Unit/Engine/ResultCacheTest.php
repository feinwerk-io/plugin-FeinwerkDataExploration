<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\CustomDimensionSource;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\ColumnProbe;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\LabelFormatter;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ResultCache;

/**
 * @group FeinwerkDataExploration
 */
class ResultCacheTest extends TestCase
{
    private const START = '2026-03-01 00:00:00';
    private const END = '2026-03-31 23:59:59';

    public function testKeyIsStableAndContainsPrefix(): void
    {
        $cache = new ResultCache();
        $key = $cache->key(1, self::START, self::END, '', $this->definition('["nb_visits"]'), '1.0.0');

        $this->assertSame(1, preg_match('/^fwde_[0-9a-f]{40}$/', $key), $key); // assertMatchesRegularExpression needs PHPUnit 9.1
        $this->assertSame($key, $cache->key(1, self::START, self::END, '', $this->definition('["nb_visits"]'), '1.0.0'));
    }

    public function testKeyChangesWithSegmentDefinitionSitePeriodAndVersion(): void
    {
        $cache = new ResultCache();
        $base = $cache->key(1, self::START, self::END, '', $this->definition('["nb_visits"]'), '1.0.0');

        $variants = [
            $cache->key(1, self::START, self::END, 'browserCode==FF', $this->definition('["nb_visits"]'), '1.0.0'),
            $cache->key(1, self::START, self::END, '', $this->definition('["nb_actions"]'), '1.0.0'),
            $cache->key(2, self::START, self::END, '', $this->definition('["nb_visits"]'), '1.0.0'),
            $cache->key(1, self::START, '2026-03-30 23:59:59', '', $this->definition('["nb_visits"]'), '1.0.0'),
            $cache->key(1, self::START, self::END, '', $this->definition('["nb_visits"]'), '1.0.1'),
        ];

        $this->assertNotContains($base, $variants);
        $this->assertCount(5, array_unique($variants));
    }

    public function testTtlTodayIsFiveMinutes(): void
    {
        $this->assertSame(300, (new ResultCache())->ttl(true));
        $this->assertSame(3600, (new ResultCache())->ttl(false));
    }

    private function definition(string $metrics): ExplorationDefinition
    {
        $noCustomDimensions = new class implements CustomDimensionSource {
            public function activeFor(int $idSite): array
            {
                return [];
            }
        };
        $dimensions = new DimensionCatalog(fn (string $coreId) => $coreId, $noCustomDimensions, new LabelFormatter(fn (string $key, array $params = []) => $key), new ColumnProbe(fn () => []));

        return ExplorationDefinition::fromJson('{"dimensions": ["browser"], "metrics": ' . $metrics . '}', 1, $dimensions, new MetricCatalog(), new Limits());
    }
}
