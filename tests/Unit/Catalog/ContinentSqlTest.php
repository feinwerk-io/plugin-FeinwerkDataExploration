<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Catalog;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\ContinentSql;

/**
 * @group FeinwerkDataExploration
 */
class ContinentSqlTest extends TestCase
{
    public function testCountriesAreGroupedByContinentLikeMatomo(): void
    {
        $sql = ContinentSql::expression(['de' => 'eur', 'us' => 'amn', 'fr' => 'eur', 'cn' => 'asi']);

        // Matomo counts Tibet ("ti") as China; unknown countries stay NULL ("Unknown")
        $this->assertSame(
            "CASE WHEN log_visit.location_country IN ('de', 'fr') THEN 'eur'"
            . " WHEN log_visit.location_country IN ('us') THEN 'amn'"
            . " WHEN log_visit.location_country IN ('cn', 'ti') THEN 'asi' END",
            $sql
        );
    }

    public function testCodesThatAreNotPlainLettersAreLeftOut(): void
    {
        $this->assertSame(
            "CASE WHEN log_visit.location_country IN ('de') THEN 'eur' END",
            ContinentSql::expression(['de' => 'eur', "x'y" => 'eur', 'zz' => "a'b"])
        );
    }

    public function testNoCountriesGiveNull(): void
    {
        $this->assertSame('NULL', ContinentSql::expression([]));
    }
}
