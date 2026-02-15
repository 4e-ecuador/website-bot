<?php

namespace App\Tests\Type;

use App\Entity\Agent;
use App\Entity\User;
use App\Type\BoardEntry;
use PHPUnit\Framework\TestCase;

class BoardEntryTest extends TestCase
{
    public function testGetters(): void
    {
        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $user = new User();
        $user->setEmail('test@example.com');

        $entry = new BoardEntry($agent, $user, 42.5);

        self::assertSame($agent, $entry->getAgent());
        self::assertSame($user, $entry->getUser());
        self::assertSame(42.5, $entry->getValue());
    }

    public function testZeroValue(): void
    {
        $entry = new BoardEntry(new Agent(), new User(), 0.0);

        self::assertSame(0.0, $entry->getValue());
    }
}
