<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration;

/**
 * Lets Matomo archive the plugin (archived reports, design 2026-11-25). Its record builders take an exploration each, so
 * Matomo cannot find them by their class; FeinwerkDataExploration::addRecordBuilders adds them per website.
 */
class Archiver extends \Piwik\Plugin\Archiver
{
}
