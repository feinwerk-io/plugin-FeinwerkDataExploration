<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration;

use Piwik\Common;
use Piwik\Option;
use Piwik\Plugins\FeinwerkDataExploration\Feedback\PluginFeedback;
use Piwik\Plugins\FeinwerkDataExploration\Reports\OpenInExploration;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationStore;
use Piwik\Plugins\FeinwerkDataExploration\SavedReport\SavedReport;
use Piwik\Plugins\FeinwerkDataExploration\SavedReport\SavedReports;

class FeinwerkDataExploration extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles' => 'getJavaScriptFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'ViewDataTable.configure.end' => 'addOpenInExploration',
            'API.getPagesComparisonsDisabledFor' => 'disableComparison',
            'Db.getTablesInstalled' => 'getTablesInstalled',
            'UsersManager.deleteUser' => 'deleteUser',
            'SitesManager.deleteSite.end' => 'deleteSite',
            // saved explorations as Matomo reports (2026-11-24)
            'Report.addReports' => 'addSavedReports',
            'Category.addSubcategories' => 'addSavedSubcategories',
            'ScheduledReports.processReports' => 'filterSavedReports',
            // KPI cards (2026-11-24): no graph over runs of weeks or months, which a card does not answer
            'API.getReportMetadata.end' => ['function' => 'graphsOnlyForDays', 'after' => true],
            // archived saved explorations (2026-11-25): one record builder per exploration of the archived website
            'Archiver.addRecordBuilders' => 'addRecordBuilders',
        ];
    }

    /**
     * The saved explorations as reports: one lister instance, which lists every saved exploration the request may see
     * on the website Matomo names (SavedReport::configureReportMetadata(), configureWidgets()).
     */
    public function addSavedReports(&$instances)
    {
        $instances[] = SavedReport::unconfigured();
    }

    /** One menu entry per saved exploration placed in a section. */
    public function addSavedSubcategories(&$subcategories)
    {
        $idSite = SavedReports::requestedSite();
        if ($idSite < 1) {
            return;
        }
        try {
            foreach ((new SavedReports())->subcategoriesFor($idSite) as $subcategory) {
                $subcategories[] = $subcategory;
            }
        } catch (\Throwable $e) {
            // the menu never fails because of explorations
        }
    }

    /** A scheduled e-mail keeps only the explorations its owner may still see. */
    public function filterSavedReports(&$processedReports, $reportType, $outputType, $report)
    {
        (new SavedReports())->filterForOwner($processedReports, (array) $report);
    }

    public function install()
    {
        (new SavedExplorationStore())->install();
    }

    public function uninstall()
    {
        (new SavedExplorationStore())->uninstall();
        Option::deleteLike(PluginFeedback::THROTTLE_OPTION_PREFIX . '%');
        Archive\HistoryQueue::deleteOptions();
    }

    public function getTablesInstalled(&$allTablesInstalled)
    {
        $allTablesInstalled[] = Common::prefixTable(SavedExplorationStore::TABLE);
    }

    /** The saved explorations of a deleted user go with the user. */
    public function deleteUser($userLogin)
    {
        (new SavedExplorationStore())->deleteForUser((string) $userLogin);
        Option::deleteLike(PluginFeedback::userOptionPattern((string) $userLogin));
    }

    /** The saved explorations of a deleted website go with the website. */
    public function deleteSite($idSite)
    {
        (new SavedExplorationStore())->deleteForSite((int) $idSite);
    }

    /**
     * Matomo's comparison mode: the free-form table compares segments and periods in its own columns; the funnel does
     * not compare (yet).
     */
    public function disableComparison(&$pages)
    {
        $pages[] = 'FeinwerkDataExploration_Explorations.FeinwerkDataExploration_Funnel';
    }

    public function getJavaScriptFiles(&$files)
    {
        $files[] = 'plugins/FeinwerkDataExploration/javascripts/exploreAction.js';
        $files[] = 'plugins/FeinwerkDataExploration/javascripts/promo.js';
        $files[] = 'plugins/FeinwerkDataExploration/javascripts/promocard.js';
        $files[] = 'plugins/FeinwerkDataExploration/javascripts/connectorinstaller.js';
    }

    /**
     * "Open in exploration" under selected standard reports.
     *
     * @param \Piwik\Plugin\ViewDataTable $view
     */
    public function addOpenInExploration($view)
    {
        try {
            (new OpenInExploration())->configureView($view);
        } catch (\Throwable $e) {
            // a report must never break because of this link
        }
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = 'plugins/FeinwerkDataExploration/stylesheets/exploration.less';
        $stylesheets[] = 'plugins/FeinwerkDataExploration/stylesheets/promo.css';
        $stylesheets[] = 'plugins/FeinwerkDataExploration/stylesheets/connectorinstaller.css';
        $stylesheets[] = 'plugins/FeinwerkDataExploration/stylesheets/feinwerklogo.css';
        $stylesheets[] = 'plugins/FeinwerkDataExploration/stylesheets/premium.css';
        $stylesheets[] = 'plugins/FeinwerkDataExploration/stylesheets/fwde-ui.css';
    }

    /**
     * The Vue app may use every key of the plugin's lang file.
     */
    /** @see SavedReport::graphsOnlyForDays() */
    public function graphsOnlyForDays(&$reports, $info): void
    {
        SavedReport::graphsOnlyForDays($reports, is_array($info) ? $info : []);
    }

    /**
     * One record builder per archived saved exploration of the website being archived (design 2026-11-25). Matomo names
     * the website in the request, as for its own plugins (CustomDimensions does the same). A failure here archives none
     * of them but never stops Matomo's archiving.
     *
     * @param \Piwik\ArchiveProcessor\RecordBuilder[] $recordBuilders
     */
    public function addRecordBuilders(&$recordBuilders): void
    {
        $idSite = Common::getRequestVar('idSite', 0, 'int');
        if ($idSite <= 0) {
            $idSites = trim(Common::getRequestVar('idSites', '', 'string'));
            $idSite = ctype_digit($idSites) ? (int) $idSites : 0;
        }
        if ($idSite <= 0) {
            return;
        }
        try {
            $settings = Archive\ArchiveSettings::fromConfig();
            foreach ((new Archive\ArchivedExplorations($settings))->forSite($idSite) as $exploration) {
                $recordBuilders[] = new Archive\ExplorationRecordBuilder($exploration, $settings);
            }
        } catch (\Throwable $e) {
            \Piwik\Container\StaticContainer::get(\Psr\Log\LoggerInterface::class)->warning(
                'Feinwerk Data Exploration: no saved explorations archived for website {idSite}: {message}',
                ['idSite' => $idSite, 'message' => $e->getMessage()]
            );
        }
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $data = json_decode((string) file_get_contents(__DIR__ . '/lang/en.json'), true);
        foreach (array_keys($data['FeinwerkDataExploration'] ?? []) as $key) {
            $translationKeys[] = 'FeinwerkDataExploration_' . $key;
        }
        array_push($translationKeys, ...self::clientCoreKeys());
    }

    /**
     * Matomo's own keys the Vue app shows, registered here so the page never depends on another plugin registering them
     * (TranslationsTest checks the Vue code against this list).
     *
     * @return string[]
     */
    public static function clientCoreKeys(): array
    {
        $keys = [
            'General_Unknown', 'CoreHome_ThereIsNoDataForThisReport', 'General_Export', 'General_Search',
            'General_Save', 'General_Cancel', 'General_Delete', 'General_Name', 'General_LoadingData',
            // the side panel's close button and the filter panel's Apply
            'General_Close', 'General_Apply',
            // the funnel table names its visits column as Matomo does
            'General_ColumnNbVisits',
            // the comparison table's total row, the rest of a cut table and the donut's last slice
            'General_Total', 'General_Others',
            // the metric list's e-commerce heading
            'Goals_Ecommerce',
        ];
        // the chart orders weekdays by Matomo's own names
        for ($day = 1; $day <= 7; $day++) {
            $keys[] = 'Intl_Day_Long_StandAlone_' . $day;
        }
        return $keys;
    }
}
