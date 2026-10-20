<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Engine;

use Piwik\Plugins\FeinwerkDataExploration\Catalog\DimensionDefinition;

/**
 * Turns a text filter on a coded dimension (e.g. browser "chrome", stored as "CH") into the stored values whose label
 * matches. Small fixed domains need no query; the others ask for the values present in the period.
 */
final class LabelFilterResolver
{
    private const STATIC_DOMAINS = [
        'weekday' => [1, 2, 3, 4, 5, 6, 7],
        'hour' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23],
        'visitorType' => [0, 1],
        'visitConverted' => [0, 1],
        'channelType' => [1, 2, 3, 6, 7, 8],
    ];

    /** @var callable fn(DimensionDefinition $dimension): list<?string> */
    private $distinctKeys;

    public function __construct(callable $distinctKeys)
    {
        $this->distinctKeys = $distinctKeys;
    }

    /**
     * @param string $operator contains, equals or notContains; for notContains the positive matches are returned
     *                         (the query builder negates them)
     * @return array{values: list<string>, includeNull: bool}
     */
    public function resolve(DimensionDefinition $dimension, string $operator, string $value, int $idSite): array
    {
        $domain = isset(self::STATIC_DOMAINS[$dimension->id])
            ? array_map('strval', self::STATIC_DOMAINS[$dimension->id])
            : ($this->distinctKeys)($dimension);

        $values = [];
        $includeNull = false;
        foreach ($domain as $raw) {
            $raw = $raw === null ? null : (string) $raw;
            $label = $dimension->formatLabel($raw, $idSite);
            $matches = $operator === 'equals'
                ? mb_strtolower($label) === mb_strtolower($value)
                : mb_stripos($label, $value) !== false;
            if (!$matches) {
                continue;
            }
            if ($raw === null) {
                $includeNull = true;
            } else {
                $values[] = $raw;
            }
        }

        return ['values' => array_values(array_unique($values)), 'includeNull' => $includeNull];
    }
}
