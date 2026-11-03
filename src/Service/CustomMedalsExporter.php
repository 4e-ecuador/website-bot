<?php

namespace App\Service;

use App\Entity\Agent;
use JsonException;

/**
 * An agent's custom medals in the current format, for other apps (AgentTools): groups anomaly,
 * annual and event, the medal keys of MedalChecker::$customMedals, tiers lowercase ("" for a medal
 * without tiers) and annual medals as levels 1 to 5. Older records (e.g. {"Anomaly":
 * {"NemesisMyriad": "1"}, "Event": {"DidactField": "Gold"}}) are converted; unknown medals are
 * left out.
 */
class CustomMedalsExporter
{
    /** Older keys of medals that were renamed. */
    private const array RENAMED = [
        '13_magnus_reawakens' => '13_magnusreawakens',
        'avenir_shard' => 'avenir_shard_challenge',
    ];

    public function __construct(private readonly MedalChecker $medalChecker)
    {
    }

    /**
     * @return array<string, array<string, int|string>>
     */
    public function export(Agent $agent): array
    {
        try {
            $stored = json_decode((string) $agent->getCustomMedals(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $stored = null;
        }

        return $this->normalize(is_array($stored) ? $stored : []);
    }

    /**
     * @param array<mixed> $stored
     *
     * @return array<string, array<string, int|string>>
     */
    public function normalize(array $stored): array
    {
        $known = $this->medalChecker->getCustomMedalGroups();
        $medals = array_fill_keys(array_keys($known), []);

        foreach ($stored as $group => $values) {
            $group = strtolower((string) $group);
            if (isset($known[$group]) && is_array($values)) {
                $medals[$group] = $this->group($group, $known[$group], $values);
            }
        }

        return $medals;
    }

    /**
     * @param array<int|string, array<int, string>|string> $known  the group's medals and their tiers
     * @param array<mixed>                                 $values the stored medals of the group
     *
     * @return array<string, int|string>
     */
    private function group(string $group, array $known, array $values): array
    {
        $medals = [];
        foreach ($values as $medal => $value) {
            $medal = $this->key((string) $medal);
            $tier = array_key_exists($medal, $known)
                ? $this->tier($group, (array) $known[$medal], is_scalar($value) ? (string) $value : '')
                : null;
            if (null !== $tier) {
                $medals[$medal] = $tier;
            }
        }

        return $medals;
    }

    /** An annual medal's level, 1 (bronze) to 5 (onyx). */
    private function level(string $value): ?int
    {
        $level = (int) $value;

        return (string) $level === $value && $level >= 1 && $level <= 5 ? $level : null;
    }

    /** NemesisMyriad → nemesis_myriad, and the renamed ones. */
    private function key(string $medal): string
    {
        $key = strtolower((string) preg_replace('/(?<=[a-z0-9])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', '_', $medal));

        return self::RENAMED[$key] ?? $key;
    }

    /**
     * The tier as stored now, null when there is none to keep.
     *
     * @param array<int|string, string> $tiers the medal's tiers, [''] (or none for annual medals) without
     */
    private function tier(string $group, array $tiers, string $value): int|string|null
    {
        $value = strtolower(trim($value));

        if ('annual' === $group) {
            return $this->level($value);
        }

        // Without tiers: older records have "1" for "earned"
        if ([''] === array_values($tiers)) {
            return in_array($value, ['', '1', 'true'], true) ? '' : null;
        }

        return in_array($value, $tiers, true) ? $value : null;
    }
}
