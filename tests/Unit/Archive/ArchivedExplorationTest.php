<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Archive;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchivedExploration;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\ColumnProbe;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\CustomDimensionSource;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\LabelFormatter;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedStateDefinition;

/**
 * Which saved explorations are archived and under which record names (design 2026-11-25): the version follows what
 * shapes the stored data, never how it is shown.
 */
class ArchivedExplorationTest extends TestCase
{
    private const BASE = ['fwdeDims' => 'browser,deviceType', 'fwdeMetrics' => 'nb_visits,bounce_rate'];

    public function testVersionIgnoresPresentation(): void
    {
        $base = $this->archived(self::BASE)->version;
        foreach ([
            ['fwdeMenu' => 'Goals_Goals'], ['fwdeView' => 'chart'], ['fwdeChartType' => 'donut'], ['fwdeSort' => 'bounce_rate'],
            ['fwdeOrder' => 'asc'], ['fwdeLimit' => '10'],
        ] as $change) {
            $this->assertSame($base, $this->archived(self::BASE + $change)->version, json_encode($change));
        }
        $this->assertSame($base, $this->archived(self::BASE, 'Another name')->version);
        $pivot = $this->archived(self::BASE + ['fwdePivot' => 'operatingSystem', 'fwdePivotLimit' => '3'])->version;
        $this->assertSame($pivot, $this->archived(self::BASE + ['fwdePivot' => 'operatingSystem', 'fwdePivotLimit' => '10'])->version);
    }

    public function testVersionFollowsData(): void
    {
        $base = $this->archived(self::BASE)->version;
        $this->assertNotSame($base, $this->archived(['fwdeDims' => 'browser'] + self::BASE)->version);
        $this->assertNotSame($base, $this->archived(['fwdeMetrics' => 'nb_visits'] + self::BASE)->version);
        $this->assertNotSame($base, $this->archived(self::BASE + ['fwdeFilters' => 'label~contains~Fire'])->version);
        $this->assertNotSame($base, $this->archived(self::BASE + ['fwdePivot' => 'operatingSystem'])->version);
        $this->assertNotSame($base, $this->archived(self::BASE, 'Name', 'browserCode==FF')->version);
    }

    public function testRecordNamesStartWithThePlugin(): void
    {
        $e = $this->archived(self::BASE);
        // preg_match: Matomo 5 runs PHPUnit 8.5, which has no assertMatchesRegularExpression
        $this->assertSame(1, preg_match('/^FeinwerkDataExploration_saved7_[0-9a-f]{8}$/', $e->recordName()), $e->recordName());
        $this->assertSame($e->recordName() . '_days', $e->markerName());
        $this->assertSame($e->recordName() . '_pt', $e->pivotTotalsName());
        $this->assertSame(7, $e->idSaved);
        $this->assertSame('anna', $e->owner);
    }

    public function testNotArchivable(): void
    {
        $this->assertNull($this->archived(self::BASE + ['fwdeKind' => 'kpi']));
        $this->assertNull($this->archived(self::BASE + ['fwdeKind' => 'funnel']));
        $this->assertNull($this->archived(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_uniq_visitors']));
        $this->assertNull($this->archived(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits,nb_uniq_visitors',
            'fwdeFilters' => 'nb_uniq_visitors~gt~10']));
        // a dimension that is gone: not archived, the live report says why
        $this->assertNull($this->archived(['fwdeDims' => 'noSuchDimension', 'fwdeMetrics' => 'nb_visits']));
        $this->assertNotNull($this->archived(['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits,nb_uniq_visitors']));
    }

    private function archived(array $state, string $name = 'Name', string $segment = ''): ?ArchivedExploration
    {
        $row = ['idsaved' => 7, 'idsite' => 1, 'login' => 'anna', 'name' => $name, 'state' => $state, 'segment' => $segment,
            'shared' => false, 'ts_created' => '2026-11-01 10:00:00', 'ts_updated' => '2026-11-02 10:00:00'];
        return ArchivedExploration::fromSavedRow($row, $this->definitionOf());
    }

    private function definitionOf(): callable
    {
        $noCustomDimensions = new class implements CustomDimensionSource {
            public function activeFor(int $idSite): array
            {
                return [];
            }
        };
        $dimensions = new DimensionCatalog(fn (string $coreId) => $coreId, $noCustomDimensions,
            new LabelFormatter(fn (string $key, array $params = []) => $key), new ColumnProbe(fn () => []));
        $metrics = new MetricCatalog(fn (int $idSite) => [3 => 'Newsletter'], fn (int $idSite) => false);
        return static fn (array $state, int $idSite): ExplorationDefinition => ExplorationDefinition::fromJson(
            (string) json_encode(SavedStateDefinition::toDefinition($state, $idSite, $dimensions, $metrics, new Limits())),
            $idSite,
            $dimensions,
            $metrics,
            new Limits()
        );
    }
}
