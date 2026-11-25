<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\SavedReport;

use Piwik\Columns\Dimension;

/**
 * A dimension of a saved exploration as Matomo's report metadata names it (saved reports, 2026-11-24): its id and its
 * name only; the exploration computes the values itself.
 */
final class SavedReportDimension extends Dimension
{
    public function __construct(private readonly string $explorationId, private readonly string $label)
    {
    }

    public function getName()
    {
        return $this->label;
    }

    public function getId()
    {
        return 'FeinwerkDataExploration.' . $this->explorationId;
    }
}
