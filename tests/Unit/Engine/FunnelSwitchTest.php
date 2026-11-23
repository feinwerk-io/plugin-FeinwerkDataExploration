<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Engine\FunnelSwitch;

/**
 * Funnels are off until [FeinwerkDataExploration] funnel = 1 turns them on (Peter, 2026-11-21: not ready to publish).
 */
class FunnelSwitchTest extends TestCase
{
    /**
     * @dataProvider sections
     */
    public function testOnlyAnExplicitOnTurnsFunnelsOn(array $section, bool $expected): void
    {
        $this->assertSame($expected, FunnelSwitch::enabledBy($section));
    }

    public static function sections(): array
    {
        return [
            'no section entry' => [[], false],
            '1' => [['funnel' => '1'], true],
            'integer 1' => [['funnel' => 1], true],
            'PHP true (INI on, yes, true)' => [['funnel' => true], true],
            'on, any case' => [['funnel' => 'On'], true],
            'yes' => [['funnel' => 'yes'], true],
            'true' => [['funnel' => 'TRUE'], true],
            '0' => [['funnel' => '0'], false],
            'PHP false (INI off, no, false)' => [['funnel' => false], false],
            'empty' => [['funnel' => ''], false],
            'anything else' => [['funnel' => '2'], false],
            'other switches only' => [['feedback' => '1', 'promotions' => '1'], false],
        ];
    }
}
