<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Piwik;

/**
 * An error the user can act on; the message is translated.
 */
final class ExplorationException extends \Exception
{
    public function __construct(private readonly string $translationKey, private readonly array $params = [])
    {
        parent::__construct(self::translate($translationKey, $params));
    }

    public function getTranslationKey(): string
    {
        return $this->translationKey;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    private static function translate(string $key, array $params): string
    {
        try {
            return Piwik::translate($key, $params);
        } catch (\Throwable $e) {
            // no translator (standalone unit tests)
            return $params === [] ? $key : $key . ' (' . implode(', ', $params) . ')';
        }
    }
}
