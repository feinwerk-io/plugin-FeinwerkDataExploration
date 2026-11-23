<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\FeinwerkDataExploration\Saved\SavedExplorationStore;
use Piwik\Plugins\FeinwerkDataExploration\Updates_1_0_0_beta_3;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater;

/**
 * Saved explorations: private ones only for their owner, shared ones for everyone; rows of deleted users and sites go.
 *
 * @group FeinwerkDataExploration
 */
class SavedExplorationStoreTest extends IntegrationTestCase
{
    private const STATE = ['fwdeDims' => 'browser', 'fwdeMetrics' => 'nb_visits'];

    private SavedExplorationStore $store;

    public function setUp(): void
    {
        parent::setUp();
        $this->store = new SavedExplorationStore();
    }

    public function testOwnAndSharedAreVisibleOthersPrivateAreNot(): void
    {
        $this->store->add(1, 'anna', 'Zeta, private', self::STATE, '', false);
        $this->store->add(1, 'anna', 'Alpha, shared', self::STATE, 'browserCode==FF', true);
        $this->store->add(1, 'bert', 'Bert private', self::STATE, '', false);
        $this->store->add(1, 'bert', 'Bert shared', self::STATE, '', true);
        $this->store->add(2, 'anna', 'Other site', self::STATE, '', false);

        $visible = $this->store->listVisible(1, 'anna');

        $this->assertSame(['Alpha, shared', 'Bert shared', 'Zeta, private'], array_column($visible, 'name'));
        $alpha = $visible[0];
        $this->assertSame(1, $alpha['idsite']);
        $this->assertSame('anna', $alpha['login']);
        $this->assertTrue($alpha['shared']);
        $this->assertSame(self::STATE, $alpha['state']);
        $this->assertSame('browserCode==FF', $alpha['segment']);
        $this->assertSame(2, $this->store->countFor(1, 'anna'));
    }

    /**
     * Logins are compared exactly (security sweep 2026-11-17): where this table's collation is looser than Matomo's
     * user table, "bob" and "Bob" (or "jurgen" and "jürgen") are two users but compare equal in SQL.
     */
    public function testPrivateExplorationsOfALoginThatOnlyLooksAlikeStayHidden(): void
    {
        $this->store->add(1, 'Bob', 'Bob private', self::STATE, '', false);
        $this->store->add(1, 'Bob', 'Bob shared', self::STATE, '', true);
        $this->store->add(1, 'jürgen', 'Jürgen private', self::STATE, '', false);

        $this->assertSame(['Bob shared'], array_column($this->store->listVisible(1, 'bob'), 'name'));
        $this->assertSame(['Bob shared'], array_column($this->store->listVisible(1, 'jurgen'), 'name'));
        $this->assertSame(0, $this->store->countFor(1, 'bob'));
        $this->assertSame(0, $this->store->countFor(1, 'jurgen'));
        $this->assertSame(2, $this->store->countFor(1, 'Bob'));
    }

    public function testDeletingAUserKeepsTheExplorationsOfALoginThatOnlyLooksAlike(): void
    {
        $this->store->add(1, 'Bob', 'Bob', self::STATE, '', false);
        $this->store->add(1, 'bob', 'bob', self::STATE, '', false);
        $this->store->add(1, 'jürgen', 'Jürgen', self::STATE, '', false);
        // "Bob" and "jürgen" are other users that still exist
        $exists = static fn (string $login): bool => in_array($login, ['Bob', 'jürgen'], true);

        $this->store->deleteForUser('bob', $exists);
        $this->store->deleteForUser('jurgen', $exists);

        $this->assertSame(['Bob'], array_column($this->store->listVisible(1, 'Bob'), 'name'));
        $this->assertSame([], array_column($this->store->listVisible(1, 'bob'), 'name'));
        $this->assertSame(['Jürgen'], array_column($this->store->listVisible(1, 'jürgen'), 'name'));
    }

    /**
     * Matomo hands the deleted login over as the caller spelled it ("bob" for the user "Bob"), after the user row is
     * gone: rows whose login no user has any more go too.
     */
    public function testDeletingAUserSpelledDifferentlyRemovesItsExplorations(): void
    {
        $this->store->add(1, 'Bob', 'Bob private', self::STATE, '', false);
        $this->store->add(2, 'Bob', 'Bob shared', self::STATE, '', true);
        $this->store->add(1, 'anna', 'Anna', self::STATE, '', true);

        $this->store->deleteForUser('bob', static fn (string $login): bool => $login === 'anna');

        $this->assertSame(0, $this->store->countFor(1, 'Bob') + $this->store->countFor(2, 'Bob'));
        $this->assertSame(['Anna'], array_column($this->store->listVisible(1, 'anna'), 'name'));
    }

    /** When Matomo's user lookup fails, a look-alike login keeps its explorations (only the exact login goes). */
    public function testALookupThatFailsKeepsTheExplorations(): void
    {
        $this->store->add(1, 'Bob', 'Bob', self::STATE, '', false);
        $this->store->add(1, 'bob', 'bob', self::STATE, '', false);

        $this->store->deleteForUser('bob', static function (string $login): bool {
            throw new \RuntimeException('lookup failed');
        });

        $this->assertSame(1, $this->store->countFor(1, 'Bob'));
        $this->assertSame(0, $this->store->countFor(1, 'bob'));
    }

    public function testUpdateAndDelete(): void
    {
        $id = $this->store->add(1, 'anna', 'First', self::STATE, '', false);

        $this->store->update($id, 'Renamed', ['fwdeDims' => 'country'], 'countryCode==de', true);
        $row = $this->store->get($id);
        $this->assertSame('Renamed', $row['name']);
        $this->assertSame(['fwdeDims' => 'country'], $row['state']);
        $this->assertSame('countryCode==de', $row['segment']);
        $this->assertTrue($row['shared']);

        $this->store->delete($id);
        $this->assertNull($this->store->get($id));
    }

    public function testRowsOfDeletedUsersAndSitesGo(): void
    {
        $this->store->add(1, 'anna', 'A', self::STATE, '', true);
        $this->store->add(2, 'anna', 'B', self::STATE, '', true);
        $this->store->add(1, 'bert', 'C', self::STATE, '', true);
        $this->store->add(2, 'bert', 'D', self::STATE, '', true);

        $this->store->deleteForUser('anna');
        $this->store->deleteForSite(2);

        $this->assertSame(['C'], array_column($this->store->listVisible(1, 'bert'), 'name'));
        $this->assertSame([], $this->store->listVisible(2, 'bert'));
    }

    public function testMigrationCreatesTheTableOnceAndUninstallDropsIt(): void
    {
        $table = Common::prefixTable(SavedExplorationStore::TABLE);
        $this->store->uninstall();
        $this->assertFalse($this->tableExists($table));

        require_once __DIR__ . '/../../Updates/1.0.0-beta.3.php';
        $migration = new Updates_1_0_0_beta_3();
        $migration->doUpdate(new Updater());
        $migration->doUpdate(new Updater());

        $this->assertTrue($this->tableExists($table));
    }

    private function tableExists(string $table): bool
    {
        return Db::fetchOne('SHOW TABLES LIKE ?', [$table]) !== false;
    }
}
