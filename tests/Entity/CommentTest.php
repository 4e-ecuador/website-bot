<?php

namespace App\Tests\Entity;

use App\Entity\Agent;
use App\Entity\Comment;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $comment = new Comment();

        self::assertNull($comment->getId());
        self::assertSame('', $comment->getText());
        self::assertNull($comment->getAgent());
        self::assertNull($comment->getDatetime());
        self::assertNull($comment->getCommenter());
    }

    public function testSettersAndGetters(): void
    {
        $comment = new Comment();
        $agent = new Agent();
        $user = new User();
        $datetime = new \DateTime('2025-06-15 10:30:00');

        $comment->setText('Great agent!')
            ->setAgent($agent)
            ->setDatetime($datetime)
            ->setCommenter($user);

        self::assertSame('Great agent!', $comment->getText());
        self::assertSame($agent, $comment->getAgent());
        self::assertSame($datetime, $comment->getDatetime());
        self::assertSame($user, $comment->getCommenter());
    }

    public function testFluentSetters(): void
    {
        $comment = new Comment();

        $result = $comment
            ->setText('text')
            ->setAgent(new Agent())
            ->setDatetime(new \DateTime())
            ->setCommenter(new User());

        self::assertSame($comment, $result);
    }
}
