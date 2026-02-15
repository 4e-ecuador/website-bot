<?php

namespace App\Tests\Entity;

use App\Entity\AgentStat;
use PHPUnit\Framework\TestCase;

class AgentStatComputeTest extends TestCase
{
    public function testComputeDiffWithIncreases(): void
    {
        $previous = new AgentStat();
        $previous->setAp(1000);
        $previous->setExplorer(100);
        $previous->setRecharger(500);

        $current = new AgentStat();
        $current->setAp(2000);
        $current->setExplorer(150);
        $current->setRecharger(500);

        $diff = $current->computeDiff($previous);

        self::assertSame(1000, $diff['ap']);
        self::assertSame(50, $diff['explorer']);
        self::assertArrayNotHasKey('recharger', $diff);
    }

    public function testComputeDiffExcludesMetaFields(): void
    {
        $previous = new AgentStat();
        $previous->setFaction('Enlightened');
        $previous->setNickname('Agent1');

        $current = new AgentStat();
        $current->setFaction('Resistance');
        $current->setNickname('Agent2');

        $diff = $current->computeDiff($previous);

        self::assertArrayNotHasKey('faction', $diff);
        self::assertArrayNotHasKey('nickname', $diff);
    }

    public function testComputeDiffEmptyWhenNothingChanged(): void
    {
        $previous = new AgentStat();
        $previous->setAp(1000);

        $current = new AgentStat();
        $current->setAp(1000);

        $diff = $current->computeDiff($previous);

        self::assertEmpty($diff);
    }

    public function testOffsetExistsWithCamelCase(): void
    {
        $stat = new AgentStat();

        self::assertTrue($stat->offsetExists('ap')); // @phpstan-ignore argument.type
        self::assertTrue($stat->offsetExists('explorer')); // @phpstan-ignore argument.type
        self::assertFalse($stat->offsetExists('nonExistent')); // @phpstan-ignore argument.type
    }

    public function testOffsetExistsWithDashedName(): void
    {
        $stat = new AgentStat();

        self::assertTrue($stat->offsetExists('mind-controller')); // @phpstan-ignore argument.type
        self::assertTrue($stat->offsetExists('longest-link')); // @phpstan-ignore argument.type
    }

    public function testOffsetGet(): void
    {
        $stat = new AgentStat();
        $stat->setAp(5000);
        $stat->setMindController(42);

        self::assertSame(5000, $stat->offsetGet('ap')); // @phpstan-ignore argument.type
        self::assertSame(42, $stat->offsetGet('mind-controller')); // @phpstan-ignore argument.type
    }

    public function testFindProperties(): void
    {
        $stat = new AgentStat();

        $properties = $stat->findProperties();

        self::assertContains('ap', $properties);
        self::assertContains('explorer', $properties);
        self::assertContains('mindController', $properties);
        self::assertNotContains('id', $properties);
        self::assertNotContains('datetime', $properties);
        self::assertNotContains('agent', $properties);
    }
}
