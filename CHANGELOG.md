# Changelog

## 6.3.0

- "Manage explorations" (before: "Manage shared explorations") lists your own explorations too, private ones
  included, each with Delete and its menu place, for every user who has saved one; administrators still see the
  explorations others shared.
- Saved explorations with two or more dimensions as Matomo reports: in the flat view each dimension column carries its
  dimension's name (before, the titles were empty or alike).
- Saved explorations are archived by Matomo like its own reports: their reports, dashboard widgets and scheduled emails
  cover any period, also weeks, months, years and date ranges. A new or changed exploration's last 6 months are built
  in the background; while days are missing, periods of up to 31 days are answered live and longer ones show how many
  of their days the report holds. Settings in `config.ini.php`, section `[FeinwerkDataExploration]` (see the README).
- The exploration page links to an exploration's report for periods longer than 31 days, and the save panel says when a
  change cannot keep a report's history (with "Save as new" as the default then).
- "Open in exploration" under a saved report for a period longer than 31 days opens the last 31 days of that period.

## 6.2.0

- Filters: new conditions "is empty" and "is not empty" for every dimension, for example to keep only rows with a
  campaign name. Empty is what the table shows as "not defined" or "Unknown". For the API: the operators empty and
  notEmpty, without a value.
- On a row without a value ("not defined" or "Unknown"), "Filter to this value" now selects it with "is empty".
- The filter panel uses its whole width.
- Filters suggest values while you type: from Matomo's reports of the period, the most visited first; weekday, hour,
  visitor type and channel type list all their values at once. For the API: getFilterSuggestions.
- KPI cards on the dashboard: the widget's header names the card, the card shows its metric, the change as a tinted
  label, the compared days and the last value as dots on the line, dates in words in the tooltips, and it lines up with
  Matomo's widgets.
- Saved explorations on the dashboard and in the menu show their name once, and their footer without the support link.
- Fixed: a saved exploration with a rate (such as the conversion rate) next to revenue shows its table on its menu
  page and as a dashboard widget; before, Matomo showed an error message there instead.
- Tips from Feinwerk: without Feinwerk Campaign Manager, the campaigns tip below the result offers this free plugin;
  with it installed, the tip opens its campaign overview; once it is connected, or switched off, that tip no longer
  shows.
- Tips from Feinwerk: on a narrow screen the tip on the Feinwerk Connector shows a short link text, so the line stays
  within the screen; the full text appears when you point at the link.

## 6.1.0

- KPI cards: save an exploration as a KPI card and put one number on your dashboard. Choose its metric, what it is
  compared with (the average of the same weekday in the last four weeks, the same weekday a week before, the day, week
  or month before, or nothing), a line of the last 7 or 30 days and the change in percent or as a number. Today counts
  up to the current time, also on the days it is compared with. In e-mails the card is a report with its number.
- A KPI card names the metric it counts unless its name already does. When saving, a new card takes the metric's
  name until you type a name of your own.
- Embedded with Widgetize, KPI cards and saved explorations show no feedback question and no link into Matomo.
- For the API: getKpiCard returns a card's number, comparison and line; the card's settings are part of the saved
  state (fwdeKind kpi, fwdeKpiMetric, fwdeKpiCompare, fwdeKpiLine, fwdeKpiChange).
- Up to three dimensions: a third dimension next to the first two, also in filters, sorting, comparisons, the trend,
  exports, links, saved explorations and reports. Break down offers the next dimension until three are chosen.
- Columns (pivot): the top 3, 5 or 10 values of one more dimension become column groups, each with every metric,
  followed by "Others" (where the rest adds up) and "Total". Weekday shows its seven days. The columns pause while
  segments or periods are compared. The chart shows one bar per value, or one line per value over a single date, hour
  or weekday dimension.
- Saved explorations with three dimensions or with columns (pivot) are reports with up to four levels: each row opens
  into the next dimension, the pivot's values last.
