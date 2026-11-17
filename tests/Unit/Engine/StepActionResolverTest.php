<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Definition\FunnelStep;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Remember;
use Piwik\Plugins\FeinwerkDataExploration\Engine\StepActionResolver;

/**
 * Text steps of a funnel become log_action ids (spec 4), so the funnel query compares ids instead of joining texts.
 *
 * @group FeinwerkDataExploration
 */
class StepActionResolverTest extends TestCase
{
    /** @var list<array{sql: string, bind: array}> */
    private array $queries = [];
    /** @var list<array> */
    private array $rows = [];
    private array $cache = [];

    public function test_equals_contains_and_starts_with(): void
    {
        $this->rows = [['idaction' => '7'], ['idaction' => '9']];
        $this->assertSame([7, 9], $this->resolve(new FunnelStep('pageUrl', 'equals', 'example.com/a', null)));
        // only the actions of the site in the period: bounded like the funnel itself, never all of log_action
        $this->assertSame(
            'SELECT DISTINCT fwde_a.idaction_url AS idaction FROM matomo_log_link_visit_action AS fwde_a'
            . ' INNER JOIN matomo_log_action AS fwde_la ON fwde_la.idaction = fwde_a.idaction_url'
            . ' WHERE fwde_a.idsite = ? AND fwde_a.server_time >= ? AND fwde_a.server_time <= ?'
            . ' AND fwde_la.type = ? AND fwde_la.name = ? LIMIT 10001',
            $this->queries[0]['sql']
        );
        $this->assertSame([3, self::START, self::END, 1, 'example.com/a'], $this->queries[0]['bind']);

        $this->resolve(new FunnelStep('download', 'contains', '/a', null));
        $this->assertStringContainsString('fwde_la.type = ? AND fwde_la.name LIKE ?', $this->queries[1]['sql']);
        $this->assertSame([3, self::START, self::END, 3, '%/a%'], $this->queries[1]['bind']);

        $this->resolve(new FunnelStep('pageUrl', 'startsWith', 'example.com/a', null));
        $this->assertSame([3, self::START, self::END, 1, 'example.com/a%'], $this->queries[2]['bind']);
    }

    public function test_wildcards_are_escaped(): void
    {
        $this->resolve(new FunnelStep('outlink', 'contains', '50%_off\\', null));
        $this->assertSame('%50\\%\\_off\\\\%', $this->queries[0]['bind'][4]);
    }

    public function test_escaped_types_compare_the_sanitized_value(): void
    {
        $this->resolve(new FunnelStep('eventName', 'equals', 'Tom & Jerry', null));
        $this->assertSame([12, 'Tom &amp; Jerry'], array_slice($this->queries[0]['bind'], 3));
        $this->resolve(new FunnelStep('pageUrl', 'equals', 'a&b', null));
        $this->assertSame([1, 'a&b'], array_slice($this->queries[1]['bind'], 3));
    }

    public function test_every_text_type_has_its_column_and_action_type(): void
    {
        $expected = [
            'pageUrl' => ['idaction_url', 1], 'pageTitle' => ['idaction_name', 4], 'eventCategory' => ['idaction_event_category', 10],
            'eventAction' => ['idaction_event_action', 11], 'eventName' => ['idaction_name', 12], 'siteSearch' => ['idaction_name', 8],
            'download' => ['idaction_url', 3], 'outlink' => ['idaction_url', 2],
        ];
        $this->assertSame(array_keys(FunnelStep::OPERATORS), array_keys(StepActionResolver::ACTIONS));
        foreach ($expected as $type => [$column, $actionType]) {
            $this->assertSame([$column, $actionType], array_slice(StepActionResolver::ACTIONS[$type], 0, 2), $type);
        }
    }

    public function test_more_matches_than_the_cap_give_null(): void
    {
        $this->rows = [['idaction' => '1'], ['idaction' => '2'], ['idaction' => '3']];
        $step = new FunnelStep('pageUrl', 'contains', '/', null);
        $this->assertNull($this->resolve($step, 0, 2));
        $this->assertStringEndsWith('LIMIT 3', $this->queries[0]['sql']);

        $this->rows = [['idaction' => '1'], ['idaction' => '2']];
        $this->assertSame([1, 2], $this->resolve($step, 0, 2));
    }

    public function test_result_is_remembered(): void
    {
        $this->rows = [['idaction' => '4']];
        $step = new FunnelStep('pageTitle', 'contains', 'Shop', null);
        $this->assertSame([4], $this->resolve($step, 3600));
        $this->assertSame([4], $this->resolve(new FunnelStep('pageTitle', 'contains', 'Shop', null), 3600));
        $this->assertCount(1, $this->queries);

        // "too many" is remembered as well
        $this->rows = [['idaction' => '1'], ['idaction' => '2']];
        $broad = new FunnelStep('pageUrl', 'contains', '/', null);
        $this->assertNull($this->resolve($broad, 3600, 1));
        $this->assertNull($this->resolve($broad, 3600, 1));
        $this->assertCount(2, $this->queries);
    }

    /** Ids are remembered per site and period: another period (e.g. one with today) asks again. */
    public function test_the_remembered_ids_belong_to_site_and_period(): void
    {
        $step = new FunnelStep('pageTitle', 'contains', 'Shop', null);
        $this->resolve($step, 3600);
        $this->resolver()->resolve($step, 3, self::START, '2026-03-28 23:59:59', 300);
        $this->resolver()->resolve($step, 4, self::START, self::END, 3600);
        $this->assertCount(3, $this->queries);
    }

    public function test_name_condition_for_a_joined_column(): void
    {
        $bind = [];
        $sql = StepActionResolver::nameCondition(new FunnelStep('eventAction', 'contains', 'a_b', null), 'fwde_la.name', $bind);
        $this->assertSame('fwde_la.name LIKE ?', $sql);
        $this->assertSame(['%a\\_b%'], $bind);
    }

    private const START = '2026-03-27 00:00:00';
    private const END = '2026-03-27 23:59:59';

    private function resolve(FunnelStep $step, int $ttl = 0, int $maxIds = StepActionResolver::MAX_IDS): ?array
    {
        return $this->resolver($maxIds)->resolve($step, 3, self::START, self::END, $ttl);
    }

    private function resolver(int $maxIds = StepActionResolver::MAX_IDS): StepActionResolver
    {
        return new StepActionResolver(
            function (array $query): array {
                $this->queries[] = $query;
                return $this->rows;
            },
            static fn (string $table): string => 'matomo_' . $table,
            new Remember(
                fn (string $key) => $this->cache[$key] ?? false,
                function (string $key, $value, int $ttl): void {
                    $this->cache[$key] = $value;
                }
            ),
            $maxIds
        );
    }
}
