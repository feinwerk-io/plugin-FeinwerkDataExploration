<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionDefinition;

/**
 * Suggestions for a filter's value (Peter, 2026-11-25: "felder wo sinnvoll mit autovervollständigen", the source he chose:
 * Matomo's reports). A dimension with a Matomo report asks that archived report for the page's period, never the raw
 * data: the typed text as Matomo's own report search (filter_pattern, literal here, case-insensitive), the most visited
 * first, at most LIMIT values, as the exploration's table shows them (page URLs without the scheme). The coded
 * dimensions whose every value the plugin knows (weekday, hour, ...) answer from that list. A report that is missing or
 * fails gives no suggestions; the field works as before.
 */
final class FilterSuggestions
{
    public const LIMIT = 15;
    /** the longest text that is searched for */
    public const MAX_TEXT = 100;

    /** the value is the row's label */
    private const LABEL = 'label';
    /** the value is the row's URL as the table shows a page URL (without the scheme and "www.") */
    private const PAGE_URL = 'pageUrl';
    /** the value is the row's URL as Matomo stores it (downloads and outlinks are not normalized) */
    private const URL = 'url';
    /** the value is the table's label of the code in the row's segment (operating systems by name, not by family) */
    private const OS_CODE = 'operatingSystemCode';

    private const PAGE = ['flat' => 1, 'filter_column' => 'url'];

    /**
     * dimension id => Matomo report (or reports, merged by visits), its parameters, and what the value is (LABEL,
     * PAGE_URL, URL, OS_CODE)
     */
    private const REPORTS = [
        // Referrers.getAll lists keywords for search engines: the reports of each kind of referrer instead
        'referrerName' => [
            ['Referrers.getSearchEngines', 'Referrers.getSocials', 'Referrers.getWebsites', 'Referrers.getCampaigns',
                'Referrers.getAIAssistants'], [], self::LABEL,
        ],
        'referrerKeyword' => ['Referrers.getKeywords', [], self::LABEL],
        'campaignName' => ['Referrers.getCampaigns', [], self::LABEL],
        'campaignSource' => ['MarketingCampaignsReporting.getSource', [], self::LABEL],
        'campaignMedium' => ['MarketingCampaignsReporting.getMedium', [], self::LABEL],
        'channelGroup' => ['FeinwerkChannelGrouping.getChannels', [], self::LABEL],
        'continent' => ['UserCountry.getContinent', [], self::LABEL],
        'country' => ['UserCountry.getCountry', [], self::LABEL],
        'region' => ['UserCountry.getRegion', [], self::LABEL],
        'city' => ['UserCountry.getCity', [], self::LABEL],
        'language' => ['UserLanguage.getLanguage', [], self::LABEL],
        'deviceType' => ['DevicesDetection.getType', [], self::LABEL],
        'deviceBrand' => ['DevicesDetection.getBrand', [], self::LABEL],
        'deviceModel' => ['DevicesDetection.getModel', [], self::LABEL],
        'operatingSystem' => ['DevicesDetection.getOsVersions', ['filter_limit' => 500], self::OS_CODE],
        'operatingSystemVersion' => ['DevicesDetection.getOsVersions', [], self::LABEL],
        'browser' => ['DevicesDetection.getBrowsers', [], self::LABEL],
        'browserVersion' => ['DevicesDetection.getBrowserVersions', [], self::LABEL],
        'resolution' => ['Resolution.getResolution', [], self::LABEL],
        'pageUrl' => ['Actions.getPageUrls', self::PAGE, self::PAGE_URL],
        'pageTitle' => ['Actions.getPageTitles', ['flat' => 1], self::LABEL],
        'entryPageUrl' => ['Actions.getEntryPageUrls', self::PAGE + ['filter_sort_column' => 'entry_nb_visits'], self::PAGE_URL],
        'entryPageTitle' => ['Actions.getEntryPageTitles', ['flat' => 1, 'filter_sort_column' => 'entry_nb_visits'], self::LABEL],
        'exitPageUrl' => ['Actions.getExitPageUrls', self::PAGE + ['filter_sort_column' => 'exit_nb_visits'], self::PAGE_URL],
        'exitPageTitle' => ['Actions.getExitPageTitles', ['flat' => 1, 'filter_sort_column' => 'exit_nb_visits'], self::LABEL],
        'downloadUrl' => ['Actions.getDownloads', self::PAGE, self::URL],
        'outlinkUrl' => ['Actions.getOutlinks', self::PAGE, self::URL],
        'siteSearchKeyword' => ['Actions.getSiteSearchKeywords', [], self::LABEL],
        'eventCategory' => ['Events.getCategory', [], self::LABEL],
        'eventAction' => ['Events.getAction', [], self::LABEL],
        'eventName' => ['Events.getName', [], self::LABEL],
    ];

    /** @var callable(string $method, array<string, mixed> $params): mixed */
    private $report;

    /**
     * @param callable|null $report fn(string $method, array $params): DataTable, Matomo's API by default (tests pass
     *                              another)
     */
    public function __construct(?callable $report = null)
    {
        $this->report = $report ?? static fn (string $method, array $params) => Request::processRequest($method, $params, []);
    }

