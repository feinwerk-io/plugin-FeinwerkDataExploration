<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

return [
    // the plugin's entry in the System Check (Administration > Diagnostic > System Check)
    'diagnostics.optional' => Piwik\DI::add([
        Piwik\DI::get(Piwik\Plugins\FeinwerkDataExploration\Diagnostic\ExplorationCheck::class),
    ]),
];
