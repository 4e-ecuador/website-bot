<?php

namespace App\Tests\Entity;

use App\Entity\Faction;
use PHPUnit\Framework\TestCase;

class FactionTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $faction = new Faction();

        self::assertNull($faction->getId());
        self::assertSame('', $faction->getName());
    }

    public function testSetName(): void
    {
        $faction = new Faction();

        $result = $faction->setName('Enlightened');

        self::assertSame('Enlightened', $faction->getName());
        self::assertSame($faction, $result);
    }
}
