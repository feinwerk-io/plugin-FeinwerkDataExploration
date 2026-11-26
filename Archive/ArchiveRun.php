<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Archive;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;
use Piwik\Plugins\LanguagesManager\Model as LanguagesModel;
use Piwik\Translation\Translator;

/**
 * The guards of one archiving run (design 2026-11-25, Peter: "1a"): in core:archive no slot of the explorer's and the
 * plugin's own statement time limit (archive_query_seconds); in a browser request, where Matomo allows browser
 * archiving, the page's guards: a slot, the page's statement time limit and the cost guard.
 */
final class ArchiveRun
{
    /** the most combinations one day's query returns; Matomo cuts each level to the archive's row caps */
    public const MAX_DAY_COMBINATIONS = 50000;

    private function __construct(
        public readonly Limits $limits,
        public readonly bool $usesSlot,
        public readonly bool $checksCost
    ) {
    }

    public static function forCli(ArchiveSettings $settings, Limits $limits): self
    {
        return new self($limits->withTimeoutSeconds($settings->querySeconds), false, false);
    }

    public static function forBrowser(Limits $limits): self
    {
        return new self($limits, true, true);
    }

    /**
     * Runs $fn with Matomo's translator in the owner's language, so label conditions on coded dimensions (weekday,
     * visitor type, channel type) mean what the owner saved; the language before is restored. An owner without a known
     * language keeps the current one.
     *
     * @template T
     * @param callable(): T $fn
     * @return T
     */
    public function withLanguage(string $login, callable $fn): mixed
    {
        $translator = StaticContainer::get(Translator::class);
        $before = $translator->getCurrentLanguage();
        try {
            $language = (string) ((new LanguagesModel())->getLanguageForUser($login) ?: '');
        } catch (\Throwable $e) {
            $language = '';
        }
        if ($language === '' || $language === $before) {
            return $fn();
        }
        $translator->setCurrentLanguage($language);
        try {
            return $fn();
        } finally {
            $translator->setCurrentLanguage($before);
        }
    }
}
