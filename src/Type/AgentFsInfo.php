<?php

declare(strict_types=1);

namespace App\Type;

class AgentFsInfo
{
    public function __construct(
        public readonly string $nickname,
        public readonly string $faction,
        public readonly string $role,
        public readonly string $location,
        public readonly int $eventId,
    ) {
    }
}
