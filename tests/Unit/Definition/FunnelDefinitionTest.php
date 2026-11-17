<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Definition;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Definition\FunnelDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;

/**
 * The funnel definition (spec 3 and 4): 2 to maxFunnelSteps steps, each a text condition on one kind of action or a
 * goal of the website.
 *
 * @group FeinwerkDataExploration
 */
class FunnelDefinitionTest extends TestCase
{
    private const GOALS = [1 => 'Order', 2 => 'Newsletter'];

    public function testParsesEveryStepType(): void
    {
        $definition = $this->parse([
            ['type' => 'pageTitle', 'operator' => 'startsWith', 'value' => '  Shop '],
            ['type' => 'eventCategory', 'operator' => 'equals', 'value' => 'Video'],
            ['type' => 'goal', 'idGoal' => 1],
        ]);
        $this->assertSame(['pageTitle', 'startsWith', 'Shop', null], self::fields($definition->steps[0]));
        $this->assertSame(['eventCategory', 'equals', 'Video', null], self::fields($definition->steps[1]));
        $this->assertSame(['goal', '', '', 1], self::fields($definition->steps[2]));
        $this->assertSame('Order', $definition->steps[2]->goalName);
        $this->assertTrue($definition->steps[2]->isGoal());
        $this->assertFalse($definition->steps[0]->isGoal());

        $types = [
            'pageUrl' => 'contains', 'eventAction' => 'contains', 'eventName' => 'equals', 'siteSearch' => 'contains',
            'download' => 'equals', 'outlink' => 'contains',
        ];
        foreach ($types as $type => $operator) {
            $parsed = $this->parse([['type' => $type, 'operator' => $operator, 'value' => 'x'], ['type' => 'anyGoal']]);
            $this->assertSame([$type, $operator, 'x', null], self::fields($parsed->steps[0]), $type);
            $this->assertSame(['anyGoal', '', '', null], self::fields($parsed->steps[1]));
            $this->assertTrue($parsed->steps[1]->isGoal());
        }
    }

    public function testStepCountMustBeTwoToMax(): void
    {
        $step = ['type' => 'pageUrl', 'operator' => 'contains', 'value' => '/a'];
        $this->assertRejected(['steps' => [$step]], 'FeinwerkDataExploration_ErrorFunnelStepCount', [3]);
        $this->assertRejected(['steps' => [$step, $step, $step, $step]], 'FeinwerkDataExploration_ErrorFunnelStepCount', [3]);
        $this->assertRejected(['steps' => ['a' => $step, 'b' => $step]], 'FeinwerkDataExploration_ErrorFunnelStepCount');
        $this->assertRejected(['steps' => 'none'], 'FeinwerkDataExploration_ErrorFunnelStepCount');
        $this->assertCount(3, $this->parse([$step, $step, $step])->steps);
    }

    public function testMaxStepsFollowsTheLimitAndIsClamped(): void
    {
        $this->assertSame(3, FunnelDefinition::maxSteps(new Limits()));
        $this->assertSame(5, FunnelDefinition::maxSteps(new Limits(maxFunnelSteps: 5)));
        $this->assertSame(2, FunnelDefinition::maxSteps(new Limits(maxFunnelSteps: 1)));
        $this->assertSame(10, FunnelDefinition::maxSteps(new Limits(maxFunnelSteps: 50)));

        $step = ['type' => 'pageUrl', 'operator' => 'contains', 'value' => '/a'];
        $json = json_encode(['steps' => [$step, $step, $step, $step]]);
        $this->assertCount(4, FunnelDefinition::fromJson($json, self::GOALS, new Limits(maxFunnelSteps: 4))->steps);
    }

    public function testInvalidStepsAreRefusedWithTheirNumber(): void
    {
        $ok = ['type' => 'pageUrl', 'operator' => 'contains', 'value' => '/a'];
        $invalid = [
            ['type' => 'pageViews', 'operator' => 'contains', 'value' => '/a'],
            ['type' => 'eventName', 'operator' => 'startsWith', 'value' => 'a'],
            ['type' => 'pageUrl', 'operator' => 'notContains', 'value' => 'a'],
            ['type' => 'pageUrl', 'operator' => 'contains', 'value' => '   '],
            ['type' => 'pageUrl', 'operator' => 'contains', 'value' => str_repeat('a', 1001)],
            ['type' => 'pageUrl', 'operator' => 'contains', 'value' => 5],
            ['type' => 'pageUrl', 'operator' => 'contains'],
            'pageUrl',
        ];
        foreach ($invalid as $step) {
            $this->assertRejected(['steps' => [$ok, $step]], 'FeinwerkDataExploration_ErrorFunnelStep', [2]);
        }
        $this->assertCount(2, $this->parse([$ok, ['type' => 'pageUrl', 'operator' => 'contains', 'value' => str_repeat('ä', 1000)]])->steps);
    }

