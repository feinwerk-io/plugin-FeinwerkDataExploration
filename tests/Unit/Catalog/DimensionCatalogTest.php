<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Catalog;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\ColumnProbe;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\CustomDimensionSource;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionCatalog;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\LabelFormatter;
use Piwik\Plugins\FeinwerkDataExploration\Query\QueryContext;

/**
 * @group FeinwerkDataExploration
 */
class DimensionCatalogTest extends TestCase
{
    private const POSITIVE_LIST = [
        'channelType' => 'visit', 'referrerName' => 'visit', 'referrerKeyword' => 'visit', 'referrerUrl' => 'visit',
        'campaignName' => 'visit',
        'visitorType' => 'visit', 'visitCount' => 'visit', 'continent' => 'visit', 'country' => 'visit', 'region' => 'visit',
        'city' => 'visit', 'language' => 'visit',
        'deviceType' => 'visit', 'deviceBrand' => 'visit', 'deviceModel' => 'visit', 'operatingSystem' => 'visit',
        'operatingSystemVersion' => 'visit', 'browser' => 'visit', 'browserVersion' => 'visit', 'resolution' => 'visit',
        'date' => 'visit', 'weekday' => 'visit', 'hour' => 'visit',
        'entryPageUrl' => 'visit', 'entryPageTitle' => 'visit', 'exitPageUrl' => 'visit', 'exitPageTitle' => 'visit',
        'visitConverted' => 'visit',
        'pageUrl' => 'action', 'pageTitle' => 'action', 'eventCategory' => 'action', 'eventAction' => 'action',
        'eventName' => 'action', 'downloadUrl' => 'action', 'outlinkUrl' => 'action', 'siteSearchKeyword' => 'action',
        'customDimension3' => 'visit', 'customDimension4' => 'action',
    ];

    public function testPositiveListIdsAndScopes(): void
    {
        $scopes = [];
        foreach ($this->catalog()->availableFor(1) as $id => $dimension) {
            $this->assertSame($id, $dimension->id);
            $scopes[$id] = $dimension->scope;
        }

        $this->assertSame(self::POSITIVE_LIST, $scopes);
    }

    public function testPageUrlJoinRestrictsToPageAndEventUrlTypes(): void
    {
        $pageUrl = $this->catalog()->get(1, 'pageUrl');
        $context = $this->context('action');

        $this->assertSame('fwde_k1a.name', $pageUrl->keySql($context, 'fwde_k1'));
        $this->assertSame([[
            'table' => 'log_action',
            'tableAlias' => 'fwde_k1a',
            'joinOn' => 'fwde_k1a.idaction = log_link_visit_action.idaction_url AND fwde_k1a.type IN (1, 10)',
        ]], $pageUrl->joins($context, 'fwde_k1'));
    }

    public function testEntryPageJoinsOnTheVisit(): void
    {
        $joins = $this->catalog()->get(1, 'entryPageTitle')->joins($this->context('visit'), 'fwde_k2');

        $this->assertSame('fwde_k2a.idaction = log_visit.visit_entry_idaction_name AND fwde_k2a.type = 4', $joins[0]['joinOn']);
    }

    public function testEntryAndExitUrlsArePageUrlsOnly(): void
    {
        $joins = $this->catalog()->get(1, 'exitPageUrl')->joins($this->context('visit'), 'fwde_k1');

        $this->assertSame('fwde_k1a.idaction = log_visit.visit_exit_idaction_url AND fwde_k1a.type = 1', $joins[0]['joinOn']);
    }

    public function testTimeDimensionsUseLastActionForDateAndFirstActionForHourInVisitScope(): void
    {
        $catalog = $this->catalog();
        $context = $this->context('visit');

        $this->assertSame('DATE(log_visit.visit_last_action_time)', $catalog->get(1, 'date')->keySql($context, 'fwde_k1'));
        $this->assertSame('WEEKDAY(log_visit.visit_last_action_time) + 1', $catalog->get(1, 'weekday')->keySql($context, 'fwde_k1'));
        $this->assertSame('HOUR(log_visit.visit_first_action_time)', $catalog->get(1, 'hour')->keySql($context, 'fwde_k1'));
    }

