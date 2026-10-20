<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Catalog;

use Piwik\Columns\DimensionsProvider;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\Query\LocalTimeSql;
use Piwik\Plugins\FeinwerkDataExploration\Query\QueryContext;

/**
 * The tested positive list of dimensions (spec 2.2). A dimension backed by a core dimension is available only when
 * Matomo's dimension registry knows it (its plugin is active); custom dimensions come from the site's configuration.
 */
final class DimensionCatalog
{
    public const SCOPE_VISIT = 'visit';
    public const SCOPE_ACTION = 'action';

    private const ALL_ACTION_KINDS = ['pageview', 'event', 'download', 'outlink', 'search'];
    private const ACTION_KINDS = [
        'pageUrl' => ['pageview', 'event'],
        'pageTitle' => ['pageview'],
        'eventCategory' => ['event'],
        'eventAction' => ['event'],
        'eventName' => ['event'],
        'downloadUrl' => ['download'],
        'outlinkUrl' => ['outlink'],
        'siteSearchKeyword' => ['search'],
    ];

    /** Values Matomo stores HTML-escaped (core TableLogAction::normaliseActionString, custom dimension values). */
    private const SANITIZED_TEXT = [
        'pageTitle', 'entryPageTitle', 'exitPageTitle', 'eventCategory', 'eventAction', 'eventName', 'siteSearchKeyword',
    ];

    /** Dimensions of MarketingCampaignsReporting: its columns in log_visit, filled for campaign visits only. */
    private const MCR_COLUMNS = ['campaignSource' => 'campaign_source', 'campaignMedium' => 'campaign_medium'];

    /** Dimensions on a log_visit column another plugin adds: hidden when the column is missing. */
    private const PLUGIN_COLUMNS = ['channelGroup' => 'channel_grouping'];

    /**
     * Standard reports that get "Open in exploration" (spec 2.1), API method => dimension id; the custom dimension
     * report links to the dimension of its idDimension.
     */
    private const REPORT_LINKS = [
        'Referrers.getReferrerType' => 'channelType',
        'Referrers.getWebsites' => 'referrerName',
        'Referrers.getSearchEngines' => 'referrerName',
        'Referrers.getSocials' => 'referrerName',
        'Referrers.getAIAssistants' => 'referrerName',
        'Referrers.getKeywords' => 'referrerKeyword',
        'Referrers.getCampaigns' => 'campaignName',
        'MarketingCampaignsReporting.getName' => 'campaignName',
        'MarketingCampaignsReporting.getSource' => 'campaignSource',
        'MarketingCampaignsReporting.getMedium' => 'campaignMedium',
        'FeinwerkChannelGrouping.getChannels' => 'channelGroup',
        'UserCountry.getContinent' => 'continent',
        'UserCountry.getCountry' => 'country',
        'UserCountry.getRegion' => 'region',
        'UserCountry.getCity' => 'city',
        'UserLanguage.getLanguage' => 'language',
        'VisitorInterest.getNumberOfVisitsByVisitCount' => 'visitCount',
        'DevicesDetection.getType' => 'deviceType',
        'DevicesDetection.getBrand' => 'deviceBrand',
        'DevicesDetection.getModel' => 'deviceModel',
        'DevicesDetection.getOsFamilies' => 'operatingSystem',
        'DevicesDetection.getOsVersions' => 'operatingSystemVersion',
        'DevicesDetection.getBrowsers' => 'browser',
        'DevicesDetection.getBrowserVersions' => 'browserVersion',
        'Resolution.getResolution' => 'resolution',
        // the hour of the exploration is the time of the site, like this report (not the visitor's local time)
        'VisitTime.getVisitInformationPerServerTime' => 'hour',
        'VisitTime.getByDayOfWeek' => 'weekday',
        'Actions.getPageUrls' => 'pageUrl',
        'Actions.getPageTitles' => 'pageTitle',
        'Actions.getEntryPageUrls' => 'entryPageUrl',
        'Actions.getEntryPageTitles' => 'entryPageTitle',
        'Actions.getExitPageUrls' => 'exitPageUrl',
        'Actions.getExitPageTitles' => 'exitPageTitle',
        'Actions.getDownloads' => 'downloadUrl',
        'Actions.getOutlinks' => 'outlinkUrl',
        'Actions.getSiteSearchKeywords' => 'siteSearchKeyword',
        'Events.getCategory' => 'eventCategory',
        'Events.getAction' => 'eventAction',
        'Events.getName' => 'eventName',
        'CustomDimensions.getCustomDimension' => 'customDimension',
    ];

