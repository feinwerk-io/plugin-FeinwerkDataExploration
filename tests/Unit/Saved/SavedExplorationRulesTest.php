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
    public function testChartTypeIsKept(): void
    {
        $state = SavedExplorationRules::state(json_encode(['fwdeDims' => 'browser', 'fwdeChartType' => 'trend']));
        $this->assertSame(['fwdeDims' => 'browser', 'fwdeChartType' => 'trend'], $state);
    }

    /** A saved funnel carries its kind and steps (spec 3 "Speichern"); entries without a kind are tables. */
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

    public function testAStateTooLargeForTheTableIsRefused(): void
    {
        $huge = array_fill_keys(SavedExplorationRules::STATE_KEYS, str_repeat('a', 9000));

        $this->expectException(ExplorationException::class);
        SavedExplorationRules::state(json_encode($huge));
    }
}
