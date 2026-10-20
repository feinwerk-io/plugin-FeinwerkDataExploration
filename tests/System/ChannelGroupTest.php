<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\System;

use Piwik\Plugins\FeinwerkDataExploration\tests\Fixtures\ChannelGroupFixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * The Feinwerk channel group as a dimension, read from FeinwerkChannelGrouping's log_visit column. Runs only where
 * that plugin is in the Matomo under test (the local harness links it; CI does not have it).
 *
 * @group FeinwerkDataExploration
 */
class ChannelGroupTest extends SystemTestCase
{
    use ParityHelper;

    /** @var ChannelGroupFixture|null */
    public static $fixture = null;

    public static function setUpBeforeClass(): void
    {
        if (static::$fixture !== null) {
            parent::setUpBeforeClass();
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (static::$fixture !== null) {
            parent::tearDownAfterClass();
        }
    }

    protected function setUp(): void
    {
        if (static::$fixture === null) {
            $this->markTestSkipped('FeinwerkChannelGrouping is not installed in this Matomo');
        }
        parent::setUp();
    }

    public function testChannelGroupRows(): void
    {
        $rows = [];
        foreach ($this->ourRows(['dimensions' => ['channelGroup'], 'metrics' => ['nb_visits']], 'day', ChannelGroupFixture::DATE) as $label => $row) {
            $rows[$label] = (int) $row['nb_visits'];
        }
        ksort($rows);

        $this->assertSame(['Direct' => 2, 'Not assigned' => 1, 'Paid Search' => 1], $rows);
    }

    public function testListedWithTheNameOfChannelGrouping(): void
    {
        $dimensions = \Piwik\API\Request::processRequest('FeinwerkDataExploration.getAvailableDimensions', ['idSite' => 1, 'format' => 'original']);
        $names = array_column($dimensions, 'name', 'id');

        $this->assertArrayHasKey('channelGroup', $names);
        $this->assertNotSame('', $names['channelGroup']);
    }
}

if (ChannelGroupFixture::fcgInstalled()) {
    ChannelGroupTest::$fixture = new ChannelGroupFixture();
}