- For the API: getExploration takes up to three dimensions and a pivot ({"dimension": id, "limit": 3, 5 or 10}); rows
  then carry tertiary_label and the pivot columns {metric}__p{n} and {metric}__p_others, and a total row lists the
  pivot's values.
- Saved explorations are Matomo reports: as dashboard widgets, in Matomo's scheduled email reports and in Feinwerk
  Email Reports Pro, and in the API. A private exploration is a report for you only, a shared one for everyone who
  may use explorations on the website.
- Show in menu: when saving, choose where an exploration gets its own menu entry (Visitors, Behaviour, Acquisition,
  Ecommerce, Goals or Explorations). Administrators can change the place of shared explorations. Explorations saved
  before are not in the menu until a place is chosen.
- Matomo's menu shows a new, moved or deleted exploration at once. Deleting one says that it also disappears from the
  menu, dashboards and email reports. KPI cards have no menu place.
- A report with two dimensions shows the first one and opens each row into the second; under each report a link opens
  it in the exploration.
- For the API: getSavedReport returns a saved exploration's table; setSavedExplorationMenu changes its menu place.
- Up to 10 metrics per exploration.
- Each goal has its own metrics: conversions, conversion rate and revenue, side by side for as many goals as you
  choose. The goal selector is gone: links and saved explorations with a goal chosen open with that goal's metrics.
- On ecommerce websites: orders, order revenue, products sold, average order value, ecommerce conversion rate,
  abandoned carts and their value.
- New visit metrics: visits with a conversion, revenue per visit and total time on website.
- The metric list shows each goal under its own heading and finds a goal's metrics by the goal's name.
- Copying a saved exploration to another website takes each goal's metrics to the goal of the same name; ecommerce
  metrics need a website with ecommerce.
- For the API: getAvailableMetrics returns each metric's unit, aggregation, group and goal. idGoal now narrows only
  the all-goals metrics (conversions, conversion rate, revenue).
- Filters in groups: the conditions of a group match all or any of them, and the rows match all or any of the groups,
  for example (A and B) or C. Up to 10 conditions in up to 5 groups. Links and saved explorations keep their filters.
- New filter conditions: "does not start with", "does not end with", "matches regular expression", "at least" and
  "at most".
- For the API: the filters as filterGroups, documented in full; a filter error names the field and the allowed values.
- Charts over days, hours or weekdays no longer break off where the table stops at its row limit: the chart takes every
  row of the period, the table keeps its limit. Where a line has no value (a number filter, or a rate or an average
  without visits), a short note says so.
- Wide tables show a shadow on the side where more columns follow, so a number at the edge never looks complete. On
  phones long values take less room, so the first metric shows next to two short dimensions.
- Today's trend by the hour now ends at the website's current hour, also in timezones other than UTC.
- The "Others" row keeps both dimensions side by side.
- With two dimensions and few metrics, the second dimension sits right next to the first one again, also when the first
  one has long values such as page URLs.
- Filters: "Apply" no longer drops a filter that has no value yet. The panel stays open and says what is missing.
- On phones, a filter's condition and value each take a full row, so the value field keeps its width in every
  language.
- "Copy link": where the browser does not allow copying, the link shows in a field to copy by hand.
- Feinwerk recommendation below the result: "×" now hides the banner for 30 days. After that the next paid plugin
  shows; the one you closed stays away for 90 days.
- On Matomo 6, the recommendation of Feinwerk plugins now includes every paid plugin, since all of them have a release
  for Matomo 6.
- Feinwerk tips: on a narrow screen the tip line shows its sentence only when it fits whole, otherwise only the logo,
  the link and "×". The button that hides a tip is larger, and the buttons that hide a tip or a recommendation keep
  the keyboard focus on the page. Where the Feinwerk Connector installer is offered, the tip on the connector leads
  super users there.
- Feinwerk Connector installer: the button shows that Matomo is working, takes no second click and fits on phones. An
  older connector that does not fit this Matomo is updated instead of activated. If Matomo asks for your password
  first, the page asks for one more click instead of showing an error. The "Feinwerk Plugins" menu shows its icon.

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
