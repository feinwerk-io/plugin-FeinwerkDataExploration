<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\SavedReport;

use Piwik\Cache;
use Piwik\Category\Subcategory;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Definition\ExplorationDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\LimitsProvider;
use Piwik\Plugins\FeinwerkDataExploration\Query\QueryContext;
use Piwik\Plugins\FeinwerkDataExploration\Saved\ReportVisibility;
use Piwik\Plugins\FeinwerkDataExploration\Saved\KpiCardSettings;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationRules;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedStateDefinition;
use Piwik\Site;

/**
 * The saved explorations of a website as Matomo reports and menu entries for the current request (saved reports,
 * 2026-11-24): who sees which is ReportVisibility's rule; the menu section is the saved place (Ecommerce falls back to
 * Explorations on a website without e-commerce), "Explorations" when it has none; equal names in one section get the
 * exploration's number. Scheduled e-mails drop reports their owner may no longer see (filterForOwner()).
 */
final class SavedReports
{
    /** @var callable fn(int $idSite): bool */
    private $ecommerceOf;
    private ?DimensionCatalog $dimensionCatalog = null;
    private ?MetricCatalog $metricCatalog = null;
    private ?\Piwik\Plugins\FeinwerkDataExploration\Engine\Limits $limits = null;

    public function __construct(private readonly ReportVisibility $visibility = new ReportVisibility(), ?callable $ecommerceOf = null)
    {
        $this->ecommerceOf = $ecommerceOf ?? static fn (int $idSite): bool => Site::isEcommerceEnabledFor($idSite);
    }

    /** @return list<SavedReport> */
    public function reportsFor(int $idSite): array
    {
        try {
            $entries = $this->entries($idSite);
        } catch (\Throwable $e) {
            // a report list never fails because of explorations
            return [];
        }
        $reports = [];
        foreach ($entries as $entry) {
            $report = SavedReport::unconfigured();
            $this->apply($report, $entry, $idSite);
            $reports[] = $report;
        }
        return $reports;
    }

    /** Configures a report Matomo created by its module and action for the request's exploration. */
    public function configure(SavedReport $report, int $idSite, int $idSaved): void
    {
        foreach ($this->entries($idSite) as $entry) {
            if ((int) $entry['row']['idsaved'] === $idSaved) {
                $this->apply($report, $entry, $idSite);
                return;
            }
        }
    }

    /** @return list<Subcategory> one menu entry per placed exploration */
    public function subcategoriesFor(int $idSite): array
    {
        $subcategories = [];
        foreach ($this->entries($idSite) as $entry) {
            if (!$entry['placed']) {
                continue;
            }
            $subcategory = new Subcategory();
            $subcategory->setCategoryId($entry['category']);
            $subcategory->setId('fwdeSaved' . (int) $entry['row']['idsaved']);
            $subcategory->setName($entry['name']);
            $subcategory->setOrder($entry['order']);
            $subcategories[] = $subcategory;
        }
        return $subcategories;
    }

    /**
     * Leaves out of a scheduled e-mail every saved exploration its owner may no longer see (made private by someone
     * else, deleted, the owner below the plugin's minimum role): Matomo checks the chosen reports only when the
     * scheduled report is saved.
     *
     * @param array $processedReports Matomo's processed reports of the e-mail
     * @param array $report the scheduled report (idsite, login)
     */
    public function filterForOwner(array &$processedReports, array $report): void
    {
        $visible = null;
        foreach ($processedReports as $i => $processed) {
            $metadata = $processed['metadata'] ?? [];
            if (($metadata['module'] ?? '') !== 'FeinwerkDataExploration' || ($metadata['action'] ?? '') !== 'getSavedReport') {
                continue;
            }
            $visible ??= array_map('intval', array_column($this->visibility->visibleTo((string) $report['login'], (int) $report['idsite']), 'idsaved'));
            if (!in_array((int) ($metadata['parameters']['idSaved'] ?? 0), $visible, true)) {
                unset($processedReports[$i]);
            }
        }
        $processedReports = array_values($processedReports);
    }

    /** The website of the request, as Matomo's report lists know it (idSite, or a single idSites). */
    public static function requestedSite(): int
    {
        $idSite = Common::getRequestVar('idSite', 0, 'int');
        if ($idSite > 0) {
            return $idSite;
        }
        $idSites = Common::getRequestVar('idSites', '', null);
        if (is_array($idSites)) {
            return count($idSites) === 1 ? (int) reset($idSites) : 0;
        }
        // Matomo 6 no longer trims request values
        $idSites = trim((string) $idSites);
        return ctype_digit($idSites) ? (int) $idSites : 0;
    }

