<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Query;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Query\StoredTextPattern;

/**
 * Matomo stores titles, event names and keywords HTML-escaped (ENT_QUOTES): a regular expression on them matches the
 * five escaped characters in both forms (final review of filter groups, 2026-11-24).
 *
 * @group FeinwerkDataExploration
 */
class StoredTextPatternTest extends TestCase
{
    public function testTheFiveEscapedCharactersMatchBothForms(): void
    {
        $this->assertSame("^l('|&#039;)été", StoredTextPattern::of("^l'été"));
        $this->assertSame('(<|&lt;)b(>|&gt;) ("|&quot;)x("|&quot;)', StoredTextPattern::of('<b> "x"'));
        $this->assertSame('Men(&|&amp;)Women', StoredTextPattern::of('Men&Women'));
    }

    public function testAnEscapedCharacterIsTheCharacterToo(): void
    {
        $this->assertSame("a('|&#039;)b", StoredTextPattern::of("a\\'b"));
        $this->assertSame('a\\.b', StoredTextPattern::of('a\\.b'));
    }

    public function testCharacterClassesStayAsTheyAre(): void
    {
        $this->assertSame("[<>']x", StoredTextPattern::of("[<>']x"));
        $this->assertSame("[]']('|&#039;)", StoredTextPattern::of("[]']'"));
        $this->assertSame("[^']+('|&#039;)", StoredTextPattern::of("[^']+'"));
    }

    public function testAPatternWithoutThemIsUnchanged(): void
    {
        $this->assertSame('^/(blog|news)/[0-9]+$', StoredTextPattern::of('^/(blog|news)/[0-9]+$'));
    }
}
