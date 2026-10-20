<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\System;

use Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures\ExplorationFixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * Date, day of week and hour in the site timezone (Europe/Berlin, DST switch on 2026-03-29).
 *
 * @group FeinwerkDataExploration
 */
class TimeDimensionParityTest extends SystemTestCase
{
    use ParityHelper;

    /** @var ExplorationFixture */
    public static $fixture = null;

    public function testDateAcrossDstMatchesVisitsSummary(): void
    {
        $metrics = ['nb_visits', 'nb_uniq_visitors', 'nb_actions'];
        $expected = [];
        foreach (ExplorationFixture::DATES as $date) {
            $summary = $this->coreRows('VisitsSummary.get', 'day', $date);
            $expected[$date] = reset($summary);
        }

        $ours = $this->ourRows(['dimensions' => ['date'], 'metrics' => $metrics], 'range', ExplorationFixture::RANGE);

        $this->assertEquals(self::pick($expected, $metrics), self::pick($ours, $metrics));
    }

    public function testWeekdayMatchesVisitTime(): void
    {
        $expected = [];
        foreach ($this->coreRows('VisitTime.getByDayOfWeek', 'range', ExplorationFixture::RANGE) as $label => $row) {
            if (!empty($row['nb_visits'])) {
                $expected[$label] = $row;
            }
        }

        $ours = $this->ourRows(['dimensions' => ['weekday'], 'metrics' => ['nb_visits', 'nb_actions']], 'range', ExplorationFixture::RANGE);

        $this->assertSame(['Friday', 'Monday', 'Saturday', 'Sunday'], array_keys(self::pick($expected, ['nb_visits'])));
        $this->assertEquals(self::pick($expected, ['nb_visits', 'nb_actions']), self::pick($ours, ['nb_visits', 'nb_actions']));
    }

    public function testHourMatchesServerTimeReport(): void
    {
        foreach (ExplorationFixture::DATES as $date) {
            // Matomo's server-time report converts a whole day with the UTC offset of the day's start. On the DST switch
            // (2026-03-29, all fixture visits after 01:00 UTC) its hours are one too early; the exploration uses the
            // exact offset of every visit.
            $shift = $date === '2026-03-29' ? 1 : 0;
            $expected = [];
            foreach ($this->coreRows('VisitTime.getVisitInformationPerServerTime', 'day', $date) as $label => $row) {
                if (!empty($row['nb_visits'])) {
                    $expected[(int) $label + $shift] = (int) $row['nb_visits'];
                }
            }

            $actual = [];
            foreach ($this->ourRows(['dimensions' => ['hour'], 'metrics' => ['nb_visits']], 'day', $date) as $label => $row) {
                $actual[(int) $label] = (int) $row['nb_visits'];
            }

            ksort($expected);
            ksort($actual);
            $this->assertNotEmpty($expected, $date);
            $this->assertSame($expected, $actual, $date);
        }
    }

    public function testActionScopeDateUsesServerTime(): void
    {
        $ours = $this->ourRows(['dimensions' => ['date'], 'metrics' => ['nb_pageviews']], 'range', ExplorationFixture::RANGE);

        foreach (ExplorationFixture::DATES as $date) {
            $actions = $this->coreRows('Actions.get', 'day', $date);
            $this->assertEquals((int) reset($actions)['nb_pageviews'], (int) $ours[$date]['nb_pageviews'], $date);
        }
    }
}

TimeDimensionParityTest::$fixture = new ExplorationFixture();
