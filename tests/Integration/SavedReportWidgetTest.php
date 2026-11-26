<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Cache;
use Piwik\Plugins\FeinwerkDataExploration\API;
use Piwik\Plugins\FeinwerkDataExploration\Controller;
use Piwik\Plugins\UsersManager\API as UsersManagerApi;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Widget\WidgetContainerConfig;
use Piwik\Widget\WidgetsList;

/**
 * Saved explorations as reports (2026-11-24): one dashboard widget per exploration the user may see, the report with
 * its footer ("Open in exploration" and the feedback line) in one container.
 *
 * @group FeinwerkDataExploration
 */
class SavedReportWidgetTest extends IntegrationTestCase
{
    public function provideContainerConfig()
    {
        return ['Piwik\\Access' => new FakeAccess()];
    }

    public function setUp(): void
    {
        parent::setUp();
        FakeAccess::$superUser = true;
        Fixture::createWebsite('2026-01-01 00:00:00');
        foreach (['anna', 'bert'] as $login) {
            UsersManagerApi::getInstance()->addUser($login, 'secret-password-1', $login . '@example.com');
            UsersManagerApi::getInstance()->setUserAccess($login, 'write', [1]);
        }
        $_GET['idSite'] = 1;
    }

    public function tearDown(): void
    {
        unset($_GET['idSite'], $_GET['idSaved'], $_GET['period'], $_GET['date'], $_GET['module'], $_GET['disableLink']);
        parent::tearDown();
    }

    public function testOneContainerPerVisibleExploration(): void
    {
        $private = $this->save('anna', 'Anna alone', 0);
        $shared = $this->save('anna', 'Team view', 1, 'Goals_Goals');

        $this->assertSame(['fwdeSavedContainer' . $private, 'fwdeSavedContainer' . $shared], $this->containers('anna'));
        $this->assertSame(['fwdeSavedContainer' . $shared], $this->containers('bert'));

        $container = $this->container('anna', $shared);
        $this->assertSame(['Goals_Goals', 'fwdeSaved' . $shared, 'Team view'],
            [$container->getCategoryId(), $container->getSubcategoryId(), $container->getName()]);
        $widgets = $container->getWidgetConfigs();
        $this->assertCount(2, $widgets);
        $this->assertSame(['getSavedReport', 'reportFooter'], [$widgets[0]->getAction(), $widgets[1]->getAction()]);
        $this->assertSame($shared, $widgets[1]->getParameters()['idSaved']);
        // no fixed showtitle: the title is decided per request (testTitleOnTheMenuPageNotInADashboard)
        $this->assertArrayNotHasKey('showtitle', $widgets[0]->getParameters());
        // not in the menu: a widget under Explorations without a page of its own
        $this->assertSame(['FeinwerkDataExploration_Explorations', null],
            [$this->container('anna', $private)->getCategoryId(), $this->container('anna', $private)->getSubcategoryId() ?: null]);
    }

    public function testFooterLinksToTheExplorationAndAsksForFeedback(): void
    {
        $shared = $this->save('anna', 'Team view', 1);
        $private = $this->save('anna', 'Anna alone', 0);
        FakeAccess::clearAccess(false, [], [1], 'bert');
        $_GET += ['period' => 'day', 'date' => '2026-03-27'];

        $_GET['idSaved'] = $shared;
        $html = (new Controller())->reportFooter();
        $this->assertStringContainsString('vue-entry="FeinwerkDataExploration.SavedReportFooter"', $html);
        $this->assertStringContainsString('fwdeSaved=' . $shared, html_entity_decode($html));
        $this->assertStringContainsString('subcategory=FeinwerkDataExploration_FreeFormTable', html_entity_decode($html));
        // the support link: the page hides it in a dashboard widget only (house rule; release review 2026-11-25)
        $this->assertStringContainsString('"supportUrl":"https:', html_entity_decode($html));

        // another user's private exploration: nothing
        $_GET['idSaved'] = $private;
        $this->assertSame('', (new Controller())->reportFooter());
    }

