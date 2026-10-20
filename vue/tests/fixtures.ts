/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { Catalog } from '../src/types';

const ACTION_KINDS = ['pageview', 'event', 'download', 'outlink', 'search'];

export const CATALOG: Catalog = {
  dimensions: [
    { id: 'channelType', name: 'Channel type', group: 'acquisition', scope: 'visit', kinds: [] },
    { id: 'browser', name: 'Browser', group: 'technology', scope: 'visit', kinds: [] },
    { id: 'country', name: 'Country', group: 'visitor', scope: 'visit', kinds: [] },
    { id: 'date', name: 'Date', group: 'time', scope: 'visit', kinds: [] },
    { id: 'hour', name: 'Hour', group: 'time', scope: 'visit', kinds: [] },
    { id: 'weekday', name: 'Weekday', group: 'time', scope: 'visit', kinds: [] },
    { id: 'pageUrl', name: 'Page URL', group: 'behaviour', scope: 'action', kinds: ['pageview', 'event'] },
    { id: 'pageTitle', name: 'Page title', group: 'behaviour', scope: 'action', kinds: ['pageview'] },
    { id: 'downloadUrl', name: 'Download URL', group: 'behaviour', scope: 'action', kinds: ['download'] },
    { id: 'eventCategory', name: 'Event category', group: 'behaviour', scope: 'action', kinds: ['event'] },
    { id: 'customDimension4', name: 'Author', group: 'custom', scope: 'action', kinds: ACTION_KINDS },
  ],
  metrics: [
    { id: 'nb_visits', scope: 'visit', name: 'Visits', nameInActionScope: 'Visits with this action', kind: null },
    { id: 'nb_actions', scope: 'visit', name: 'Actions', nameInActionScope: 'Actions of these visits', kind: null },
    { id: 'bounce_rate', scope: 'visit', name: 'Bounce rate', nameInActionScope: 'Bounce rate of these visits', kind: null },
    { id: 'avg_time_on_site', scope: 'visit', name: 'Avg. time', nameInActionScope: 'Avg. time of these visits', kind: null },
    { id: 'nb_conversions', scope: 'goal', name: 'Conversions', nameInActionScope: null, kind: null },
    { id: 'revenue', scope: 'goal', name: 'Revenue', nameInActionScope: null, kind: null },
    { id: 'nb_pageviews', scope: 'action', name: 'Pageviews', nameInActionScope: null, kind: 'pageview' },
    { id: 'nb_events', scope: 'action', name: 'Events', nameInActionScope: null, kind: 'event' },
    { id: 'nb_downloads', scope: 'action', name: 'Downloads', nameInActionScope: null, kind: 'download' },
  ],
};

export const LIMITS = { defaultRows: 100, maxRows: 1000 };
