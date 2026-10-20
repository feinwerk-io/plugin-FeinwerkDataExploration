<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Piwik;
use Piwik\Plugins\FeinwerkDataExploration\SystemSettings;

/**
 * The limits in force: the super user's settings (free defaults when they cannot be read), which another plugin may
 * replace through the event "FeinwerkDataExploration.getLimits".
 */
final class LimitsProvider
{
    public const EVENT = 'FeinwerkDataExploration.getLimits';

    public function get(): Limits
    {
        $limits = self::fromSettings();

        /**
         * Triggered before an exploration runs. Replace $limits to change the protections (e.g. longer periods).
         *
         * @param Limits &$limits
         */
        Piwik::postEvent(self::EVENT, [&$limits]);

        return $limits instanceof Limits ? $limits : new Limits();
    }

    private static function fromSettings(): Limits
    {
        try {
            $settings = new SystemSettings();
            $maxRows = (int) $settings->maxRows->getValue();
            return new Limits(
                maxDays: (int) $settings->maxDays->getValue(),
                defaultRows: min(100, $maxRows),
                maxRows: $maxRows,
                timeoutSeconds: (int) $settings->timeoutSeconds->getValue(),
                maxVisits: (int) $settings->maxVisits->getValue(),
                useReader: (bool) $settings->useReader->getValue()
            );
        } catch (\Throwable $e) {
            return new Limits();
        }
    }
}
