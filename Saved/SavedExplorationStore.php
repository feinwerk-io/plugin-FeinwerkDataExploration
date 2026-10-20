<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Saved;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;

/**
 * Saved explorations (spec section 13): one row per exploration of a site, private to its owner or shared with
 * everyone who may use explorations on that site. Access rules live in the API; this class only stores.
 */
final class SavedExplorationStore
{
    public const TABLE = 'fw_dataexploration_saved';

    public function install(): void
    {
        DbHelper::createTable(self::TABLE, "
            `idsaved` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `idsite` INT(10) UNSIGNED NOT NULL,
            `login` VARCHAR(100) NOT NULL,
            `name` VARCHAR(100) NOT NULL,
            `state` TEXT NOT NULL,
            `segment` TEXT NOT NULL,
            `shared` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            `ts_created` DATETIME NOT NULL,
            `ts_updated` DATETIME NOT NULL,
            PRIMARY KEY (`idsaved`),
            KEY `site_login` (`idsite`, `login`)");
    }

    public function uninstall(): void
    {
        Db::dropTables([$this->table()]);
    }

    /**
     * @return list<array<string, mixed>> the user's own explorations of the site and those shared by others, by name
     */
    public function listVisible(int $idSite, string $login): array
    {
        $rows = Db::fetchAll(
            'SELECT * FROM `' . $this->table() . '` WHERE idsite = ? AND (login = ? OR shared = 1) ORDER BY name ASC, idsaved ASC',
            [$idSite, $login]
        );
        return array_map([self::class, 'row'], $rows);
    }

    /** @return array<string, mixed>|null */
    public function get(int $id): ?array
    {
        $row = Db::fetchRow('SELECT * FROM `' . $this->table() . '` WHERE idsaved = ?', [$id]);
        return $row ? self::row($row) : null;
    }

    public function add(int $idSite, string $login, string $name, array $state, string $segment, bool $shared): int
    {
        $now = Date::now()->getDatetime();
        Db::query(
            'INSERT INTO `' . $this->table() . '` (idsite, login, name, state, segment, shared, ts_created, ts_updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [$idSite, $login, $name, json_encode($state), $segment, $shared ? 1 : 0, $now, $now]
        );
        return (int) Db::get()->lastInsertId();
    }

    public function update(int $id, string $name, array $state, string $segment, bool $shared): void
    {
        Db::query(
            'UPDATE `' . $this->table() . '` SET name = ?, state = ?, segment = ?, shared = ?, ts_updated = ? WHERE idsaved = ?',
            [$name, json_encode($state), $segment, $shared ? 1 : 0, Date::now()->getDatetime(), $id]
        );
    }

    public function delete(int $id): void
    {
        Db::query('DELETE FROM `' . $this->table() . '` WHERE idsaved = ?', [$id]);
    }

    public function deleteForUser(string $login): void
    {
        Db::query('DELETE FROM `' . $this->table() . '` WHERE login = ?', [$login]);
    }

    public function deleteForSite(int $idSite): void
    {
        Db::query('DELETE FROM `' . $this->table() . '` WHERE idsite = ?', [$idSite]);
    }

    public function countFor(int $idSite, string $login): int
    {
        return (int) Db::fetchOne('SELECT COUNT(*) FROM `' . $this->table() . '` WHERE idsite = ? AND login = ?', [$idSite, $login]);
    }

    public function table(): string
    {
        return Common::prefixTable(self::TABLE);
    }

    /** @return array<string, mixed> */
    private static function row(array $row): array
    {
        $state = json_decode((string) $row['state'], true);
        return [
            'idsaved' => (int) $row['idsaved'],
            'idsite' => (int) $row['idsite'],
            'login' => (string) $row['login'],
            'name' => (string) $row['name'],
            'state' => is_array($state) ? $state : [],
            'segment' => (string) $row['segment'],
            'shared' => (int) $row['shared'] === 1,
            'ts_created' => (string) $row['ts_created'],
            'ts_updated' => (string) $row['ts_updated'],
        ];
    }
}
