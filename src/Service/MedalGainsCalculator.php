<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\AgentStat;
use App\Repository\AgentStatRepository;

/**
 * Tracks which medal levels agents gained over a series of stat entries.
 */
class MedalGainsCalculator
{
    public function __construct(private readonly MedalChecker $medalChecker)
    {
    }

    /**
     * @param iterable<AgentStat> $entries
     *
     * @return array{byDate: array<string, array<string, array<string, int>>>, byMedal: array<string, array<int, array{agent: string|null, level: int}>>}
     */
    public function getMedalsGained(
        iterable $entries,
        AgentStatRepository $statRepository
    ): array {
        $medalsGained = [];
        $medalsGained1 = [];
        $previous = [];

        foreach ($entries as $entry) {
            $agentName = $entry->getAgent()?->getNickname();

            if (false === isset($previous[$agentName])) {
                $previousEntry = $statRepository->getPrevious($entry);

                $previous[$agentName] = $previousEntry instanceof AgentStat
                    ? $this->medalChecker->checkLevels($previousEntry)
                    : $this->medalChecker->checkLevels($entry);
            }

            $levels = $this->medalChecker->checkLevels($entry);
            $dateString = $entry->getDatetime()?->format('Y-m-d');

            $this->processMedalLevels(
                $medalsGained,
                $medalsGained1,
                $previous,
                $agentName,
                $dateString,
                $levels
            );
        }

        foreach ($medalsGained1 as $name => $items) {
            usort(
                $items,
                static fn ($a, $b) => $b['level'] <=> $a['level']
            );
            $medalsGained1[$name] = $items;
        }

        return ['byDate' => $medalsGained, 'byMedal' => $medalsGained1];
    }

    /**
     * @param array<string, array<string, array<string, int>>>                 $medalsGained
     * @param array<string, array<int, array{agent: string|null, level: int}>> $medalsGained1
     * @param array<string|null, array<string, int>>                           $previous
     * @param array<string, int>                                               $levels
     */
    private function processMedalLevels(
        array &$medalsGained,
        array &$medalsGained1,
        array &$previous,
        ?string $agentName,
        ?string $dateString,
        array $levels
    ): void {
        foreach ($levels as $name => $level) {
            if (!$level) {
                continue;
            }

            if (false === isset($previous[$agentName][$name])
                || $previous[$agentName][$name] < $level
            ) {
                $medalsGained[$dateString][$agentName][$name] = $level;
                $medalsGained1[$name][] = [
                    'agent' => $agentName,
                    'level' => $level,
                ];
                $previous[$agentName][$name] = $level;
            }
        }
    }
}
