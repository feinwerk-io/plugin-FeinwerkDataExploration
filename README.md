# Feinwerk Data Exploration

Explore your raw data on the spot: a free-form table with a secondary dimension, without creating a report and
without waiting for archiving.

## Description

**Ask a question, get the table: any dimension, a second dimension and up to five metrics, straight from your raw data,
without setting up a report.**

Matomo's reports are archived: they are fast, but you only get the combinations someone has set up in advance.
Questions like "which browsers do visitors from our newsletter use?" or "which events happen on which page?" need a
secondary dimension or a quick cross table, often just once.

Feinwerk Data Exploration answers them directly from the raw data of your website:

- **Free-form table:** choose a dimension, an optional secondary dimension and up to five metrics. The table appears
  at once, one row per combination, sortable by every column.
- **Many dimensions:** acquisition, location, devices and software, time, pages, events, downloads, site search,
  visitors and your Custom Dimensions.
- **Visit, goal and action metrics:** visits, unique visitors, actions, bounce rate, average visit duration, actions
  per visit, maximum actions in one visit, conversions (all goals or one goal), conversion rate, revenue, pageviews,
  unique pageviews, events, sum and average of event values, downloads, outlinks and site searches.
- **Funnel:** describe two or three steps (a page URL or title, an event, a site search, a download, an outlink, a
  goal or any goal) and see how many visits reached each step in this order, with the share of step 1, the share of
  the previous step and the drop-offs, as bars and as a table. No setup in advance: the funnel is counted from the raw
  data of the period, for every period and segment.
- **Compare segments:** click the compare icon next to a saved segment, and the table shows one column per segment
  for every metric, with a total row and each count's share of its column; the bar chart shows one bar per segment.
- **Compare periods:** choose "Compare to" in the date picker, for example this month against the last one: one
  column per period for every metric with the change in percent, and with "Date" as dimension day by day. Segments
  and periods together: up to 4 columns per metric, so one segment and one period fit side by side.
- **Trend:** switch the chart from "Bars" to "Trend" and the first 5 rows of the table become lines over the days of
  the period (over the hours for a single day). While comparing, the trend shows one line per comparison column, a
  compared period dashed and lined up day by day.
- **Save and share:** save an exploration with its segment, for yourself or (with write access) for everyone who may
  use explorations on the website, open it again from the list at the top of the page, and copy it to your other
  websites (Matomo 5.14 or newer).
- **Filters:** every Matomo segment, plus simple row filters: a dimension contains, equals or does not contain a
  text; a metric is greater or less than a number.
- **Open from your reports:** "Open in exploration" under the standard reports (channels, referrers, keywords,
  continents, countries, devices, browsers and their versions, page URLs, events, campaigns and more) opens the exploration with that report's dimension, period and segment, and "Explore
  this row" on a report row opens it on that row's visits, ready to break them down by a second dimension.
- **A menu on every row:** filter to the row's value, break it down by a second dimension, or show the visits behind
  it in Matomo's Visits Log.
- **Chart, table or both:** bars for every dimension, lines over time for date, hour and weekday (one line per value
  of the secondary dimension); the chart shows one of the chosen metrics. In the table, counts and sums carry a bar.
- **Export and share:** export as CSV or TSV, or copy the link: the exploration is part of the URL.
- **Safe for your Matomo:** limits per exploration, a size check before every query and your read replica if you
  have one; details in the FAQ.
- **Light and dark mode, English and German.**

**Get started:** open *Explorations › Free-form table* in the reporting menu, or click *Open in exploration* under a
report.

With Feinwerk Channel Grouping, your own channel groups become a dimension.

