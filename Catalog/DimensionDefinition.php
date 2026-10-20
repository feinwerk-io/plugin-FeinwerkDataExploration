<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Catalog;

use Piwik\Plugins\FeinwerkDataExploration\Query\QueryContext;

/**
 * One dimension of the positive list: how to group by it in SQL and how to label its values.
 *
 * Scope "visit" dimensions read log_visit (time dimensions read the action time in action scope); scope "action"
 * dimensions read log_link_visit_action and make the whole query an action query (spec 2.4).
 */
final class DimensionDefinition
{
    /**
     * @param \Closure $keySql fn(QueryContext $context, string $alias): string
     * @param \Closure|null $joins fn(QueryContext $context, string $alias): array, Segment::getSelectQuery() $from entries
     * @param string|null $pageIdentityColumn the log_link_visit_action column that tells pages apart for unique pageviews
     *                                        when this dimension is used (default: the page URL)
     * @param string[] $actionKinds the kinds of action rows that can have a value of this action dimension (pageview,
     *                              event, download, outlink, search); empty for visit dimensions
     * @param bool $storesSanitizedText Matomo stores the values HTML-escaped (titles, event names, site search
     *                                  keywords, custom dimension values), so text filters compare escaped text
     * @param \Closure|null $segment fn(string $raw): ?string, the Matomo segment of the visits with this stored value
     */
    public function __construct(
        public readonly string $id,
        public readonly string $group,
        public readonly string $scope,
        public readonly string $name,
        public readonly string $labelKind,
        private readonly \Closure $keySql,
        private readonly ?\Closure $joins,
        private readonly LabelFormatter $labels,
        public readonly ?string $pageIdentityColumn = null,
        public readonly array $actionKinds = [],
        public readonly bool $storesSanitizedText = false,
        private readonly ?\Closure $segment = null
    ) {
    }

    /**
     * The Matomo segment that selects the visits behind a row (row action "show these visits"), or null: no core
     * segment for this dimension (date, weekday, hour) or no value ("Unknown", "not defined").
     */
    public function segmentFor(?string $raw): ?string
    {
        return $this->segment === null || $raw === null || $raw === '' ? null : ($this->segment)($raw);
    }

    public function keySql(QueryContext $context, string $alias): string
    {
        return ($this->keySql)($context, $alias);
    }

    public function joins(QueryContext $context, string $alias): array
    {
        return $this->joins === null ? [] : ($this->joins)($context, $alias);
    }

    /**
     * Whether text filters must match the formatted label (codes such as "CH" for Chrome) instead of the stored value.
     */
    public function filtersByLabel(): bool
    {
        return !in_array($this->labelKind, ['raw', 'trimmed'], true);
    }

    public function formatLabel(?string $raw, int $idSite): string
    {
        return $this->labels->format($this->labelKind, $raw, $this->name, $idSite);
    }
}
