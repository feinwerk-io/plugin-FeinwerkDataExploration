<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Archive;

use Piwik\Cache;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\LimitsProvider;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationStore;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedStateDefinition;

/**
 * The saved explorations Matomo archives (design 2026-11-25, Peter: "1, wie empfohlen"): every saved table of a website,
 * private and shared, at most `archive_max_explorations_per_site` of them (shared ones first, then private ones, each by
 * most recent change); none while `archive_reports` is off. The rest stay live reports. One query per call.
 */
final class ArchivedExplorations
{
    /** @var callable(int): array */
    private $rowsOfSite;

    /** @var callable(): array */
    private $rowsOfEverySite;

    /** @var callable(array, int): ExplorationDefinition */
    private $definitionOf;

    /**
     * @param callable(int): array|null $rowsOfSite a website's saved rows (default: SavedExplorationStore::listAll)
     * @param callable(): array|null $rowsOfEverySite every website's saved rows (default: listEverySite)
     * @param callable(array, int): ExplorationDefinition|null $definitionOf a saved state as a definition
     */
    public function __construct(
        private readonly ArchiveSettings $settings,
        ?callable $rowsOfSite = null,
        ?callable $rowsOfEverySite = null,
        ?callable $definitionOf = null
    ) {
        $store = null;
        $this->rowsOfSite = $rowsOfSite ?? static function (int $idSite) use (&$store): array {
            $store ??= new SavedExplorationStore();
            return $store->listAll($idSite);
        };
        $this->rowsOfEverySite = $rowsOfEverySite ?? static function () use (&$store): array {
            $store ??= new SavedExplorationStore();
            return $store->listEverySite();
        };
        $this->definitionOf = $definitionOf ?? self::definitions();
    }

    /** @return list<ArchivedExploration> */
    public function forSite(int $idSite): array
    {
        if (!$this->settings->reports) {
            return [];
        }
        return $this->select(($this->rowsOfSite)($idSite));
    }

    /**
     * The website's archived exploration with this id, from a list read once per request (Matomo's transient cache):
     * a flat or expanded report asks every sub table, each a request of its own to the API (final review 2026-11-26).
     */
    public static function cachedFind(int $idSite, int $idSaved, ArchiveSettings $settings): ?ArchivedExploration
    {
        $cache = Cache::getTransientCache();
        $key = 'FeinwerkDataExploration_archived_' . $idSite . '_' . md5(serialize($settings));
        $list = $cache->fetch($key);
        if (!is_array($list)) {
            $list = (new self($settings))->forSite($idSite);
            $cache->save($key, $list);
        }
        foreach ($list as $exploration) {
            if ($exploration->idSaved === $idSaved) {
                return $exploration;
            }
        }
        return null;
    }

    /** the website's archived exploration with this id, null when it is not archived (or not this website's) */
    public function find(int $idSite, int $idSaved): ?ArchivedExploration
    {
        foreach ($this->forSite($idSite) as $exploration) {
            if ($exploration->idSaved === $idSaved) {
                return $exploration;
            }
        }
        return null;
    }

    /** @return list<ArchivedExploration> every website's, the cap applied per website */
    public function forAllSites(): array
    {
        if (!$this->settings->reports) {
            return [];
        }
        $bySite = [];
        foreach (($this->rowsOfEverySite)() as $row) {
            $bySite[(int) $row['idsite']][] = $row;
        }
        $all = [];
        foreach ($bySite as $rows) {
            $all = array_merge($all, $this->select($rows));
        }
        return $all;
    }

    /** @return list<ArchivedExploration> */
    private function select(array $rows): array
    {
        $archived = [];
        foreach ($rows as $row) {
            $exploration = ArchivedExploration::fromSavedRow($row, $this->definitionOf);
            if ($exploration !== null) {
                $archived[] = $exploration;
            }
        }
        usort($archived, static fn (ArchivedExploration $a, ArchivedExploration $b): int => [$b->shared, $b->updated, $b->idSaved]
            <=> [$a->shared, $a->updated, $a->idSaved]);
        $cap = $this->settings->maxExplorationsPerSite;
        return $cap > 0 ? array_slice($archived, 0, $cap) : $archived;
    }

    /** @return callable(array, int): ExplorationDefinition as getSavedReport builds it */
    private static function definitions(): callable
    {
        $dimensions = null;
        $metrics = null;
        $limits = null;
        return static function (array $state, int $idSite) use (&$dimensions, &$metrics, &$limits): ExplorationDefinition {
            $dimensions ??= new DimensionCatalog();
            $metrics ??= new MetricCatalog();
            $limits ??= (new LimitsProvider())->get();
            return ExplorationDefinition::fromJson(
                (string) json_encode(SavedStateDefinition::toDefinition($state, $idSite, $dimensions, $metrics, $limits)),
                $idSite,
                $dimensions,
                $metrics,
                $limits
            );
        };
    }
}
