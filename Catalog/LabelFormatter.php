<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Catalog;

use Piwik\Piwik;

/**
 * Turns the raw grouping key of a dimension into the label users see. Core label helpers are called only for the
 * kinds that need them, so the kinds without core helpers work without a Matomo environment.
 */
final class LabelFormatter
{
    /** Kinds whose empty value Matomo's own reports show as "Unknown". */
    private const EMPTY_IS_UNKNOWN = [
        'continent', 'country', 'region', 'city', 'language', 'deviceType', 'deviceBrand', 'deviceModel', 'operatingSystem',
        'operatingSystemVersion', 'browser', 'browserVersion',
    ];

    /** @var callable */
    private $translate;

    public function __construct(?callable $translate = null)
    {
        $this->translate = $translate ?? [Piwik::class, 'translate'];
    }

    public function translate(string $key, array $params = []): string
    {
        return ($this->translate)($key, $params);
    }

    public function format(string $kind, ?string $raw, string $dimensionName, int $idSite): string
    {
        if (($raw === null || $raw === '') && $kind === 'channelGroup') {
            // FeinwerkChannelGrouping's own text for visits without a channel
            $text = $this->translate('FeinwerkChannelGrouping_Unassigned');
            if ($text !== 'FeinwerkChannelGrouping_Unassigned') {
                return $text;
            }
        }
        if ($raw === null || $raw === '') {
            return in_array($kind, self::EMPTY_IS_UNKNOWN, true)
                ? $this->translate('General_Unknown')
                : $this->translate('FeinwerkDataExploration_NotDefined', [$dimensionName]);
        }

        switch ($kind) {
            case 'trimmed':
                return trim($raw);
            case 'campaign':
                return $this->campaignLabel($raw);
            case 'visitorType':
                return $this->translate($raw === '0' ? 'FeinwerkDataExploration_VisitorTypeNew' : 'FeinwerkDataExploration_VisitorTypeReturning');
            case 'yesNo':
                return $this->translate($raw === '0' ? 'General_No' : 'General_Yes');
            case 'weekday':
                return $this->translate('Intl_Day_Long_StandAlone_' . (int) $raw);
            case 'hour':
                return \Piwik\Plugins\VisitTime\getTimeLabel((int) $raw);
            case 'channelType':
                return \Piwik\Plugins\Referrers\getReferrerTypeLabel((int) $raw);
            case 'continent':
                return \Piwik\Plugins\UserCountry\continentTranslate($raw);
            case 'country':
                return \Piwik\Plugins\UserCountry\countryTranslate($raw);
            case 'region':
                return \Piwik\Plugins\UserCountry\getPrettyRegionName($raw);
            case 'city':
                return \Piwik\Plugins\UserCountry\getPrettyCityName($raw);
            case 'language':
                return \Piwik\Plugins\UserLanguage\languageTranslate($raw);
            case 'deviceType':
                return \Piwik\Plugins\DevicesDetection\getDeviceTypeLabel($raw);
            case 'operatingSystem':
                return \Piwik\Plugins\DevicesDetection\getOsFullName($raw . ';');
            case 'browser':
                return \Piwik\Plugins\DevicesDetection\getBrowserName($raw);
            case 'browserVersion':
                return \Piwik\Plugins\DevicesDetection\getBrowserNameWithVersion($raw);
            case 'operatingSystemVersion':
                return \Piwik\Plugins\DevicesDetection\getOsFullName($raw);
            case 'deviceBrand':
                return \Piwik\Plugins\DevicesDetection\getDeviceBrandLabel($raw);
            case 'deviceModel':
                return \Piwik\Plugins\DevicesDetection\getModelName($raw);
            default:
                return $raw;
        }
    }

    /**
     * Matomo's privacy setting "mask campaign values" stores a placeholder (PrivacyManager
     * CampaignParameterValuesMasked, Matomo 5.13 and newer); older Matomo versions have no text for it.
     */
    private function campaignLabel(string $raw): string
    {
        if ($raw !== '__discarded_by_policy__') {
            return $raw;
        }
        $text = $this->translate('PrivacyManager_CampaignParameterDiscarded');
        return $text === 'PrivacyManager_CampaignParameterDiscarded' ? $raw : $text;
    }
}