    public function testTimeDimensionsUseServerTimeInActionScope(): void
    {
        $catalog = $this->catalog();
        $context = $this->context('action');

        $this->assertSame('DATE(log_link_visit_action.server_time)', $catalog->get(1, 'date')->keySql($context, 'fwde_k1'));
        $this->assertSame('HOUR(log_link_visit_action.server_time)', $catalog->get(1, 'hour')->keySql($context, 'fwde_k1'));
    }

    public function testTimeDimensionsConvertToSiteTimezone(): void
    {
        $context = new QueryContext('visit', 'Europe/Berlin', '2026-06-30 22:00:00', '2026-07-31 21:59:59');

        $this->assertSame(
            'DATE(DATE_ADD(log_visit.visit_last_action_time, INTERVAL 7200 SECOND))',
            $this->catalog()->get(1, 'date')->keySql($context, 'fwde_k1')
        );
    }

    public function testUnavailableCoreDimensionIsHidden(): void
    {
        $catalog = $this->catalog(fn (string $coreId) => $coreId === 'DevicesDetection.BrowserName' ? null : 'Core ' . $coreId);

        $this->assertArrayNotHasKey('browser', $catalog->availableFor(1));
        $this->assertNull($catalog->get(1, 'browser'));
        $this->assertNotNull($catalog->get(1, 'deviceType'));
    }

    public function testKeysOfVersionsDevicesReferrerDetailsAndVisitCount(): void
    {
        $catalog = $this->catalog();
        $visit = $this->context('visit');
        $key = fn (string $id): string => $catalog->get(1, $id)->keySql($visit, 'fwde_k1');

        // the labels Matomo's DevicesDetection reports build from "code;version" and "brand;model"
        $this->assertSame(
            "CASE WHEN log_visit.config_browser_name IS NULL OR log_visit.config_browser_name IN ('', 'UNK') THEN NULL"
            . " ELSE CONCAT(log_visit.config_browser_name, ';', COALESCE(log_visit.config_browser_version, '')) END",
            $key('browserVersion')
        );
        $this->assertSame(
            "CASE WHEN log_visit.config_os IS NULL OR log_visit.config_os IN ('', 'UNK') THEN NULL"
            . " ELSE CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, '')) END",
            $key('operatingSystemVersion')
        );
        $this->assertSame("NULLIF(log_visit.config_device_brand, '')", $key('deviceBrand'));
        $this->assertSame(
            "CASE WHEN COALESCE(log_visit.config_device_brand, '') = '' AND COALESCE(log_visit.config_device_model, '') = ''"
            . " THEN NULL ELSE CONCAT(COALESCE(log_visit.config_device_brand, ''), ';', COALESCE(log_visit.config_device_model, '')) END",
            $key('deviceModel')
        );
        $this->assertSame("NULLIF(log_visit.referer_keyword, '')", $key('referrerKeyword'));
        $this->assertSame("NULLIF(log_visit.referer_url, '')", $key('referrerUrl'));
        $this->assertSame('log_visit.visitor_count_visits', $key('visitCount'));
    }

    public function testDeviceModelIsHiddenWhenMatomoTurnsModelDetectionOff(): void
    {
        // Matomo's compliance policy disables its device model report per site; the exploration follows
        $catalog = $this->catalog(null, [], false, fn (int $idSite): bool => $idSite === 1);

        $this->assertArrayNotHasKey('deviceModel', $catalog->availableFor(1));
        $this->assertArrayHasKey('deviceBrand', $catalog->availableFor(1));
        $this->assertArrayHasKey('deviceModel', $catalog->availableFor(2));
    }

