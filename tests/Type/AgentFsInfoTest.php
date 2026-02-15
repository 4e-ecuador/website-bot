<?php

namespace App\Tests\Type;

use App\Type\AgentFsInfo;
use PHPUnit\Framework\TestCase;

class AgentFsInfoTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $info = new AgentFsInfo(
            nickname: 'TestAgent',
            faction: 'Enlightened',
            role: 'POC',
            location: 'Quito',
            eventId: 42,
        );

        self::assertSame('TestAgent', $info->nickname);
        self::assertSame('Enlightened', $info->faction);
        self::assertSame('POC', $info->role);
        self::assertSame('Quito', $info->location);
        self::assertSame(42, $info->eventId);
    }

    public function testPropertiesAreReadonly(): void
    {
        $info = new AgentFsInfo('A', 'B', 'C', 'D', 1);

        $ref = new \ReflectionClass($info);
        foreach ($ref->getProperties() as $property) {
            self::assertTrue($property->isReadOnly());
        }
    }
}