    /**
     * @return list<string> at most LIMIT values (a fixed list all of its values), as the exploration's table shows
     *                      them
     */
    public function suggest(DimensionDefinition $dimension, int $idSite, string $period, string $date, string $text): array
    {
        $text = mb_substr(trim($text), 0, self::MAX_TEXT);
        if (isset(LabelFilterResolver::STATIC_DOMAINS[$dimension->id])) {
            return $this->fixed($dimension, $idSite, $text);
        }
        $source = self::reportOf($dimension->id);
        if ($source === null) {
            return [];
        }
        [$methods, $params, $mode] = $source;
        $params += [
            'idSite' => $idSite,
            'period' => $period,
            'date' => $date,
            'segment' => '',
            'format' => 'original',
            'filter_sort_column' => 'nb_visits',
            'filter_sort_order' => 'desc',
            // a few more than shown: empty and duplicate values drop out
            'filter_limit' => self::LIMIT * 2,
        ];
        if ($text !== '' && $mode !== self::OS_CODE) {
            // Matomo's report search takes a regular expression and quotes its delimiter itself: the text is literal
            $params['filter_pattern'] = preg_quote($text);
        }
        $rows = [];
        foreach ((array) $methods as $method) {
            try {
                $table = ($this->report)($method, $params);
            } catch (\Throwable $e) {
                // a plugin that is not active, a report that fails: the others still suggest
                continue;
            }
            if (!$table instanceof DataTable) {
                continue;
            }
            foreach ($table->getRows() as $row) {
                $visits = $row->getColumn('nb_visits');
                // Matomo lists some values without visits (every device type): only values the data has
                if ($row->getColumn('label') === DataTable::LABEL_SUMMARY_ROW || ($visits !== false && (float) $visits <= 0)) {
                    continue;
                }
                $value = $this->valueOf($row, $mode, $dimension, $idSite);
                if ($value === '' || $this->isEmptyLabel($value, $dimension, $idSite)) {
                    continue;
                }
                if ($mode === self::OS_CODE && $text !== '' && mb_stripos($value, $text) === false) {
                    continue;
                }
                $rows[] = [$value, (float) $visits];
            }
        }
        // one value per label (an operating system has a row per version), the most visited first, the reports'
        // own order among equals
        $visitsOf = [];
        foreach ($rows as [$value, $visits]) {
            $visitsOf[$value] = ($visitsOf[$value] ?? 0) + $visits;
        }
        $values = array_keys($visitsOf);
        $order = array_flip($values);
        usort($values, static fn ($a, $b): int => [$visitsOf[$b], $order[$a]] <=> [$visitsOf[$a], $order[$b]]);
        return array_slice(array_map('strval', $values), 0, self::LIMIT);
    }

    /** A report row's value as the exploration's table shows it, '' when there is none. */
    private function valueOf(DataTable\Row $row, string $mode, DimensionDefinition $dimension, int $idSite): string
    {
        switch ($mode) {
            case self::PAGE_URL:
                return self::urlLabel((string) $row->getMetadata('url'));
            case self::URL:
                return trim((string) $row->getMetadata('url'));
            case self::OS_CODE:
                $segment = (string) $row->getMetadata('segment');
                return preg_match('/(?:^|;)' . self::OS_CODE . '==([^;]+)/', $segment, $m) === 1
                    ? $dimension->formatLabel(urldecode($m[1]), $idSite)
                    : '';
            default:
                return trim((string) $row->getColumn('label'));
        }
    }

    /**
     * Whether a report's label is what a table shows for no value ("... not defined", "Unknown"): no value to type,
     * "is empty" is the condition for it.
     */
    private function isEmptyLabel(string $value, DimensionDefinition $dimension, int $idSite): bool
    {
        if ($value === $dimension->formatLabel(null, $idSite) || $value === Piwik::translate('General_Unknown')) {
            return true;
        }
        // Matomo's own "%s not defined", whatever the name in it, also in English: some reports are archived in the
        // language of the archiving run, CustomDimensions always writes English
        foreach ([null, 'en'] as $language) {
            [$before, $after] = array_pad(explode("\x01", Piwik::translate('General_NotDefined', ["\x01"], $language), 2), 2, '');
            if ($after !== '' && str_starts_with($value, $before) && str_ends_with($value, $after)) {
                return true;
            }
        }
        return false;
    }

    /**
     * The report a dimension's values come from, or null.
     *
     * @return array{0: string|list<string>, 1: array<string, mixed>, 2: string}|null
     */
    public static function reportOf(string $dimensionId): ?array
    {
        if (preg_match('/^customDimension(\d+)$/', $dimensionId, $m) === 1) {
            return ['CustomDimensions.getCustomDimension', ['idDimension' => (int) $m[1]], self::LABEL];
        }
        return self::REPORTS[$dimensionId] ?? null;
    }

    /** A page URL as the exploration's table shows it: without the scheme and "www.", as Matomo stores it. */
    public static function urlLabel(string $url): string
    {
        return (string) preg_replace('~^[a-z][a-z0-9+.-]*://(www\.)?~i', '', trim($url));
    }

    /** @return list<string> */
    private function fixed(DimensionDefinition $dimension, int $idSite, string $text): array
    {
        $values = [];
        foreach (LabelFilterResolver::STATIC_DOMAINS[$dimension->id] as $raw) {
            $label = $dimension->formatLabel((string) $raw, $idSite);
            if ($text === '' || mb_stripos($label, $text) !== false) {
                $values[] = $label;
            }
        }
        // every value of a fixed list (24 hours)
        return $values;
    }
}
