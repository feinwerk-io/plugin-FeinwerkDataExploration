<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Saved;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\Engine\AccessPolicy;
use Piwik\Scheduler\Scheduler;

/**
 * Which saved explorations are Matomo reports for whom (saved reports, Peter 2026-11-24 "B"): the website's shared
 * ones for everyone who may use explorations there, private ones for their owner only, super users included. Inside
 * Matomo's scheduler run every one of the website, because scheduled e-mails are built there as super user without
 * naming the owner (each scheduled report is filtered again by its owner: visibleTo()). Email Reports Pro names the
 * owner with the request parameter fwReportOwner: honoured for super user callers only, only narrowing, also inside
 * the scheduler run. Funnels are never reports.
 */
final class ReportVisibility
{
    /** @var callable fn(int $idSite): list<array> */
    private $rowsOf;
    /** @var callable fn(): array{login: string, superUser: bool, runningTask: bool, owner: string} */
    private $context;
    /** @var callable fn(string $login, int $idSite): bool */
    private $canUseAs;
    /** @var callable fn(int $idSite): bool the current user */
    private $canUse;

    public function __construct(?callable $rowsOf = null, ?callable $context = null, ?callable $canUseAs = null, ?callable $canUse = null)
    {
        $this->rowsOf = $rowsOf ?? static fn (int $idSite): array => (new SavedExplorationStore())->listAll($idSite);
        $this->context = $context ?? [self::class, 'currentContext'];
        $this->canUseAs = $canUseAs ?? [AccessPolicy::class, 'canUseAs'];
        $this->canUse = $canUse ?? [AccessPolicy::class, 'canUse'];
    }

    /**
     * The saved explorations that are reports for the current request.
     *
     * @return list<array<string, mixed>>
     */
    public function rows(int $idSite): array
    {
        $context = ($this->context)();
        if ($context['superUser'] && $context['owner'] !== '') {
            return $this->visibleTo($context['owner'], $idSite);
        }
        if ($context['runningTask']) {
            return $this->tables($idSite);
        }
        if (!($this->canUse)($idSite)) {
            return [];
        }
        return self::sharedOrOwnedBy($this->tables($idSite), $context['login']);
    }

    /**
     * The reports a given user may see (a scheduled report's owner): shared and own ones, nothing below the plugin's
     * minimum role.
     *
     * @return list<array<string, mixed>>
     */
    public function visibleTo(string $login, int $idSite): array
    {
        if (!($this->canUseAs)($login, $idSite)) {
            return [];
        }
        return self::sharedOrOwnedBy($this->tables($idSite), $login);
    }

    /** @return array{login: string, superUser: bool, runningTask: bool, owner: string} who asks (cache keys) */
    public function context(): array
    {
        return ($this->context)();
    }

    /** Whether the current request may fetch the report of this saved exploration. */
    public function canFetch(array $row, int $idSite): bool
    {
        return in_array((int) $row['idsaved'], array_map('intval', array_column($this->rows($idSite), 'idsaved')), true);
    }

    /** @return array{login: string, superUser: bool, runningTask: bool, owner: string} */
    public static function currentContext(): array
    {
        try {
            $runningTask = (bool) StaticContainer::get(Scheduler::class)->isRunningTask();
        } catch (\Throwable $e) {
            $runningTask = false;
        }
        return [
            'login' => (string) Piwik::getCurrentUserLogin(),
            'superUser' => Piwik::hasUserSuperUserAccess(),
            'runningTask' => $runningTask,
            'owner' => trim(Common::getRequestVar('fwReportOwner', '', 'string')),
        ];
    }

    /** @return list<array<string, mixed>> the website's free-form tables and KPI cards (funnels are never reports) */
    private function tables(int $idSite): array
    {
        return array_values(array_filter(($this->rowsOf)($idSite),
            static fn (array $row): bool => ($row['state']['fwdeKind'] ?? '') !== SavedExplorationRules::KIND_FUNNEL));
    }

    /** @return list<array<string, mixed>> */
    private static function sharedOrOwnedBy(array $rows, string $login): array
    {
        // logins compared exactly, as the store does (SavedExplorationStore::SAME_LOGIN)
        return array_values(array_filter($rows, static fn (array $row): bool => $row['shared'] || $row['login'] === $login));
    }
}
