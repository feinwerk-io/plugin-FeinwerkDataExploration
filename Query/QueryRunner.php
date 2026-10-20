<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Query;

use Piwik\Db;
use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\RoleText;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;

/**
 * Runs exploration SQL on the read replica when one is configured (and allowed), with the statement time limit.
 */
class QueryRunner
{
    public function __construct(private readonly Limits $limits)
    {
    }

    /**
     * @param array{sql: string, bind: array} $query
     * @throws ExplorationException when the statement hits the time limit
     */
    /**
     * The version string of the database the explorations run on (read replica or main database).
     */
    public function serverVersion(): string
    {
        return (string) ($this->limits->useReader ? Db::getReader() : Db::get())->getServerVersion();
    }

    public function fetchAll(array $query): array
    {
        $db = $this->limits->useReader ? Db::getReader() : Db::get();
        $sql = StatementTimeLimit::apply(StatementTimeLimit::tag($query['sql']), $this->limits->timeoutSeconds, (string) $db->getServerVersion());

        try {
            return $db->fetchAll($sql, $query['bind']);
        } catch (\Exception $e) {
            if (StatementTimeLimit::isTimeoutError($e, $db)) {
                throw new ExplorationException(RoleText::key('FeinwerkDataExploration_ErrorTimeout'), [$this->limits->timeoutSeconds]);
            }
            throw $e;
        }
    }
}
