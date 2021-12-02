<?php

namespace App\Type;

use App\Entity\Agent;
use App\Entity\User;

class BoardEntry
{
    public function __construct(
        private Agent $agent,
        private User $user,
        private float $value
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
