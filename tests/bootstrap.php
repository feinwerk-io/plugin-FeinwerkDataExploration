<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// Standalone unit tests (vendor/bin/phpunit, testsuite "unit"): no database. Matomo core classes come from a Matomo
// install given by MATOMO_PATH (read only). Framework tests (tests/Integration, tests/System) run with Matomo's own
// runner in tests/harness or in CI, which also runs tests/Unit without this bootstrap.
$matomoPath = getenv('MATOMO_PATH') ?: dirname(__DIR__, 3);
if (!is_file($matomoPath . '/core/Version.php')) {
    fwrite(STDERR, "Matomo not found at {$matomoPath}, set MATOMO_PATH\n");
    exit(1);
}
if (!defined('PIWIK_INCLUDE_PATH')) {
    define('PIWIK_INCLUDE_PATH', $matomoPath);
}
date_default_timezone_set('UTC');

// Our autoloader first: Matomo's maps Piwik\Plugins\ to its own plugins/ directory, which may hold another copy.
require __DIR__ . '/../vendor/autoload.php';
require_once $matomoPath . '/vendor/autoload.php';