    /** UI review 2026-11-26 F2: a period longer than an exploration covers opens its last days, never the error */
    public function testFooterOpensTheLastDaysOfALongPeriod(): void
    {
        $shared = $this->save('anna', 'Team view', 1);
        FakeAccess::clearAccess(false, [], [1], 'bert');
        $_GET['idSaved'] = $shared;
        $_GET['period'] = 'year';
        $_GET['date'] = '2025-06-15';
        $html = html_entity_decode((new Controller())->reportFooter());
        $this->assertSame(2, substr_count($html, 'period=range&date=2025-12-01,2025-12-31'), 'in the address and its hash');
        $this->assertStringNotContainsString('period=year', $html);

        // 30 days fit: the period stays
        $_GET['period'] = 'month';
        $html = html_entity_decode((new Controller())->reportFooter());
        $this->assertStringContainsString('period=month&date=2025-06-15', $html);
    }

    /** the KPI card widget (2026-11-24): the card's entry for visible cards, no support link, nothing otherwise */
    public function testCardWidgetAction(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'anna', [1]);
        $card = (int) API::getInstance()->addSavedExploration(1, 'Visits card', json_encode(['fwdeKind' => 'kpi', 'fwdeDims' => 'browser',
            'fwdeMetrics' => 'nb_visits']), '', 1)['id'];
        $private = (int) API::getInstance()->addSavedExploration(1, 'Anna card', json_encode(['fwdeKind' => 'kpi', 'fwdeDims' => 'browser',
            'fwdeMetrics' => 'nb_visits']))['id'];
        FakeAccess::clearAccess(false, [], [1], 'bert');
        $_GET += ['period' => 'day', 'date' => '2026-03-27'];

        $_GET['idSaved'] = $card;
        $html = html_entity_decode((new Controller())->kpiCard());
        $this->assertStringContainsString('vue-entry="FeinwerkDataExploration.KpiCard"', $html);
        $this->assertStringContainsString('id-saved="' . $card . '"', $html);
        $this->assertStringContainsString('fwdeSaved=' . $card, $html);
        $this->assertStringContainsString('"supportUrl":""', $html);

