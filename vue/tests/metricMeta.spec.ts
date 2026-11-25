/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  hasShare, isInteger, isMoney, isRate, isSeconds, metricMeta, zeroForMissing,
} from '../src/metricMeta';

describe('metricMeta', () => {
  // the same table as tests/Unit/Catalog/MetricCatalogTest.php (MetricCatalog::META)
  it('knows unit and aggregation of every fixed metric', () => {
    const expected: Record<string, [string, string]> = {
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
    Object.entries(expected).forEach(([id, [unit, aggregation]]) => {
      expect(metricMeta(id)).toEqual({ unit, aggregation });
    });
  });

  it('reads goal and e-commerce metrics by Matomo\'s naming', () => {
    expect(metricMeta('goal_12_conversion_rate')).toEqual({ unit: 'rate', aggregation: 'ratio' });
    expect(metricMeta('goal_3_revenue')).toEqual({ unit: 'money', aggregation: 'sum' });
    expect(metricMeta('goal_ecommerceOrder_items')).toEqual({ unit: 'count', aggregation: 'sum' });
    expect(metricMeta('goal_ecommerceOrder_avg_order_revenue'))
      .toEqual({ unit: 'money', aggregation: 'average' });
    expect(metricMeta('goal_ecommerceAbandonedCart_revenue'))
      .toEqual({ unit: 'money', aggregation: 'sum' });
    expect(metricMeta('goal_ecommerceAbandonedCart_items')).toBeNull();
    expect(metricMeta('goal_3_items')).toBeNull();
    expect(metricMeta('nb_hits')).toBeNull();
  });

  it('derives the rules from unit and aggregation', () => {
    expect(isRate('goal_3_conversion_rate')).toBe(true);
    expect(isRate('revenue')).toBe(false);
    expect(isMoney('goal_3_revenue')).toBe(true);
    expect(isMoney('sum_event_value')).toBe(false);
    expect(isSeconds('sum_visit_length')).toBe(true);
    expect(hasShare('nb_uniq_visitors')).toBe(true);
    expect(hasShare('goal_3_nb_conversions')).toBe(true);
    expect(hasShare('revenue_per_visit')).toBe(false);
    expect(hasShare('goal_3_conversion_rate')).toBe(false);
    expect(zeroForMissing('goal_ecommerceOrder_revenue')).toBe(true);
    expect(zeroForMissing('max_actions')).toBe(false);
    expect(zeroForMissing('goal_ecommerceOrder_avg_order_revenue')).toBe(false);
    expect(isInteger('sum_visit_length')).toBe(true);
    expect(isInteger('max_actions')).toBe(true);
    expect(isInteger('goal_ecommerceOrder_items')).toBe(true);
    expect(isInteger('goal_ecommerceOrder_revenue')).toBe(false);
    expect(isInteger('nb_hits')).toBe(false);
  });
});
