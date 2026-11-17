<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Period\Factory as PeriodFactory;
use Piwik\Piwik;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorApi;
use Piwik\Site;

/**
 * The names of comparison columns for exports and error messages: a stored segment's name (or the definition), the
 * period as Matomo writes it short.
 */
final class ComparisonTitles
{
    public static function segment(int $idSite, string $definition): string
    {
        if ($definition === '') {
            return Piwik::translate('SegmentEditor_DefaultAllVisits');
        }
        try {
            foreach (SegmentEditorApi::getInstance()->getAll($idSite) as $stored) {
                if (in_array($definition, [$stored['definition'], urldecode($stored['definition'])], true)) {
                    return (string) $stored['name'];
                }
            }
        } catch (\Throwable $e) {
            // without the segment list the definition names the column
        }
        return $definition;
    }

    public static function period(int $idSite, string $period, string $date): string
    {
        try {
            return PeriodFactory::makePeriodFromQueryParams(Site::getTimezoneFor($idSite), $period, $date)->getLocalizedShortString();
        } catch (\Throwable $e) {
            return $period . ' ' . $date;
        }
    }

    /** "segment", "period" or "segment, period" by what the column changes. */
    public static function column(int $idSite, string $kind, string $segment, string $period, string $date): string
    {
        if ($kind === ComparisonTable::KIND_SEGMENT) {
            return self::segment($idSite, $segment);
        }
        if ($kind === ComparisonTable::KIND_PERIOD) {
            return self::period($idSite, $period, $date);
        }
        return self::segment($idSite, $segment) . ', ' . self::period($idSite, $period, $date);
    }
}