    /**
     * Reports of one channel: their link opens the exploration on that channel's visits (the report's rows carry the
     * same condition in their own segments). Name and value are plain words, they travel as a CSS class.
     */
    private const REPORT_SCOPES = [
        'Referrers.getWebsites' => 'referrerType==website',
        'Referrers.getSearchEngines' => 'referrerType==search',
        'Referrers.getSocials' => 'referrerType==social',
        'Referrers.getAIAssistants' => 'referrerType==ai',
        'Referrers.getKeywords' => 'referrerType==search',
    ];

    private const VISIT = 'log_visit';
    private const ACTION = 'log_link_visit_action';

    /** @var callable fn(string $coreDimensionId): ?string, the translated name or null when unavailable */
    private $coreDimensionName;
    private CustomDimensionSource $customDimensions;
    private LabelFormatter $labels;
    private ColumnProbe $columns;
    /** @var callable fn(): bool, whether campaign values are compared in lower case (MarketingCampaignsReporting) */
    private $lowerCampaignCase;
    /** @var callable fn(int $idSite): bool, whether Matomo's compliance policy turns device model detection off */
    private $deviceModelOff;

    /** @var array<int, array<string, DimensionDefinition>> */
    private array $bySite = [];

    public function __construct(
        ?callable $coreDimensionName = null,
        ?CustomDimensionSource $customDimensions = null,
        ?LabelFormatter $labels = null,
        ?ColumnProbe $columns = null,
        ?callable $lowerCampaignCase = null,
        ?callable $deviceModelOff = null
    ) {
        $this->coreDimensionName = $coreDimensionName ?? static function (string $coreDimensionId): ?string {
            $dimension = (new DimensionsProvider())->factory($coreDimensionId);
            return $dimension === null ? null : $dimension->getName();
        };
        $this->customDimensions = $customDimensions ?? new CustomDimensionsApiSource();
        $this->labels = $labels ?? new LabelFormatter();
        $this->columns = $columns ?? new ColumnProbe();
        $this->lowerCampaignCase = $lowerCampaignCase ?? [self::class, 'campaignCaseLowered'];
        $this->deviceModelOff = $deviceModelOff ?? static function (int $idSite): bool {
            try {
                return \Piwik\Plugins\DevicesDetection\DevicesDetection::isDeviceModelDetectionDisabledByCompliancePolicy($idSite);
            } catch (\Throwable $e) {
                return false;
            }
        };
    }

    /**
     * Whether campaign values are lower-cased: MarketingCampaignsReporting answers this event and lower-cases its reports
     * when it says so. A failing listener leaves the values as they are.
     */
    public static function campaignCaseLowered(): bool
    {
        $lower = false;
        try {
            Piwik::postEvent('Plugin.shouldLowerCampaignCase', ['FeinwerkDataExploration', &$lower]);
        } catch (\Throwable $e) {
            return false;
        }
        return (bool) $lower;
    }

    /**
     * @return array<string, DimensionDefinition> in display order
     */
    public function availableFor(int $idSite): array
    {
        if (!isset($this->bySite[$idSite])) {
            $this->bySite[$idSite] = $this->build($idSite);
        }
        return $this->bySite[$idSite];
    }

    public function get(int $idSite, string $id): ?DimensionDefinition
    {
        return $this->availableFor($idSite)[$id] ?? null;
    }

    /** The segment a report's link adds (see REPORT_SCOPES), or null. */
    public static function reportScope(string $apiMethod): ?string
    {
        return self::REPORT_SCOPES[$apiMethod] ?? null;
    }

    public static function linksReport(string $apiMethod): bool
    {
        return isset(self::REPORT_LINKS[$apiMethod]);
    }

    /**
     * The dimension a standard report opens in the exploration, or null when the report has none or the dimension is
     * not available for the site.
     *
     * @param array $params request parameters of the report (idDimension for custom dimensions)
     */
    public function reportLink(int $idSite, string $apiMethod, array $params): ?string
    {
        $id = self::REPORT_LINKS[$apiMethod] ?? null;
        if ($id === 'customDimension') {
            $id .= (int) ($params['idDimension'] ?? 0);
        }
        return $id !== null && $this->get($idSite, $id) !== null ? $id : null;
    }