**More Plugins for Matomo by Feinwerk:** install [Feinwerk Connector](https://plugins.matomo.org/FeinwerkMarketplaceConnector)
from the Matomo Marketplace to discover, install and update all Feinwerk plugins, free and paid, right in your Matomo.
Documentation: [plugins.feinwerk.io](https://plugins.feinwerk.io/en/plugins/FeinwerkDataExploration).

## How it works

An exploration reads Matomo's raw visit and action data for the chosen period and site; nothing is archived and
nothing is saved as a report (results are only cached for up to an hour). A saved exploration keeps its settings, never
its results, in a table of its own. The plugin never changes Matomo core or
other plugins, so it survives Matomo updates.

When a visit dimension stands next to an action dimension (for example browser next to page URL), visit metrics
count the visits with that action ("visits with this page"). The column titles say so, because these numbers must
not be added up across rows.

## Works with

- **Marketing Campaigns Reporting:** campaign source and medium become dimensions, and the campaign name comes from
  its campaign data, lower-cased when its setting says so, like its reports.
- **[Feinwerk Channel Grouping](https://plugins.feinwerk.io/en/plugins/FeinwerkChannelGrouping):** the channel group of
  each visit becomes a dimension.
- **Custom Dimensions:** every active Custom Dimension of the site is available.

All of this is optional: dimensions of plugins that are not active do not appear. Roll-up sites (Roll-Up Reporting)
have no raw data of their own, so explorations are not offered there.

## Requirements

- Matomo 5.12 or newer.
- PHP 8.1 or newer.

## Installation

1. Copy this folder to `plugins/FeinwerkDataExploration` in your Matomo installation.
2. Activate it under **Administration → Plugins** (`FeinwerkDataExploration`), or on the command line:
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
6. Click **Save** at the top of the result to keep the exploration: for yourself only, or, with write access to the
   website, for everyone who may use explorations on it. Saved explorations open from the list at the top of the left
   column.

**Comparing:** on **Explorations → Free-form table**, compare a saved segment with the compare icon in the segment
list, or a period with "Compare to" in the date picker. The table then shows, for each metric, one column for your own
view and one per comparison (up to 4 columns per metric), a total row at the top, the share of each count in its column,
a bar behind each count (against the largest value of that metric in all its columns), and in period columns the change of your period against that period (for example "+12 %" in the September
column when October has 12 % more visits). With "Date" as dimension, days line up by position: the first day of your
period next to the first day of the compared one. Rows come from your own view; a comparison column shows the values
of exactly these rows. The bar chart shows one bar per comparison column for each of the 10 largest rows of your view
(by the chart's metric), in the colours of Matomo's comparison; the trend shows one line per comparison column (the
total of that column per day). Exports contain every column.

**Trend:** in the chart, switch from **Bars** to **Trend**. The first 5 rows of the table, in its sort and with its
filters, become lines over the days of the period, or over the hours when the period is a single day, counted in the
website's timezone as the dimensions "Date" and "Hour" count them. A table with a single row shows its trend right
away. A table with "Date", "Hour" or "Weekday" as a dimension already has a time axis and shows lines over it. The
choice is part of the link and of saved explorations.

**Funnel:** open **Explorations → Funnel**. Each step is a condition (page URL or title contains, is or starts with
a text; event category, action or name, site search keyword, download or outlink URL contains or is a text; a goal of
the website; any goal), up to three steps. A visit counts on a step when it reached all steps up to it in this order;
other actions in between are allowed, one action counts for one step only, and a goal step may be reached by the
conversion of the previous step's page (for example the thank-you page). Every visit starts with step 1. Texts are
compared without case, and page URLs may be typed with or without "https://" and "www.". Funnels are saved, shared,
exported and linked like free-form tables.

**Settings** (super users, **Administration → System → General settings → Feinwerk Data Exploration**): the longest
period (up to 31 days), the row limit, the query time limit, the largest number of visits per exploration, whether to
use the read replica, and the role that may use explorations (default: view access).

**Feedback and support:** below the result you can tell us whether the exploration is helpful (thumbs up or down).
Your Matomo server sends the answer to Feinwerk together with the plugin and Matomo version, the browser family and
version and the language of Matomo, nothing about you, your website or your visitors. To switch it off, add to
`config/config.ini.php`:

```ini
[FeinwerkDataExploration]
feedback = 0
```

With `enable_internet_features = 0` in Matomo's `[General]` section nothing is sent either, and the question is not
shown. "Support" opens the Feinwerk contact form in a new tab.

**Tips from Feinwerk:** below the result, users with admin access to at least one website and super users see one
short line with a tip on another Feinwerk offer, for example Feinwerk Campaigns or Matomo hosting. It appears only in
Matomo's interface, never in exports, API results, email reports or embedded widgets, and never for a Feinwerk plugin
you already have installed. "×" hides a tip for 30 days in your browser. The tips are part of the plugin: nothing is
loaded from outside and nothing is sent. Links to feinwerk.io open in a new tab and carry only the campaign
parameters `mtm_campaign`, `mtm_source` and `mtm_content`; the tip on the Feinwerk Marketplace Connector opens it in
your Matomo Marketplace (super users), on plugins.matomo.org (other administrators) or as a download where Matomo's
Marketplace is switched off. To switch the tips off for this plugin, add to
`config/config.ini.php`:

```ini
[FeinwerkDataExploration]
promotions = 0
```

`promotions = 0` in the `[FeinwerkMarketplaceConnector]` section switches them off for all Feinwerk plugins.

## Privacy features

- Explorations read the raw data Matomo already stores for the chosen website and period, with your Matomo segment and
  your Matomo access rights. Nothing is tracked in addition, and visitor data never leaves your Matomo.
- Results, visit counts and filter values are kept in Matomo's cache for a short time (one hour for past periods, five
  minutes when today is included). Saved explorations (name, settings, segment) are stored in the table
  `fw_dataexploration_saved` of your Matomo database and are deleted with their user or website and on uninstall.
- If Matomo deletes old raw data (Administration, Privacy, Anonymize data), explorations cover only the days that are
  left.
- The feedback question sends only what is described under "Feedback and support", and only when you click a thumb.
  The tips from Feinwerk are part of the plugin: nothing is loaded from outside and nothing is sent.

## Known limitations

- The sum and the average of event values can be too high when a segment combines a goal condition and an action
  condition with OR (for example "converted goal 1 OR event category is Video").
- Copying the link needs a browser that allows access to the clipboard (Matomo over HTTPS).
- A text filter you type matches the value as Matomo stores it: page URLs keep characters like "ü" percent-encoded
  ("%C3%BC"), and page titles keep "&" as "&amp;". "Filter to this value" in the row menu always matches.

## License

GPL v3 or later. See the `LICENSE` file.

## Author

Peter Boehlke, [Feinwerk.io](https://feinwerk.io) · support@feinwerk.io
