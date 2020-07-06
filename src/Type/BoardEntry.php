<?php

namespace App\Type;

use App\Entity\Agent;

class BoardEntry
{
    private Agent $agent;
    private float $value;

    public function __construct(Agent $agent, float $value)
    {
        $this->agent = $agent;
        $this->value = $value;
    }

    public function getAgent(): Agent
    {
        return $this->agent;
    }

    public function getValue(): float
    {
        return $this->value;
    }
}
