<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration;

use Piwik\Db;
use Piwik\Piwik;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Plugin\SystemSetting;

/**
 * Limits and access of explorations (super users only). The free version allows at most 31 days.
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    public const ROLES = ['view', 'write', 'admin', 'superuser'];

    /** @var SystemSetting */
    public $maxDays;
    /** @var SystemSetting */
    public $maxRows;
    /** @var SystemSetting */
    public $timeoutSeconds;
    /** @var SystemSetting */
    public $maxVisits;
    /** @var SystemSetting */
    public $useReader;
    /** @var SystemSetting */
    public $minimumRole;

    protected function init()
    {
        // the heading in Administration > General settings (Matomo shows the technical name otherwise)
        $this->title = 'Feinwerk Data Exploration';

        $this->maxDays = $this->makeWholeNumberSetting('maxDays', 31, 1, 31);
        $this->maxRows = $this->makeWholeNumberSetting('maxRows', 1000, 10, 1000);
        $this->timeoutSeconds = $this->makeWholeNumberSetting('timeoutSeconds', 30, 5, 300);
        $this->maxVisits = $this->makeWholeNumberSetting('maxVisits', 2000000, 10000, 100000000);

        $this->useReader = $this->makeSetting('useReader', true, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->introduction = Piwik::translate('FeinwerkDataExploration_SettingsDatabaseIntro');
            $field->title = Piwik::translate('FeinwerkDataExploration_SettingUseReader');
            // without [database_reader] explorations use the main database; the switch stays visible with that hint
            $field->inlineHelp = Piwik::translate(Db::hasReaderConfigured()
                ? 'FeinwerkDataExploration_SettingUseReaderHelp'
                : 'FeinwerkDataExploration_SettingUseReaderNoReaderHelp');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
        });

        $this->minimumRole = $this->makeSetting('minimumRole', 'view', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = Piwik::translate('FeinwerkDataExploration_SettingMinimumRole');
            $field->inlineHelp = Piwik::translate('FeinwerkDataExploration_SettingMinimumRoleHelp');
            $field->uiControl = FieldConfig::UI_CONTROL_SINGLE_SELECT;
            $field->availableValues = [
                'view' => Piwik::translate('UsersManager_PrivView'),
                'write' => Piwik::translate('UsersManager_PrivWrite'),
                'admin' => Piwik::translate('UsersManager_PrivAdmin'),
                'superuser' => Piwik::translate('Installation_SuperUser'),
            ];
        });
    }

    private function makeWholeNumberSetting(string $name, int $default, int $min, int $max): SystemSetting
    {
        return $this->makeSetting($name, $default, FieldConfig::TYPE_INT, function (FieldConfig $field) use ($name, $min, $max) {
            if ($name === 'maxDays') {
                $field->introduction = Piwik::translate('FeinwerkDataExploration_SettingsLimitsIntro');
            }
            $field->title = Piwik::translate('FeinwerkDataExploration_Setting' . ucfirst($name));
            $field->inlineHelp = Piwik::translate('FeinwerkDataExploration_Setting' . ucfirst($name) . 'Help');
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            // Matomo's own numeric check would turn "abc" into 0; whole numbers in range only
            $field->validate = static function ($value) use ($min, $max) {
                $isWholeNumber = is_int($value) || (is_string($value) && ctype_digit(trim($value)));
                if (!$isWholeNumber || (int) $value < $min || (int) $value > $max) {
                    throw new \Exception(Piwik::translate('FeinwerkDataExploration_ErrorSettingRange', [$min, $max]));
                }
            };
        });
    }
}