    public function testSegmentsOfVersionsDevicesReferrerDetailsAndVisitCount(): void
    {
        require_once PIWIK_INCLUDE_PATH . '/plugins/DevicesDetection/functions.php';
        $catalog = $this->catalog();
        $segment = fn (string $id, string $raw): ?string => $catalog->get(1, $id)->segmentFor($raw);

        $this->assertSame('browserCode==CH;browserVersion==120.0', $segment('browserVersion', 'CH;120.0'));
        $this->assertSame('operatingSystemCode==WIN;operatingSystemVersion==10', $segment('operatingSystemVersion', 'WIN;10'));
        $this->assertSame('operatingSystemCode==LIN;operatingSystemVersion==', $segment('operatingSystemVersion', 'LIN;'));
        // Matomo's deviceBrand segment takes the brand's name
        $this->assertSame('deviceBrand==Apple', $segment('deviceBrand', 'AP'));
        $this->assertNull($segment('deviceBrand', 'no-such-brand'));
        $this->assertSame('deviceBrand==Apple;deviceModel==iPhone%2B15', $segment('deviceModel', 'AP;iPhone 15'));
        // a brand code DeviceDetector no longer knows: Matomo's segment cannot select it ("Unknown" means no brand)
        $this->assertNull($segment('deviceModel', 'no-such-brand;Phone 1'));
        $this->assertSame('referrerKeyword==matomo%2Banalytics', $segment('referrerKeyword', 'matomo analytics'));
        $this->assertSame('referrerUrl==' . urlencode(urlencode('https://a.example/x?y=1')), $segment('referrerUrl', 'https://a.example/x?y=1'));
        $this->assertSame('continentCode==eur', $segment('continent', 'eur'));
        $this->assertSame('visitCount==3', $segment('visitCount', '3'));
    }

    public function testUnknownCodesAreNormalisedToNull(): void
    {
        $catalog = $this->catalog();
        $visit = $this->context('visit');
        $action = $this->context('action');

        $this->assertSame(
            "CASE WHEN log_visit.location_country IN ('xx', '') THEN NULL WHEN log_visit.location_country = 'ti' THEN 'cn'"
            . ' ELSE log_visit.location_country END',
            $catalog->get(1, 'country')->keySql($visit, 'fwde_k1')
        );
        $this->assertSame("NULLIF(NULLIF(log_visit.config_browser_name, ''), 'UNK')", $catalog->get(1, 'browser')->keySql($visit, 'fwde_k1'));
        $this->assertSame("NULLIF(NULLIF(log_visit.config_os, ''), 'UNK')", $catalog->get(1, 'operatingSystem')->keySql($visit, 'fwde_k1'));
        $this->assertStringStartsWith('NULLIF(LOWER(', $catalog->get(1, 'language')->keySql($visit, 'fwde_k1'));
        $this->assertStringEndsWith(", '')", $catalog->get(1, 'language')->keySql($visit, 'fwde_k1'));
        $this->assertSame('TRIM(fwde_k1a.name)', $catalog->get(1, 'pageTitle')->keySql($action, 'fwde_k1'));
        $this->assertSame('TRIM(fwde_k1a.name)', $catalog->get(1, 'entryPageTitle')->keySql($visit, 'fwde_k1'));
        $this->assertSame('TRIM(fwde_k1a.name)', $catalog->get(1, 'exitPageTitle')->keySql($visit, 'fwde_k1'));
    }

