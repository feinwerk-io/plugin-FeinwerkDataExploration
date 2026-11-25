/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/** The sides of a horizontally scrolled box that hide content (the table's edge shadows). */
export interface ScrollEdges {
  left: boolean;
  right: boolean;
}

/**
 * Which sides of a box hide content, from its scroll position and widths (pixels). A pixel of
 * rounding counts as nothing hidden, so a table that just fits shows no shadow.
 */
export function scrollEdges(
  scrollLeft: number,
  clientWidth: number,
  scrollWidth: number,
): ScrollEdges {
  const hidden = scrollWidth - clientWidth;
  if (hidden <= 1) {
    return { left: false, right: false };
  }
  return { left: scrollLeft > 1, right: scrollLeft < hidden - 1 };
}

/** What the table scroller reports: is the table wider than its box, and the box width (px). */
export interface TableLayout {
  overflow: boolean;
  width: number;
}

/**
 * Whether a table with two dimensions shows its first one at its natural width (as wide as its
 * longest value, so the second sits right next to it and takes the free width; Peter, 2026-11-23)
 * or lets long values shrink (the look when room is short). A table that does not fit in natural
 * width shrinks; one that shrank tries the natural width again only when its box gets wider, so
 * the two never alternate.
 */
export function roomyAfter(
  roomy: boolean,
  natural: boolean,
  layout: TableLayout,
  lastWidth: number,
): boolean {
  if (layout.overflow && natural) {
    return false;
  }
  if (!roomy && layout.width > lastWidth + 1) {
    return true;
  }
  return roomy;
}
