<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Query;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Definition\FunnelDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\FeinwerkDataExploration\Query\FunnelQueryBuilder;
use Piwik\Plugins\FeinwerkDataExploration\Query\QueryContext;

/**
 * The funnel in one statement (spec 4): the shape of the SQL. The counts themselves are proven against a fixture in
 * ApiFunnelTest.
 *
 * @group FeinwerkDataExploration
 */
class FunnelQueryBuilderTest extends TestCase
{
    private const PAGE = ['type' => 'pageUrl', 'operator' => 'contains', 'value' => '/a'];
    private const EVENT = ['type' => 'eventName', 'operator' => 'equals', 'value' => 'Play'];
    private const GOAL = ['type' => 'goal', 'idGoal' => 1];

    public function test_counts_one_column_per_step(): void
    {
        $query = $this->build([self::PAGE, self::EVENT, self::GOAL], [0 => [5], 1 => [6]]);
        foreach (['fwde_step1', 'fwde_step2', 'fwde_step3'] as $column) {
            $this->assertStringContainsString(' AS ' . $column, $query['sql']);
        }
        $this->assertStringNotContainsString('fwde_step4', $query['sql']);
        $this->assertSame(substr_count($query['sql'], '?'), count($query['bind']));
        $this->assertStringContainsString('matomo_log_link_visit_action', $query['sql']);
        $this->assertStringContainsString('matomo_log_conversion', $query['sql']);
    }

    public function test_binds_follow_the_placeholders(): void
    {
        $query = $this->build([self::PAGE, self::GOAL], [0 => [5]]);
        // level 1: site and window; level 2: window and goal id
        $this->assertSame([3, '2026-03-27 00:00:00', '2026-03-27 23:59:59', '2026-03-27 00:00:00', '2026-03-27 23:59:59', 1], $query['bind']);
    }

    public function test_every_step_is_strictly_later(): void
    {
        $actions = $this->build([self::PAGE, self::EVENT], [0 => [5], 1 => [6]])['sql'];
        $this->assertMatches('/\) > fwde_s\.fwde_p1/', $actions);
        $goals = $this->build([self::GOAL, self::GOAL], [])['sql'];
        $this->assertMatches('/\) > fwde_s\.fwde_p1/', $goals);
        $this->assertStringNotContainsString('>= fwde_s', $goals);
        // position: time to the second, then the action id
        $this->assertStringContainsString("DATE_FORMAT(fwde_a.server_time, '%Y%m%d%H%i%s'), LPAD(fwde_a.idlink_va, 20, '0')", $actions);
        // a conversion: its action's position (later than that action, as a longer string), then goal and buster, so
        // each conversion has its own position and never counts for two goal steps
        $this->assertStringContainsString(
            "IF(fwde_c.idlink_va IS NULL, '99999999999999999999', LPAD(fwde_c.idlink_va, 20, '0')),"
            . " LPAD(fwde_c.idgoal + 1, 11, '0'), LPAD(fwde_c.buster, 10, '0'))",
            $goals
        );
    }

    public function test_unresolved_step_joins_log_action_by_its_key_and_an_empty_list_matches_nothing(): void
    {
        $query = $this->build([self::PAGE, self::EVENT], [0 => null, 1 => []]);
        // by primary key per action row of the period, never a scan of all of log_action
        $this->assertStringContainsString('FROM (matomo_log_link_visit_action AS fwde_a INNER JOIN matomo_log_action AS fwde_la'
            . ' ON fwde_la.idaction = fwde_a.idaction_url)', $query['sql']);
        $this->assertStringContainsString('fwde_la.type = ? AND fwde_la.name LIKE ?', $query['sql']);
        $this->assertStringNotContainsString('IN (SELECT', $query['sql']);
        $this->assertContains('%/a%', $query['bind']);
        $this->assertStringContainsString('1 = 0', $query['sql']);
        $this->assertSame(substr_count($query['sql'], '?'), count($query['bind']));

        $level2 = $this->build([self::EVENT, self::PAGE], [0 => [6], 1 => null]);
        $this->assertStringContainsString('LEFT JOIN (matomo_log_link_visit_action AS fwde_a INNER JOIN matomo_log_action AS fwde_la'
            . ' ON fwde_la.idaction = fwde_a.idaction_url) ON fwde_a.idvisit = fwde_s.idvisit', $level2['sql']);
        $this->assertSame(substr_count($level2['sql'], '?'), count($level2['bind']));
        $this->assertStringContainsString('fwde_a.idaction_url IN (5, 7)', $this->build([self::PAGE, self::EVENT], [0 => [5, 7], 1 => [6]])['sql']);
    }

    public function test_page_steps_count_pageviews_only(): void
    {
        $title = ['type' => 'pageTitle', 'operator' => 'contains', 'value' => 'Shop'];
        $sql = $this->build([self::PAGE, $title], [0 => [5], 1 => [6]])['sql'];
        $this->assertSame(2, substr_count($sql, 'fwde_a.idaction_event_category IS NULL'));
        $sql = $this->build([self::EVENT, self::GOAL], [0 => [6]])['sql'];
        $this->assertStringNotContainsString('idaction_event_category IS NULL', $sql);
    }

    public function test_segment_visits_join_level_one_only(): void
    {
        $segment = ['sql' => 'SELECT log_visit.idvisit AS idvisit FROM log_visit WHERE log_visit.config_browser_name = ?', 'bind' => ['FF']];
        $query = $this->build([self::PAGE, self::EVENT, self::GOAL], [0 => [5], 1 => [6]], $segment);
        $this->assertSame(1, substr_count($query['sql'], 'AS fwde_seg'));
        $this->assertSame('FF', $query['bind'][0]);
        $this->assertSame(substr_count($query['sql'], '?'), count($query['bind']));
        $this->assertStringNotContainsString('fwde_seg', $this->build([self::PAGE, self::EVENT], [0 => [5], 1 => [6]])['sql']);
    }

    public function test_any_goal_counts_orders_not_abandoned_carts(): void
    {
        $sql = $this->build([['type' => 'anyGoal'], self::PAGE], [1 => [5]])['sql'];
        $this->assertStringContainsString('fwde_c.idgoal >= 0', $sql);
        $this->assertStringContainsString('FROM matomo_log_conversion AS fwde_c', $sql);
    }

    public function test_grouping_names_every_carried_position(): void
    {
        // MySQL 5.7 with ONLY_FULL_GROUP_BY refuses carried columns that are not grouped
        $sql = $this->build([self::PAGE, self::EVENT, self::GOAL], [0 => [5], 1 => [6]])['sql'];
        $this->assertStringContainsString('GROUP BY fwde_s.idvisit, fwde_s.fwde_p1, fwde_s.fwde_p2', $sql);
    }

    private function build(array $steps, array $resolved, ?array $segment = null): array
    {
        $definition = FunnelDefinition::fromJson(json_encode(['steps' => $steps]), [1 => 'Order'], new Limits());
        $context = new QueryContext(QueryContext::SCOPE_ACTION, 'UTC', '2026-03-27 00:00:00', '2026-03-27 23:59:59');
        return (new FunnelQueryBuilder(static fn (string $table): string => 'matomo_' . $table))
            ->build($definition, 3, $context, $resolved, $segment);
    }

    /** PHPUnit 8.5 (Matomo 5 runner) lacks assertMatchesRegularExpression */
    private function assertMatches(string $pattern, string $subject): void
    {
        $this->assertSame(1, preg_match($pattern, $subject), $pattern);
    }
}
