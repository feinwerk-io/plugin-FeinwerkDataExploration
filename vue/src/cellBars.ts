/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

type Row = Record<string, unknown>;

function positive(value: unknown): number|null {
  if (value === null || value === undefined || value === '') {
    return null;
  }
  const number = Number(value);
  return Number.isFinite(number) && number > 0 ? number : null;
}

/**
 * The largest value of the given columns in the data rows (the total row left out): what a cell
 * bar is measured against. 0 when there is none.
 */
export function barMaximum(rows: Row[], keys: string[]): number {
  let maximum = 0;
  rows.forEach((row) => {
    if (row.fwde_total) {
      return;
    }
    keys.forEach((key) => {
      const value = positive(row[key]);
      if (value !== null && value > maximum) {
        maximum = value;
      }
    });
  });
  return maximum;
}

/** The width of a cell's bar in percent, one decimal; null for no bar (no value, 0 or less). */
export function cellBarWidth(value: unknown, maximum: number): number|null {
  const number = positive(value);
  if (number === null || maximum <= 0) {
    return null;
  }
  return Math.round(Math.min(100, (number / maximum) * 100) * 10) / 10;
}

interface GraphemeSegmenter {
  segment(text: string): Iterable<{ segment: string }>;
}
type SegmenterClass = new (locale?: string, options?: { granularity: string }) => GraphemeSegmenter;
let segmenter: GraphemeSegmenter|null|undefined;

/** The label's characters as a reader sees them: an emoji or a flag is one character. */
function characters(label: string): string[] {
  if (segmenter === undefined) {
    const { Segmenter } = Intl as unknown as { Segmenter?: SegmenterClass };
    segmenter = typeof Segmenter === 'function'
      ? new Segmenter(undefined, { granularity: 'grapheme' })
      : null;
  }
  if (!segmenter) {
    return Array.from(label); // code points: browsers without Intl.Segmenter
  }
  return Array.from(segmenter.segment(label), (part) => part.segment);
}

/**
 * A label split for shortening in the middle: the head shrinks with an ellipsis, the tail keeps
 * the last characters (the end of a URL). Short labels stay whole.
 */
export function headAndTail(label: string, tail = 14): [string, string] {
  if (label.length <= tail + 6) {
    return [label, ''];
  }
  const chars = characters(label);
  if (chars.length <= tail + 6) {
    return [label, ''];
  }
  // a space at the split would vanish at the end or start of a line: keep it as a no-break space
  return [
    chars.slice(0, chars.length - tail).join('').replace(/ $/, '\u00a0'),
    chars.slice(chars.length - tail).join('').replace(/^ /, '\u00a0'),
  ];
}
