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

    public function getBoard(
        array $users,
        string $typeOnly = 'all'
    ) {
        $boardEntries = [];

        foreach ($users as $user) {
            if ($user instanceof User) {
                $agent = $user->getAgent();
            } else {
                throw new \UnexpectedValueException(
                    'Unsupported user type:'.$user::class
                );
            }

            if (!$agent) {
                continue;
            }

            $agentEntry = $this->statRepository->getAgentLatest($agent);

            if (!$agentEntry) {
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

            $boardEntries['Fields/Links'][] = new BoardEntry(
                $agent,
                $user,
                $agentEntry->getMindController() / $agentEntry->getConnector()
            );
        }

        foreach ($boardEntries as $type => $entries) {
            usort(
                $boardEntries[$type],
                static function ($a, $b) {
                    if ($a->getValue() === $b->getValue()) {
                        return 0;
                    }

                    return ($a->getValue() > $b->getValue()) ? -1 : 1;
                }
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
