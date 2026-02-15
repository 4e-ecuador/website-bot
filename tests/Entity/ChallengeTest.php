<?php

namespace App\Tests\Entity;

use App\Entity\Challenge;
use PHPUnit\Framework\TestCase;

class ChallengeTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $challenge = new Challenge();

        self::assertNull($challenge->getId());
        self::assertSame('', $challenge->getName());
        self::assertNull($challenge->getDateStart());
        self::assertNull($challenge->getDateEnd());
        self::assertNull($challenge->getCodeName());
    }

    public function testSettersAndGetters(): void
    {
        $challenge = new Challenge();
        $start = new \DateTime('2025-03-01');
        $end = new \DateTime('2025-03-31');

        $challenge->setName('March Challenge')
            ->setDateStart($start)
            ->setDateEnd($end)
            ->setCodeName('march_2025');

        self::assertSame('March Challenge', $challenge->getName());
        self::assertSame($start, $challenge->getDateStart());
        self::assertSame($end, $challenge->getDateEnd());
        self::assertSame('march_2025', $challenge->getCodeName());
    }

    public function testFluentSetters(): void
    {
        $challenge = new Challenge();

        $result = $challenge
            ->setName('Test')
            ->setDateStart(new \DateTime())
            ->setDateEnd(new \DateTime())
            ->setCodeName('test');

        self::assertSame($challenge, $result);
    }
}
