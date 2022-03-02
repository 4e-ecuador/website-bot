<?php

namespace App\Service;

use App\Entity\AgentStat;

class ChallengeHelper
{
    /**
     * @param array<AgentStat> $entries
     *
     * @return array<string, int>
     */
    public function getResults(array $entries): array
    {
        $results = [];

        /* @type AgentStat $entry */
        foreach ($entries as $entry) {
            if ($entry->getCurrentChallenge()) {
                $results[$entry->getAgent()->getNickname()]
                    = $entry->getCurrentChallenge();
            }
        }

        arsort($results);

        return $results;
    }
}
