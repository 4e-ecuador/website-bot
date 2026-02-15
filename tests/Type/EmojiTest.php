<?php

namespace App\Tests\Type;

use App\Type\Emoji;
use PHPUnit\Framework\TestCase;

class EmojiTest extends TestCase
{
    public function testGetters(): void
    {
        $emoji = new Emoji('tadaa', 'party popper', 'U+1F389', "\xF0\x9F\x8E\x89", '🎉');

        self::assertSame('tadaa', $emoji->getName());
        self::assertSame('party popper', $emoji->getDescription());
        self::assertSame('U+1F389', $emoji->getUnicode());
        self::assertSame("\xF0\x9F\x8E\x89", $emoji->getBytecode());
        self::assertSame('🎉', $emoji->getNative());
    }

    public function testDefaultNativeValue(): void
    {
        $emoji = new Emoji('test', 'desc', 'U+0000', "\x00");

        self::assertSame('x', $emoji->getNative());
    }
}
