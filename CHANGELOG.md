# Changelog

## 6.0.0

The first stable version for Matomo 6. Compared with the last beta before the new version numbers (1.0.0-beta.5):

- Version numbers follow Matomo: 6.x for Matomo 6, 5.x for Matomo 5.
- Comparisons: the free-form table compares segments and periods with Matomo's own comparison, one column per comparison
  for every metric (up to 4 per metric, so one segment and one period fit together), with a total row, shares and the
  change against your period. The bar chart shows one bar per comparison column in the colours of Matomo's comparison.
- Trend: "Bars | Trend" turns the first 5 rows of the table into lines over the days of the period (the hours of a
  single day); while comparing, one line per comparison column, a compared period dashed and lined up day by day.
- Donut: a third chart type for counts and sums shows the largest rows and "Others" as shares of the whole, with the
  total and the metric in the middle.
- "Others": below a table whose rows were cut, "Others" calculates the exact rest of every metric on request, rates
  included. Where one visit can count in several rows, for example visits per page URL, a hint below the table says
  why the rows can add up to more than the total.
- Filters: new row filters "is not", "starts with" and "ends with", and a switch whether a row must meet all filters or
  any of them. Filters open in a panel at the side and run once with "Apply"; the left column shows the applied filters
  in short. A result that the filters emptied says so and offers "Edit filters".
- The table is easier to read: both dimensions side by side on the left and a line before the metrics, long values on
  one line shortened in the middle (the full text on hover), a bar behind every count and sum, and header rows that
  stand apart, in light and dark mode. The period and the segment the numbers cover show below the result's title.
- Saving and sharing: saving and managing shared explorations open in the panel at the side. Administrators of a
  website manage its shared explorations and can delete them; private explorations stay private. Saved explorations
  can be copied to other websites (a goal, custom dimension or segment is matched by name), and logins are compared
  exactly, so users whose logins differ only in case or accents never see each other's private explorations.
- The switches for the view, the chart type and "Match all / any" look and work alike: one Tab stop, the arrow keys
  choose. The lists of dimensions and metrics work with the keyboard (arrow keys, Enter, Escape), and the dimensions
  show how many of two are chosen.
- Below the result you can tell us whether the exploration is helpful (thumbs up or down), and "Support" opens the
  Feinwerk contact form. Your Matomo server sends only the answer, the plugin and Matomo version, the browser family
  and major version and the language of Matomo; `feedback = 0` in the `[FeinwerkDataExploration]` section of
  `config/config.ini.php` switches the question off.
- Below the result, administrators see a one-line tip on another Feinwerk offer, or a borderless banner with one paid
  Feinwerk plugin this Matomo does not have yet (a different one first each day; "×" hides it for 30 days).
  `promotions = 0` switches both off. The tip on the Feinwerk Connector does not show where Matomo's Marketplace can
  only offer a download.
- While the free Feinwerk Connector is not active, super users find "Feinwerk Plugins" > "Install Feinwerk Connector"
  in Matomo's Administration. One click installs it from the Matomo Marketplace and activates it, after Matomo's
  password check; where Matomo cannot install from its Marketplace, the page explains the manual way. Hidden with
  `connector_installer = 0`.
- The plugin now also speaks French, Spanish and Italian, besides English and German.
- API requests that fail answer with an HTTP status: 400 for a request the plugin cannot answer, 403 or 404 for a saved
  exploration you may not change or that does not exist, and 503 while other explorations take all places or a query
  reached the time limit. A period, date or segment with spaces around it is accepted.
- The funnel page of the 6.0.0 betas is no longer included; saved funnels are kept.
- Explorations no longer write "Trying to add two strings" warnings to Matomo's log.

## 1.0.0-beta.5

- A result with a single row says "1 row" instead of "1 rows".

## 1.0.0-beta.4

- Sharing an exploration with the website's users needs at least write access to the website. With view access you
  save explorations for yourself.
- "Open in exploration" also under the reports of websites, search engines, social networks, AI assistants and
  keywords (each opens on the visits of its channel), continents, visits by visit number, device brands and models,
  and operating system and browser versions.

## 1.0.0-beta.3

- Save explorations and open them again: for yourself only, or for everyone who may use explorations on the website.
  A saved exploration keeps its settings and segment, the period stays the one you have selected.
- Eight more dimensions: keyword, referrer URL, visit number, continent, device brand, device model, operating system
  version and browser version.
- Three more metrics: average event value, site searches and maximum actions in one visit.
- "None" in the choice of the second dimension is no longer written in capitals.
- The settings use one term for the read replica, and the plugin is named "Feinwerk Data Exploration".

## 1.0.0-beta.2

- Long values such as page URLs wrap in the result table instead of running into the next column.

## 1.0.0-beta.1

First version for Matomo 6.

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
