# Changelog

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
