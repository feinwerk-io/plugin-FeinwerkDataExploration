<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Archive;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ReadFilter;

/**
 * Conditions applied to the added-up table of a period (design 2026-11-25): a metric condition holds for the period's
 * total, and the text conditions of the same groups behave as the live SQL (Query/ExplorationQueryBuilder::textFilterSql).
 */
class ReadFilterTest extends TestCase
{
    public function testMetricOnTheMonth(): void
    {
        // 60 visits on each of two days: under 100 per day, 120 in the month
        $groups = $this->filterGroups('all', [['all', [['nb_visits', 'gt', 100]]]]);
        $this->assertTrue(ReadFilter::keep($groups, $this->texts([]), $this->values(['nb_visits' => 120])));
        $this->assertFalse(ReadFilter::keep($groups, $this->texts([]), $this->values(['nb_visits' => 60])));
        foreach ([['gte', 120, true], ['lt', 120, false], ['lte', 120, true]] as [$op, $value, $expected]) {
            $this->assertSame($expected, ReadFilter::keep($this->filterGroups('all', [['all', [['nb_visits', $op, $value]]]]),
                $this->texts([]), $this->values(['nb_visits' => 120])), $op);
        }
        // a value Matomo cannot give for the period (a distinct count outside a day) holds no condition
        $this->assertFalse(ReadFilter::keep($groups, $this->texts([]), $this->values(['nb_visits' => null])));
    }

    public function testMixedAnyGroup(): void
    {
        $groups = $this->filterGroups('all', [['any', [['nb_visits', 'gt', 100], ['label', 'contains', 'fire']]]]);
        $this->assertTrue(ReadFilter::keep($groups, $this->texts(['label' => 'Firefox']), $this->values(['nb_visits' => 1])));
        $this->assertTrue(ReadFilter::keep($groups, $this->texts(['label' => 'Chrome']), $this->values(['nb_visits' => 500])));
        $this->assertFalse(ReadFilter::keep($groups, $this->texts(['label' => 'Chrome']), $this->values(['nb_visits' => 5])));
        $any = $this->filterGroups('any', [['all', [['nb_visits', 'gt', 100]]], ['all', [['secondary_label', 'equals', 'tablet']]]]);
        $this->assertTrue(ReadFilter::keep($any, $this->texts(['secondary_label' => 'Tablet']), $this->values(['nb_visits' => 1])));
        $this->assertFalse(ReadFilter::keep($any, $this->texts(['secondary_label' => 'Desktop']), $this->values(['nb_visits' => 1])));
    }

    public function testTextOperatorsMatchSql(): void
    {
        $cases = [
            ['contains', 'IRE', 'Firefox', true], ['contains', 'ire', null, false],
            ['notContains', 'ire', 'Firefox', false], ['notContains', 'ire', null, true], ['notContains', 'ire', 'Chrome', true],
            ['equals', 'firefox', 'Firefox', true], ['equals', 'x', null, false],
            ['notEquals', 'firefox', 'Firefox', false], ['notEquals', 'x', null, true],
            ['startsWith', 'fire', 'Firefox', true], ['startsWith', 'fox', 'Firefox', false], ['notStartsWith', 'fire', null, true],
            ['endsWith', 'FOX', 'Firefox', true], ['notEndsWith', 'fox', 'Firefox', false],
            ['matches', '^fire.*x$', 'Firefox', true], ['matches', '^chrome', 'Firefox', false], ['matches', '.*', null, false],
            // % and _ are literal, as the SQL escapes them
            ['contains', '50%', '50% off', true], ['contains', '5_', '50% off', false],
        ];
        foreach ($cases as [$operator, $value, $text, $expected]) {
            $groups = $this->filterGroups('all', [['all', [['label', $operator, $value]]]]);
            $this->assertSame($expected, ReadFilter::keep($groups, $this->texts(['label' => $text]), $this->values([])),
                $operator . ' ' . $value . ' on ' . var_export($text, true));
        }
    }

    public function testEmptyOperators(): void
    {
        $empty = $this->filterGroups('all', [['all', [['label', 'empty', '']]]]);
        $notEmpty = $this->filterGroups('all', [['all', [['label', 'notEmpty', '']]]]);
        foreach ([[null, true], ['', true], ['Firefox', false]] as [$text, $isEmpty]) {
            $this->assertSame($isEmpty, ReadFilter::keep($empty, $this->texts(['label' => $text]), $this->values([])));
            $this->assertSame(!$isEmpty, ReadFilter::keep($notEmpty, $this->texts(['label' => $text]), $this->values([])));
        }
    }

    private function filterGroups(string $match, array $groups): array
    {
        return ['match' => $match, 'groups' => array_map(static fn (array $g): array => ['match' => $g[0], 'filters' => array_map(
            static fn (array $f): array => ['target' => $f[0], 'operator' => $f[1], 'value' => $f[2]],
            $g[1]
        )], $groups)];
    }

    private function texts(array $texts): callable
    {
        return static fn (string $column): ?string => $texts[$column] ?? null;
    }

    private function values(array $values): callable
    {
        return static fn (string $metric) => $values[$metric] ?? null;
    }
}
