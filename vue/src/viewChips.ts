/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/** an entry of Matomo's comparison lists (ComparisonsStore), as far as the chips need it */
interface ListEntry {
  title: string;
  params: { segment?: string };
}

/**
 * The period and segment the result covers, for the chips in the result header (UI item 5): the
 * first entry of Matomo's segment and period lists, a segment Matomo has no name for (titled
 * unknownTitle, Matomo's "Unknown") by its definition. Nothing while comparing: Matomo's
 * comparison bar names every column then.
 */
export function viewChips(
  segments: ListEntry[],
  periods: { title: string }[],
  unknownTitle: string,
): { period: string, segment: string } | null {
  if (segments.length !== 1 || periods.length !== 1) {
    return null;
  }
  const [segment] = segments;
  const segmentTitle = segment.title === unknownTitle && segment.params.segment
    ? segment.params.segment
    : segment.title;
  return { period: periods[0].title, segment: segmentTitle };
}
