<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration;

use Piwik\Site;
use Piwik\Common;
use Piwik\Date;
use Piwik\Period;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Plugins\FeinwerkDataExploration\Engine\LimitsProvider;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedReportTable;
use Piwik\Container\StaticContainer;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\FeinwerkDataExploration\ConnectorInstaller\ConnectorInstaller;
use Piwik\Plugins\FeinwerkDataExploration\Engine\AccessPolicy;
use Piwik\Plugins\FeinwerkDataExploration\Feedback\PluginFeedback;
use Piwik\Plugins\FeinwerkDataExploration\Saved\ReportVisibility;
use Piwik\Plugins\FeinwerkDataExploration\Widgets\WidgetProps;
use Piwik\Translation\Translator;
use Piwik\Url;
use Piwik\View;

/**
 * The plugin's admin pages: only the Feinwerk Connector installer (promotion kit, house rule "Connector installer in
 * every plugin"; the explorations themselves are Vue widgets). Extends Plugin\Controller, so the installer page sets up
 * the admin variables itself (renderConnectorInstaller()).
 */
class Controller extends \Piwik\Plugin\Controller
{
    /**
     * The footer under a saved exploration's report (saved reports, 2026-11-24): "Open in exploration" (the free-form
     * table with the saved exploration, same website and period) and the feedback line. Empty when the user may not see
     * the exploration and in an embed (WidgetProps::isEmbedded()).
     */
    public function reportFooter()
    {
        $idSite = Common::getRequestVar('idSite', 0, 'int');
        $idSaved = Common::getRequestVar('idSaved', 0, 'int');
        if ($idSite < 1 || !AccessPolicy::canUse($idSite)) {
            return '';
        }
        $visible = array_map('intval', array_column((new ReportVisibility())->rows($idSite), 'idsaved'));
        if (!in_array($idSaved, $visible, true)) {
            return '';
        }
        if (WidgetProps::isEmbedded()) {
            // the website's own viewers see an embed: no feedback line, no link into Matomo (UI review 2026-11-24)
            return '';
        }
        [$period, $date] = self::explorablePeriod($idSite, Common::getRequestVar('period', 'day', 'string'),
            Common::getRequestVar('date', 'yesterday', 'string'));
        $params = ['idSite' => $idSite, 'period' => $period, 'date' => $date];
        $hash = $params + ['category' => 'FeinwerkDataExploration_Explorations', 'subcategory' => 'FeinwerkDataExploration_FreeFormTable',
            'fwdeSaved' => $idSaved];
        $view = new View('@FeinwerkDataExploration/reportFooter');
        $view->idSite = $idSite;
        $view->exploreUrl = 'index.php?' . Url::getQueryStringFromParameters(['module' => 'CoreHome', 'action' => 'index'] + $params)
            . '#?' . Url::getQueryStringFromParameters($hash);
        // the page drops the support link in a dashboard widget only (house rule "Feedback under every report"), the
        // report's menu page keeps it (SavedReportFooter.vue)
        $view->feedback = PluginFeedback::clientProps(
            StaticContainer::get(PluginFeedback::class)->isEnabled(),
            (string) StaticContainer::get(Translator::class)->getCurrentLanguage(),
            (string) PluginManager::getInstance()->getLoadedPlugin(PluginFeedback::PLUGIN)->getVersion()
        );
        return $view->render();
    }

