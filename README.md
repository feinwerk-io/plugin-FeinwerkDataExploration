# FeinwerkDataExploration

Explore your raw data on the spot: a free-form table with a secondary dimension, without creating a report and
without waiting for archiving.

## Description

Matomo's reports are archived: they are fast, but you only get the combinations someone has set up in advance.
Questions like "which browsers do visitors from our newsletter use?" or "which events happen on which page?" need a
secondary dimension or a quick cross table, often just once.

FeinwerkDataExploration answers them directly from the raw data of your website:

- **Free-form table:** choose a dimension, an optional secondary dimension and up to five metrics. The table appears
  at once, one row per combination, sortable by every column.
- **Many dimensions:** channel type, referrer, campaign name, source and medium, country, region, city, language,
  device type, operating system, browser, screen resolution, date, weekday, hour, entry and exit pages, page URLs and
  titles, event category, action and name, downloads, outlinks, site search keywords, visitor type, "visit converted"
  and your Custom Dimensions (visit and action scope).
- **Visit, goal and action metrics:** visits, unique visitors, actions, bounce rate, average visit duration, actions
  per visit, conversions (all goals or one goal), conversion rate, revenue, pageviews, unique pageviews, events, sum
  of event values, downloads and outlinks.
- **Filters:** every Matomo segment, plus simple row filters: a dimension contains, equals or does not contain a
  text; a metric is greater or less than a number.
- **Open from your reports:** "Open in exploration" under the standard reports (browsers, countries, page URLs,
  events, campaigns and more) opens the exploration with that report's dimension, period and segment, and "Explore
  this row" on a report row opens it on that row's visits, ready to break them down by a second dimension.
- **A menu on every row:** filter to the row's value, break it down by a second dimension, or show the visits behind
  it in Matomo's Visits Log.
- **Chart, table or both:** bars for every dimension, lines over time for date, hour and weekday (one line per value
  of the secondary dimension); the chart shows one of the chosen metrics.
- **Export and share:** export as CSV or TSV, or copy the link: the exploration is part of the URL.
- **Safe for your Matomo:** at most 31 days per exploration and 1,000 rows, a size check before every query (a
  period that is too large gets an offer of its most recent days that fit), a query time limit, at most two
  explorations at the same time, results cached, and queries on the read replica if your Matomo has one. Super users
  can adjust these limits (31 days and 1,000 rows are the maximum), and Matomo's System Check shows how explorations
  run on your server.
- **Light and dark mode, English and German.**

Documentation and more Plugins for Matomo: [Feinwerk Plugins](https://plugins.feinwerk.io/en/plugins/FeinwerkDataExploration).

## How it works

An exploration reads Matomo's raw visit and action data for the chosen period and site; nothing is archived and
nothing is saved as a report (results are only cached for up to an hour). The plugin never changes Matomo core or
other plugins, so it survives Matomo updates.

When a visit dimension stands next to an action dimension (for example browser next to page URL), visit metrics
count the visits with that action ("visits with this page"). The column titles say so, because these numbers must
not be added up across rows.

## Works with

- **MarketingCampaignsReporting:** campaign source and medium become dimensions, and the campaign name comes from
  its campaign data, lower-cased when its setting says so, like its reports.
- **[FeinwerkChannelGrouping](https://plugins.feinwerk.io/en/plugins/FeinwerkChannelGrouping):** the channel group of
  each visit becomes a dimension.
- **Custom Dimensions:** every active Custom Dimension of the site is available.

All of this is optional: dimensions of plugins that are not active do not appear. Roll-up sites (RollUpReporting)
have no raw data of their own, so explorations are not offered there.

## Requirements

- Matomo 5.12 or newer.
- PHP 8.1 or newer.

## Installation

1. Copy this folder to `plugins/FeinwerkDataExploration` in your Matomo installation.
2. Activate it: **Administration → Plugins → FeinwerkDataExploration → Activate**, or on the command line:
   ```
   ./console plugin:activate FeinwerkDataExploration
   ```

## Usage

1. Open **Explorations → Free-form table** in the reporting menu, or click the flask icon **Open in exploration** under
   a standard report. On a report row, the flask icon **Explore this row** opens the visits of that row.
2. Choose the dimensions and metrics on the left; the table updates on every change.
3. Use the period selector and the segment editor of Matomo as usual (at most 31 days).
4. Add row filters, sort by a column, choose the number of rows, and switch between table, chart or both. The menu
   at the end of each row filters to it, breaks it down or shows its visits.
5. Export the table as CSV or TSV, or copy the link to share the exploration with colleagues who can see the site.

**Settings** (super users, **Administration → System → General settings → FeinwerkDataExploration**): the longest
period (up to 31 days), the row limit, the query time limit, the largest number of visits per exploration, whether to
use the read replica, and the role that may use explorations (default: view access).

## Known limitations

- The sum of event values can be too high when a segment combines a goal condition and an action condition with OR
  (for example "converted goal 1 OR event category is Video").
- Copying the link needs a browser that allows access to the clipboard (Matomo over HTTPS).
- A text filter you type matches the value as Matomo stores it: page URLs keep characters like "ü" percent-encoded
  ("%C3%BC"), and page titles keep "&" as "&amp;". "Filter to this value" in the row menu always matches.

## License

GPL v3 or later. See the `LICENSE` file.

## Author

Peter Boehlke, [Feinwerk.io](https://feinwerk.io) · support@feinwerk.io
