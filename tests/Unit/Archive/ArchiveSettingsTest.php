<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Archive;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Archive\ArchiveSettings;

/**
 * The plugin's own archiving settings (Peter, 2026-11-25: "bitte eigene Config-Parameter für das Plugin anlegen, da diese
 * von den matomo standardeinstellungen abweichen können").
 */
class ArchiveSettingsTest extends TestCase
{
    public function testDefaults(): void
    {
        $s = ArchiveSettings::fromSection([]);
        $this->assertTrue($s->reports);
        $this->assertSame(6, $s->historyMonths);
        $this->assertFalse($s->historySegments);
        $this->assertFalse($s->segments);
        $this->assertSame(5, $s->historyBatch);
        $this->assertSame(50, $s->maxExplorationsPerSite);
        $this->assertSame(1000, $s->maxRows);
        $this->assertSame(100, $s->maxRowsSubtable);
        $this->assertSame(300, $s->querySeconds);
    }

    public function testValues(): void
    {
        $s = ArchiveSettings::fromSection([
            'archive_reports' => '0', 'archive_history_months' => '12', 'archive_history_segments' => 'yes',
            'archive_segments' => 'On', 'archive_history_batch' => 2, 'archive_max_explorations_per_site' => '7',
            'archive_max_rows' => '500', 'archive_max_rows_subtable' => '20', 'archive_query_seconds' => '60',
        ]);
        $this->assertFalse($s->reports);
        $this->assertSame(12, $s->historyMonths);
        $this->assertTrue($s->historySegments);
        $this->assertTrue($s->segments);
        $this->assertSame(2, $s->historyBatch);
        $this->assertSame(7, $s->maxExplorationsPerSite);
        $this->assertSame(500, $s->maxRows);
        $this->assertSame(20, $s->maxRowsSubtable);
        $this->assertSame(60, $s->querySeconds);
        // Matomo's INI reader may hand over PHP booleans
        $this->assertTrue(ArchiveSettings::fromSection(['archive_segments' => true])->segments);
        $this->assertFalse(ArchiveSettings::fromSection(['archive_reports' => 'off'])->reports);
    }

    public function testInvalidFallsBack(): void
    {
        $s = ArchiveSettings::fromSection([
            'archive_history_months' => '-1', 'archive_history_batch' => '0', 'archive_max_rows' => '0',
            'archive_max_rows_subtable' => 'many', 'archive_query_seconds' => '0', 'archive_max_explorations_per_site' => '-3',
            'archive_reports' => 'maybe',
        ]);
        $this->assertSame(6, $s->historyMonths);
        $this->assertSame(5, $s->historyBatch);
        $this->assertSame(1000, $s->maxRows);
        $this->assertSame(100, $s->maxRowsSubtable);
        $this->assertSame(300, $s->querySeconds);
        $this->assertSame(50, $s->maxExplorationsPerSite);
        $this->assertTrue($s->reports);

        // 0 is a meaning of its own for these two: no history, no cap
        $zero = ArchiveSettings::fromSection(['archive_history_months' => '0', 'archive_max_explorations_per_site' => '0']);
        $this->assertSame(0, $zero->historyMonths);
        $this->assertSame(0, $zero->maxExplorationsPerSite);
    }
}
