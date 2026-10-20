<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Diagnostic;

use Piwik\Db;
use Piwik\NumberFormatter;
use Piwik\Piwik;
use Piwik\Plugins\Diagnostics\Diagnostic\Diagnostic;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResultItem;
use Piwik\Plugins\FeinwerkDataExploration\Engine\LimitsProvider;
use Piwik\Plugins\FeinwerkDataExploration\Query\StatementTimeLimit;
use Piwik\Plugins\PrivacyManager\PrivacyManager;

/**
 * The plugin's entry in Matomo's System Check (optional section): where explorations run, whether the database stops
 * long queries, how far back raw data goes and the limits in force. Admins look here first when explorations are slow.
 */
class ExplorationCheck implements Diagnostic
{
    public function execute()
    {
        $result = new DiagnosticResult(Piwik::translate('FeinwerkDataExploration_DiagnosticLabel'));
        try {
            $this->addItems($result);
        } catch (\Throwable $e) {
            // e.g. an unreachable [database_reader]: Matomo's System Check page must still show its other checks
            $result->setItems([new DiagnosticResultItem(
                DiagnosticResult::STATUS_WARNING,
                Piwik::translate('FeinwerkDataExploration_DiagnosticFailed', htmlspecialchars($e->getMessage(), ENT_QUOTES))
            )]);
        }

        return [$result];
    }

    private function addItems(DiagnosticResult $result): void
    {
        $limits = (new LimitsProvider())->get();

        if (!$limits->useReader) {
            $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_INFORMATIONAL, Piwik::translate('FeinwerkDataExploration_DiagnosticReplicaOff')));
        } elseif (Db::hasReaderConfigured()) {
            $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_OK, Piwik::translate('FeinwerkDataExploration_DiagnosticReplica')));
        } else {
            $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_INFORMATIONAL, Piwik::translate('FeinwerkDataExploration_DiagnosticNoReplica')));
        }

        $db = $limits->useReader ? Db::getReader() : Db::get();
        $stopsLongQueries = StatementTimeLimit::apply('SELECT 1', max(1, $limits->timeoutSeconds), (string) $db->getServerVersion()) !== 'SELECT 1';
        $result->addItem($stopsLongQueries
            ? new DiagnosticResultItem(DiagnosticResult::STATUS_OK, Piwik::translate('FeinwerkDataExploration_DiagnosticTimeLimit', [$limits->timeoutSeconds]))
            : new DiagnosticResultItem(DiagnosticResult::STATUS_WARNING, Piwik::translate('FeinwerkDataExploration_DiagnosticNoTimeLimit')));

        $purge = PrivacyManager::getPurgeDataSettings();
        $result->addItem(!empty($purge['delete_logs_enable']) && (int) ($purge['delete_logs_older_than'] ?? 0) > 0
            ? new DiagnosticResultItem(DiagnosticResult::STATUS_INFORMATIONAL, Piwik::translate('FeinwerkDataExploration_DiagnosticRawDataDeleted', [(int) $purge['delete_logs_older_than']]))
            : new DiagnosticResultItem(DiagnosticResult::STATUS_INFORMATIONAL, Piwik::translate('FeinwerkDataExploration_DiagnosticRawDataKept')));

        $numbers = NumberFormatter::getInstance();
        $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_INFORMATIONAL, Piwik::translate('FeinwerkDataExploration_DiagnosticLimits', [
            (string) $limits->maxDays,
            $numbers->formatNumber($limits->maxRows),
            $numbers->formatNumber($limits->maxVisits),
            (string) $limits->maxConcurrent,
        ])));
    }
}