    private function build(int $idSite): array
    {
        $dimensions = [];
        $mcrCampaignName = $this->hasPluginColumn('MarketingCampaignsReporting.CampaignName', 'campaign_name');
        $lowerCampaigns = null;
        foreach (self::positiveList() as $entry) {
            [$id, $group, $scope, $coreId, $nameKey, $labelKind, $keySql, $joins] = $entry;
            $coreName = $coreId === null ? null : ($this->coreDimensionName)($coreId);
            if ($coreId !== null && $coreName === null) {
                continue;
            }
            if (isset(self::PLUGIN_COLUMNS[$id]) && !$this->columns->hasColumn('log_visit', self::PLUGIN_COLUMNS[$id])) {
                continue;
            }
            // like Matomo's device model report, which its compliance policy turns off per site
            if ($id === 'deviceModel' && ($this->deviceModelOff)($idSite)) {
                continue;
            }
            $campaignColumn = $id === 'campaignName' && $mcrCampaignName ? 'campaign_name' : (self::MCR_COLUMNS[$id] ?? null);
            if ($campaignColumn !== null) {
                if (!$this->columns->hasColumn('log_visit', $campaignColumn)) {
                    continue;
                }
                $lowerCampaigns ??= ($this->lowerCampaignCase)();
                $keySql = self::campaignKey($campaignColumn, $lowerCampaigns);
            }
            $name = $nameKey !== null ? $this->labels->translate($nameKey) : $coreName;
            $dimensions[$id] = new DimensionDefinition(
                $id, $group, $scope, $name, $labelKind, $keySql, $joins, $this->labels, $entry[8] ?? null, self::ACTION_KINDS[$id] ?? [],
                in_array($id, self::SANITIZED_TEXT, true),
                self::segment($id, $campaignColumn)
            );
        }

        foreach ($this->customDimensions->activeFor($idSite) as $custom) {
            $id = 'customDimension' . $custom['id'];
            $column = ($custom['scope'] === self::SCOPE_ACTION ? self::ACTION : self::VISIT) . '.custom_dimension_' . (int) $custom['index'];
            $dimensions[$id] = new DimensionDefinition(
                $id,
                'custom',
                $custom['scope'],
                $custom['name'],
                'raw',
                static fn (QueryContext $c, string $alias): string => $column,
                null,
                $this->labels,
                null,
                $custom['scope'] === self::SCOPE_ACTION ? self::ALL_ACTION_KINDS : [],
                true,
                self::equals('dimension' . (int) $custom['id'])
            );
        }

        return $dimensions;
    }

