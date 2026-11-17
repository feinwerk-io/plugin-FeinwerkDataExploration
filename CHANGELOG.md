# Changelog

## 5.1.0

- The table is easier to read: both dimensions side by side on the left and a line before the metrics, long values on
  one line shortened in the middle (the full text on hover), a bar behind every count and sum, and header rows that
  stand apart, in light and dark mode. While comparing, each column shows the colour of its comparison with its
  segment and period on two lines.
- Saved explorations compare logins exactly: two users whose logins differ only in upper and lower case or in accents
  never see each other's private explorations, and deleting one of them keeps the other's explorations.
- The chart has a trend: "Bars | Trend" turns the first 5 rows of the table into lines over the days of the period
  (the hours of a single day); while comparing, one line per comparison column, a compared period dashed and lined up
  day by day. A table with a time dimension in second place (for example "Browser × Date") is now drawn as lines too.
- The free-form table compares segments and periods with Matomo's own comparison: one column per comparison for every
  metric, a total row, shares, and the change against your period; up to 3 columns per metric.
- New page **Explorations → Funnel**: up to three steps (page, event, site search, download, outlink, goal or any goal),
  counted per visit and in order from the raw data of the period, with bars, a table, export, saving and sharing.
- Saved explorations can be copied to other websites (Matomo 5.14 and newer): a goal is matched by its name, and a
  copy is shared only where you may share.
- Below the result, administrators see a one-line tip on another Feinwerk offer, starting with the Feinwerk Plugins
  logo (on narrow screens only its round mark); `promotions = 0` in the `[FeinwerkDataExploration]` section of
  `config/config.ini.php` switches it off.
- The feedback row below the result follows the compact footer of all Feinwerk plugins, with "Support" at its right end.
- API requests that fail answer with an HTTP status: 400 for a request the plugin cannot answer (for example a period
  longer than 31 days), 403 or 404 for a saved exploration you may not change or that does not exist, and 503 while
  other explorations take all places or a query reached the time limit. The plugin's own page shows its messages as
  before.
- A copy of a saved exploration on another website uses that website's custom dimension or goal of the same name in
  its segment too; without one, the website is skipped with a note.
- Explorations with two dimensions, trends and funnels no longer write "Trying to add two strings" warnings to Matomo's
  log for every request.

## 5.0.1

- The feedback question below the result now takes a single compact line.
- A failing connection to Feinwerk while sending a feedback vote no longer shows an error in Matomo.

## 5.0.0

- Below the result you can tell us whether the exploration is helpful (thumbs up or down), and "Support" opens the
  Feinwerk contact form. Your Matomo server sends only the answer, the plugin and Matomo version, the browser family
  and major version and the language of Matomo; `feedback = 0` in the `[FeinwerkDataExploration]` section of `config/config.ini.php`
  switches the question off.
- Version numbers now follow Matomo: version 5.0.0 follows 0.3.1 and is offered as an update as usual.

## 0.3.1

- A result with a single row says "1 row" instead of "1 rows".

## 0.3.0

- Sharing an exploration with the website's users needs at least write access to the website. With view access you
  save explorations for yourself.
- "Open in exploration" also under the reports of websites, search engines, social networks, AI assistants and
  keywords (each opens on the visits of its channel), continents, visits by visit number, device brands and models,
  and operating system and browser versions.

## 0.2.0

- Save explorations and open them again: for yourself only, or for everyone who may use explorations on the website.
  A saved exploration keeps its settings and segment, the period stays the one you have selected.
- Eight more dimensions: keyword, referrer URL, visit number, continent, device brand, device model, operating system
  version and browser version.
- Three more metrics: average event value, site searches and maximum actions in one visit.
- "None" in the choice of the second dimension is no longer written in capitals.
- The settings use one term for the read replica, and the plugin is named "Feinwerk Data Exploration".

## 0.1.1

- Long values such as page URLs wrap in the result table instead of running into the next column.

## 0.1.0

First version for Matomo 5.12 and newer.

- Free-form table on the raw data: one dimension, an optional secondary dimension and up to five metrics, sortable
  by every column, up to 1,000 rows.
- Dimensions for acquisition, visitors, technology, time, visits, behaviour (pages, events, downloads, outlinks,
  site search) and Custom Dimensions; campaign source and medium with Marketing Campaigns Reporting, the channel group
  with Feinwerk Channel Grouping.
- Visit, goal and action metrics.
- Results as a table, a chart or both: bars per row, lines over time for date, hour and weekday.
- Matomo segments and row filters on dimensions and metrics.
- "Open in exploration" under the standard reports, and "Explore this row" on their rows.
- A menu on every result row: filter to this value, break it down by a second dimension, show these visits.
- A period that is too large gets an offer of its most recent days that fit.
- An entry in Matomo's System Check. No explorations on roll-up sites.
- Export as CSV or TSV, shareable links.
- Protection of your Matomo: at most 31 days, a size check before every query, a query time limit, at most two
  explorations at the same time, cached results, read replica support. Settings for super users.
- Light and dark mode, English and German.
