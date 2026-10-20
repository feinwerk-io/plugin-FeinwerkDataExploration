# Changelog

## 0.1.1

- Long values such as page URLs wrap in the result table instead of running into the next column.

## 0.1.0

First version for Matomo 5.12 and newer.

- Free-form table on the raw data: one dimension, an optional secondary dimension and up to five metrics, sortable
  by every column, up to 1,000 rows.
- Dimensions for acquisition, visitors, technology, time, visits, behaviour (pages, events, downloads, outlinks,
  site search) and Custom Dimensions; campaign source and medium with MarketingCampaignsReporting, the channel group
  with FeinwerkChannelGrouping.
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