    /**
     * [id, group, scope, core dimension id, own name key, label kind, key SQL, joins, page identity column (optional)]
     */
    private static function positiveList(): array
    {
        $column = static fn (string $sql): \Closure => static fn (QueryContext $c, string $alias): string => $sql;
        $actionName = static fn (QueryContext $c, string $alias): string => $alias . 'a.name';
        // titles with surrounding whitespace would otherwise form rows with the same label
        $trimmedActionName = static fn (QueryContext $c, string $alias): string => 'TRIM(' . $alias . 'a.name)';

        return [
            ['channelType', 'acquisition', self::SCOPE_VISIT, 'Referrers.ReferrerType', null, 'channelType',
                $column('CASE WHEN log_visit.referer_type IS NULL OR log_visit.referer_type = 0 THEN 1 ELSE log_visit.referer_type END'), null],
            ['referrerName', 'acquisition', self::SCOPE_VISIT, 'Referrers.ReferrerName', null, 'raw',
                $column('log_visit.referer_name'), null],
            // search keywords and the keywords of campaign visits, like Matomo's segment referrerKeyword
            ['referrerKeyword', 'acquisition', self::SCOPE_VISIT, 'Referrers.Keyword', null, 'raw',
                $column("NULLIF(log_visit.referer_keyword, '')"), null],
            ['referrerUrl', 'acquisition', self::SCOPE_VISIT, 'Referrers.ReferrerUrl', null, 'raw',
                $column("NULLIF(log_visit.referer_url, '')"), null],
            // with MarketingCampaignsReporting the campaign columns are read instead (build())
            ['campaignName', 'acquisition', self::SCOPE_VISIT, 'Referrers.Campaign', 'FeinwerkDataExploration_DimensionCampaignName', 'campaign',
                $column('CASE WHEN log_visit.referer_type = 6 THEN log_visit.referer_name END'), null],
            ['campaignSource', 'acquisition', self::SCOPE_VISIT, 'MarketingCampaignsReporting.CampaignSource',
                'FeinwerkDataExploration_DimensionCampaignSource', 'campaign', null, null],
            ['campaignMedium', 'acquisition', self::SCOPE_VISIT, 'MarketingCampaignsReporting.CampaignMedium',
                'FeinwerkDataExploration_DimensionCampaignMedium', 'campaign', null, null],
            // FeinwerkChannelGrouping stores the channel name of each visit as it is
            ['channelGroup', 'acquisition', self::SCOPE_VISIT, 'FeinwerkChannelGrouping.FeinwerkChannelGroupingDimension', null,
                'channelGroup', $column("NULLIF(log_visit.channel_grouping, '')"), null],

            ['visitorType', 'visitor', self::SCOPE_VISIT, 'CoreHome.VisitorReturning', 'FeinwerkDataExploration_DimensionVisitorType', 'visitorType',
                $column('CASE WHEN log_visit.visitor_returning = 0 THEN 0 ELSE 1 END'), null],
            ['visitCount', 'visitor', self::SCOPE_VISIT, 'CoreHome.VisitsCount', 'FeinwerkDataExploration_DimensionVisitCount', 'raw',
                $column('log_visit.visitor_count_visits'), null],
            ['continent', 'visitor', self::SCOPE_VISIT, 'UserCountry.Continent', null, 'continent',
                static fn (QueryContext $c, string $alias): string => ContinentSql::forMatomo(), null],
            ['country', 'visitor', self::SCOPE_VISIT, 'UserCountry.Country', null, 'country',
                // 'xx' and '' are both "Unknown"; NULL gives them one row
                $column("CASE WHEN log_visit.location_country IN ('xx', '') THEN NULL WHEN log_visit.location_country = 'ti' THEN 'cn'"
                    . ' ELSE log_visit.location_country END'), null],
            ['region', 'visitor', self::SCOPE_VISIT, 'UserCountry.Region', null, 'region',
                $column("CASE WHEN log_visit.location_region IS NULL OR log_visit.location_region = '' THEN NULL"
                    . " ELSE CONCAT(log_visit.location_region, '|', log_visit.location_country) END"), null],
            ['city', 'visitor', self::SCOPE_VISIT, 'UserCountry.City', null, 'city',
                $column("CASE WHEN log_visit.location_city IS NULL OR log_visit.location_city = '' THEN NULL"
                    . " ELSE CONCAT(log_visit.location_city, '|', COALESCE(log_visit.location_region, ''), '|', log_visit.location_country) END"), null],
            ['language', 'visitor', self::SCOPE_VISIT, 'UserLanguage.Language', 'FeinwerkDataExploration_DimensionLanguage', 'language',
                $column("NULLIF(LOWER(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(log_visit.location_browser_lang, ',', 1), ';', 1), '-', 1)), '')"), null],

            ['deviceType', 'technology', self::SCOPE_VISIT, 'DevicesDetection.DeviceType', null, 'deviceType',
                $column('log_visit.config_device_type'), null],
            ['deviceBrand', 'technology', self::SCOPE_VISIT, 'DevicesDetection.DeviceBrand', null, 'deviceBrand',
                $column("NULLIF(log_visit.config_device_brand, '')"), null],
            // "brand;model" and "code;version" as Matomo's DevicesDetection reports label them
            ['deviceModel', 'technology', self::SCOPE_VISIT, 'DevicesDetection.DeviceModel', null, 'deviceModel',
                $column("CASE WHEN COALESCE(log_visit.config_device_brand, '') = '' AND COALESCE(log_visit.config_device_model, '') = ''"
                    . " THEN NULL ELSE CONCAT(COALESCE(log_visit.config_device_brand, ''), ';', COALESCE(log_visit.config_device_model, '')) END"), null],
            ['operatingSystem', 'technology', self::SCOPE_VISIT, 'DevicesDetection.Os', null, 'operatingSystem',
                $column("NULLIF(NULLIF(log_visit.config_os, ''), 'UNK')"), null],
            ['operatingSystemVersion', 'technology', self::SCOPE_VISIT, 'DevicesDetection.OsVersion', null, 'operatingSystemVersion',
                $column("CASE WHEN log_visit.config_os IS NULL OR log_visit.config_os IN ('', 'UNK') THEN NULL"
                    . " ELSE CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, '')) END"), null],
            ['browser', 'technology', self::SCOPE_VISIT, 'DevicesDetection.BrowserName', null, 'browser',
                $column("NULLIF(NULLIF(log_visit.config_browser_name, ''), 'UNK')"), null],
            ['browserVersion', 'technology', self::SCOPE_VISIT, 'DevicesDetection.BrowserVersion', null, 'browserVersion',
                $column("CASE WHEN log_visit.config_browser_name IS NULL OR log_visit.config_browser_name IN ('', 'UNK') THEN NULL"
                    . " ELSE CONCAT(log_visit.config_browser_name, ';', COALESCE(log_visit.config_browser_version, '')) END"), null],
            ['resolution', 'technology', self::SCOPE_VISIT, 'Resolution.Resolution', null, 'raw',
                $column('log_visit.config_resolution'), null],

            ['date', 'time', self::SCOPE_VISIT, null, 'FeinwerkDataExploration_DimensionDate', 'raw',
                self::timeKey('DATE(%s)', 'visit_last_action_time'), null],
            ['weekday', 'time', self::SCOPE_VISIT, null, 'FeinwerkDataExploration_DimensionWeekday', 'weekday',
                self::timeKey('WEEKDAY(%s) + 1', 'visit_last_action_time'), null],
            ['hour', 'time', self::SCOPE_VISIT, 'VisitTime.LocalTime', 'FeinwerkDataExploration_DimensionHour', 'hour',
                self::timeKey('HOUR(%s)', 'visit_first_action_time'), null],

            ['entryPageUrl', 'visit', self::SCOPE_VISIT, 'Actions.EntryPageUrl', null, 'raw',
                $actionName, self::actionJoin(self::VISIT, 'visit_entry_idaction_url', '= 1')],
            ['entryPageTitle', 'visit', self::SCOPE_VISIT, 'Actions.EntryPageTitle', null, 'trimmed',
                $trimmedActionName, self::actionJoin(self::VISIT, 'visit_entry_idaction_name', '= 4')],
            ['exitPageUrl', 'visit', self::SCOPE_VISIT, 'Actions.ExitPageUrl', null, 'raw',
                $actionName, self::actionJoin(self::VISIT, 'visit_exit_idaction_url', '= 1')],
            ['exitPageTitle', 'visit', self::SCOPE_VISIT, 'Actions.ExitPageTitle', null, 'trimmed',
                $trimmedActionName, self::actionJoin(self::VISIT, 'visit_exit_idaction_name', '= 4')],
            ['visitConverted', 'visit', self::SCOPE_VISIT, 'CoreHome.VisitGoalConverted', null, 'yesNo',
                $column('log_visit.visit_goal_converted'), null],

            ['pageUrl', 'behaviour', self::SCOPE_ACTION, 'Actions.PageUrl', null, 'raw',
                $actionName, self::actionJoin(self::ACTION, 'idaction_url', 'IN (1, 10)')],
            ['pageTitle', 'behaviour', self::SCOPE_ACTION, 'Actions.PageTitle', null, 'trimmed',
                // Matomo's page title report counts unique pageviews per title, not per URL
                $trimmedActionName, self::actionJoin(self::ACTION, 'idaction_name', '= 4'), 'idaction_name'],
            ['eventCategory', 'behaviour', self::SCOPE_ACTION, 'Events.EventCategory', null, 'raw',
                $actionName, self::actionJoin(self::ACTION, 'idaction_event_category')],
            ['eventAction', 'behaviour', self::SCOPE_ACTION, 'Events.EventAction', null, 'raw',
                $actionName, self::actionJoin(self::ACTION, 'idaction_event_action')],
            ['eventName', 'behaviour', self::SCOPE_ACTION, 'Events.EventName', null, 'raw',
                $actionName, self::actionJoin(self::ACTION, 'idaction_name', '= 12')],
            ['downloadUrl', 'behaviour', self::SCOPE_ACTION, 'Actions.DownloadUrl', null, 'raw',
                $actionName, self::actionJoin(self::ACTION, 'idaction_url', '= 3')],
            ['outlinkUrl', 'behaviour', self::SCOPE_ACTION, 'Actions.ClickedUrl', null, 'raw',
                $actionName, self::actionJoin(self::ACTION, 'idaction_url', '= 2')],
            ['siteSearchKeyword', 'behaviour', self::SCOPE_ACTION, 'Actions.SearchKeyword', null, 'raw',
                $actionName, self::actionJoin(self::ACTION, 'idaction_name', '= 8')],
        ];
    }