    public function testUnknownGoalIsRefused(): void
    {
        $ok = ['type' => 'pageUrl', 'operator' => 'contains', 'value' => '/a'];
        $this->assertRejected(['steps' => [$ok, ['type' => 'goal', 'idGoal' => 99]]], 'FeinwerkDataExploration_ErrorFunnelGoal', [2]);
        $this->assertRejected(['steps' => [['type' => 'goal', 'idGoal' => '1'], $ok]], 'FeinwerkDataExploration_ErrorFunnelGoal', [1]);
        $this->assertRejected(['steps' => [$ok, ['type' => 'goal']]], 'FeinwerkDataExploration_ErrorFunnelGoal', [2]);
    }

    public function testUrlStepsDropSchemeAndWww(): void
    {
        $values = [
            'https://www.example.com/shop' => 'example.com/shop',
            'HTTP://Example.com/a' => 'Example.com/a',
            'www.example.com' => 'example.com',
            '/shop' => '/shop',
        ];
        foreach (['pageUrl', 'download', 'outlink'] as $type) {
            foreach ($values as $typed => $stored) {
                $parsed = $this->parse([['type' => $type, 'operator' => 'equals', 'value' => $typed], ['type' => 'anyGoal']]);
                $this->assertSame($stored, $parsed->steps[0]->value, $type . ' ' . $typed);
            }
        }
        $title = $this->parse([['type' => 'pageTitle', 'operator' => 'equals', 'value' => 'https://x'], ['type' => 'anyGoal']]);
        $this->assertSame('https://x', $title->steps[0]->value);

        $this->assertRejected(
            ['steps' => [['type' => 'pageUrl', 'operator' => 'equals', 'value' => 'https://'], ['type' => 'anyGoal']]],
            'FeinwerkDataExploration_ErrorFunnelStep',
            [1]
        );
    }

    public function testHtmlEscapedQuotesAreAccepted(): void
    {
        $json = htmlspecialchars(json_encode(['steps' => [
            ['type' => 'pageUrl', 'operator' => 'contains', 'value' => '/a'],
            ['type' => 'anyGoal'],
        ]]), ENT_QUOTES);
        $this->assertCount(2, FunnelDefinition::fromJson($json, self::GOALS, new Limits())->steps);
    }

    public function testNotJsonIsRefused(): void
    {
        foreach (['', 'steps', '[]', '"x"'] as $json) {
            try {
                FunnelDefinition::fromJson($json, self::GOALS, new Limits());
                $this->fail('expected a refusal of ' . $json);
            } catch (ExplorationException $e) {
                $this->assertSame('FeinwerkDataExploration_ErrorInvalidDefinition', $e->getTranslationKey(), $json);
            }
        }
    }

    public function testNormalizedIsStableForTheCacheKey(): void
    {
        $a = FunnelDefinition::fromJson('{"steps":[{"type":"pageUrl","operator":"contains","value":"/a"},{"type":"goal","idGoal":1}]}', self::GOALS, new Limits());
        $b = FunnelDefinition::fromJson('{"steps":[{"value":"/a","operator":"contains","type":"pageUrl"},{"idGoal":1,"type":"goal"}]}', self::GOALS, new Limits());
        $expected = ['steps' => [['pageUrl', 'contains', '/a', null], ['goal', '', '', 1]]];
        $this->assertSame($expected, $a->normalized());
        $this->assertSame($expected, $b->normalized());
    }

    private function parse(array $steps): FunnelDefinition
    {
        return FunnelDefinition::fromJson(json_encode(['steps' => $steps]), self::GOALS, new Limits());
    }

    private function assertRejected(array $data, string $key, ?array $params = null): void
    {
        $json = json_encode($data);
        try {
            FunnelDefinition::fromJson($json, self::GOALS, new Limits());
            $this->fail('expected an ExplorationException for ' . $json);
        } catch (ExplorationException $e) {
            $this->assertSame($key, $e->getTranslationKey(), $json);
            if ($params !== null) {
                $this->assertSame($params, $e->getParams(), $json);
            }
        }
    }

    private static function fields($step): array
    {
        return [$step->type, $step->operator, $step->value, $step->idGoal];
    }
}
