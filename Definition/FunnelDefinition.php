<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\Definition;

use Piwik\Plugins\FeinwerkDataExploration\Engine\ExplorationException;
use Piwik\Plugins\FeinwerkDataExploration\Engine\Limits;

/**
 * A funnel as the API receives it (spec 3 and 4), e.g.
 * {"steps": [{"type": "pageUrl", "operator": "contains", "value": "/shop"}, {"type": "goal", "idGoal": 3}]}.
 * It has 2 to maxSteps() steps; texts are trimmed and at most 1,000 characters; URL steps drop a leading scheme and
 * "www.", as Matomo stores URLs without them; goal steps name a goal of the website.
 */
final class FunnelDefinition
{
    /** Pro may raise the free limit of 3 through the event FeinwerkDataExploration.getLimits, up to this many. */
    public const MAX_STEPS = 10;
    public const MIN_STEPS = 2;

    /** @param list<FunnelStep> $steps */
    private function __construct(public readonly array $steps)
    {
    }

    public static function maxSteps(Limits $limits): int
    {
        return min(self::MAX_STEPS, max(self::MIN_STEPS, $limits->maxFunnelSteps));
    }

    /**
     * @param string $json the definition as sent; HTML-escaped quotes (sanitized request parameters) are accepted
     * @param array<int, string> $goals the website's goals, idgoal => name
     * @throws ExplorationException
     */
    public static function fromJson(string $json, array $goals, Limits $limits): self
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            $data = json_decode(html_entity_decode($json, ENT_QUOTES), true);
        }
        if (!is_array($data) || array_is_list($data)) {
            // a JSON object is expected; [] and lists are not definitions
            throw new ExplorationException('FeinwerkDataExploration_ErrorInvalidDefinition');
        }

        $max = self::maxSteps($limits);
        $steps = $data['steps'] ?? null;
        if (!is_array($steps) || !array_is_list($steps) || count($steps) < self::MIN_STEPS || count($steps) > $max) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorFunnelStepCount', [$max]);
        }

        $parsed = [];
        foreach ($steps as $i => $step) {
            $parsed[] = self::step($step, $i + 1, $goals);
        }
        return new self($parsed);
    }

    /**
     * The definition with a fixed order, e.g. for cache keys.
     */
    public function normalized(): array
    {
        return ['steps' => array_map(
            static fn (FunnelStep $s): array => [$s->type, $s->operator, $s->value, $s->idGoal],
            $this->steps
        )];
    }

    private static function step($step, int $number, array $goals): FunnelStep
    {
        $type = is_array($step) ? ($step['type'] ?? null) : null;
        if ($type === 'anyGoal') {
            return new FunnelStep('anyGoal', '', '', null);
        }
        if ($type === 'goal') {
            $idGoal = $step['idGoal'] ?? null;
            if (!is_int($idGoal) || !isset($goals[$idGoal])) {
                throw new ExplorationException('FeinwerkDataExploration_ErrorFunnelGoal', [$number]);
            }
            return new FunnelStep('goal', '', '', $idGoal, (string) $goals[$idGoal]);
        }

        $operator = is_array($step) ? ($step['operator'] ?? null) : null;
        $value = is_array($step) ? ($step['value'] ?? null) : null;
        if (!is_string($type) || !isset(FunnelStep::OPERATORS[$type]) || !in_array($operator, FunnelStep::OPERATORS[$type], true) || !is_string($value)) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorFunnelStep', [$number]);
        }
        $value = trim($value);
        if (in_array($type, FunnelStep::URL_TYPES, true)) {
            $value = (string) preg_replace('~^www\.~i', '', (string) preg_replace('~^[a-z][a-z0-9+.\-]*://~i', '', $value));
        }
        if ($value === '' || mb_strlen($value) > ExplorationDefinition::MAX_FILTER_TEXT) {
            throw new ExplorationException('FeinwerkDataExploration_ErrorFunnelStep', [$number]);
        }
        return new FunnelStep($type, $operator, $value, null);
    }
}
