<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\ConnectorInstaller;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

/** The installer page rendered with Twig itself: a button only where one click works, a message escaped unless HTML. */
class ConnectorInstallerTemplateTest extends TestCase
{
    /** @param array<string, mixed> $vars */
    private function render(array $vars): string
    {
        $files = new FilesystemLoader();
        $files->addPath(__DIR__ . '/../../../templates', 'FeinwerkDataExploration');
        // admin.twig of Matomo, reduced to the content block
        $twig = new Environment(new ChainLoader([new ArrayLoader(['admin.twig' => '{% block content %}{% endblock %}']), $files]), [
            'strict_variables' => false,
            'autoescape' => 'html',
        ]);
        $twig->addFilter(new TwigFilter('translate', static function (string $key, ...$args): string {
            return $key . ($args === [] ? '' : '(' . implode(',', $args) . ')');
        }));
        $twig->addFunction(new TwigFunction('linkTo', static function (array $params): string {
            return 'index.php?' . http_build_query($params);
        }));

        return $twig->render('@FeinwerkDataExploration/connectorInstaller.twig', $vars + [
            'installerError' => null,
            'installerNonce' => 'n0nce',
            'installerLinks' => ['marketplace' => 'https://plugins.matomo.org/x', 'download' => 'https://plugins.feinwerk.io/x'],
            'installerWordPressUrl' => '',
            'installerConnectorUrl' => 'index.php?module=FeinwerkMarketplaceConnector&action=index',
            'installerPhpFloor' => '8.1.0',
            'installerPluginsAdmin' => true,
            'installerConsoleCommand' => './console plugin:activate FeinwerkMarketplaceConnector',
        ]);
    }

    public function test_install_and_activate_show_one_button_with_the_nonce(): void
    {
        $install = $this->render(['installerState' => 'install']);
        self::assertSame(1, substr_count($install, 'fwci__run'));
        self::assertStringContainsString('action=connectorInstallerRun&amp;nonce=n0nce', $install);
        self::assertStringContainsString('FeinwerkDataExploration_ConnectorInstallerButton<', str_replace([' ', "\n"], '', $install));

        $activate = $this->render(['installerState' => 'activate']);
        self::assertStringContainsString('FeinwerkDataExploration_ConnectorInstallerButtonActivate', $activate);
    }

    public function test_no_button_where_one_click_cannot_work(): void
    {
        foreach (['manual', 'php', 'wordpress', 'console'] as $state) {
            self::assertStringNotContainsString('fwci__run', $this->render(['installerState' => $state]), $state);
        }
        $manual = $this->render(['installerState' => 'manual']);
        self::assertStringContainsString('href="https://plugins.feinwerk.io/x" target="_blank" rel="noopener"', $manual);
        self::assertStringContainsString('FeinwerkDataExploration_ConnectorInstallerPhp(8.1.0)', $this->render(['installerState' => 'php']));
    }

    /** Plugin administration off (Campaign Score's review): the command line instead of a menu that is not there. */
    public function test_without_plugin_administration_the_page_names_the_console_command(): void
    {
        $console = $this->render(['installerState' => 'console', 'installerPluginsAdmin' => false]);
        self::assertStringContainsString('FeinwerkDataExploration_ConnectorInstallerConsole', $console);
        self::assertStringContainsString('<code>./console plugin:activate FeinwerkMarketplaceConnector</code>', $console);
        self::assertStringNotContainsString('ManualStep1', $console, 'no download, the files are there');

        $manual = $this->render(['installerState' => 'manual', 'installerPluginsAdmin' => false]);
        self::assertStringContainsString('FeinwerkDataExploration_ConnectorInstallerManualStep3Console', $manual);
        self::assertStringContainsString('./console plugin:activate FeinwerkMarketplaceConnector', $manual);

        $withMenu = $this->render(['installerState' => 'manual']);
        self::assertStringContainsString('FeinwerkDataExploration_ConnectorInstallerManualStep3<', str_replace([' ', "\n"], '', $withMenu));
        self::assertStringNotContainsString('./console', $withMenu);
    }

    /** The button carries its progress label; the folder name is code; after a stale click the page asks once more. */
    public function test_progress_label_folder_as_code_and_the_click_again_note(): void
    {
        $install = $this->render(['installerState' => 'install']);
        self::assertStringContainsString('data-fwci-busy="FeinwerkDataExploration_ConnectorInstallerInstalling"', $install);
        self::assertStringNotContainsString('fwci__again', $install);
        self::assertStringContainsString('data-fwci-busy="FeinwerkDataExploration_ConnectorInstallerActivating"', $this->render(['installerState' => 'activate']));

        $again = $this->render(['installerState' => 'install', 'installerClickAgain' => true]);
        self::assertStringContainsString('FeinwerkDataExploration_ConnectorInstallerClickAgain', $again);
        self::assertSame(1, substr_count($again, 'fwci__run'), 'a fresh button');
        self::assertStringContainsString('retry=1', $again, 'the next failure shows the reason');
        self::assertStringContainsString('retry=0', $install);

        $manual = $this->render(['installerState' => 'manual']);
        self::assertStringContainsString('FeinwerkDataExploration_ConnectorInstallerManualStep2(<code>FeinwerkMarketplaceConnector</code>)', $manual);
    }

    public function test_an_error_is_escaped_unless_matomo_marks_it_as_html(): void
    {
        $text = $this->render(['installerState' => 'install', 'installerError' => ['ok' => false, 'message' => '<script>x</script>', 'html' => false]]);
        $html = $this->render(['installerState' => 'install', 'installerError' => ['ok' => false, 'message' => 'not <b>writable</b>', 'html' => true]]);

        self::assertStringContainsString('&lt;script&gt;x&lt;/script&gt;', $text);
        self::assertStringNotContainsString('<script>', $text);
        self::assertStringContainsString('not <b>writable</b>', $html);
    }
}