    /**
     * A KPI card's dashboard widget (2026-11-24): the card for the dashboard's period and segment, asked by the page
     * through getKpiCard, with the footer (Open in exploration, the feedback thumbs; no support link in a dashboard
     * widget; neither in an embed, WidgetProps::isEmbedded()). Empty for a card the user may not see.
     */
    public function kpiCard()
    {
        $idSite = Common::getRequestVar('idSite', 0, 'int');
        $idSaved = Common::getRequestVar('idSaved', 0, 'int');
        if ($idSite < 1 || !AccessPolicy::canUse($idSite)) {
            return '';
        }
        $visible = array_map('intval', array_column((new ReportVisibility())->rows($idSite), 'idsaved'));
        if (!in_array($idSaved, $visible, true)) {
            return '';
        }
        [$period, $date] = self::explorablePeriod($idSite, Common::getRequestVar('period', 'day', 'string'),
            Common::getRequestVar('date', 'yesterday', 'string'));
        $params = ['idSite' => $idSite, 'period' => $period, 'date' => $date];
        $hash = $params + ['category' => 'FeinwerkDataExploration_Explorations', 'subcategory' => 'FeinwerkDataExploration_FreeFormTable',
            'fwdeSaved' => $idSaved];
        $feedback = PluginFeedback::clientProps(
            StaticContainer::get(PluginFeedback::class)->isEnabled(),
            (string) StaticContainer::get(Translator::class)->getCurrentLanguage(),
            (string) PluginManager::getInstance()->getLoadedPlugin(PluginFeedback::PLUGIN)->getVersion()
        );
        $view = new View('@FeinwerkDataExploration/kpiCard');
        $view->idSite = $idSite;
        $view->idSaved = $idSaved;
        $embedded = WidgetProps::isEmbedded();
        // in an embed the website's own viewers see the card: no link into Matomo, no feedback (UI review 2026-11-24)
        $view->exploreUrl = $embedded ? '' : 'index.php?' . Url::getQueryStringFromParameters(['module' => 'CoreHome', 'action' => 'index'] + $params)
            . '#?' . Url::getQueryStringFromParameters($hash);
        $view->currencySymbol = Site::getCurrencySymbolFor($idSite);
        $view->feedback = ['supportUrl' => ''] + ($embedded ? ['enabled' => false] : []) + $feedback;
        return $view->render();
    }

    /**
     * The period "Open in exploration" opens (UI review 2026-11-26 F2): a report's period, or for one longer than an
     * exploration covers (archived reports answer any period) its last days as a range, ending with the period or today;
     * several periods (a KPI card's line) and anything Matomo cannot read stay as they are.
     *
     * @return array{0: string, 1: string} period and date
     */
    private static function explorablePeriod(int $idSite, string $period, string $date): array
    {
        try {
            if (Period::isMultiplePeriod($date, $period)) {
                return [$period, $date];
            }
            $maxDays = (new LimitsProvider())->get()->maxDays;
            if (SavedReportTable::periodDays($idSite, $period, $date) <= $maxDays) {
                return [$period, $date];
            }
            $timezone = Site::getTimezoneFor($idSite);
            $built = PeriodFactory::makePeriodFromQueryParams($timezone, $period, $date);
            $end = $built->getDateEnd();
            $today = Date::factory('today', $timezone);
            if ($end->isLater($today)) {
                $end = $today;
            }
            $start = $end->subDay($maxDays - 1);
            if ($built->getDateStart()->isLater($start)) {
                $start = $built->getDateStart();
            }
            return ['range', $start->toString() . ',' . $end->toString()];
        } catch (\Throwable $e) {
            return [$period, $date];
        }
    }

    /**
     * Kit: the Feinwerk Connector installer page (ConnectorInstaller/ConnectorInstaller.php). Super users only.
     */
    public function connectorInstaller()
    {
        Piwik::checkUserHasSuperUserAccess();
        $env = ConnectorInstaller::environment();
        $redirect = ConnectorInstaller::redirectFor($env);
        if ($redirect !== null) {
            Url::redirectToUrl($redirect);

            return '';
        }

        return $this->renderConnectorInstaller($env, null);
    }

