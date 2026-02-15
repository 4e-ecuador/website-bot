<?php

namespace App\Tests\Util;

use App\Util\Slugger;
use PHPUnit\Framework\TestCase;

class SluggerTest extends TestCase
{
    public function testSlugifyBasicString(): void
    {
        self::assertSame('hello-world', Slugger::slugify('Hello World'));
    }

    public function testSlugifyTrimsWhitespace(): void
    {
        self::assertSame('hello', Slugger::slugify('  Hello  '));
    }

    public function testSlugifyConvertsToLowercase(): void
    {
        self::assertSame('uppercase', Slugger::slugify('UPPERCASE'));
    }

    public function testSlugifyStripsHtmlTags(): void
    {
        self::assertSame('bold-text', Slugger::slugify('<b>Bold</b> <i>Text</i>'));
    }

    public function testSlugifyMultipleSpacesBecomeSingleDash(): void
    {
        self::assertSame('a-b', Slugger::slugify('a   b'));
    }

    public function testSlugifyEmptyString(): void
    {
        self::assertSame('', Slugger::slugify(''));
    }

    public function testSlugifyUtf8(): void
    {
        self::assertSame('über-cool', Slugger::slugify('Über Cool'));
    }
}