    private function hasPluginColumn(string $coreDimensionId, string $column): bool
    {
        return ($this->coreDimensionName)($coreDimensionId) !== null && $this->columns->hasColumn('log_visit', $column);
    }

    /**
     * The Matomo segment of a dimension's stored value (see DimensionDefinition::segmentFor), null when Matomo has
     * none that matches the exploration's key (date, weekday and hour are computed in the site's timezone).
     *
     * @param string|null $campaignColumn the MarketingCampaignsReporting column a campaign dimension reads, if any
     */
    private static function segment(string $id, ?string $campaignColumn): ?\Closure
    {
        $mcr = ['campaign_name' => 'campaignName', 'campaign_source' => 'campaignSource', 'campaign_medium' => 'campaignMedium'];
        if ($campaignColumn !== null) {
            return self::equals($mcr[$campaignColumn]);
        }
        switch ($id) {
            case 'channelType':
                // 1 direct, 2 search, 3 website, 6 campaign, 7 social, 8 ai (Referrers' own short names)
                return static function (string $raw): ?string {
                    try {
                        return 'referrerType==' . self::value((string) \Piwik\Plugins\Referrers\getReferrerTypeFromShortName((int) $raw));
                    } catch (\Throwable $e) {
                        // a type Matomo does not know (its label is "Others"), or Referrers is not loaded
                        return null;
                    }
                };
            case 'campaignName':
                return static fn (string $raw): string => 'referrerType==campaign;referrerName==' . self::value($raw);
            case 'visitorType':
                return static fn (string $raw): string => $raw === '0'
                    ? 'visitorType==new'
                    : 'visitorType==returning,visitorType==returningCustomer';
            case 'country':
                // Matomo shows Tibet ("ti") as China, so does the exploration
                return static fn (string $raw): string => $raw === 'cn' ? 'countryCode==cn,countryCode==ti' : 'countryCode==' . self::value($raw);
            case 'region':
                return static function (string $raw): string {
                    [$region, $country] = array_pad(explode('|', $raw), 2, '');
                    return 'regionCode==' . self::value($region) . ';countryCode==' . self::value($country);
                };
            case 'city':
                return static function (string $raw): string {
                    [$city, $region, $country] = array_pad(explode('|', $raw), 3, '');
                    return 'city==' . self::value($city) . ($region === '' ? '' : ';regionCode==' . self::value($region))
                        . ';countryCode==' . self::value($country);
                };
            case 'language':
                // the exploration groups "de", "de-de" and "de-at"
                return static fn (string $raw): string => 'languageCode==' . self::value($raw) . ',languageCode=^' . self::value($raw . '-');
            case 'deviceType':
                return static function (string $raw): ?string {
                    $names = array_flip(\DeviceDetector\Parser\Device\AbstractDeviceParser::getAvailableDeviceTypes());
                    return isset($names[(int) $raw]) ? 'deviceType==' . self::value($names[(int) $raw]) : null;
                };
            case 'browserVersion':
                return self::codeAndVersion('browserCode', 'browserVersion');
            case 'operatingSystemVersion':
                return self::codeAndVersion('operatingSystemCode', 'operatingSystemVersion');
            case 'deviceBrand':
                // Matomo's segment takes the brand's name
                return static function (string $raw): ?string {
                    $brands = \DeviceDetector\Parser\Device\AbstractDeviceParser::$deviceBrands;
                    return isset($brands[$raw]) ? 'deviceBrand==' . self::value($brands[$raw]) : null;
                };
            case 'deviceModel':
                return static function (string $raw): ?string {
                    [$code, $model] = array_pad(explode(';', $raw, 2), 2, '');
                    $brands = \DeviceDetector\Parser\Device\AbstractDeviceParser::$deviceBrands;
                    if ($code !== '' && !isset($brands[$code])) {
                        // a brand DeviceDetector no longer knows: Matomo's segment has no value for it
                        return null;
                    }
                    // "Unknown" is what Matomo's segment takes for visits without a brand (its model report does the same)
                    $brand = $code === '' ? Piwik::translate('General_Unknown') : $brands[$code];
                    return 'deviceBrand==' . self::value($brand) . ';deviceModel==' . self::value($model);
                };
            case 'date':
            case 'weekday':
            case 'hour':
                return null;
        }
        $segments = [
            'referrerName' => 'referrerName', 'operatingSystem' => 'operatingSystemCode', 'browser' => 'browserCode',
            'referrerKeyword' => 'referrerKeyword', 'referrerUrl' => 'referrerUrl', 'visitCount' => 'visitCount',
            'continent' => 'continentCode',
            'resolution' => 'resolution', 'entryPageUrl' => 'entryPageUrl', 'entryPageTitle' => 'entryPageTitle',
            'exitPageUrl' => 'exitPageUrl', 'exitPageTitle' => 'exitPageTitle', 'visitConverted' => 'visitConverted',
            'pageUrl' => 'pageUrl', 'pageTitle' => 'pageTitle', 'eventCategory' => 'eventCategory',
            'eventAction' => 'eventAction', 'eventName' => 'eventName', 'downloadUrl' => 'downloadUrl',
            'outlinkUrl' => 'outlinkUrl', 'siteSearchKeyword' => 'siteSearchKeyword', 'channelGroup' => 'channelGrouping',
        ];
        return isset($segments[$id]) ? self::equals($segments[$id]) : null;
    }

