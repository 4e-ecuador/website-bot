<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\AgentStatRepository;
use App\Type\BoardEntry;

class LeaderBoardService
{
    public function __construct(
        private readonly AgentStatRepository $statRepository,
    ) {
    }

    /**
     * @param array<User> $users
     *
     * @return array<string, array<int, BoardEntry>>|array<int, BoardEntry>
     */
    public function getBoard(
        array $users,
        string $typeOnly = 'all'
    ): array {
        $boardEntries = [];

        foreach ($users as $user) {
            $agent = $user->getAgent();

            if (!$agent) {
                continue;
            }

            $agentEntry = $this->statRepository->getAgentLatest($agent);

            if (!$agentEntry instanceof \App\Entity\AgentStat) {
                continue;
            }

            foreach ($agentEntry->findProperties() as $property) {
                if (in_array(
                    $property,
                    [
                        'current_challenge',
                        'level',
                        'faction',
                        'nickname',
                        'csv',
                    ]
                )
                ) {
                    continue;
                }

                $methodName = 'get'.str_replace('_', '', $property);
                if ($agentEntry->$methodName()) {
                    $boardEntries[$property][] = new BoardEntry(
                        $agent,
                        $user,
                        $agentEntry->$methodName()
                    );
                }
            }

            $connector = $agentEntry->getConnector();
            if ($connector) {
                $boardEntries['Fields/Links'][] = new BoardEntry(
                    $agent,
                    $user,
                    $agentEntry->getMindController() / $connector
                );
            }
        }

        foreach (array_keys($boardEntries) as $type) {
            usort(
                $boardEntries[$type],
                static fn($a, $b) => $b->getValue() <=> $a->getValue()
            );
        }

        if ($typeOnly && $typeOnly !== 'all') {
            if (array_key_exists($typeOnly, $boardEntries)) {
                return $boardEntries[$typeOnly];
            }

            throw new \UnexpectedValueException('Unknown type'.$typeOnly);
        }

        return $boardEntries;
    }
}
