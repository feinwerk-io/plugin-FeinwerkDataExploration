<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Catalog;

use Piwik\Common;
use Piwik\DbHelper;

/**
 * Tells whether a log table has a column, e.g. one another plugin adds. An active plugin whose install failed, or a
 * deactivated one, must not make the exploration query a column that is not there.
 */
class ColumnProbe
{
    /** @var callable fn(string $table): string[] */
    private $columnsOf;

    /** @var array<string, string[]> */
    private array $columns = [];

    public function __construct(?callable $columnsOf = null)
    {
        $this->columnsOf = $columnsOf ?? static fn (string $table): array => array_keys(DbHelper::getTableColumns(Common::prefixTable($table)));
    }

    /**
     * @param string $table without the table prefix, e.g. log_visit
     */
    public function hasColumn(string $table, string $column): bool
    {
        if (!isset($this->columns[$table])) {
            $this->columns[$table] = ($this->columnsOf)($table);
        }
        return in_array($column, $this->columns[$table], true);
    }
}
