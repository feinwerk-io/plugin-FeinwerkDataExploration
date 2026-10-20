<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Query;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Query\GroupingQuirk;

/**
 * @group FeinwerkDataExploration
 */
class GroupingQuirkTest extends TestCase
{
    public function testOnlyMysqlBeforeEightSortsOutsideTheGrouping(): void
    {
        $this->assertTrue(GroupingQuirk::applies('5.7.44'));
        $this->assertTrue(GroupingQuirk::applies('5.6.51-log'));
        $this->assertTrue(GroupingQuirk::applies('5.7.44-48'), 'Percona Server 5.7');
        $this->assertFalse(GroupingQuirk::applies('8.0.36'));
        $this->assertFalse(GroupingQuirk::applies('8.4.2'));
        $this->assertFalse(GroupingQuirk::applies('10.11.19-MariaDB-deb11'));
        $this->assertFalse(GroupingQuirk::applies('5.5.5-10.6.19-MariaDB'), 'MariaDB behind the old client prefix');
        $this->assertFalse(GroupingQuirk::applies(''));
    }
}