    public function testActionKinds(): void
    {
        $catalog = $this->catalog();
        $kinds = fn (string $id) => $catalog->get(1, $id)->actionKinds;

        $this->assertSame(['pageview', 'event'], $kinds('pageUrl'));
        $this->assertSame(['pageview'], $kinds('pageTitle'));
        $this->assertSame(['event'], $kinds('eventCategory'));
        $this->assertSame(['event'], $kinds('eventName'));
        $this->assertSame(['download'], $kinds('downloadUrl'));
        $this->assertSame(['outlink'], $kinds('outlinkUrl'));
        $this->assertSame(['search'], $kinds('siteSearchKeyword'));
        $this->assertSame(['pageview', 'event', 'download', 'outlink', 'search'], $kinds('customDimension4'));
        $this->assertSame([], $kinds('browser'));
        $this->assertSame([], $kinds('customDimension3'));
    }

    public function testSanitizedTextFlags(): void
    {
        $catalog = $this->catalog();
        foreach (['pageTitle', 'entryPageTitle', 'exitPageTitle', 'eventCategory', 'eventAction', 'eventName', 'siteSearchKeyword', 'customDimension3', 'customDimension4'] as $id) {
            $this->assertTrue($catalog->get(1, $id)->storesSanitizedText, $id);
        }
        foreach (['pageUrl', 'downloadUrl', 'outlinkUrl', 'entryPageUrl', 'referrerName', 'browser'] as $id) {
            $this->assertFalse($catalog->get(1, $id)->storesSanitizedText, $id);
        }
    }

    public function testUnknownRegionAndCityAreNull(): void
    {
        $catalog = $this->catalog();
        $visit = $this->context('visit');

        $this->assertStringContainsString("log_visit.location_region = '' THEN NULL ELSE", $catalog->get(1, 'region')->keySql($visit, 'fwde_k1'));
        $this->assertStringContainsString("log_visit.location_city = '' THEN NULL ELSE", $catalog->get(1, 'city')->keySql($visit, 'fwde_k1'));
    }

    public function testCampaignSourceAndMediumNeedMcrAndColumns(): void
    {
        $withoutMcr = fn (string $coreId) => strpos($coreId, 'MarketingCampaignsReporting.') === 0 ? null : 'Core ' . $coreId;
        $this->assertNull($this->catalog($withoutMcr, ['campaign_source', 'campaign_medium'])->get(1, 'campaignSource'));
        $this->assertNull($this->catalog(null, ['campaign_medium'])->get(1, 'campaignSource'));

        $catalog = $this->catalog(null, ['campaign_name', 'campaign_source', 'campaign_medium']);
        $visit = $this->context('visit');
        foreach (['campaignSource' => 'campaign_source', 'campaignMedium' => 'campaign_medium'] as $id => $column) {
            $dimension = $catalog->get(1, $id);
            $this->assertSame('acquisition', $dimension->group, $id);
            $this->assertSame('campaign', $dimension->labelKind, $id);
            $this->assertFalse($dimension->storesSanitizedText, $id);
            $this->assertSame("CASE WHEN log_visit.referer_type = 6 THEN NULLIF(log_visit.$column, '') END", $dimension->keySql($visit, 'fwde_k1'));
        }
        $this->assertSame('FeinwerkDataExploration_DimensionCampaignSource', $catalog->get(1, 'campaignSource')->name);
    }

    public function testCampaignValuesAreLowerCasedWhenMcrSaysSo(): void
    {
        $catalog = $this->catalog(null, ['campaign_name', 'campaign_source', 'campaign_medium'], true);
        $visit = $this->context('visit');

        $this->assertSame("CASE WHEN log_visit.referer_type = 6 THEN NULLIF(LOWER(log_visit.campaign_source), '') END", $catalog->get(1, 'campaignSource')->keySql($visit, 'fwde_k1'));
        $this->assertSame("CASE WHEN log_visit.referer_type = 6 THEN NULLIF(LOWER(log_visit.campaign_name), '') END", $catalog->get(1, 'campaignName')->keySql($visit, 'fwde_k1'));
    }

