<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

return [
    // a saved exploration as a Matomo report is fetched by its id (saved reports, 2026-11-24): Matomo passes it on to
    // the sub tables of a two-dimension report (flat=1, expanding rows) and keeps it in API requests
    'entities.idNames' => Piwik\DI::add(['idSaved']),
    // the plugin's entry in the System Check (Administration > Diagnostic > System Check)
    'diagnostics.optional' => Piwik\DI::add([
        Piwik\DI::get(Piwik\Plugins\FeinwerkDataExploration\Diagnostic\ExplorationCheck::class),
    ]),
];
