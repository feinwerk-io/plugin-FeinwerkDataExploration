<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\System;

use Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures\ExplorationFixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * A filtered exploration must equal the unfiltered one with the same filter applied to its rows.
 *
 * @group FeinwerkDataExploration
 */
class FilterParityTest extends SystemTestCase
{
    use ParityHelper;

    /** @var ExplorationFixture */
    public static $fixture = null;

    /**
     * @dataProvider filters
     */
    public function testFilteredEqualsUnfilteredRowsFilteredHere(array $dimensions, array $metrics, array $filters, int $minRows): void
    {
        $definition = ['dimensions' => $dimensions, 'metrics' => $metrics];
        $all = $this->ourRows($definition, 'range', ExplorationFixture::RANGE);
        $expected = array_filter($all, fn (array $row) => self::rowMatches($row, $filters));

        $actual = $this->ourRows($definition + ['filters' => $filters], 'range', ExplorationFixture::RANGE);

        ksort($expected);
        ksort($actual);
        $this->assertGreaterThanOrEqual($minRows, count($actual), 'the filter keeps rows');
        $this->assertLessThan(count($all), count($actual), 'the filter drops rows');
        $this->assertEquals($expected, $actual);
    }

    public static function filters(): array
    {
        return [
            'page URL contains' => [['pageUrl'], ['nb_pageviews'], [['target' => 'label', 'operator' => 'contains', 'value' => 'blog']], 2],
            'page URL not contains' => [['pageUrl'], ['nb_pageviews'], [['target' => 'label', 'operator' => 'notContains', 'value' => 'blog']], 2],
            'browser equals, label resolved' => [['browser'], ['nb_visits'], [['target' => 'label', 'operator' => 'equals', 'value' => 'chrome']], 1],
            'country contains, label resolved' => [['country'], ['nb_visits'], [['target' => 'label', 'operator' => 'contains', 'value' => 'an']], 2],
            'secondary label contains' => [['browser', 'pageUrl'], ['nb_pageviews'], [['target' => 'secondary_label', 'operator' => 'contains', 'value' => 'shop']], 2],
            'visits greater than' => [['browser'], ['nb_visits'], [['target' => 'nb_visits', 'operator' => 'gt', 'value' => 3]], 1],
            'pageviews less than' => [['pageUrl'], ['nb_pageviews'], [['target' => 'nb_pageviews', 'operator' => 'lt', 'value' => 3]], 1],
            'revenue greater than' => [['browser'], ['revenue'], [['target' => 'revenue', 'operator' => 'gt', 'value' => 0]], 1],
            'conversions greater than' => [['browser'], ['nb_conversions'], [['target' => 'nb_conversions', 'operator' => 'gt', 'value' => 0]], 1],
            'title with ampersand and apostrophe' => [['pageTitle'], ['nb_pageviews'], [['target' => 'label', 'operator' => 'contains', 'value' => "Tom & Jerry's"]], 1],
            'URL with ampersand' => [['pageUrl'], ['nb_pageviews'], [['target' => 'label', 'operator' => 'equals', 'value' => 'example.com/tips?a=1&b=2']], 1],
            'two filters' => [['pageUrl', 'eventCategory'], ['nb_events'], [
                ['target' => 'secondary_label', 'operator' => 'equals', 'value' => 'video'],
                ['target' => 'label', 'operator' => 'notContains', 'value' => 'shop'],
            ], 1],
        ];
    }

    private static function rowMatches(array $row, array $filters): bool
    {
        foreach ($filters as $filter) {
            // labels are compared as users see them (the JSON and HTML output decode Matomo's escaped texts)
            $value = is_string($row[$filter['target']]) ? html_entity_decode($row[$filter['target']], ENT_QUOTES) : $row[$filter['target']];
            $ok = match ($filter['operator']) {
                'contains' => mb_stripos((string) $value, (string) $filter['value']) !== false,
                'notContains' => mb_stripos((string) $value, (string) $filter['value']) === false,
                'equals' => mb_strtolower((string) $value) === mb_strtolower((string) $filter['value']),
                'gt' => $value > $filter['value'],
                'lt' => $value < $filter['value'],
            };
            if (!$ok) {
                return false;
            }
        }
        return true;
    }
}

FilterParityTest::$fixture = new ExplorationFixture();
