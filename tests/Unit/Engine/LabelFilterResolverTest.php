<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\LabelFormatter;
use Piwik\Plugins\FeinwerkDataExploration\Engine\LabelFilterResolver;
use Piwik\Plugins\FeinwerkDataExploration\Query\QueryContext;

/**
 * @group FeinwerkDataExploration
 */
class LabelFilterResolverTest extends TestCase
{
    private const DAYS = [
        'Intl_Day_Long_StandAlone_1' => 'Monday', 'Intl_Day_Long_StandAlone_2' => 'Tuesday',
        'Intl_Day_Long_StandAlone_3' => 'Wednesday', 'Intl_Day_Long_StandAlone_7' => 'Sunday',
        'General_Unknown' => 'Unknown',
    ];

    public function testContainsMatchesLabelsCaseInsensitively(): void
    {
        $resolver = new LabelFilterResolver(fn () => ['1', '2', '3']);

        $this->assertSame(['values' => ['2', '3'], 'includeNull' => false], $resolver->resolve($this->dimension('coded', 'weekday'), 'contains', 'ES', 1));
    }

    public function testEqualsNeedsWholeLabel(): void
    {
        $resolver = new LabelFilterResolver(fn () => ['1', '2', '3']);

        $this->assertSame(['values' => ['1'], 'includeNull' => false], $resolver->resolve($this->dimension('coded', 'weekday'), 'equals', 'monday', 1));
        $this->assertSame(['values' => [], 'includeNull' => false], $resolver->resolve($this->dimension('coded', 'weekday'), 'equals', 'mon', 1));
    }

    public function testNotContainsResolvesThePositiveMatches(): void
    {
        $resolver = new LabelFilterResolver(fn () => ['1', '2']);

        // the query builder negates; the resolver always returns the values whose label contains the text
        $this->assertSame(['values' => ['1'], 'includeNull' => false], $resolver->resolve($this->dimension('coded', 'weekday'), 'notContains', 'mon', 1));
    }

    public function testStaticDomainNeedsNoQuery(): void
    {
        $resolver = new LabelFilterResolver(function () {
            throw new \LogicException('no query for a static domain');
        });

        $this->assertSame(['values' => ['7'], 'includeNull' => false], $resolver->resolve($this->dimension('weekday', 'weekday'), 'equals', 'Sunday', 1));
    }

    public function testUnknownLabelMatchesNull(): void
    {
        $resolver = new LabelFilterResolver(fn () => [null]);

        $this->assertSame(['values' => [], 'includeNull' => true], $resolver->resolve($this->dimension('coded', 'country'), 'contains', 'unk', 1));
    }

    public function testEmptyStoredValueIsAValueNotNull(): void
    {
        // '' and NULL both read "Unknown", but only NULL may become "IS NULL" in SQL
        $resolver = new LabelFilterResolver(fn () => ['', null]);

        $this->assertSame(['values' => [''], 'includeNull' => true], $resolver->resolve($this->dimension('coded', 'country'), 'contains', 'unk', 1));
    }

    private function dimension(string $id, string $labelKind): DimensionDefinition
    {
        return new DimensionDefinition(
            $id,
            'test',
            'visit',
            'Test',
            $labelKind,
            static fn (QueryContext $c, string $alias): string => 'log_visit.test',
            null,
            new LabelFormatter(fn (string $key, array $params = []) => self::DAYS[$key] ?? $key)
        );
    }
}
