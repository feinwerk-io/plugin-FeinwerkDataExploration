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
use Piwik\Plugins\FeinwerkDataExploration\Updates_0_2_0;
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

        require_once __DIR__ . '/../../Updates/0.2.0.php';
        $migration = new Updates_0_2_0();
        $migration->doUpdate(new Updater());
        $migration->doUpdate(new Updater());

        $this->assertTrue($this->tableExists($table));
    }

    private function tableExists(string $table): bool
    {
        return Db::fetchOne('SHOW TABLES LIKE ?', [$table]) !== false;
    }
}
