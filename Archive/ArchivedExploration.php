<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Archive;

use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Saved\KpiCardSettings;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationRules;

/**
 * A saved exploration that Matomo archives (design 2026-11-25): a table (no KPI card, no funnel) whose metrics are not
 * only distinct counts and whose filters do not compare a distinct count (they cannot be added up across days; such
 * explorations stay live reports). Its version is a short hash of what shapes the stored data, so a change of the data
 * writes new records and no period mixes two definitions; how it is shown (name, menu, view, chart, sort, row limit,
 * pivot limit) is applied when reading and keeps the version.
 */
final class ArchivedExploration
{
    /** raised when the stored shape of every record changes (a new version for every exploration) */
    public const FORMAT = 1;

    private const PREFIX = 'FeinwerkDataExploration_saved';

    private function __construct(
        public readonly int $idSaved,
        public readonly int $idSite,
        public readonly string $owner,
        public readonly bool $shared,
        public readonly string $updated,
        public readonly ExplorationDefinition $definition,
        public readonly string $segment,
        public readonly string $version
    ) {
    }

    /**
     * @param array<string, mixed> $row a saved exploration as SavedExplorationStore lists it
     * @param callable(array<string, string>, int): ExplorationDefinition $definitionOf a saved state as a definition
     *                                                                                  (throws when it is unusable)
     */
    public static function fromSavedRow(array $row, callable $definitionOf): ?self
    {
        $state = is_array($row['state'] ?? null) ? $row['state'] : [];
        if (($state['fwdeKind'] ?? '') === SavedExplorationRules::KIND_FUNNEL || KpiCardSettings::isCard($state)) {
            return null;
        }
        $idSite = (int) ($row['idsite'] ?? 0);
        try {
            $definition = $definitionOf($state, $idSite);
        } catch (\Throwable $e) {
            return null;
        }
        if (!self::archivable($definition)) {
            return null;
        }
        $segment = (string) ($row['segment'] ?? '');
        return new self(
            (int) ($row['idsaved'] ?? 0),
            $idSite,
            (string) ($row['login'] ?? ''),
            (bool) ($row['shared'] ?? false),
            (string) ($row['ts_updated'] ?? ''),
            $definition,
            $segment,
            self::versionOf($definition, $segment)
        );
    }

    /** @return list<string> its distinct metrics (unique visitors, unique pageviews): days cannot be added up for them */
    public function distinctMetrics(): array
    {
        return array_values(array_filter($this->definition->metricIds,
            static fn (string $id): bool => (MetricCatalog::meta($id)['aggregation'] ?? '') === MetricDefinition::AGG_DISTINCT));
    }

    public function recordName(): string
    {
        return self::PREFIX . $this->idSaved . '_' . $this->version;
    }

    /** a numeric record per day (1), added up by Matomo: the number of a period's days that are archived */
    public function markerName(): string
    {
        return $this->recordName() . '_days';
    }

    /** the exact totals per pivot value, from which reading picks a period's top values */
    public function pivotTotalsName(): string
    {
        return $this->recordName() . '_pt';
    }

    /** the record names of one exploration start with this (every version) */
    public static function namePrefix(int $idSaved): string
    {
        return self::PREFIX . $idSaved . '_';
    }

    /** the filters applied when archiving, in the API's shape; null without filters */
    public function archiveFilters(): ?array
    {
        return self::archiveFiltersOf($this->definition);
    }

    /** only the conditions the archive applies (Archive/FilterSplit.php); the rest is applied when reading */
    private static function archiveFiltersOf(ExplorationDefinition $definition): ?array
    {
        [$archive] = FilterSplit::split($definition, FilterSplit::isMetricTarget(...));
        $groups = $archive->filterGroupsArray();
        return $groups === null ? null : ['match' => $archive->match, 'groups' => $groups];
    }

    /** the filter groups applied when reading, null when the archive applies them all */
    public function readFilters(): ?array
    {
        return FilterSplit::split($this->definition, FilterSplit::isMetricTarget(...))[1];
    }

    private static function versionOf(ExplorationDefinition $definition, string $segment): string
    {
        $stored = [];
        foreach ($definition->metricIds as $metricId) {
            $stored = array_merge($stored, StoredMetrics::columns($metricId));
        }
        $shape = [
            self::FORMAT,
            $definition->dimensionIds,
            $definition->pivot['dimension'] ?? null,
            $stored,
            $definition->idGoal,
            self::archiveFiltersOf($definition),
            $segment,
        ];
        return substr(sha1((string) json_encode($shape)), 0, 8);
    }

    private static function archivable(ExplorationDefinition $definition): bool
    {
        $distinct = static fn (string $id): bool => (MetricCatalog::meta($id)['aggregation'] ?? '') === MetricDefinition::AGG_DISTINCT;
        if ($definition->metricIds === [] || count(array_filter($definition->metricIds, $distinct)) === count($definition->metricIds)) {
            return false;
        }
        foreach ($definition->filters as $filter) {
            if ($distinct((string) ($filter['target'] ?? ''))) {
                return false;
            }
        }
        return true;
    }
}
