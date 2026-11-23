<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration;

use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationStore;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

/**
 * The table of saved explorations for installs from before 1.0.0-beta.3 (new installs get it in install()).
 * Idempotent: the table is created only when it does not exist yet.
 */
class Updates_1_0_0_beta_3 extends PiwikUpdates
{
    public function doUpdate(Updater $updater)
    {
        (new SavedExplorationStore())->install();
    }
}
