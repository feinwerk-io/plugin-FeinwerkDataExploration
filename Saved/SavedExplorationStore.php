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
use Piwik\Plugins\UsersManager\Model as UsersModel;
use Piwik\DbHelper;

/**
 * Saved explorations (spec section 13): one row per exploration of a site, private to its owner or shared with
 * everyone who may use explorations on that site. Access rules live in the API; this class only stores.
 */
final class SavedExplorationStore
{
    public const TABLE = 'fw_dataexploration_saved';

    /**
     * A login compared exactly: this table's collation may be looser than Matomo's user table (case, accents), where
     * "bob" and "Bob" or "jurgen" and "jürgen" are different users. The first comparison keeps the index in use.
     */
    private const SAME_LOGIN = '(login = ? AND CAST(login AS BINARY) = CAST(? AS BINARY))';

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
            'SELECT * FROM `' . $this->table() . '` WHERE idsite = ? AND (' . self::SAME_LOGIN . ' OR shared = 1) ORDER BY name ASC, idsaved ASC',
            [$idSite, $login, $login]
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

    /**
     * The explorations of a deleted user (UsersManager.deleteUser, after the user row is gone). Matomo hands the login
     * over as the caller spelled it, so the rows the table's collation matches are checked one exact login at a time:
     * a login goes when it is exactly the deleted one or when no Matomo user has it any more; it stays when it is
     * another existing user's or when the lookup fails.
     *
     * @param callable(string): bool|null $userExists whether a Matomo user has this login (default: Matomo's lookup)
     */
    public function deleteForUser(string $login, ?callable $userExists = null): void
    {
        $userExists = $userExists ?? static function (string $candidate): bool {
            return !empty((new UsersModel())->getUser($candidate));
        };
        $candidates = Db::fetchAll('SELECT login FROM `' . $this->table() . '` WHERE login = ?', [$login]);
        // exact, in PHP: DISTINCT would fold the spellings together under the table's collation
        $logins = array_values(array_unique(array_map(static fn (array $row): string => (string) $row['login'], $candidates), SORT_STRING));
        foreach ($logins as $candidate) {
            if ($candidate !== $login) {
                try {
                    if ($userExists($candidate)) {
                        continue;
                    }
                } catch (\Throwable $e) {
                    continue;
                }
            }
            Db::query('DELETE FROM `' . $this->table() . '` WHERE ' . self::SAME_LOGIN, [$candidate, $candidate]);
        }
    }

    public function deleteForSite(int $idSite): void
    {
        Db::query('DELETE FROM `' . $this->table() . '` WHERE idsite = ?', [$idSite]);
    }

    public function countFor(int $idSite, string $login): int
    {
        return (int) Db::fetchOne('SELECT COUNT(*) FROM `' . $this->table() . '` WHERE idsite = ? AND ' . self::SAME_LOGIN, [$idSite, $login, $login]);
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
