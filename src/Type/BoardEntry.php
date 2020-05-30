<?php

namespace App\Type;

use App\Entity\Agent;

class BoardEntry
{
    /**
     * @var Agent
     */
    private $agent;

    /**
     * @var float
     */
    private $value;

    public function __construct(Agent $agent, float $value)
    {
        $this->agent = $agent;
        $this->value = $value;
    }

    /**
     * @return Agent
     */
    public function getAgent(): Agent
    {
        return $this->agent;
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }
}
