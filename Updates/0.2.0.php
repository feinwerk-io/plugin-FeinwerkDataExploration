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
 * The table of saved explorations for installs from 0.1.x (new installs get it in install()). Written for the 5.x line;
 * the 6.x line has its own Updates/1.0.0-beta.3.php. Idempotent: the table is created only when it does not exist yet.
 */
class Updates_0_2_0 extends PiwikUpdates
{
    public function doUpdate(Updater $updater)
    {
        (new SavedExplorationStore())->install();
    }
}
