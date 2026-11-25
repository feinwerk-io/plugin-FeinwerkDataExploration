<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Saved;

use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;

/**
 * A saved exploration that cannot be shown as a report right now (saved reports, 2026-11-24): its state no longer
 * resolves on the website (a dimension is gone, no metric is left), or the period is longer than an exploration may
 * cover. The message is translated; Email Reports Pro recognises the class to show it in its e-mail, Matomo's own
 * scheduled e-mail leaves such a report out.
 */
final class SavedReportUnavailable extends ExplorationException
{
}
