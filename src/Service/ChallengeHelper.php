<?php

namespace App\Service;

use App\Entity\AgentStat;

class ChallengeHelper
{
    public function getResults(array $entries): array
    {
        $results = [];

        /* @type AgentStat $entry */
        foreach ($entries as $entry) {
            $results[$entry->getAgent()->getNickname()] = $entry->getCurrentChallenge();
        }

        arsort($results);

        return $results;
    }
}
