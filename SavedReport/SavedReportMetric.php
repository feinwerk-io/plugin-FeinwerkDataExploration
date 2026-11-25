<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\SavedReport;

use Piwik\Metrics\Formatter;
use Piwik\Plugin\Metric;
use Piwik\Plugins\FeinwerkDataExploration\Catalog\MetricCatalog;

/**
 * A column of a saved exploration's report as Matomo formats it (saved reports, 2026-11-24): Matomo's widgets and
 * format_metrics=1 format only metric objects, so each column comes with one that formats by the metric's unit, as the
 * exploration page shows it (money in the website's currency, rates from fractions as percent, durations as time).
 */
final class SavedReportMetric extends Metric
{
    public function __construct(private readonly string $id, private readonly string $label, private readonly int $idSite)
    {
    }

    public function getName()
    {
        return $this->id;
    }

    public function getTranslatedName()
    {
        return $this->label;
    }

    public function getSemanticType(): ?string
    {
        return ['count' => 'number', 'number' => 'number', 'money' => 'money', 'rate' => 'percent',
            'seconds' => 'duration_s'][MetricCatalog::meta($this->id)['unit'] ?? 'number'] ?? 'number';
    }

    public function format($value, Formatter $formatter)
    {
        if (!is_numeric($value)) {
            return $value;
        }
        switch (MetricCatalog::meta($this->id)['unit'] ?? 'number') {
            case 'money':
                return $formatter->getPrettyMoney($value, $this->idSite);
            case 'rate':
                return $formatter->getPrettyPercentFromQuotient($value);
            case 'seconds':
                return $formatter->getPrettyTimeFromSeconds($value, true);
            case 'count':
                return $formatter->getPrettyNumber($value);
            default:
                return $formatter->getPrettyNumber($value, 2);
        }
    }
}
