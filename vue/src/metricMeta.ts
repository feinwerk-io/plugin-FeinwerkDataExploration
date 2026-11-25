/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Unit and aggregation of a metric by its id (more metrics, 2026-11-24): the same table and
 * patterns as Catalog/MetricCatalog.php (META, GOAL_METRICS); goal and e-commerce ids follow
 * Matomo's naming, so the page needs no catalogue for them. Every rule about shares, bars, whole
 * numbers, gaps and percent input is derived from these two fields.
 */
export type MetricUnit = 'count'|'money'|'rate'|'seconds'|'number';
export type MetricAggregation = 'sum'|'distinct'|'ratio'|'average'|'max';

export interface MetricMeta {
  unit: MetricUnit;
  aggregation: MetricAggregation;
}

const META: Record<string, [MetricUnit, MetricAggregation]> = {
  nb_visits: ['count', 'sum'],
  nb_uniq_visitors: ['count', 'distinct'],
  nb_actions: ['count', 'sum'],
  bounce_rate: ['rate', 'ratio'],
  avg_time_on_site: ['seconds', 'average'],
  nb_actions_per_visit: ['number', 'average'],
  max_actions: ['count', 'max'],
  sum_visit_length: ['seconds', 'sum'],
  nb_conversions: ['count', 'sum'],
  conversion_rate: ['rate', 'ratio'],
  revenue: ['money', 'sum'],
  nb_visits_converted: ['count', 'sum'],
  revenue_per_visit: ['money', 'average'],
  nb_pageviews: ['count', 'sum'],
  nb_uniq_pageviews: ['count', 'distinct'],
  nb_events: ['count', 'sum'],
  sum_event_value: ['number', 'sum'],
  avg_event_value: ['number', 'average'],
  nb_downloads: ['count', 'sum'],
  nb_outlinks: ['count', 'sum'],
  nb_searches: ['count', 'sum'],
};

const GOAL_METRICS: Record<string, Record<string, [MetricUnit, MetricAggregation]>> = {
  goal: {
    nb_conversions: ['count', 'sum'],
    conversion_rate: ['rate', 'ratio'],
    revenue: ['money', 'sum'],
  },
  ecommerceOrder: {
    nb_conversions: ['count', 'sum'],
    revenue: ['money', 'sum'],
    items: ['count', 'sum'],
    avg_order_revenue: ['money', 'average'],
    conversion_rate: ['rate', 'ratio'],
  },
  ecommerceAbandonedCart: {
    nb_conversions: ['count', 'sum'],
    revenue: ['money', 'sum'],
  },
};

const GOAL_ID = /^goal_(\d+|ecommerceOrder|ecommerceAbandonedCart)_([a-z_]+)$/;

/** Unit and aggregation of a metric, null for an unknown id. */
export function metricMeta(id: string): MetricMeta|null {
  let entry = META[id];
  if (!entry) {
    const match = id.match(GOAL_ID);
    if (match) {
      entry = GOAL_METRICS[/^\d+$/.test(match[1]) ? 'goal' : match[1]][match[2]];
    }
  }
  return entry ? { unit: entry[0], aggregation: entry[1] } : null;
}

/** A fraction from 0 to 1: shown and entered in percent. */
export function isRate(id: string): boolean {
  return metricMeta(id)?.unit === 'rate';
}

/** An amount in the website's currency. */
export function isMoney(id: string): boolean {
  return metricMeta(id)?.unit === 'money';
}

/** A duration in seconds: shown as time, entered in seconds. */
export function isSeconds(id: string): boolean {
  return metricMeta(id)?.unit === 'seconds';
}

/** Counts and sums: a share of the column total and a bar in the cell make sense. */
export function hasShare(id: string): boolean {
  const aggregation = metricMeta(id)?.aggregation;
  return aggregation === 'sum' || aggregation === 'distinct';
}

/** A missing row or point counts 0 (counts and sums); rates, averages and maxima leave a gap. */
export function zeroForMissing(id: string): boolean {
  return hasShare(id);
}

/** Whole numbers (counts, seconds, maxima): an axis steps by whole numbers. */
export function isInteger(id: string): boolean {
  const meta = metricMeta(id);
  return meta !== null
    && (meta.unit === 'count' || meta.unit === 'seconds' || meta.aggregation === 'max');
}