    public function testCampaignNameReadsMcrColumnWhenActive(): void
    {
        $visit = $this->context('visit');

        $this->assertSame("CASE WHEN log_visit.referer_type = 6 THEN NULLIF(log_visit.campaign_name, '') END",
            $this->catalog(null, ['campaign_name'])->get(1, 'campaignName')->keySql($visit, 'fwde_k1'));
        $this->assertSame('CASE WHEN log_visit.referer_type = 6 THEN log_visit.referer_name END',
            $this->catalog(null, [])->get(1, 'campaignName')->keySql($visit, 'fwde_k1'));
        $this->assertSame('campaign', $this->catalog(null, [])->get(1, 'campaignName')->labelKind);
    }

    public function testChannelGroupNeedsPluginAndColumn(): void
    {
        $withoutFcg = fn (string $coreId) => strpos($coreId, 'FeinwerkChannelGrouping.') === 0 ? null : 'Core ' . $coreId;
        $this->assertNull($this->catalog($withoutFcg, ['channel_grouping'])->get(1, 'channelGroup'));
        $this->assertNull($this->catalog(null, [])->get(1, 'channelGroup'));

        $dimension = $this->catalog(null, ['channel_grouping'])->get(1, 'channelGroup');
        $this->assertSame('acquisition', $dimension->group);
        $this->assertSame('channelGroup', $dimension->labelKind);
        $this->assertFalse($dimension->storesSanitizedText);
        $this->assertSame('Core FeinwerkChannelGrouping.FeinwerkChannelGroupingDimension', $dimension->name);
        $this->assertSame("NULLIF(log_visit.channel_grouping, '')", $dimension->keySql($this->context('visit'), 'fwde_k1'));
    }

    public function testReportLinks(): void
    {
        $catalog = $this->catalog(null, ['campaign_name', 'campaign_source', 'campaign_medium', 'channel_grouping']);
        $expected = [
            'Referrers.getReferrerType' => 'channelType',
            'Referrers.getCampaigns' => 'campaignName',
            'MarketingCampaignsReporting.getName' => 'campaignName',
            'MarketingCampaignsReporting.getSource' => 'campaignSource',
            'MarketingCampaignsReporting.getMedium' => 'campaignMedium',
            'FeinwerkChannelGrouping.getChannels' => 'channelGroup',
            'UserCountry.getCountry' => 'country',
            'UserCountry.getRegion' => 'region',
            'UserCountry.getCity' => 'city',
            'UserLanguage.getLanguage' => 'language',
            'DevicesDetection.getType' => 'deviceType',
            'DevicesDetection.getOsFamilies' => 'operatingSystem',
            'DevicesDetection.getBrowsers' => 'browser',
            'Resolution.getResolution' => 'resolution',
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
            // reports of the dimensions of iteration 2 and further reports of existing dimensions (Peter, 2026-10-20)
            'Referrers.getWebsites' => 'referrerName',
            'Referrers.getSearchEngines' => 'referrerName',
            'Referrers.getSocials' => 'referrerName',
            'Referrers.getAIAssistants' => 'referrerName',
            'Referrers.getKeywords' => 'referrerKeyword',
            'UserCountry.getContinent' => 'continent',
            'VisitorInterest.getNumberOfVisitsByVisitCount' => 'visitCount',
            'DevicesDetection.getBrand' => 'deviceBrand',
            'DevicesDetection.getModel' => 'deviceModel',
            'DevicesDetection.getOsVersions' => 'operatingSystemVersion',
            'DevicesDetection.getBrowserVersions' => 'browserVersion',
        ];
        foreach ($expected as $method => $id) {
            $this->assertSame($id, $catalog->reportLink(1, $method, []), $method);
        }
        $this->assertSame('customDimension3', $catalog->reportLink(1, 'CustomDimensions.getCustomDimension', ['idDimension' => '3']));
    }

