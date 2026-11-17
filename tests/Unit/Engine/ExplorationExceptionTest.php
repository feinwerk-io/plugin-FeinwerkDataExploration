<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Http\HttpCodeException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;

/**
 * Errors the user can act on answer with an HTTP status: 400 by default, 403 and 404 where the API
 * says so; Matomo sets the response status from the code of an HttpCodeException.
 *
 * @group FeinwerkDataExploration
 */
class ExplorationExceptionTest extends TestCase
{
    public function testDefaultIsBadRequest(): void
    {
        $e = new ExplorationException('FeinwerkDataExploration_ErrorInvalidDefinition');
        $this->assertInstanceOf(HttpCodeException::class, $e);
        $this->assertSame(400, $e->getCode());
    }

    /**
     * The plugin's own page (Matomo's UI session, force_api_session=1) shows the message inline: Matomo's AjaxHelper
     * replaces the body of an HTTP error with "Something went wrong" when the page handles errors itself. So the status
     * applies to API clients only; the page gets the message in the body as before.
     */
    public function testTheUiSessionKeepsTheMessageInTheBody(): void
    {
        $this->assertSame(0, ExplorationException::statusFor(400, true));
        $this->assertSame(0, ExplorationException::statusFor(404, true));
        $this->assertSame(403, ExplorationException::statusFor(403, false));
    }

    public function testCarriesTheGivenStatus(): void
    {
        $this->assertSame(404, (new ExplorationException('FeinwerkDataExploration_ErrorSavedNotFound', [], 404))->getCode());
    }
}
