<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Catalog;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;

/**
 * @group FeinwerkDataExploration
 */
class MetricCatalogTest extends TestCase
{
    public function testIdsAndScopesMatchSpec(): void
    {
        $expected = [
            'nb_visits' => 'visit',
            'nb_uniq_visitors' => 'visit',
            'nb_actions' => 'visit',
            'bounce_rate' => 'visit',
            'avg_time_on_site' => 'visit',
            'nb_actions_per_visit' => 'visit',
            'max_actions' => 'visit',
            'nb_conversions' => 'goal',
            'conversion_rate' => 'goal',
            'revenue' => 'goal',
            'nb_pageviews' => 'action',
            'nb_uniq_pageviews' => 'action',
            'nb_events' => 'action',
            'sum_event_value' => 'action',
            'avg_event_value' => 'action',
            'nb_downloads' => 'action',
            'nb_outlinks' => 'action',
            'nb_searches' => 'action',
        ];

        $scopes = [];
        foreach ((new MetricCatalog())->all() as $id => $metric) {
            $this->assertSame($id, $metric->id);
            $scopes[$id] = $metric->scope;
        }

        $this->assertSame($expected, $scopes);
    }

    public function testUnknownIdReturnsNull(): void
    {
        $this->assertNull((new MetricCatalog())->get('nb_hits'));
    }

    public function testKnownIdReturnsDefinition(): void
    {
        $this->assertSame('action', (new MetricCatalog())->get('nb_events')->scope);
    }

    public function testMetricKinds(): void
    {
        $kinds = array_map(fn ($metric) => $metric->kind, (new MetricCatalog())->all());

        $this->assertSame('pageview', $kinds['nb_pageviews']);
        $this->assertSame('pageview', $kinds['nb_uniq_pageviews']);
        $this->assertSame('event', $kinds['nb_events']);
        $this->assertSame('event', $kinds['sum_event_value']);
        $this->assertSame('download', $kinds['nb_downloads']);
        $this->assertSame('outlink', $kinds['nb_outlinks']);
        $this->assertSame('event', $kinds['avg_event_value']);
        $this->assertSame('search', $kinds['nb_searches']);
        $this->assertNull($kinds['max_actions']);
        $this->assertNull($kinds['nb_visits']);
        $this->assertNull($kinds['revenue']);
    }

    public function testEveryVisitMetricHasActionScopeName(): void
    {
        foreach ((new MetricCatalog())->all() as $id => $metric) {
            if ($metric->scope === 'visit') {
                $this->assertNotNull($metric->nameInActionScopeKey, $id);
                $this->assertNotSame($metric->nameKey, $metric->nameInActionScopeKey, $id);
            } else {
                $this->assertNull($metric->nameInActionScopeKey, $id);
            }
        }
    }
}