    public function testReportsOfOneChannelOpenTheExplorationOnThatChannel(): void
    {
        $this->assertSame('referrerType==website', DimensionCatalog::reportScope('Referrers.getWebsites'));
        $this->assertSame('referrerType==search', DimensionCatalog::reportScope('Referrers.getSearchEngines'));
        $this->assertSame('referrerType==social', DimensionCatalog::reportScope('Referrers.getSocials'));
        $this->assertSame('referrerType==ai', DimensionCatalog::reportScope('Referrers.getAIAssistants'));
        $this->assertSame('referrerType==search', DimensionCatalog::reportScope('Referrers.getKeywords'));
        $this->assertNull(DimensionCatalog::reportScope('DevicesDetection.getBrowsers'));
    }

    public function testNoActionWhenDimensionUnavailable(): void
    {
        $withoutVisitTime = fn (string $coreId) => $coreId === 'VisitTime.LocalTime' ? null : 'Core ' . $coreId;

        $this->assertNull($this->catalog($withoutVisitTime)->reportLink(1, 'VisitTime.getVisitInformationPerServerTime', []));
        $this->assertNull($this->catalog()->reportLink(1, 'CustomDimensions.getCustomDimension', ['idDimension' => '99']));
        $this->assertNull($this->catalog()->reportLink(1, 'CustomDimensions.getCustomDimension', []));
        $this->assertNull($this->catalog()->reportLink(1, 'MarketingCampaignsReporting.getSource', []), 'MCR columns missing');
        $this->assertNull($this->catalog()->reportLink(1, 'Live.getLastVisitsDetails', []));
        $this->assertNull($this->catalog()->reportLink(2, 'CustomDimensions.getCustomDimension', ['idDimension' => '3']), 'other site');
        // reports whose rows do not fit a dimension of the exploration (Referrers.getAll lists search keywords next to
        // websites; the no-result keywords would open every keyword)
        $this->assertNull($this->catalog()->reportLink(1, 'Referrers.getAll', []));
        $this->assertNull($this->catalog()->reportLink(1, 'Actions.getSiteSearchNoResultKeywords', []));
        $modelDetectionOff = $this->catalog(null, [], false, fn (int $idSite): bool => true);
        $this->assertNull($modelDetectionOff->reportLink(1, 'DevicesDetection.getModel', []), 'device model detection off');
    }

    public function testSegmentsOfPluginDimensions(): void
    {
        $catalog = $this->catalog(null, ['campaign_name', 'campaign_source', 'campaign_medium', 'channel_grouping']);

        $this->assertSame('campaignSource==news%2Bletter', $catalog->get(1, 'campaignSource')->segmentFor('news letter'));
        $this->assertSame('campaignMedium==email', $catalog->get(1, 'campaignMedium')->segmentFor('email'));
        $this->assertSame('campaignName==spring', $catalog->get(1, 'campaignName')->segmentFor('spring'));
        $this->assertSame('channelGrouping==Paid%2BSearch', $catalog->get(1, 'channelGroup')->segmentFor('Paid Search'));
        $this->assertSame('dimension3==B2B', $catalog->get(1, 'customDimension3')->segmentFor('B2B'));
        // without MarketingCampaignsReporting the campaign name is core's referrer name of campaign visits
        $this->assertSame('referrerType==campaign;referrerName==spring', $this->catalog()->get(1, 'campaignName')->segmentFor('spring'));
    }

    public function testSegmentValuesAreEncodedTwice(): void
    {
        // Matomo decodes the segment string and then the value, like the segments of its own Actions reports
        $catalog = $this->catalog();

        $this->assertSame('pageUrl==' . urlencode(urlencode('example.com/search?q=a+b')), $catalog->get(1, 'pageUrl')->segmentFor('example.com/search?q=a+b'));
        $this->assertSame('eventName==a%252Bb%252C%253Bc', $catalog->get(1, 'eventName')->segmentFor('a+b,;c'));
    }

