/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { barMaximum, cellBarWidth, headAndTail } from '../src/cellBars';

describe('bars in the table cells', () => {
  const rows = [
    { label: 'Total', fwde_total: 1, nb_visits: 900, nb_visits__c1: 400 },
    { label: 'Chrome', nb_visits: 80, nb_visits__c1: 120 },
    { label: 'Firefox', nb_visits: 40, nb_visits__c1: null },
    { label: 'Unknown', nb_visits: '', nb_visits__c1: -5 },
  ];

  it('scales against the largest value of the data rows, never the total row', () => {
    expect(barMaximum(rows, ['nb_visits'])).toBe(80);
    expect(barMaximum(rows, ['nb_visits', 'nb_visits__c1'])).toBe(120);
  });

  it('is as wide as the value against the largest one', () => {
    expect(cellBarWidth(80, 80)).toBe(100);
    expect(cellBarWidth(40, 120)).toBe(33.3);
    expect(cellBarWidth('12', 120)).toBe(10);
  });

  it('draws nothing for empty values, zero or less, or without a maximum', () => {
    expect(cellBarWidth(null, 80)).toBeNull();
    expect(cellBarWidth('', 80)).toBeNull();
    expect(cellBarWidth(0, 80)).toBeNull();
    expect(cellBarWidth(-5, 80)).toBeNull();
    expect(cellBarWidth(10, 0)).toBeNull();
    expect(barMaximum([{ label: 'A', nb_visits: -1 }], ['nb_visits'])).toBe(0);
  });

  it('keeps the end of a long label, so it can be shortened in the middle', () => {
    expect(headAndTail('outdoor-shop-demo.example/wandern/', 12))
      .toEqual(['outdoor-shop-demo.exam', 'ple/wandern/']);
    expect(headAndTail('Chrome', 12)).toEqual(['Chrome', '']);
  });

  it('never splits an emoji or a flag', () => {
    const fire = '\u{1F525}';
    const flag = '\u{1F1E9}\u{1F1EA}';
    expect(headAndTail(`${'a'.repeat(30)}${fire}${'b'.repeat(13)}`, 14)).toEqual(['a'.repeat(30), `${fire}${'b'.repeat(13)}`]);
    expect(headAndTail(`${'x'.repeat(30)}${flag}${'y'.repeat(13)}`, 14)).toEqual(['x'.repeat(30), `${flag}${'y'.repeat(13)}`]);
  });

  it('keeps a space at the split as a no-break space (it would vanish at the end or start of a line)', () => {
    expect(headAndTail('Outdoor Shop English Version Home', 4)).toEqual(['Outdoor Shop English Version\u00a0', 'Home']);
    expect(headAndTail('Outdoor Shop English Version Home', 5)).toEqual(['Outdoor Shop English Version', '\u00a0Home']);
  });
});
