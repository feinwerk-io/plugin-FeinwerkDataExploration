<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Saved;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Saved\KpiCardSettings;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedReportUnavailable;

/**
 * The settings of a KPI card (2026-11-24, Peter: "ja, passt so"): metric, comparison, line and change, with defaults.
 *
 * @group FeinwerkDataExploration
 */
class KpiCardSettingsTest extends TestCase
{
    private const METRICS = ['nb_visits', 'bounce_rate', 'goal_3_revenue'];

    public function testDefaults(): void
    {
        $settings = KpiCardSettings::fromState(['fwdeKind' => 'kpi'], self::METRICS);
        $this->assertSame(['nb_visits', 'average4', 30, 'percent'], [$settings->metric, $settings->compare, $settings->line, $settings->change]);
    }

    public function testValuesKept(): void
    {
        $settings = KpiCardSettings::fromState(['fwdeKind' => 'kpi', 'fwdeKpiMetric' => 'bounce_rate', 'fwdeKpiCompare' => 'weekday',
            'fwdeKpiLine' => '7', 'fwdeKpiChange' => 'absolute'], self::METRICS);
        $this->assertSame(['bounce_rate', 'weekday', 7, 'absolute'], [$settings->metric, $settings->compare, $settings->line, $settings->change]);
        $this->assertSame(0, KpiCardSettings::fromState(['fwdeKpiLine' => '0'], self::METRICS)->line);
    }

    /** a card without a metric counts the exploration's first one */
    public function testMetricFallsBackToTheFirst(): void
    {
        $this->assertSame('nb_visits', KpiCardSettings::fromState(['fwdeKind' => 'kpi'], self::METRICS)->metric);
        $this->assertSame('nb_visits', KpiCardSettings::fromState(['fwdeKpiMetric' => ''], self::METRICS)->metric);
        $this->assertSame('average4', KpiCardSettings::fromState(['fwdeKpiCompare' => 'yearly'], self::METRICS)->compare);
    }

    /**
     * Release review 2026-11-25: a card whose metric is gone (a deleted goal) is refused naming it, never another
     * metric under the card's name.
     */
    public function testAGoneMetricIsRefusedNamingIt(): void
    {
        try {
            KpiCardSettings::fromState(['fwdeKpiMetric' => 'goal_9_revenue'], self::METRICS);
            $this->fail('expected SavedReportUnavailable');
        } catch (SavedReportUnavailable $e) {
            $this->assertSame('FeinwerkDataExploration_ErrorSavedKpiMetricGone', $e->getTranslationKey());
            $this->assertSame(['goal_9_revenue'], $e->getParams());
        }
    }

    public function testIsCard(): void
    {
        $this->assertTrue(KpiCardSettings::isCard(['fwdeKind' => 'kpi']));
        $this->assertFalse(KpiCardSettings::isCard(['fwdeKind' => 'funnel']));
        $this->assertFalse(KpiCardSettings::isCard([]));
    }
}