    public function testUnknownReferrerTypeHasNoSegment(): void
    {
        require_once PIWIK_INCLUDE_PATH . '/plugins/Referrers/functions.php';
        $channelType = $this->catalog()->get(1, 'channelType');

        $this->assertSame('referrerType==search', $channelType->segmentFor('2'));
        $this->assertNull($channelType->segmentFor('99'), 'a type Matomo does not know stays without a segment');
    }

    public function testNoSegmentForTimeDimensionsAndMissingValues(): void
    {
        $catalog = $this->catalog();

        $this->assertNull($catalog->get(1, 'date')->segmentFor('2026-03-27'));
        $this->assertNull($catalog->get(1, 'hour')->segmentFor('8'));
        $this->assertNull($catalog->get(1, 'browser')->segmentFor(null));
        $this->assertNull($catalog->get(1, 'browser')->segmentFor(''));
        $this->assertSame('browserCode==CH', $catalog->get(1, 'browser')->segmentFor('CH'));
    }

    public function testHourNeedsTheVisitTimePlugin(): void
    {
        // the hour label comes from VisitTime's getTimeLabel(), which does not exist when VisitTime is deactivated
        $catalog = $this->catalog(fn (string $coreId) => str_starts_with($coreId, 'VisitTime.') ? null : 'Core ' . $coreId);

        $this->assertNull($catalog->get(1, 'hour'));
        $this->assertNotNull($catalog->get(1, 'date'));
        $this->assertSame('FeinwerkDataExploration_DimensionHour', $this->catalog()->get(1, 'hour')->name, 'own name, not the core one');
    }

    public function testCustomDimensionColumnsAndNames(): void
    {
        $catalog = $this->catalog();

        $visitScope = $catalog->get(1, 'customDimension3');
        $actionScope = $catalog->get(1, 'customDimension4');

        $this->assertSame('log_visit.custom_dimension_1', $visitScope->keySql($this->context('visit'), 'fwde_k1'));
        $this->assertSame('log_link_visit_action.custom_dimension_2', $actionScope->keySql($this->context('action'), 'fwde_k1'));
        $this->assertSame('Customer type', $visitScope->name);
        $this->assertSame('custom', $actionScope->group);
    }

    public function testNamesComeFromOwnKeyOrCoreDimension(): void
    {
        $catalog = $this->catalog();

        $this->assertSame('FeinwerkDataExploration_DimensionDate', $catalog->get(1, 'date')->name);
        $this->assertSame('Core DevicesDetection.BrowserName', $catalog->get(1, 'browser')->name);
    }

    public function testCustomDimensionsOfAnotherSiteAreUnknown(): void
    {
        $this->assertNull($this->catalog()->get(2, 'customDimension3'));
    }

    private function context(string $scope): QueryContext
    {
        return new QueryContext($scope, 'UTC', '2026-03-01 00:00:00', '2026-03-31 23:59:59');
    }

    /**
     * @param string[] $visitColumns the log_visit columns of other plugins that exist
     */
    private function catalog(
        ?callable $coreName = null,
        array $visitColumns = [],
        bool $lowerCampaigns = false,
        ?callable $modelDetectionOff = null
    ): DimensionCatalog {
        $customDimensions = new class implements CustomDimensionSource {
            public function activeFor(int $idSite): array
            {
                if ($idSite !== 1) {
                    return [];
                }
                return [
                    ['id' => 3, 'index' => 1, 'scope' => 'visit', 'name' => 'Customer type'],
                    ['id' => 4, 'index' => 2, 'scope' => 'action', 'name' => 'Page author'],
                ];
            }
        };

        return new DimensionCatalog(
            $coreName ?? fn (string $coreId) => 'Core ' . $coreId,
            $customDimensions,
            new LabelFormatter(fn (string $key, array $params = []) => $key),
            new ColumnProbe(fn (string $table) => $table === 'log_visit' ? $visitColumns : []),
            fn () => $lowerCampaigns,
            $modelDetectionOff ?? fn (int $idSite): bool => false
        );
    }
}