    /**
     * The visible explorations of the website with name, section and order, read once per request and asker: Matomo
     * asks for its report lists, menus and widgets many times per page.
     *
     * @return list<array{row: array, name: string, category: string, placed: bool, order: int}>
     */
    private function entries(int $idSite): array
    {
        $cache = Cache::getTransientCache();
        $key = 'FeinwerkDataExploration_savedReports_' . $idSite . '_' . md5((string) json_encode($this->visibility->context()));
        if ($cache->contains($key)) {
            return $cache->fetch($key);
        }
        $entries = $this->readEntries($idSite);
        $cache->save($key, $entries);
        return $entries;
    }

    /** @return list<array{row: array, name: string, category: string, placed: bool, order: int}> */
    private function readEntries(int $idSite): array
    {
        $ecommerce = null;
        $entries = [];
        foreach ($this->visibility->rows($idSite) as $position => $row) {
            // a KPI card is a dashboard widget and an e-mail report, never a menu entry (2026-11-24)
            $menu = KpiCardSettings::isCard($row['state']) ? null : SavedExplorationRules::menuOf($row['state']);
            if ($menu === SavedExplorationRules::MENU_ECOMMERCE && !($ecommerce ??= ($this->ecommerceOf)($idSite))) {
                $menu = SavedExplorationRules::MENU_EXPLORATIONS;
            }
            $entries[] = [
                'row' => $row,
                'name' => (string) $row['name'],
                'category' => $menu ?? SavedExplorationRules::MENU_EXPLORATIONS,
                'placed' => $menu !== null,
                'order' => 1000 + $position,
            ];
        }
        $count = [];
        foreach ($entries as $entry) {
            $key = $entry['category'] . "\n" . mb_strtolower($entry['name']);
            $count[$key] = ($count[$key] ?? 0) + 1;
        }
        foreach ($entries as $i => $entry) {
            if ($count[$entry['category'] . "\n" . mb_strtolower($entry['name'])] > 1) {
                $entries[$i]['name'] .= ' (' . (int) $entry['row']['idsaved'] . ')';
            }
        }
        return $entries;
    }

    private function apply(SavedReport $report, array $entry, int $idSite): void
    {
        // one set of catalogs for every exploration of the request (no catalog reads per exploration)
        $this->dimensionCatalog ??= new DimensionCatalog();
        $this->metricCatalog ??= new MetricCatalog();
        $this->limits ??= (new LimitsProvider())->get();
        $dimensionCatalog = $this->dimensionCatalog;
        $metricCatalog = $this->metricCatalog;
        $limits = $this->limits;
        $translate = [Piwik::class, 'translate'];
        $dimensions = null;
        $metrics = [];
        try {
            $definition = ExplorationDefinition::fromJson(
                (string) json_encode(SavedStateDefinition::toDefinition($entry['row']['state'], $idSite, $dimensionCatalog, $metricCatalog, $limits)),
                $idSite,
                $dimensionCatalog,
                $metricCatalog,
                $limits
            );
            $dimensions = [];
            $card = KpiCardSettings::isCard($entry['row']['state'])
                ? KpiCardSettings::fromState($entry['row']['state'], $definition->metricIds)
                : null;
            // a KPI card is a report without a dimension: its one metric for the period (2026-11-24)
            $levelIds = $card !== null ? [] : $definition->dimensionIds;
            if ($card === null && $definition->pivot !== null) {
                // the pivot is the last level of the report (P2)
                $levelIds[] = $definition->pivot['dimension'];
            }
            foreach ($levelIds as $id) {
                $dimensions[] = new SavedReportDimension($id, $dimensionCatalog->get($idSite, $id)->name);
            }
            $actionScope = $definition->scope($dimensionCatalog, $metricCatalog, $idSite) === QueryContext::SCOPE_ACTION;
            foreach ($card !== null ? [$card->metric] : $definition->metricIds as $id) {
                $metric = $metricCatalog->get($id, $idSite) ?? $metricCatalog->get($id);
                $metrics[$id] = [
                    'name' => $metric->label($translate, $actionScope),
                    'unit' => $metric->unit,
                    'description' => MetricCatalog::description($id, $translate, $metric->goalName),
                    'goal' => $metric->goalKey !== null,
                ];
            }
        } catch (ExplorationException $e) {
            // still a report: the API method answers with the reason (Email Reports Pro shows it); any other refusal of
            // the saved state (also SavedReportUnavailable) never breaks Matomo's report and widget lists for the
            // website (release review 2026-11-25)
            $dimensions = null;
            $metrics = [];
        }
        $report->configureFor($entry['row'], $entry['name'], $entry['category'], $entry['placed'], $entry['order'], $dimensions, $metrics);
    }
}
