<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\System;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures\CampaignFixture;
use Piwik\Plugins\MarketingCampaignsReporting\SystemSettings as McrSettings;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * Campaign dimensions of MarketingCampaignsReporting must give the numbers of its own reports, with and without its
 * setting that lower-cases campaign values. MCR leaves out visits without a value; so does this comparison. Without
 * lower-casing, MySQL still groups values that differ only in case (case-insensitive collation), and which spelling
 * labels the row is up to the database; those data sets compare labels in lower case.
 *
 * @group FeinwerkDataExploration
 */
class CampaignParityTest extends SystemTestCase
{
    use ParityHelper;

    /** @var CampaignFixture|null */
    public static $fixture = null;

    public static function setUpBeforeClass(): void
    {
        if (static::$fixture === null) {
            return;
        }
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        if (static::$fixture === null) {
            return;
        }
        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        if (static::$fixture === null) {
            $this->markTestSkipped('MarketingCampaignsReporting is not installed in this Matomo');
        }
        parent::setUp();
    }

    /**
     * @dataProvider dimensions
     */
    public function testMatchesMcrReport(string $dimension, string $mcrMethod, bool $keepCase, array $expected): void
    {
        $this->setKeepCase($keepCase);

        $key = fn (string $label): string => $keepCase ? mb_strtolower($label) : $label;
        $core = [];
        foreach ($this->coreRows($mcrMethod, 'day', CampaignFixture::DATE) as $label => $row) {
            $core[$key($label)] = (int) $row['nb_visits'];
        }
        $ours = [];
        $notDefined = Piwik::translate('FeinwerkDataExploration_NotDefined', [$this->dimensionName($dimension)]);
        foreach ($this->ourRows(['dimensions' => [$dimension], 'metrics' => ['nb_visits']], 'day', CampaignFixture::DATE) as $label => $row) {
            if ($label !== $notDefined) {
                $ours[$key($label)] = (int) $row['nb_visits'];
            }
        }
        ksort($core);
        ksort($ours);

        $this->assertSame($expected, $core, 'fixture and MCR report');
        $this->assertSame($core, $ours);
    }

    public function dimensions(): array
    {
        return [
            'source, case kept' => ['campaignSource', 'MarketingCampaignsReporting.getSource', true,
                ['facebook' => 1, 'google' => 1, 'newsletter' => 3, 'partner' => 1]],
            'source, lower case' => ['campaignSource', 'MarketingCampaignsReporting.getSource', false,
                ['facebook' => 1, 'google' => 1, 'newsletter' => 3, 'partner' => 1]],
            'medium, case kept' => ['campaignMedium', 'MarketingCampaignsReporting.getMedium', true,
                ['cpc' => 1, 'email' => 3]],
            'medium, lower case' => ['campaignMedium', 'MarketingCampaignsReporting.getMedium', false,
                ['cpc' => 1, 'email' => 3]],
            'name, case kept' => ['campaignName', 'MarketingCampaignsReporting.getName', true,
                ['autumn' => 2, 'spring' => 3]],
            'name, lower case' => ['campaignName', 'MarketingCampaignsReporting.getName', false,
                ['autumn' => 2, 'spring' => 3]],
        ];
    }

    /**
     * MCR's setting "do not change the case of campaign values"; archives of the other setting are removed.
     */
    private function setKeepCase(bool $keepCase): void
    {
        $settings = StaticContainer::get(McrSettings::class);
        $settings->doNotChangeCaseOfUtmParameters->setValue($keepCase);
        $settings->save();

        foreach (DbHelper::getTablesInstalled() as $table) {
            if (strpos($table, Common::prefixTable('archive_')) === 0) {
                Db::query('DELETE FROM `' . $table . '`');
            }
        }
    }

    private function dimensionName(string $dimension): string
    {
        foreach ($this->dimensionsList() as $row) {
            if ($row['id'] === $dimension) {
                return $row['name'];
            }
        }
        $this->fail('dimension ' . $dimension . ' is not available');
    }

    private function dimensionsList(): array
    {
        return \Piwik\API\Request::processRequest('FeinwerkDataExploration.getAvailableDimensions', ['idSite' => 1, 'format' => 'original']);
    }
}

if (CampaignFixture::mcrInstalled()) {
    CampaignParityTest::$fixture = new CampaignFixture();
}
