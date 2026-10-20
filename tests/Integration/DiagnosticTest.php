<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\EventDispatcher;
use Piwik\Piwik;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Plugins\FeinwerkDataExploration\Diagnostic\ExplorationCheck;
use Piwik\Plugins\FeinwerkDataExploration\Engine\LimitsProvider;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The plugin's entry in Matomo's System Check: what makes explorations fast or slow on this server.
 *
 * @group FeinwerkDataExploration
 */
class DiagnosticTest extends IntegrationTestCase
{
    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->extraTestEnvVars['loadRealTranslations'] = true;
    }

    public function setUp(): void
    {
        parent::setUp();
        Fixture::loadAllTranslations();
    }

    public function testReportsReplicaTimeLimitRawDataAndLimits(): void
    {
        $results = (new ExplorationCheck())->execute();

        $this->assertCount(1, $results);
        $this->assertSame(Piwik::translate('FeinwerkDataExploration_DiagnosticLabel'), $results[0]->getLabel());
        $items = array_map(fn ($item) => [$item->getStatus(), $item->getComment()], $results[0]->getItems());

        // the harness: no [database_reader], MariaDB with max_statement_time, raw data kept, default limits
        $this->assertSame(
            [
                [DiagnosticResult::STATUS_INFORMATIONAL, Piwik::translate('FeinwerkDataExploration_DiagnosticNoReplica')],
                [DiagnosticResult::STATUS_OK, Piwik::translate('FeinwerkDataExploration_DiagnosticTimeLimit', [30])],
                [DiagnosticResult::STATUS_INFORMATIONAL, Piwik::translate('FeinwerkDataExploration_DiagnosticRawDataKept')],
                [DiagnosticResult::STATUS_INFORMATIONAL, Piwik::translate('FeinwerkDataExploration_DiagnosticLimits', ['31', '1,000', '2,000,000', '2'])],
            ],
            $items
        );
    }

    public function testAFailureBecomesAWarningInsteadOfBreakingTheSystemCheck(): void
    {
        // e.g. an unreachable [database_reader] or a failing listener: the rest of the System Check must still show
        EventDispatcher::getInstance()->addObserver(LimitsProvider::EVENT, function () {
            throw new \RuntimeException('reader unreachable');
        });

        $results = (new ExplorationCheck())->execute();

        $this->assertCount(1, $results);
        $this->assertSame(DiagnosticResult::STATUS_WARNING, $results[0]->getStatus());
        $this->assertStringContainsString('reader unreachable', $results[0]->getItems()[0]->getComment());
    }

    public function testIsPartOfTheSystemCheck(): void
    {
        $classes = array_map('get_class', StaticContainer::get('diagnostics.optional'));

        $this->assertContains(ExplorationCheck::class, $classes);
    }
}
