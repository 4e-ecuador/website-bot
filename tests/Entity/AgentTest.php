<?php

namespace App\Tests\Entity;

use App\Entity\Agent;
use App\Entity\Comment;
use App\Entity\Faction;
use App\Entity\MapGroup;
use PHPUnit\Framework\TestCase;

class AgentTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $agent = new Agent();

        self::assertNull($agent->getId());
        self::assertSame('', $agent->getNickname());
        self::assertSame('', $agent->getRealName());
        self::assertNull($agent->getLat());
        self::assertNull($agent->getLon());
        self::assertNull($agent->getFaction());
        self::assertCount(0, $agent->getComments());
        self::assertSame('', $agent->getCustomMedals());
        self::assertNull($agent->getMapGroup());
        self::assertSame('', $agent->getTelegramName());
        self::assertNull($agent->getTelegramId());
        self::assertNull($agent->getTelegramConnectionSecret());
        self::assertNull($agent->getHasNotifyUploadStats());
        self::assertNull($agent->getHasNotifyEvents());
        self::assertNull($agent->getHasNotifyStatsResult());
        self::assertSame('', $agent->getLocale());
    }

    public function testSettersAndGetters(): void
    {
        $agent = new Agent();
        $faction = new Faction();
        $faction->setName('Enlightened');

        $mapGroup = new MapGroup();

        $agent->setNickname('TestAgent')
            ->setRealName('John Doe')
            ->setLat('-0.180653')
            ->setLon('-78.467834')
            ->setFaction($faction)
            ->setCustomMedals('custom1,custom2')
            ->setMapGroup($mapGroup)
            ->setTelegramName('@testagent')
            ->setTelegramId(12345)
            ->setTelegramConnectionSecret('secret123')
            ->setHasNotifyUploadStats(true)
            ->setHasNotifyEvents(false)
            ->setHasNotifyStatsResult(true)
            ->setLocale('es');

        self::assertSame('TestAgent', $agent->getNickname());
        self::assertSame('John Doe', $agent->getRealName());
        self::assertSame('-0.180653', $agent->getLat());
        self::assertSame('-78.467834', $agent->getLon());
        self::assertSame($faction, $agent->getFaction());
        self::assertSame('custom1,custom2', $agent->getCustomMedals());
        self::assertSame($mapGroup, $agent->getMapGroup());
        self::assertSame('@testagent', $agent->getTelegramName());
        self::assertSame(12345, $agent->getTelegramId());
        self::assertSame('secret123', $agent->getTelegramConnectionSecret());
        self::assertTrue($agent->getHasNotifyUploadStats());
        self::assertFalse($agent->getHasNotifyEvents());
        self::assertTrue($agent->getHasNotifyStatsResult());
        self::assertSame('es', $agent->getLocale());
    }

    public function testToString(): void
    {
        $agent = new Agent();
        $agent->setNickname('TestAgent');

        self::assertSame('TestAgent', (string) $agent);
    }

    public function testAddComment(): void
    {
        $agent = new Agent();
        $comment = new Comment();

        $agent->addComment($comment);

        self::assertCount(1, $agent->getComments());
        self::assertSame($agent, $comment->getAgent());
    }

    public function testAddCommentDoesNotDuplicate(): void
    {
        $agent = new Agent();
        $comment = new Comment();

        $agent->addComment($comment);
        $agent->addComment($comment);

        self::assertCount(1, $agent->getComments());
    }

    public function testRemoveComment(): void
    {
        $agent = new Agent();
        $comment = new Comment();

        $agent->addComment($comment);
        $agent->removeComment($comment);

        self::assertCount(0, $agent->getComments());
        self::assertNull($comment->getAgent());
    }

    public function testSleep(): void
    {
        $agent = new Agent();

        self::assertSame(['id'], $agent->__sleep());
    }
}
