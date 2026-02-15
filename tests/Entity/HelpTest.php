<?php

namespace App\Tests\Entity;

use App\Entity\Help;
use PHPUnit\Framework\TestCase;

class HelpTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $help = new Help();

        self::assertNull($help->getId());
        self::assertSame('', $help->getText());
        self::assertSame('', $help->getSlug());
        self::assertSame('', $help->getTitle());
    }

    public function testSettersAndGetters(): void
    {
        $help = new Help();

        $help->setText('Some help text');
        self::assertSame('Some help text', $help->getText());

        $help->setSlug('some-help');
        self::assertSame('some-help', $help->getSlug());

        $help->setTitle('Some Help');
        self::assertSame('Some Help', $help->getTitle());
    }

    public function testFluentSetters(): void
    {
        $help = new Help();

        $result = $help
            ->setText('text')
            ->setSlug('slug')
            ->setTitle('title');

        self::assertSame($help, $result);
    }
}