    /** "code;version" keys: both segments of Matomo's version reports. */
    private static function codeAndVersion(string $codeSegment, string $versionSegment): \Closure
    {
        return static function (string $raw) use ($codeSegment, $versionSegment): string {
            [$code, $version] = array_pad(explode(';', $raw, 2), 2, '');
            return $codeSegment . '==' . self::value($code) . ';' . $versionSegment . '==' . self::value($version);
        };
    }

    private static function equals(string $segmentName): \Closure
    {
        return static fn (string $raw): string => $segmentName . '==' . self::value($raw);
    }

    /**
     * A value in a segment string. Matomo decodes the segment string and then each value, so a value is encoded twice,
     * as the segments of Matomo's Actions reports are: one encoding would turn a literal "+" or "%2C" into a space or a
     * comma.
     */
    private static function value(string $raw): string
    {
        return urlencode(urlencode($raw));
    }

    /**
     * A campaign column of MarketingCampaignsReporting, like its reports: campaign visits only, lower-cased when its
     * setting says so.
     */
    private static function campaignKey(string $column, bool $lower): \Closure
    {
        $value = $lower ? 'LOWER(log_visit.' . $column . ')' : 'log_visit.' . $column;
        $sql = 'CASE WHEN log_visit.referer_type = 6 THEN NULLIF(' . $value . ", '') END";
        return static fn (QueryContext $c, string $alias): string => $sql;
    }

