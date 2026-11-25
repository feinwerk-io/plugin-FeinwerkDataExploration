<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Saved;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationRules;

/**
 * @group FeinwerkDataExploration
 */
class SavedExplorationRulesTest extends TestCase
{
    public function testTheLongestFiltersThePageAllowsCanBeSaved(): void
    {
        // five filters of 1,000 characters, URL-encoded as the page writes them (up to 9 bytes per character)
        $filter = 'label~contains~' . rawurlencode(str_repeat('€', 1000)) . 'x';
        $filters = implode(';', array_fill(0, 5, $filter));

        $state = SavedExplorationRules::state(json_encode(['fwdeDims' => 'pageTitle', 'fwdeFilters' => $filters]));

        $this->assertSame($filters, $state['fwdeFilters']);
    }

    /** The chart type (trend design 3) belongs to a saved table. */
    /** saved reports (2026-11-24): the menu place is a state key, one of Matomo's sections or empty */
    public function testMenuSectionsAccepted(): void
    {
        foreach (SavedExplorationRules::MENU_SECTIONS as $section) {
            $state = SavedExplorationRules::state('{"fwdeDims":"browser","fwdeMenu":"' . $section . '"}');
            $this->assertSame($section, SavedExplorationRules::menuOf($state));
        }
        $this->assertNull(SavedExplorationRules::menuOf(SavedExplorationRules::state('{"fwdeDims":"browser","fwdeMenu":""}')));
        $this->assertNull(SavedExplorationRules::menuOf(['fwdeDims' => 'browser']));
        $this->assertNull(SavedExplorationRules::menuOf(['fwdeDims' => 'browser', 'fwdeMenu' => 'Gone_Section']));
        $this->assertSame(['General_Visitors', 'General_Actions', 'Referrers_Referrers', 'Goals_Ecommerce', 'Goals_Goals',
            'FeinwerkDataExploration_Explorations'], SavedExplorationRules::MENU_SECTIONS);
    }

    public function testUnknownMenuRefusedNamingTheField(): void
    {
        try {
            SavedExplorationRules::state('{"fwdeDims":"browser","fwdeMenu":"General_Bogus"}');
            $this->fail('an unknown menu place was accepted');
        } catch (ExplorationException $e) {
            $this->assertSame('FeinwerkDataExploration_ErrorSavedMenu', $e->getTranslationKey());
            $this->assertSame('fwdeMenu', $e->getParams()[0]);
            $this->assertStringContainsString('Goals_Goals', $e->getParams()[1]);
            $this->assertSame(400, $e->getCode());
        }
    }

    public function testChartTypeIsKept(): void
    {
        $state = SavedExplorationRules::state(json_encode(['fwdeDims' => 'browser', 'fwdeChartType' => 'trend']));
        $this->assertSame(['fwdeDims' => 'browser', 'fwdeChartType' => 'trend'], $state);
    }

    /** A saved funnel carries its kind and steps (spec 3 "Speichern"); entries without a kind are tables. */
    /** KPI cards (2026-11-24): their kind and settings are kept; a setting outside its choices names them */
    public function testKpiCardKeys(): void
    {
        $state = ['fwdeKind' => 'kpi', 'fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits', 'fwdeKpiMetric' => 'nb_visits',
            'fwdeKpiCompare' => 'previous', 'fwdeKpiLine' => '7', 'fwdeKpiChange' => 'absolute'];
        $this->assertSame($state, SavedExplorationRules::state(json_encode($state)));

        $bad = ['fwdeKpiLine' => ['14', '0, 7, 30'], 'fwdeKpiCompare' => ['yearly', 'none, previous, weekday, average4'],
            'fwdeKpiChange' => ['ratio', 'percent, absolute']];
        foreach ($bad as $key => [$value, $allowed]) {
            try {
                SavedExplorationRules::state(json_encode(['fwdeKind' => 'kpi', $key => $value]));
                $this->fail($key . ' ' . $value . ' was accepted');
            } catch (ExplorationException $e) {
                $this->assertSame(['FeinwerkDataExploration_ErrorKpiSetting', [$key, $allowed]], [$e->getTranslationKey(), $e->getParams()]);
            }
        }
    }

    public function testFunnelKeysAreAcceptedAndKindIsChecked(): void
    {
        $steps = implode(';', array_fill(0, 3, 'pageUrl~contains~' . rawurlencode(str_repeat('€', 1000))));
        $state = SavedExplorationRules::state(json_encode(['fwdeKind' => 'funnel', 'fwdeSteps' => $steps]));
        $this->assertSame(['fwdeKind' => 'funnel', 'fwdeSteps' => $steps], $state);

        foreach (['table', 'x', ''] as $kind) {
            try {
                SavedExplorationRules::state(json_encode(['fwdeKind' => $kind, 'fwdeSteps' => 'anyGoal~~']));
                $this->fail('kind ' . $kind . ' was accepted');
            } catch (ExplorationException $e) {
                $this->assertSame('FeinwerkDataExploration_ErrorSavedState', $e->getTranslationKey());
            }
        }
    }

    /** Filter groups (2026-11-23): ten conditions of 1,000 Latin characters fit; a larger state says why it is refused */
    public function testStateOverTheLimitIsTooLarge(): void
    {
        $filter = 'label~contains~' . rawurlencode(str_repeat('a', 1000));
        $ten = implode(';', array_fill(0, 5, $filter)) . '|' . implode(';', array_fill(0, 5, $filter));
        $this->assertSame($ten, SavedExplorationRules::state(json_encode(['fwdeDims' => 'pageTitle', 'fwdeFilters' => $ten]))['fwdeFilters']);
        $this->assertSame(60000, strlen(SavedExplorationRules::state(json_encode(['fwdeFilters' => str_repeat('a', 60000)]))['fwdeFilters']));

        foreach ([['fwdeFilters' => str_repeat('a', 60001)], array_fill_keys(['fwdeFilters', 'fwdeSteps'], str_repeat('a', 33000))] as $state) {
            try {
                SavedExplorationRules::state(json_encode($state));
                $this->fail('a state of ' . strlen(json_encode($state)) . ' bytes was accepted');
            } catch (ExplorationException $e) {
                $this->assertSame('FeinwerkDataExploration_ErrorSavedTooLarge', $e->getTranslationKey());
                $this->assertSame(400, $e->getCode());
            }
        }
    }

    public function testAStateTooLargeForTheTableIsRefused(): void
    {
        $huge = array_fill_keys(SavedExplorationRules::STATE_KEYS, str_repeat('a', 9000));

        $this->expectException(ExplorationException::class);
        SavedExplorationRules::state(json_encode($huge));
    }
}