        $_GET['idSaved'] = $private;
        $this->assertSame('', (new Controller())->kpiCard());
    }

    /**
     * UI review 2026-11-24 (F1): an embed (Widgetize, disableLink=1) is seen by the website's own viewers, so the saved
     * report's footer stays empty and the card keeps its number without "Open in exploration" and the feedback line.
     */
    public function testEmbedsShowNoFooter(): void
    {
        $shared = $this->save('anna', 'Team view', 1);
        FakeAccess::clearAccess(false, [], [1], 'anna', [1]);
        $card = (int) API::getInstance()->addSavedExploration(1, 'Visits card', json_encode(['fwdeKind' => 'kpi', 'fwdeDims' => 'browser',
            'fwdeMetrics' => 'nb_visits']), '', 1)['id'];
        $_GET += ['period' => 'day', 'date' => '2026-03-27'];

        foreach ([['module' => 'Widgetize'], ['disableLink' => 1]] as $embed) {
            $_GET = $embed + $_GET;
            $_GET['idSaved'] = $shared;
            $this->assertSame('', (new Controller())->reportFooter(), json_encode($embed));

            $_GET['idSaved'] = $card;
            $html = html_entity_decode((new Controller())->kpiCard());
            $this->assertStringContainsString('vue-entry="FeinwerkDataExploration.KpiCard"', $html);
            $this->assertStringContainsString('explore-url=""""', $html, json_encode($embed));
            $this->assertStringContainsString('"enabled":false', $html, json_encode($embed));
            $this->assertStringContainsString('"supportUrl":""', $html, json_encode($embed));
            $this->assertStringNotContainsString('fwdeSaved=', $html, json_encode($embed));
            unset($_GET['module'], $_GET['disableLink']);
        }
    }

    /**
     * Release review 2026-11-25: explorations saved as a bar chart or a donut, and saved filters the server cannot use
     * (a pattern that does not compile, a number that is not finite), never break Matomo's widget list.
     */
    public function testChartViewsAndUnusableFiltersKeepTheWidgetList(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'anna', [1]);
        $ids = [];
        foreach ([
            ['fwdeView' => 'chart'],
            ['fwdeView' => 'chart', 'fwdeChartType' => 'bar'],
            ['fwdeView' => 'chart', 'fwdeChartType' => 'donut'],
            ['fwdeFilters' => 'label~matches~('],
            ['fwdeFilters' => 'nb_visits~gt~1e999'],
        ] as $i => $extra) {
            $state = ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits'] + $extra;
            $ids[] = 'fwdeSavedContainer' . (int) API::getInstance()->addSavedExploration(1, 'Saved ' . $i, json_encode($state), '', 1)['id'];
        }
        sort($ids);
        $this->assertSame($ids, $this->containers('anna'));
        $views = [];
        foreach ($ids as $id) {
            $views[] = $this->container('anna', (int) substr($id, strlen('fwdeSavedContainer')))->getWidgetConfigs()[0]->getViewDataTable();
        }
        $this->assertSame(['graphVerticalBar', 'graphVerticalBar', 'graphPie', 'table', 'table'], $views);
    }

    /**
     * UI review 2026-11-25 (F1): the report's own menu page shows its title and Matomo's card; a dashboard widget or an
     * embed (widget=1), whose header names it, shows no second title.
     */
    public function testTitleOnTheMenuPageNotInADashboard(): void
    {
        $id = $this->save('anna', 'Team view', 1);
        FakeAccess::$superUser = true;
        $_GET += ['period' => 'day', 'date' => '2026-03-27', 'idSaved' => $id, 'showtitle' => 1];
        try {
            $menuPage = \Piwik\ViewDataTable\Factory::build('table', 'FeinwerkDataExploration.getSavedReport', 'FeinwerkDataExploration.getSavedReport', true);
            $_GET['widget'] = 1;
            $dashboard = \Piwik\ViewDataTable\Factory::build('table', 'FeinwerkDataExploration.getSavedReport', 'FeinwerkDataExploration.getSavedReport', true);
            // Matomo 6's Widgetize embed of a container: the child draws its own header, the only place for the name
            // (release review 2026-11-25)
            $_GET['own_report_header'] = 1;
            $embed = \Piwik\ViewDataTable\Factory::build('table', 'FeinwerkDataExploration.getSavedReport', 'FeinwerkDataExploration.getSavedReport', true);
        } finally {
            unset($_GET['showtitle'], $_GET['widget'], $_GET['own_report_header']);
        }
        $this->assertTrue($menuPage->config->show_title);
        $this->assertFalse($dashboard->config->show_title);
        $this->assertTrue($embed->config->show_title);
    }

    private function save(string $login, string $name, int $shared, string $menu = ''): int
    {
        FakeAccess::clearAccess(false, [], [1], $login, [1]);
        $state = ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits'] + ($menu === '' ? [] : ['fwdeMenu' => $menu]);
        return (int) API::getInstance()->addSavedExploration(1, $name, json_encode($state), '', $shared)['id'];
    }

    /** @return string[] the saved explorations' container ids in the widget list */
    /** a KPI card (2026-11-24) is one widget, the card, under Explorations; no report container */
    public function testCardIsOneWidget(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'anna', [1]);
        $id = (int) API::getInstance()->addSavedExploration(1, 'Visits card', json_encode(['fwdeKind' => 'kpi', 'fwdeDims' => 'browser',
            'fwdeMetrics' => 'nb_visits']))['id'];

        $this->assertNotContains('fwdeSavedContainer' . $id, $this->containers('anna'));
        $cards = array_values(array_filter(WidgetsList::get()->getWidgetConfigs(),
            fn ($config) => !$config instanceof WidgetContainerConfig && $config->getAction() === 'kpiCard'));
        $this->assertCount(1, $cards);
        $this->assertSame(['FeinwerkDataExploration', $id, 'FeinwerkDataExploration_Explorations', 'Visits card'],
            [$cards[0]->getModule(), $cards[0]->getParameters()['idSaved'], $cards[0]->getCategoryId(), $cards[0]->getName()]);
        $this->assertTrue($cards[0]->isWidgetizeable());
    }

    private function containers(string $login): array
    {
        FakeAccess::clearAccess(false, [], [1], $login);
        Cache::getTransientCache()->flushAll();
        $ids = [];
        foreach (WidgetsList::get()->getWidgetConfigs() as $config) {
            if ($config instanceof WidgetContainerConfig && str_starts_with($config->getId(), 'fwdeSavedContainer')) {
                $ids[] = $config->getId();
            }
        }
        sort($ids);
        return $ids;
    }

    private function container(string $login, int $idSaved): WidgetContainerConfig
    {
        FakeAccess::clearAccess(false, [], [1], $login);
        Cache::getTransientCache()->flushAll();
        foreach (WidgetsList::get()->getWidgetConfigs() as $config) {
            if ($config instanceof WidgetContainerConfig && $config->getId() === 'fwdeSavedContainer' . $idSaved) {
                return $config;
            }
        }
        $this->fail('no container for ' . $idSaved);
    }
}
