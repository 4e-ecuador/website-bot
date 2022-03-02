<?php

namespace App\Tests\Entity;

use App\Entity\AgentStat;
use PHPUnit\Framework\TestCase;

class AgentStatTest extends TestCase
{
    public function testGetPropertiesReturnsOnlyValidProperties(): void
    {
        $entity = new AgentStat();

        $properties = $entity->findProperties();

        self::assertCount(41, $properties);
    }
}
