<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\FeinwerkDataExploration;

/**
 * Every script in javascripts/ and every stylesheet in stylesheets/ reaches Matomo's asset manager, and nothing is
 * registered that is not there (kit d4ce520 brought javascripts/connectorinstaller.js and
 * stylesheets/connectorinstaller.css: without the registration the installer button would keep a second click).
 */
class AssetsTest extends TestCase
{
    private const PREFIX = 'plugins/FeinwerkDataExploration/';

    private static function plugin(): FeinwerkDataExploration
    {
        // the asset hooks need nothing the constructor sets up (plugin.json, Matomo's container)
        return (new \ReflectionClass(FeinwerkDataExploration::class))->newInstanceWithoutConstructor();
    }

    /** @return string[] the plugin's files in $directory with one of $extensions, as Matomo paths */
    private static function files(string $directory, array $extensions): array
    {
        $files = [];
        foreach ((array) scandir(__DIR__ . '/../../' . $directory) as $name) {
            if (in_array(pathinfo((string) $name, PATHINFO_EXTENSION), $extensions, true)) {
                $files[] = self::PREFIX . $directory . '/' . $name;
            }
        }
        sort($files);

        return $files;
    }

    public function test_every_script_is_registered(): void
    {
        $registered = [];
        self::plugin()->getJavaScriptFiles($registered);

        self::assertContains(self::PREFIX . 'javascripts/connectorinstaller.js', $registered);
        $sorted = $registered;
        sort($sorted);
        self::assertSame(self::files('javascripts', ['js']), $sorted);
    }

    public function test_every_stylesheet_is_registered(): void
    {
        $registered = [];
        self::plugin()->getStylesheetFiles($registered);

        self::assertContains(self::PREFIX . 'stylesheets/connectorinstaller.css', $registered);
        $sorted = $registered;
        sort($sorted);
        self::assertSame(self::files('stylesheets', ['css', 'less']), $sorted);
    }
}
