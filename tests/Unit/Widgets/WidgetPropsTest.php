<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Widgets;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Widgets\WidgetProps;

/**
 * One Feinwerk notice per page (docs/promotions.md): the premium banner below the result takes the place of the tip
 * line; nothing in embeds; a failing promotion empties only its own prop.
 */
class WidgetPropsTest extends TestCase
{
    private const BANNER = ['plugins' => [['id' => 'premium-FeinwerkSearchReport']], 'days' => 30, 'demo' => false, 'note' => ''];
    private const LINE = ['id' => 'campaigns'];

    public function test_the_banner_takes_the_place_of_the_tip_line(): void
    {
        $asked = [];
        $banner = static function () use (&$asked): array {
            $asked[] = 'banner';
            return self::BANNER;
        };
        $line = static function () use (&$asked): array {
            $asked[] = 'line';
            return self::LINE;
        };
        $none = static function (): ?array {
            return null;
        };
        $throws = static function (): array {
            throw new \RuntimeException('connector broken');
        };

        self::assertSame(['premium' => null, 'promo' => null], WidgetProps::promotions(true, $banner, $line), 'embedded');
        self::assertSame([], $asked, 'nothing is asked in an embed');

        self::assertSame(['premium' => self::BANNER, 'promo' => null], WidgetProps::promotions(false, $banner, $line));
        self::assertSame(['banner'], $asked, 'the tip line is not even chosen while the banner shows');

        self::assertSame(['premium' => null, 'promo' => self::LINE], WidgetProps::promotions(false, $none, $line));
        self::assertSame(['premium' => null, 'promo' => self::LINE], WidgetProps::promotions(false, $throws, $line));
        self::assertSame(['premium' => self::BANNER, 'promo' => null], WidgetProps::promotions(false, $banner, $throws));
        self::assertSame(['premium' => null, 'promo' => null], WidgetProps::promotions(false, $none, $throws));
    }
}
