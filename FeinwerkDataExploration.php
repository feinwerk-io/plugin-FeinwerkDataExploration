<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration;

use Piwik\Common;
use Piwik\Plugins\FeinwerkDataExploration\Reports\OpenInExploration;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationStore;

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
        ];
    }

    public function install()
    {
        (new SavedExplorationStore())->install();
    }

    public function uninstall()
    {
        (new SavedExplorationStore())->uninstall();
    }

    public function getTablesInstalled(&$allTablesInstalled)
    {
        $allTablesInstalled[] = Common::prefixTable(SavedExplorationStore::TABLE);
    }

    /** The saved explorations of a deleted user go with the user. */
    public function deleteUser($userLogin)
    {
        (new SavedExplorationStore())->deleteForUser((string) $userLogin);
    }

    /** The saved explorations of a deleted website go with the website. */
    public function deleteSite($idSite)
    {
        (new SavedExplorationStore())->deleteForSite((int) $idSite);
    }

    /**
     * Matomo's comparison mode does not apply to explorations (one table of one period and segment).
     */
    public function disableComparison(&$pages)
    {
        $pages[] = 'FeinwerkDataExploration_Explorations.FeinwerkDataExploration_FreeFormTable';
    }

    public function getJavaScriptFiles(&$files)
    {
        $files[] = 'plugins/FeinwerkDataExploration/javascripts/exploreAction.js';
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
    }

    /**
     * The Vue app may use every key of the plugin's lang file.
     */
    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $data = json_decode((string) file_get_contents(__DIR__ . '/lang/en.json'), true);
        foreach (array_keys($data['FeinwerkDataExploration'] ?? []) as $key) {
            $translationKeys[] = 'FeinwerkDataExploration_' . $key;
        }
        array_push($translationKeys, 'General_Unknown', 'CoreHome_ThereIsNoDataForThisReport', 'General_Export', 'General_Search');
        array_push($translationKeys, 'General_Save', 'General_Cancel', 'General_Delete');
        // the chart orders weekdays by Matomo's own names
        for ($day = 1; $day <= 7; $day++) {
            $translationKeys[] = 'Intl_Day_Long_StandAlone_' . $day;
        }
    }
}
