<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Catalog;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\LabelFormatter;

/**
 * @group FeinwerkDataExploration
 */
class LabelFormatterTest extends TestCase
{
    private LabelFormatter $labels;

    protected function setUp(): void
    {
        $this->labels = new LabelFormatter(
            fn (string $key, array $params = []) => $params === [] ? $key : $key . '(' . implode(',', $params) . ')'
        );
    }

    public function testNullAndEmptyAreNotDefined(): void
    {
        $this->assertSame('FeinwerkDataExploration_NotDefined(Referrer name)', $this->labels->format('raw', null, 'Referrer name', 1));
        $this->assertSame('FeinwerkDataExploration_NotDefined(Referrer name)', $this->labels->format('raw', '', 'Referrer name', 1));
    }

    public function testLocationAndDeviceKindsShowEmptyAsUnknown(): void
    {
        $kinds = [
            'country', 'region', 'city', 'language', 'deviceType', 'operatingSystem', 'browser',
            'continent', 'browserVersion', 'operatingSystemVersion', 'deviceBrand', 'deviceModel',
        ];
        foreach ($kinds as $kind) {
            $this->assertSame('General_Unknown', $this->labels->format($kind, '', 'Name', 1), $kind);
            $this->assertSame('General_Unknown', $this->labels->format($kind, null, 'Name', 1), $kind);
        }
    }

    public function testVersionsBrandAndModelLikeDevicesDetection(): void
    {
        require_once PIWIK_INCLUDE_PATH . '/plugins/DevicesDetection/functions.php';

        $this->assertSame('Chrome 120.0', $this->labels->format('browserVersion', 'CH;120.0', 'Browser version', 1));
        $this->assertSame('Windows 10', $this->labels->format('operatingSystemVersion', 'WIN;10', 'OS version', 1));
        $this->assertSame('Apple', $this->labels->format('deviceBrand', 'AP', 'Device brand', 1));
        // the model's label needs Matomo's translations: VisitDimensionParityTest compares it with the model report
    }

    public function testVisitorType(): void
    {
        $this->assertSame('FeinwerkDataExploration_VisitorTypeNew', $this->labels->format('visitorType', '0', 'Visitor type', 1));
        $this->assertSame('FeinwerkDataExploration_VisitorTypeReturning', $this->labels->format('visitorType', '1', 'Visitor type', 1));
    }

    public function testYesNo(): void
    {
        $this->assertSame('General_Yes', $this->labels->format('yesNo', '1', 'Visit converted', 1));
        $this->assertSame('General_No', $this->labels->format('yesNo', '0', 'Visit converted', 1));
    }

    public function testWeekday(): void
    {
        $this->assertSame('Intl_Day_Long_StandAlone_1', $this->labels->format('weekday', '1', 'Weekday', 1));
        $this->assertSame('Intl_Day_Long_StandAlone_7', $this->labels->format('weekday', '7', 'Weekday', 1));
    }

    public function testRawDateAndTrimmed(): void
    {
        $this->assertSame('2026-03-29', $this->labels->format('raw', '2026-03-29', 'Date', 1));
        $this->assertSame('Contact us', $this->labels->format('trimmed', "  Contact us \n", 'Page title', 1));
    }

    public function testMaskedCampaignValue(): void
    {
        $labels = new LabelFormatter(fn (string $key, array $params = []) => $key === 'PrivacyManager_CampaignParameterDiscarded' ? 'Discarded' : $key);

        $this->assertSame('Discarded', $labels->format('campaign', '__discarded_by_policy__', 'Source', 1));
        $this->assertSame('newsletter', $labels->format('campaign', 'newsletter', 'Source', 1));
        $this->assertSame('FeinwerkDataExploration_NotDefined', $labels->format('campaign', null, 'Source', 1));
        // a Matomo without the placeholder translation shows the stored value
        $this->assertSame('__discarded_by_policy__', $this->labels->format('campaign', '__discarded_by_policy__', 'Source', 1));
    }

    public function testChannelGroupLabels(): void
    {
        $labels = new LabelFormatter(fn (string $key, array $params = []) => $key === 'FeinwerkChannelGrouping_Unassigned' ? 'Not assigned' : $key);

        $this->assertSame('Paid Search', $labels->format('channelGroup', 'Paid Search', 'Channel group', 1));
        $this->assertSame('Not assigned', $labels->format('channelGroup', null, 'Channel group', 1));
        // without FeinwerkChannelGrouping's text the usual "not defined" label
        $this->assertSame('FeinwerkDataExploration_NotDefined(Channel group)', $this->labels->format('channelGroup', null, 'Channel group', 1));
    }

    public function testTranslateDelegates(): void
    {
        $this->assertSame('General_Unknown', $this->labels->translate('General_Unknown'));
    }
}