    /**
     * Time keys in the site timezone: in visit scope from the given log_visit column, in action scope from the
     * time of the action.
     */
    private static function timeKey(string $pattern, string $visitColumn): \Closure
    {
        return static function (QueryContext $c, string $alias) use ($pattern, $visitColumn): string {
            $utcColumn = $c->scope === self::SCOPE_ACTION ? self::ACTION . '.server_time' : self::VISIT . '.' . $visitColumn;
            return sprintf($pattern, LocalTimeSql::expression($utcColumn, $c->timezone, $c->windowStartUtc, $c->windowEndUtc));
        };
    }

    /**
     * A LEFT JOIN of log_action on an idaction column, optionally restricted to action types, so rows of other
     * types get a NULL key.
     */
    private static function actionJoin(string $table, string $column, ?string $typeCondition = null): \Closure
    {
        return static function (QueryContext $c, string $alias) use ($table, $column, $typeCondition): array {
            $actionAlias = $alias . 'a';
            $joinOn = $actionAlias . '.idaction = ' . $table . '.' . $column;
            if ($typeCondition !== null) {
                $joinOn .= ' AND ' . $actionAlias . '.type ' . $typeCondition;
            }
            return [['table' => 'log_action', 'tableAlias' => $actionAlias, 'joinOn' => $joinOn]];
        };
    }
}
