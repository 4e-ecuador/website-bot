<?php

namespace App\Type;

use App\Entity\Agent;
use App\Entity\User;

class BoardEntry
{
    private Agent $agent;
    private float $value;
    private User $user;

    public function __construct(Agent $agent, User $user, float $value)
    {
        $this->agent = $agent;
        $this->value = $value;
        $this->user = $user;
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
