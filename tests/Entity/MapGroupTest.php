<?php

namespace App\Tests\Entity;

use App\Entity\Agent;
use App\Entity\MapGroup;
use PHPUnit\Framework\TestCase;

class MapGroupTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $group = new MapGroup();

        self::assertNull($group->getId());
        self::assertSame('', $group->getName());
        self::assertCount(0, $group->getAgents());
    }

    public function testSetName(): void
    {
        $group = new MapGroup();

        $result = $group->setName('Quito');

        self::assertSame('Quito', $group->getName());
        self::assertSame($group, $result);
    }

    public function testAddAgent(): void
    {
        $group = new MapGroup();
        $agent = new Agent();

        $result = $group->addAgent($agent);

        self::assertCount(1, $group->getAgents());
        self::assertTrue($group->getAgents()->contains($agent));
        self::assertSame($group, $agent->getMapGroup());
        self::assertSame($group, $result);
    }

    public function testAddAgentDoesNotDuplicate(): void
    {
        $group = new MapGroup();
        $agent = new Agent();

        $group->addAgent($agent);
        $group->addAgent($agent);

        self::assertCount(1, $group->getAgents());
    }

    public function testRemoveAgent(): void
    {
        $group = new MapGroup();
        $agent = new Agent();

        $group->addAgent($agent);
        $result = $group->removeAgent($agent);

        self::assertCount(0, $group->getAgents());
        self::assertNull($agent->getMapGroup());
        self::assertSame($group, $result);
    }

    public function testRemoveAgentNotInCollection(): void
    {
        $group = new MapGroup();
        $agent = new Agent();

        $result = $group->removeAgent($agent);

        self::assertCount(0, $group->getAgents());
        self::assertSame($group, $result);
    }
}
