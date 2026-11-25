<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Query;

/**
 * A regular expression for a dimension Matomo stores HTML-escaped (titles, event names, site search keywords, custom
 * dimensions: htmlspecialchars with ENT_QUOTES): each of the five escaped characters outside a character class matches
 * itself or its escaped form, so "^l'été" finds the stored "l&#039;été" (final review of filter groups, 2026-11-24).
 * The alternations are plain groups, which every dialect knows (MySQL 8 ICU, MariaDB PCRE, MySQL 5.7 POSIX). Inside a
 * character class a character matches its plain form only.
 */
final class StoredTextPattern
{
    private const BOTH_FORMS = [
        '&' => '(&|&amp;)',
        "'" => "('|&#039;)",
        '"' => '("|&quot;)',
        '<' => '(<|&lt;)',
        '>' => '(>|&gt;)',
    ];

    public static function of(string $pattern): string
    {
        $chars = preg_split('//u', $pattern, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $out = '';
        $inClass = false;
        $count = count($chars);
        for ($i = 0; $i < $count; $i++) {
            $char = $chars[$i];
            if ($char === '\\' && $i + 1 < $count) {
                $next = $chars[++$i];
                // an escaped character is the character itself
                $out .= !$inClass && isset(self::BOTH_FORMS[$next]) ? self::BOTH_FORMS[$next] : $char . $next;
                continue;
            }
            if ($inClass) {
                $out .= $char;
                if ($char === ']') {
                    $inClass = false;
                }
                continue;
            }
            if ($char === '[') {
                $inClass = true;
                $out .= $char;
                // "]" right after "[" or "[^" belongs to the class
                if (($chars[$i + 1] ?? '') === '^') {
                    $out .= $chars[++$i];
                }
                if (($chars[$i + 1] ?? '') === ']') {
                    $out .= $chars[++$i];
                }
                continue;
            }
            $out .= self::BOTH_FORMS[$char] ?? $char;
        }
        return $out;
    }
}