    /**
     * Kit: the click. Matomo's password check first (it comes back here by GET with these parameters), then the nonce
     * (verified and discarded, as Matomo's own install action does), then install when the files are missing, and
     * activate. Success: on to the connector; any failure: back on the page with Matomo's reason.
     */
    public function connectorInstallerRun()
    {
        Piwik::checkUserHasSuperUserAccess();
        $env = ConnectorInstaller::environment();
        $redirect = ConnectorInstaller::redirectFor($env);
        if ($redirect !== null) {
            // active: the connector; switched off: admin home (never the page's "open the connector" fallback)
            Url::redirectToUrl($redirect);

            return '';
        }
        $state = ConnectorInstaller::decide($env);
        if ($state !== ConnectorInstaller::INSTALL && $state !== ConnectorInstaller::ACTIVATE) {
            return $this->renderConnectorInstaller($env, null);
        }

        $nonce = Common::getRequestVar('nonce', '', 'string');
        $retry = Common::getRequestVar('retry', 0, 'int') === 1;
        $verified = StaticContainer::get(\Piwik\Plugins\Login\PasswordVerifier::class)->requirePasswordVerifiedRecently([
            'module' => ConnectorInstaller::PLUGIN,
            'action' => 'connectorInstallerRun',
            'nonce' => $nonce,
            'retry' => $retry ? 1 : 0,
        ]);
        if (!$verified) {
            // Matomo is sending the user to its password page (it returns only when its redirect is off)
            return '';
        }

        $nonceError = Nonce::verifyNonceWithErrorMessage(ConnectorInstaller::NONCE, $nonce);
        if ($nonceError !== '') {
            // An expired nonce or Matomo's referrer check (seen right after its password step, e.g. after an SSO login):
            // nothing is installed, the page comes back with a fresh button and a short note. A second failure in a row
            // has a lasting cause (proxy, trusted host, origin): then Matomo's own reason, which may hold its FAQ link.
            return $retry
                ? $this->renderConnectorInstaller($env, ['ok' => false, 'message' => $nonceError, 'html' => true])
                : $this->renderConnectorInstaller($env, null, true);
        }
        Nonce::discardNonce(ConnectorInstaller::NONCE);

        $result = ConnectorInstaller::run(
            ConnectorInstaller::usableFiles($env),
            static function (): void {
                (new \Piwik\Plugins\CorePluginsAdmin\PluginInstaller())->installOrUpdatePluginFromMarketplace(ConnectorInstaller::CONNECTOR);
            },
            static function (): void {
                PluginManager::getInstance()->activatePlugin(ConnectorInstaller::CONNECTOR);
            }
        );
        if ($result['ok']) {
            Url::redirectToUrl(ConnectorInstaller::CONNECTOR_URL);
        }

        return $this->renderConnectorInstaller(ConnectorInstaller::environment(), $result);
    }

    /**
     * @param array<string, bool> $env
     * @param array{ok: bool, message: string, html: bool}|null $result
     * @param bool $clickAgain the nonce no longer matched: a fresh button and a short note
     */
    private function renderConnectorInstaller(array $env, ?array $result, bool $clickAgain = false): string
    {
        $view = new View('@FeinwerkDataExploration/connectorInstaller');
        // an admin page without a website: setGeneralVariablesView() needs idSite (the menu entry is reached from
        // admin pages without one); the admin variables also send X-Frame-Options as enable_framed_settings says
        $this->setBasicVariablesView($view);
        \Piwik\Plugin\ControllerAdmin::setBasicVariablesAdminView($view);
        $state = ConnectorInstaller::decide($env);
        $view->installerState = $state;
        $view->installerError = $result !== null && !$result['ok'] ? $result : null;
        $view->installerNonce = $state === ConnectorInstaller::INSTALL || $state === ConnectorInstaller::ACTIVATE
            ? Nonce::getNonce(ConnectorInstaller::NONCE)
            : '';
        $view->installerLinks = ConnectorInstaller::manualLinks();
        $view->installerWordPressUrl = ConnectorInstaller::wordpressUrl();
        $view->installerConnectorUrl = ConnectorInstaller::CONNECTOR_URL;
        $view->installerPhpFloor = ConnectorInstaller::PHP_FLOOR;
        $view->installerPluginsAdmin = $env['pluginsAdmin'];
        $view->installerConsoleCommand = ConnectorInstaller::CONSOLE_COMMAND;
        $view->installerClickAgain = $clickAgain;
        // <html lang> and piwik.language: setBasicVariablesView() does not set them on this page
        $view->language = ConnectorInstaller::pageLanguage();

        return $view->render();
    }
}
