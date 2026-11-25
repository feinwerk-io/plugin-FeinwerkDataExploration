<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Common;
use Piwik\Http\HttpCodeException;
use Piwik\Piwik;

/**
 * An error the user can act on; the message is translated. API clients get its HTTP status (an HttpCodeException):
 * 400 for invalid input by default, 403 and 404 where the API passes them. The plugin's own page (Matomo's UI session)
 * gets no status, so the message stays in the body: Matomo's AjaxHelper replaces the body of an HTTP error with a
 * generic text when the page shows errors itself (statusFor()).
 */
class ExplorationException extends \Exception implements HttpCodeException
{
    public function __construct(
        private readonly string $translationKey,
        private readonly array $params = [],
        int $httpCode = 400
    ) {
        parent::__construct(self::translate($translationKey, $params), self::statusFor($httpCode));
    }

    /**
     * @param bool|null $uiRequest null: the request decides (Matomo's UI sends force_api_session=1)
     * @return int the status for Matomo's response, 0 (no status) in the UI session
     */
    public static function statusFor(int $httpCode, ?bool $uiRequest = null): int
    {
        return ($uiRequest ?? self::isUiRequest()) ? 0 : $httpCode;
    }

    private static function isUiRequest(): bool
    {
        try {
            return Common::getRequestVar('force_api_session', 0, 'int') === 1;
        } catch (\Throwable $e) {
            return false;
        }
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
