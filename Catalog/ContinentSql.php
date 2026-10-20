<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Catalog;

use Piwik\Container\StaticContainer;
use Piwik\Intl\Data\Provider\RegionDataProvider;

/**
 * The continent of a visit, from its country as Matomo's continent report groups it (Common::getContinent): the
 * database has no continent column. Countries Matomo does not know give NULL ("Unknown").
 */
final class ContinentSql
{
    private static ?string $matomo = null;

    public static function forMatomo(): string
    {
        if (self::$matomo === null) {
            self::$matomo = self::expression(StaticContainer::get(RegionDataProvider::class)->getCountryList());
        }
        return self::$matomo;
    }

    /**
     * @param array<string, string> $countryToContinent country code => continent code
     */
    public static function expression(array $countryToContinent): string
    {
        $byContinent = [];
        foreach ($countryToContinent as $country => $continent) {
            // codes go into the SQL as they are: plain lower-case codes only
            if (!preg_match('/^[a-z0-9]{2,3}$/', (string) $country) || !preg_match('/^[a-z]{2,4}$/', (string) $continent)) {
                continue;
            }
            $byContinent[$continent][] = (string) $country;
            if ($country === 'cn') {
                // Matomo counts Tibet as China
                $byContinent[$continent][] = 'ti';
            }
        }
        if ($byContinent === []) {
            return 'NULL';
        }

        $sql = 'CASE';
        foreach ($byContinent as $continent => $countries) {
            $sql .= " WHEN log_visit.location_country IN ('" . implode("', '", array_unique($countries)) . "') THEN '" . $continent . "'";
        }
        return $sql . ' END';
    }
}
