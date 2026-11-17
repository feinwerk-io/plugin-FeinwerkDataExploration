<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Definition;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ComparisonList;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;

/**
 * The comparison columns of getExploration (comparison design, section 5): the columns after the own one, each a
 * segment, a period and a date; free up to 3 columns per metric, the own column included.
 *
 * @group FeinwerkDataExploration
 */
class ComparisonListTest extends TestCase
{
    public function testParsesSegmentAndPeriodColumns(): void
    {
        $list = ComparisonList::fromJson(json_encode([
            ['segment' => ' browserCode==FF ', 'period' => ' day ', 'date' => ' 2026-03-27 '],
            ['segment' => '', 'period' => 'range', 'date' => '2026-03-01,2026-03-07'],
        ]), new Limits());

        $this->assertCount(2, $list);
        $this->assertSame(['browserCode==FF', 'day', '2026-03-27'], [$list[0]->segment, $list[0]->period, $list[0]->date]);
        $this->assertSame(['', 'range', '2026-03-01,2026-03-07'], [$list[1]->segment, $list[1]->period, $list[1]->date]);
    }

    public function testEmptyMeansNoComparison(): void
    {
        foreach (['', '  ', '[]'] as $json) {
            $this->assertSame([], ComparisonList::fromJson($json, new Limits()), var_export($json, true));
        }
    }

    public function testColumnLimitCountsTheOwnColumn(): void
    {
        $column = ['segment' => '', 'period' => 'day', 'date' => '2026-03-27'];
        $this->assertCount(2, ComparisonList::fromJson(json_encode([$column, $column]), new Limits()));
        $this->assertRefused(json_encode([$column, $column, $column]), 'FeinwerkDataExploration_ErrorComparisonCount', [3]);
        $this->assertCount(4, ComparisonList::fromJson(json_encode(array_fill(0, 4, $column)), new Limits(maxCompareColumns: 5)));
    }

    public function testMaxColumnsIsClamped(): void
    {
        $this->assertSame(3, ComparisonList::maxColumns(new Limits()));
        $this->assertSame(1, ComparisonList::maxColumns(new Limits(maxCompareColumns: 0)));
        $this->assertSame(10, ComparisonList::maxColumns(new Limits(maxCompareColumns: 50)));
    }

    public function testInvalidEntriesAreRefused(): void
    {
        $ok = ['segment' => '', 'period' => 'day', 'date' => '2026-03-27'];
        $invalid = [
            '{}',
            '{"segment": ""}',
            '[1]',
            'no json',
            json_encode([['period' => 'decade', 'date' => '2026'] + $ok]),
            json_encode([['date' => 5] + $ok]),
            json_encode([['segment' => []] + $ok]),
            json_encode([['segment' => '', 'period' => 'day']]),
        ];
        foreach ($invalid as $json) {
            $this->assertRefused($json, 'FeinwerkDataExploration_ErrorComparisons');
        }
    }

    public function testHtmlEscapedQuotesAreAccepted(): void
    {
        $json = htmlspecialchars(json_encode([['segment' => 'browserCode==FF', 'period' => 'day', 'date' => 'yesterday']]), ENT_QUOTES);
        $this->assertSame('browserCode==FF', ComparisonList::fromJson($json, new Limits())[0]->segment);
    }

    private function assertRefused(string $json, string $key, ?array $params = null): void
    {
        try {
            ComparisonList::fromJson($json, new Limits());
            $this->fail('expected a refusal of ' . $json);
        } catch (ExplorationException $e) {
            $this->assertSame($key, $e->getTranslationKey(), $json);
            if ($params !== null) {
                $this->assertSame($params, $e->getParams(), $json);
            }
        }
    }
}
