<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Promo;

use PHPUnit\Framework\TestCase;

/**
 * stylesheets/promo.css keeps every notice hidden until promo.js has checked the dismissal and the one-per-page rule
 * (`.fwpromo-line:not([data-fwpromo-ready])`). A rule that shows a notice and weighs as much or more must require
 * `[data-fwpromo-ready]`, or it beats the hidden rule and the notice shows before the check (the first footer variant A
 * had `.fwpromo-line[data-fwpromo-placement="line"] { display: flex }`, docs/footer.md).
 * The same holds for stylesheets/premium.css and javascripts/promocard.js: a banner item stays hidden until the script
 * has checked its 30 days and its turn (`.fwde-premium__item:not([data-fwpromo-ready])`).
 */
class PromoStylesheetTest extends TestCase
{
    private const HIDDEN_WEIGHT = [0, 2];
    private const NOTICE = '/^\.fwpromo-(line|box)(?![\w-])/';
    private const BANNER_ITEM = '/^\.fwde-premium__item(?![\w-])/';

    public function test_no_rule_shows_a_notice_before_promo_js_has_checked_it(): void
    {
        $css = (string) file_get_contents(__DIR__ . '/../../../stylesheets/promo.css');

        $this->assertStringContainsString('.fwpromo-line:not([data-fwpromo-ready])', $css);
        $this->assertSame([], self::earlyShowingSelectors($css));
    }

    public function test_no_rule_shows_a_banner_item_before_promocard_js_has_checked_it(): void
    {
        $css = (string) file_get_contents(__DIR__ . '/../../../stylesheets/premium.css');

        $this->assertStringContainsString('.fwde-premium__item:not([data-fwpromo-ready])', $css);
        $this->assertSame([], self::earlyShowingSelectors($css, self::BANNER_ITEM));
    }

    public function test_the_check_catches_a_banner_item_shown_too_early(): void
    {
        $css = ".fwde-premium__item:not([data-fwpromo-ready]) { display: none; }\n"
            . ".fwde-premium .fwde-premium__item { display: flex; }\n"
            . ".fwde-premium__item[data-fwpromo-ready], .fwde-premium__item .fwde-premium__head { display: flex; }\n";

        $this->assertSame(['.fwde-premium .fwde-premium__item'], self::earlyShowingSelectors($css, self::BANNER_ITEM));
    }

    public function test_the_tip_line_still_outranks_the_plain_class_of_older_kit_copies(): void
    {
        $css = (string) file_get_contents(__DIR__ . '/../../../stylesheets/promo.css');

        // PHPUnit 8.5 (Matomo 5 runner) lacks assertMatchesRegularExpression
        $this->assertSame(1, preg_match('/\.fwpromo-line\[data-fwpromo-ready\]\s*\{[^}]*flex-wrap:\s*nowrap/', $css));
    }

    public function test_the_check_catches_a_showing_rule_as_heavy_as_the_hidden_rule(): void
    {
        $css = ".fwpromo-line:not([data-fwpromo-ready]) { display: none; }\n"
            . ".fwpromo-line, .fwpromo-line[data-fwpromo-placement=\"line\"] { display: flex; }\n"
            . ".matomo-widget .fwpromo-box { display: block; }\n"
            . ".fwpromo-box, .fwpromo-line[data-fwpromo-ready], .x .fwpromo-line__text { display: flex; }\n";

        $this->assertSame(
            ['.fwpromo-line[data-fwpromo-placement="line"]', '.matomo-widget .fwpromo-box'],
            self::earlyShowingSelectors($css)
        );
    }

    /**
     * Selectors whose subject is a notice (`.fwpromo-line` or `.fwpromo-box`, or what $subjectPattern matches), that set
     * a display other than none, weigh at least as much as the hidden rule and do not require `[data-fwpromo-ready]` on
     * the notice.
     *
     * @return string[]
     */
    private static function earlyShowingSelectors(string $css, string $subjectPattern = self::NOTICE): array
    {
        $css = (string) preg_replace('~/\*.*?\*/~s', '', $css);
        preg_match_all('/([^{}]+)\{([^{}]*)\}/', $css, $rules, PREG_SET_ORDER);

        $found = [];
        foreach ($rules as $rule) {
            if (!preg_match('/(^|;)\s*display\s*:\s*(?!none\b)[a-z-]+/i', $rule[2])) {
                continue;
            }
            foreach (explode(',', $rule[1]) as $selector) {
                $selector = trim($selector);
                $parts = preg_split('/\s*[\s>+~]\s*(?![^\[]*\])/', $selector);
                $subject = (string) end($parts);
                if (!preg_match($subjectPattern, $subject)) {
                    continue;
                }
                if (strpos($subject, '[data-fwpromo-ready]') !== false) {
                    continue;
                }
                if (self::weight($selector) >= self::HIDDEN_WEIGHT) {
                    $found[] = $selector;
                }
            }
        }

        return $found;
    }

    /**
     * Specificity as [ids, classes + attributes + pseudo-classes]; `:not()` itself counts nothing, its argument does.
     *
     * @return int[]
     */
    private static function weight(string $selector): array
    {
        $ids = preg_match_all('/#[\w-]+/', $selector);
        $attributes = preg_match_all('/\[[^\]]*\]/', $selector);
        $plain = (string) preg_replace('/\[[^\]]*\]/', '', $selector);
        $classes = preg_match_all('/\.[\w-]+/', $plain);
        $pseudo = preg_match_all('/(?<!:):(?!not\()[\w-]+/', $plain);

        return [$ids, $classes + $attributes + $pseudo];
    }
}
