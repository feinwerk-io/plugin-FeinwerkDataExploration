<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\Definition\FunnelDefinition;
use Piwik\Plugins\FeinwerkDataExploration\Query\FunnelQueryBuilder;
use Piwik\Plugins\FeinwerkDataExploration\Query\QueryContext;
use Piwik\Plugins\FeinwerkDataExploration\Query\QueryRunner;
use Piwik\Segment;

/**
 * Runs a funnel (spec 3 and 4) with the guards of the free-form table: the period window, the visit threshold, the
 * concurrency slots, the result cache and the statement time limit. One row per step: the visits that reached it in
 * order, their share of step 1 and of the previous step (percent, one decimal) and the drop-offs.
 */
final class FunnelExplorer
{
    public function __construct(
        private readonly Limits $limits,
        private readonly ?QueryRunner $runner = null,
        private readonly int $maxIds = StepActionResolver::MAX_IDS
    ) {
    }

    /**
     * @param bool $readableColumns name the columns in the user's language (exports)
     */
    public function explore(int $idSite, string $period, string $date, FunnelDefinition $definition, string $segment, bool $readableColumns = false): DataTable
    {
        $window = ExplorationWindow::for($idSite, $period, $date, $this->limits);
        $context = new QueryContext(QueryContext::SCOPE_ACTION, $window->timezone, $window->startUtc, $window->endUtc);
        $segmentObject = new Segment($segment, [$idSite], $window->start, $window->end);
        $cache = new ResultCache();
        $cacheKey = $cache->funnelKey($idSite, $context->windowStartUtc, $context->windowEndUtc, $segment, $definition, Explorer::pluginVersion());
        $counts = $cache->fetch($cacheKey);
        if ($counts === null) {
            $runner = $this->runner ?? new QueryRunner($this->limits);
            $slot = (new ConcurrencyGate())->acquire($this->limits);
            try {
                (new CostGuard($runner))->check($idSite, $context->windowStartUtc, $context->windowEndUtc, $this->limits, $window->cacheTtl());
                $counts = $this->count($definition, $idSite, $context, $segmentObject, $runner, $window->cacheTtl());
            } finally {
                $slot->unlock();
            }
            $cache->save($cacheKey, $counts, $window->cacheTtl());
        }
        return self::toDataTable($definition, $counts, $readableColumns);
    }

    /** @return list<int> visits per step */
    private function count(FunnelDefinition $definition, int $idSite, QueryContext $context, Segment $segment, QueryRunner $runner, int $ttl): array
    {
        $prefixTable = static fn (string $table): string => Common::prefixTable($table);
        $resolver = new StepActionResolver(static fn (array $query): array => $runner->fetchAll($query), $prefixTable, null, $this->maxIds);
        $resolved = [];
        foreach ($definition->steps as $i => $step) {
            if (!$step->isGoal()) {
                $resolved[$i] = $resolver->resolve($step, $idSite, $context->windowStartUtc, $context->windowEndUtc, $ttl);
            }
        }

        $segmentVisits = null;
        if (!$segment->isEmpty()) {
            $segmentVisits = $segment->getSelectQuery(
                'log_visit.idvisit AS idvisit',
                'log_visit',
                'log_visit.idsite = ? AND log_visit.visit_last_action_time >= ? AND log_visit.visit_last_action_time <= ?',
                [$idSite, $context->windowStartUtc, $context->windowEndUtc],
                false,
                'log_visit.idvisit'
            );
        }

        $rows = $runner->fetchAll((new FunnelQueryBuilder($prefixTable))->build($definition, $idSite, $context, $resolved, $segmentVisits));
        $counts = [];
        foreach (array_keys($definition->steps) as $i) {
            $counts[] = (int) ($rows[0]['fwde_step' . ($i + 1)] ?? 0);
        }
        return $counts;
    }

    private static function toDataTable(FunnelDefinition $definition, array $counts, bool $readableColumns): DataTable
    {
        $names = $readableColumns ? [
            'step' => Piwik::translate('FeinwerkDataExploration_FunnelColumnStep'),
            'label' => Piwik::translate('FeinwerkDataExploration_FunnelColumnCondition'),
            'nb_visits' => Piwik::translate('General_ColumnNbVisits'),
            'share_of_start' => Piwik::translate('FeinwerkDataExploration_FunnelColumnShareOfStart'),
            'share_of_previous' => Piwik::translate('FeinwerkDataExploration_FunnelColumnShareOfPrevious'),
            'nb_dropoffs' => Piwik::translate('FeinwerkDataExploration_FunnelColumnDropoffs'),
        ] : null;

        $table = new DataTable();
        foreach ($definition->steps as $i => $step) {
            $visits = $counts[$i];
            $previous = $i === 0 ? $visits : $counts[$i - 1];
            $columns = [
                'step' => $i + 1,
                'label' => $step->label(),
                'nb_visits' => $visits,
                'share_of_start' => self::share($visits, $counts[0]),
                'share_of_previous' => self::share($visits, $previous),
                'nb_dropoffs' => $previous - $visits,
            ];
            if ($names !== null) {
                $columns = array_combine(array_map(static fn (string $c): string => $names[$c], array_keys($columns)), $columns);
            }
            $table->addRow(new DataTable\Row([DataTable\Row::COLUMNS => $columns]));
        }
        // one row per step in this order: Matomo's default sort and filter_limit must not touch it
        $table->setMetadata(DataTable::GENERIC_FILTERS_TO_DISABLE_METADATA_NAME, ['Sort', 'Limit', 'Truncate']);
        return $table;
    }

    private static function share(int $visits, int $of): float
    {
        return $of > 0 ? round(100 * $visits / $of, 1) : 0.0;
    }
}
