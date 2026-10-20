<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * The lang files: same keys in German and English, every key the code uses exists, customer text rules.
 * Assertions use preg_match because Matomo 5's test runner has PHPUnit 8.5.
 *
 * @group FeinwerkDataExploration
 */
class TranslationsTest extends TestCase
{
    /** Keys built at run time from these prefixes ('Setting' . ucfirst($name)). */
    private const DYNAMIC_PREFIXES = ['Setting'];

    private static function keys(string $language): array
    {
        $json = json_decode((string) file_get_contents(__DIR__ . "/../../lang/$language.json"), true);
        return $json['FeinwerkDataExploration'];
    }

    public function testBothLanguagesHaveTheSameKeys(): void
    {
        $this->assertSame(array_keys(self::keys('en')), array_keys(self::keys('de')));
    }

    public function testNoDashesAndNoBarePercentSigns(): void
    {
        foreach (['en', 'de'] as $language) {
            foreach (self::keys($language) as $key => $text) {
                $this->assertSame(0, preg_match('/[–—]/u', $text), "$language $key has an en or em dash");
                // a bare % breaks Matomo's sprintf based translation; only %s and %1$s style placeholders
                $this->assertSame(0, preg_match('/%(?!(\d+\$)?s)/', $text), "$language $key has a bare percent sign");
            }
        }
    }

    public function testEveryKeyUsedInTheCodeExists(): void
    {
        $keys = self::keys('en');
        $root = dirname(__DIR__, 2);
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        $checked = 0;
        foreach ($files as $file) {
            $path = $file->getPathname();
            if (!preg_match('/\.(php|twig|vue|ts)$/', $path) || preg_match('~/(vendor|node_modules|tests|\.git|dist|\.superpowers)/~', $path)) {
                continue;
            }
            preg_match_all('/FeinwerkDataExploration_([A-Z][A-Za-z]*)/', (string) file_get_contents($path), $matches);
            foreach ($matches[1] as $key) {
                if (!in_array($key, self::DYNAMIC_PREFIXES, true)) {
                    $this->assertArrayHasKey($key, $keys, "$path uses FeinwerkDataExploration_$key");
                    $checked++;
                }
            }
        }
        $this->assertGreaterThan(20, $checked);
    }

    /**
     * German texts use the words of Matomo's own German interface: Konversion, Ereignis, Seitenansicht.
     */
    public function testGermanTextsUseMatomosTerms(): void
    {
        foreach (self::keys('de') as $key => $text) {
            foreach (['/\bConversions?\b/u', '/\bEvents?\b/u', '/Event-/u', '/Seitenaufruf/u'] as $word) {
                $this->assertSame(0, preg_match($word, $text), "de $key uses a word Matomo's German interface does not use");
            }
        }
    }

    public function testGermanTextsAreFormal(): void
    {
        foreach (self::keys('de') as $key => $text) {
            $this->assertSame(0, preg_match('/\b(du|dein|deine|dir|dich)\b/iu', $text), "de $key: use the formal Sie");
        }
    }
}
