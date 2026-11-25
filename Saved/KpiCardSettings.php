<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Saved;

/**
 * What a KPI card shows (2026-11-24, Peter: "ja, passt so"): one metric of its exploration, a comparison, a line of the
 * last days and the change as percent or absolute. A card is a saved exploration with fwdeKind "kpi"; its settings are
 * the state keys fwdeKpiMetric, fwdeKpiCompare, fwdeKpiLine and fwdeKpiChange. Values outside their choices read as the
 * defaults (the save checks them, SavedExplorationRules::state()); a metric the exploration no longer has (a deleted
 * goal) reads as its first metric.
 */
final class KpiCardSettings
{
    /** none, the period before, the same weekday a week before, the mean of the same weekday in the 4 weeks before */
    public const COMPARES = ['none', 'previous', 'weekday', 'average4'];
    /** days of the line, 0 for none */
    public const LINES = [0, 7, 30];
    public const CHANGES = ['percent', 'absolute'];

    public const DEFAULT_COMPARE = 'average4';
    public const DEFAULT_LINE = 30;
    public const DEFAULT_CHANGE = 'percent';

    private function __construct(
        public readonly string $metric,
        public readonly string $compare,
        public readonly int $line,
        public readonly string $change
    ) {
    }

    /**
     * @param array<string, string> $state a saved exploration's state
     * @param list<string> $metricIds the metrics of its definition, in order
     * @throws SavedReportUnavailable when the card's metric is no longer among them (a deleted goal)
     */
    public static function fromState(array $state, array $metricIds): self
    {
        $metric = (string) ($state['fwdeKpiMetric'] ?? '');
        if ($metric !== '' && !in_array($metric, $metricIds, true)) {
            // its metric is gone (a deleted goal): never another metric under the card's name (release review 2026-11-25)
            throw new SavedReportUnavailable('FeinwerkDataExploration_ErrorSavedKpiMetricGone', [$metric]);
        }
        $compare = (string) ($state['fwdeKpiCompare'] ?? '');
        $line = $state['fwdeKpiLine'] ?? null;
        $change = (string) ($state['fwdeKpiChange'] ?? '');
        return new self(
            $metric !== '' ? $metric : (string) ($metricIds[0] ?? ''),
            in_array($compare, self::COMPARES, true) ? $compare : self::DEFAULT_COMPARE,
            is_string($line) && ctype_digit($line) && in_array((int) $line, self::LINES, true) ? (int) $line : self::DEFAULT_LINE,
            in_array($change, self::CHANGES, true) ? $change : self::DEFAULT_CHANGE
        );
    }

    /** @param array<string, string> $state */
    public static function isCard(array $state): bool
    {
        return ($state['fwdeKind'] ?? '') === SavedExplorationRules::KIND_KPI;
    }

    /**
     * The allowed values of a card key as the error names them, null for any other key.
     */
    public static function allowed(string $key): ?array
    {
        switch ($key) {
            case 'fwdeKpiCompare':
                return self::COMPARES;
            case 'fwdeKpiLine':
                return array_map('strval', self::LINES);
            case 'fwdeKpiChange':
                return self::CHANGES;
        }
        return null;
    }
}
