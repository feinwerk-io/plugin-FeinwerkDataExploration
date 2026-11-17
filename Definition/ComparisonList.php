<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Definition;

use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;

/**
 * The comparison columns of getExploration (comparison design, section 5), e.g.
 * [{"segment": "browserCode==FF", "period": "day", "date": "2026-03-27"}]: the columns after the own one. The free
 * version shows up to 3 columns per metric, the own one included; another plugin may raise the limit through
 * FeinwerkDataExploration.getLimits, up to MAX_COLUMNS.
 */
final class ComparisonList
{
    public const PERIODS = ['day', 'week', 'month', 'year', 'range'];
    public const MAX_COLUMNS = 10;

    public static function maxColumns(Limits $limits): int
    {
        return min(self::MAX_COLUMNS, max(1, $limits->maxCompareColumns));
    }

    /**
     * @param string $json as sent; '' and '[]' mean no comparison; HTML-escaped quotes are accepted
     * @return list<Comparison>
     * @throws ExplorationException
     */
    public static function fromJson(string $json, Limits $limits): array
    {
        if (trim($json) === '') {
            return [];
        }
        // decoded without assoc first: a JSON object ({}) must not pass as an empty list
        $raw = json_decode($json);
        if (!is_array($raw)) {
            $json = html_entity_decode($json, ENT_QUOTES);
            $raw = json_decode($json);
        }
        $data = is_array($raw) ? json_decode($json, true) : null;
        if (!is_array($data) || !array_is_list($data)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorComparisons');
        }
        $max = self::maxColumns($limits);
        if (count($data) > $max - 1) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorComparisonCount', [$max]);
        }

        $list = [];
        foreach ($data as $entry) {
            $segment = is_array($entry) ? ($entry['segment'] ?? null) : null;
            $period = is_array($entry) ? ($entry['period'] ?? null) : null;
            $date = is_array($entry) ? ($entry['date'] ?? null) : null;
            if (!is_string($segment) || !is_string($period) || !is_string($date)
                || !in_array(trim($period), self::PERIODS, true) || trim($date) === '') {
                throw new ExplorationException('FeinwerkDataExploration_ErrorComparisons');
            }
            $list[] = new Comparison(trim($segment), trim($period), trim($date));
        }
        return $list;
    }
}
