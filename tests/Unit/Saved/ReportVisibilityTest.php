<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Saved;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Saved\ReportVisibility;

/**
 * Saved reports (Peter, 2026-11-24, "B"): shared explorations for everyone who may use explorations on the website,
 * private ones for their owner only, super users included; Matomo's scheduler run sees every one of the website (each
 * scheduled report is filtered again by its owner); Email Reports Pro's owner narrows for super user callers only.
 *
 * @group FeinwerkDataExploration
 */
class ReportVisibilityTest extends TestCase
{
    private const ROWS = [
        ['idsaved' => 1, 'login' => 'anna', 'shared' => false, 'state' => ['fwdeDims' => 'browser']],
        ['idsaved' => 2, 'login' => 'anna', 'shared' => true, 'state' => ['fwdeDims' => 'browser']],
        ['idsaved' => 3, 'login' => 'bert', 'shared' => false, 'state' => ['fwdeDims' => 'browser']],
        ['idsaved' => 4, 'login' => 'bert', 'shared' => true, 'state' => ['fwdeDims' => 'browser']],
        ['idsaved' => 5, 'login' => 'anna', 'shared' => true, 'state' => ['fwdeKind' => 'funnel', 'fwdeSteps' => 'x']],
    ];

    public function testOwnerSeesPrivateOthersDoNot(): void
    {
        $this->assertSame([1, 2, 4], $this->ids(['login' => 'anna']));
        $this->assertSame([2, 3, 4], $this->ids(['login' => 'bert']));
        $this->assertSame([2, 4], $this->ids(['login' => 'carla']));
    }

    public function testSuperUserDoesNotSeeOthersPrivate(): void
    {
        $this->assertSame([2, 4], $this->ids(['login' => 'root', 'superUser' => true]));
    }

    public function testWithoutTheRoleNothing(): void
    {
        $this->assertSame([], $this->ids(['login' => 'anna'], canUse: false));
    }

    /** the scheduler run: every exploration of the website, so a private one in its owner's e-mail is built */
    public function testSchedulerRunSeesAll(): void
    {
        $this->assertSame([1, 2, 3, 4], $this->ids(['login' => 'root', 'superUser' => true, 'runningTask' => true]));
    }

    public function testOwnerParamNarrowsForSuperUserOnly(): void
    {
        $this->assertSame([1, 2, 4], $this->ids(['login' => 'root', 'superUser' => true, 'owner' => 'anna']));
        // it wins over the scheduler run's widening
        $this->assertSame([2, 3, 4], $this->ids(['login' => 'root', 'superUser' => true, 'runningTask' => true, 'owner' => 'bert']));
        // an owner below the plugin's minimum role sees nothing
        $this->assertSame([], $this->ids(['login' => 'root', 'superUser' => true, 'owner' => 'anna'], canUseAs: false));
    }

    public function testOwnerParamIgnoredForOtherCallers(): void
    {
        $this->assertSame([2, 3, 4], $this->ids(['login' => 'bert', 'owner' => 'anna']));
    }

    public function testFunnelsNeverReports(): void
    {
        $this->assertNotContains(5, $this->ids(['login' => 'anna', 'superUser' => true, 'runningTask' => true]));
    }

    public function testVisibleToAndCanFetch(): void
    {
        $visibility = $this->visibility(['login' => 'root', 'superUser' => true]);
        $this->assertSame([2, 3, 4], array_column($visibility->visibleTo('bert', 1), 'idsaved'));
        $this->assertTrue($visibility->canFetch(self::ROWS[3], 1));
        $this->assertFalse($visibility->canFetch(self::ROWS[2], 1));
        $this->assertFalse($visibility->canFetch(self::ROWS[4], 1));
    }

    private function ids(array $context, bool $canUse = true, bool $canUseAs = true): array
    {
        return array_column($this->visibility($context, $canUse, $canUseAs)->rows(1), 'idsaved');
    }

    private function visibility(array $context, bool $canUse = true, bool $canUseAs = true): ReportVisibility
    {
        return new ReportVisibility(
            fn (int $idSite) => self::ROWS,
            fn () => $context + ['login' => '', 'superUser' => false, 'runningTask' => false, 'owner' => ''],
            fn (string $login, int $idSite) => $canUseAs,
            fn (int $idSite) => $canUse
        );
    }
}
