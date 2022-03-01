<?php

namespace App\Type;

use App\Entity\Agent;
use App\Entity\User;

class BoardEntry
{
    public function __construct(
        private readonly Agent $agent,
        private readonly User $user,
        private readonly float $value
    ) {
    }

    public function getAgent(): Agent
    {
        return $this->agent;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
