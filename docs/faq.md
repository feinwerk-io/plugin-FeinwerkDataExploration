__How is this different from archived reports?__
Archived reports last, keep a long history and serve dashboard widgets and scheduled emails. Feinwerk Data
Exploration is for questions you ask once or now: no setup, no waiting for archiving, at most 31 days, nothing is
saved as a report.

__How do I compare goals?__
Each goal of your website has its own conversions, conversion rate and revenue in the metric list, under the goal's
name (the search finds them by it). Choose them for two or more goals to see them side by side. "Conversions (all
goals)" and the other all-goals metrics count every goal and ecommerce order together. Goal metrics count the
conversions of the visits behind each row: next to a page URL, the conversions of the visits that viewed the page,
wherever in the visit the conversion happened. Links and saved explorations from before, with one goal chosen, open
with that goal's own metrics.

__Why does the trend show only five rows?__
Five lines stay readable. The trend takes the first five rows of the table, so sort or filter the table to choose
them.

__Why does a row show 0 in a comparison column?__
The rows come from your own view (its segment and period, sorted and limited as usual). A comparison column shows the
values of exactly these rows: 0 means the compared segment or period has no visits with that value. Rows that only
the compared segment or period has do not appear; compare the other way round to see them.

__What does a saved exploration keep, and who sees it?__
Its dimensions, metrics, goal, filters (with their groups and "all" or "any"), sorting, number of rows, view, chart metric and
chart type (bars, trend or donut), and its segment. Not the period: it opens with the period you have selected, so one exploration serves last week as
well as today. A private exploration is seen only by you; a shared one by everyone who may use explorations on the website (never by
anonymous users). Sharing needs at least write access to the website; with view access you save explorations for
yourself. Only the person who saved it, or a super user, can change it; administrators of the website can also delete
shared ones. Each person can save up to 100 explorations per website.

__Where do saved explorations appear as reports?__
Every saved exploration is a Matomo report for the people who see it: a private one for you only, a shared one for
everyone who may use explorations on the website. You find it in the dashboard's widgets (under Explorations or the
section you chose), in Matomo's scheduled email reports, in Feinwerk Email Reports Pro and, with a place chosen under
**Show in menu**, as its own menu entry. Administrators of the website can change the place of shared explorations
under **Manage shared explorations**. Explorations saved before this version are not in the menu until you choose a
place. A report with two or three dimensions shows the first one and opens each row into the next; a dimension under
**Columns (pivot)** is the last level there, with the same top values under every row and "Others" where every metric
adds up. Reports cover at most
31 days, as explorations do: daily, weekly and monthly emails work, yearly ones do not. Matomo's own scheduled email
leaves out a report it cannot build (an exploration that was deleted or that the email's owner can no longer see, or
a period that is too long); Feinwerk Email Reports Pro shows a note in its place.

__What does a KPI card compare?__
A KPI card shows one metric of an exploration for the dashboard's period (its segment and filters included) and
compares it with what you chose when saving it: the average of the same weekday in the four weeks before (for a week or
a month: the four weeks or months before), the same weekday a week before, the day, week or month before, or nothing.
Today, and a week or month that includes today, count up to the current time, and so does every day they are compared
with. Days before your website's data starts and rates on days without visits are left out of the average; the card
says how many it used. A date range has no comparison. The card shows the change in percent or as a number, green when
it is good news for that metric (a lower bounce rate is good news), and a line of the last 7 or 30 days with the
compared days marked. In Matomo's scheduled e-mails and in Feinwerk Email Reports Pro the card is a report with its one
number.

__How do columns (pivot) work?__
Choose a dimension under **Columns (pivot)** and 3, 5 or 10 of its values: the values with the most of the first metric
become column groups, each with every chosen metric, followed by "Others" and "Total". "Total" is the row as it is
without the columns, rates and unique visitors included. "Others" sums the remaining values and shows only for metrics
that add up across them: visits under a page pivot do not, because one visit sees several pages. A row and its values
can have at most 60 cells (values times metrics). Date and Hour have too many values for columns; Weekday shows its
seven days. The rows keep their sorting by a dimension or by a metric's total. While segments or periods are compared,
the columns pause and come back when the comparison ends. Exports name the columns "value: metric".

__Why at most 31 days?__
Explorations read raw data. A month keeps the queries fast and protects your database. For long periods, archived
reports are the right tool.

__Why can anonymous users not use explorations?__
Explorations query raw data and can be expensive, so they are only for signed-in users, even when anonymous users
may see the site's reports.

__Why do the visits next to page URLs not add up to the total?__
Next to an action dimension, visits count the visits that had this action. A visit with three pages counts on three
rows.

__Why can an exploration differ from an archived report?__
If Matomo deletes old raw data (Administration → Privacy → Anonymize data → delete old raw data), explorations only
see the days still present; the page shows the earliest complete day. Archived reports may also group rare values
under "Others"; explorations list single values up to the row limit instead.

__Does an exploration slow down Matomo?__
An exploration covers at most 31 days and shows at most 1,000 rows. Every exploration checks the number of visits
first and offers a shorter period when one is too large, stops queries that take too long, and runs at most two at
the same time; results are cached. If your Matomo has a read replica, explorations use it. Super users can adjust
these limits in the plugin settings (31 days and 1,000 rows are the maximum).

__How does it behave on large websites and long periods?__
An exploration reads the raw data of the period, so its time grows with the rows it reads: visits for visit dimensions
(channel, country, device), actions for page, event, download or outlink dimensions, which are often many times the
visits. Before exploring, the page counts the visits of the period: above a quarter of the threshold (default
2 million) it warns, above the threshold it offers the most recent days of the period that fit. A query that
takes longer than the time limit (default 30 seconds) is stopped, and after a slow exploration the page says how to
make it faster. Results, visit counts and filter values are kept for an hour (five minutes while today is included),
so going back to an earlier exploration is instant, and a few quick changes start only one query.

__What happens when a period is too large?__
The page offers the most recent days of the period that fit the threshold, and one click loads them. When even a
single day is too large, super users can raise the visit threshold in the settings (a segment does not help here:
the threshold counts all visits of the period).

__Where do administrators see how explorations run?__
In Matomo's System Check (**Administration → Diagnostic → System Check**): whether a read replica is used, whether
the database stops long queries, whether old raw data is deleted, and the limits in force.

__What can I do when an exploration stops at the time limit?__
- Choose a shorter period, for example one week instead of a month.
- Add a segment, so fewer visits are read.
- Use a visit dimension instead of pages or events, or leave out the action metrics.
- Super users can let explorations run on a read replica (`[database_reader]` in `config/config.ini.php`) and raise
  the time limit when the database has room for it.
- Database operators of large instances: enough memory for temporary tables (MariaDB: `tmp_table_size` and
  `max_heap_table_size`, MySQL 8: `temptable_max_ram`) and a buffer pool that holds the indexes of the log tables
  help large explorations.
- For questions you ask regularly over long periods, archived reports are the better tool.

__Where do I find more Feinwerk plugins?__
Install Feinwerk Connector from the Matomo Marketplace. It lists all Plugins for Matomo by Feinwerk, free and paid, and
installs and updates them with one click.
