<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\System;

use Piwik\API\Request;
use Piwik\DataTable;

/**
 * Calls a core report and an exploration through the API and puts both into "label => numbers" maps.
 */
trait ParityHelper
{
    /**
     * @return array<string, array> label => columns, metadata under '__metadata'
     */
    private function coreRows(string $method, string $period, string $date, array $params = []): array
    {
        $table = Request::processRequest($method, array_merge([
            'idSite' => 1, 'period' => $period, 'date' => $date, 'format_metrics' => 0, 'filter_limit' => -1, 'language' => 'en',
        ], $params));
        $this->assertInstanceOf(DataTable::class, $table, $method);

        $rows = [];
        foreach ($table->getRows() as $row) {
            $rows[(string) $row->getColumn('label')] = $row->getColumns() + ['__metadata' => $row->getMetadata()];
        }
        return $rows;
    }

    /**
     * @return array<string, array> label (two dimensions: "label | secondary label") => columns
     */
    private function ourRows(array $definition, string $period, string $date, string $segment = ''): array
    {
        $table = Request::processRequest('FeinwerkDataExploration.getExploration', [
            'idSite' => 1, 'period' => $period, 'date' => $date, 'segment' => $segment, 'language' => 'en',
            'definition' => json_encode($definition + ['limit' => 1000]),
        ]);
        $this->assertInstanceOf(DataTable::class, $table);

        $rows = [];
        foreach ($table->getRows() as $row) {
            $key = (string) $row->getColumn('label');
            if ($row->getColumn('secondary_label') !== false) {
                $key .= ' | ' . $row->getColumn('secondary_label');
            }
            $this->assertArrayNotHasKey($key, $rows, 'duplicate exploration row ' . $key);
            $rows[$key] = $row->getColumns();
        }
        return $rows;
    }

    /**
     * Our visit metrics, computed from the raw columns of a core row (format_metrics=0).
     */
    private static function visitMetricsOfCoreRow(array $row, bool $withUniques, string $prefix = ''): array
    {
        $visits = (int) $row[$prefix . 'nb_visits'];
        $metrics = [
            'nb_visits' => $visits,
            'nb_actions' => (int) $row[$prefix . 'nb_actions'],
            'bounce_rate' => round($row[$prefix . 'bounce_count'] / $visits, 2),
            'avg_time_on_site' => round($row[$prefix . 'sum_visit_length'] / $visits),
        ];
        if ($withUniques) {
            $metrics['nb_uniq_visitors'] = (int) $row[$prefix . 'nb_uniq_visitors'];
        }
        return $metrics;
    }

    /**
     * label => [metric => value] with only the given metrics, as floats, sorted by label
     */
    private static function pick(array $rows, array $metrics): array
    {
        $picked = [];
        foreach ($rows as $label => $row) {
            foreach ($metrics as $metric) {
                $picked[$label][$metric] = (float) $row[$metric];
            }
        }
        ksort($picked);
        return $picked;
    }

    /**
     * Our visit metric comparison against a core report: every core row with visits must equal our row.
     *
     * @param callable|null $ourLabel maps our label to the core label
     * @param string[] $ignoreOurLabels our rows without a core counterpart (e.g. "… not defined")
     */
    private function assertVisitMetricsMatch(string $method, array $coreParams, string $dimension, string $period, string $date,
        bool $withUniques, ?callable $ourLabel = null, array $ignoreOurLabels = [], string $segment = ''): void
    {
        $metrics = ['nb_visits', 'nb_actions', 'bounce_rate', 'avg_time_on_site'];
        if ($withUniques) {
            $metrics[] = 'nb_uniq_visitors';
        }

        $expected = [];
        foreach ($this->coreRows($method, $period, $date, $coreParams + ['segment' => $segment]) as $label => $row) {
            if (!empty($row['nb_visits'])) {
                $expected[$label] = self::visitMetricsOfCoreRow($row, $withUniques);
            }
        }

        $actual = [];
        foreach ($this->ourRows(['dimensions' => [$dimension], 'metrics' => $metrics], $period, $date, $segment) as $label => $row) {
            if (!in_array($label, $ignoreOurLabels, true)) {
                $actual[$ourLabel ? $ourLabel($label) : $label] = $row;
            }
        }

        $this->assertNotEmpty($expected, "$method has no rows for $period $date");
        $this->assertEquals(self::pick($expected, $metrics), self::pick($actual, $metrics), "$dimension vs $method, $period $date");
    }

    /**
     * "example.com/blog/a" → "/blog/a"
     */
    private static function path(string $url): string
    {
        $url = preg_replace('~^[a-z]+://~i', '', $url);
        $slash = strpos($url, '/');
        return $slash === false ? '/' : substr($url, $slash);
    }
}
