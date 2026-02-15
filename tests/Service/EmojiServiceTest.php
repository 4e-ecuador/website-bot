<?php

namespace App\Tests\Service;

use App\Exception\EmojiNotFoundException;
use App\Service\EmojiService;
use App\Type\Emoji;
use PHPUnit\Framework\TestCase;

class EmojiServiceTest extends TestCase
{
    private EmojiService $service;

    protected function setUp(): void
    {
        $this->service = new EmojiService();
    }

    public function testGetEmojiReturnsEmoji(): void
    {
        $emoji = $this->service->getEmoji('tadaa');

        self::assertInstanceOf(Emoji::class, $emoji);
        self::assertSame('tadaa', $emoji->getName());
        self::assertSame('party popper', $emoji->getDescription());
        self::assertSame('U+1F389', $emoji->getUnicode());
    }

    public function testGetEmojiWithNative(): void
    {
        $emoji = $this->service->getEmoji('check-mark');

        self::assertSame('✅', $emoji->getNative());
    }

    public function testGetEmojiWithoutNative(): void
    {
        $emoji = $this->service->getEmoji('tadaa');

        self::assertSame('', $emoji->getNative());
    }

    public function testGetEmojiThrowsForUnknown(): void
    {
        $this->expectException(EmojiNotFoundException::class);

        $this->service->getEmoji('nonexistent');
    }

    public function testGetKeysReturnsAllKeys(): void
    {
        $keys = $this->service->getKeys();

        self::assertContains('tadaa', $keys);
        self::assertContains('check-mark', $keys);
        self::assertContains('cross-mark', $keys);
        self::assertContains('sparkles', $keys);
        self::assertGreaterThanOrEqual(9, count($keys));
    }

    public function testGetAllReturnsEmojiObjects(): void
    {
        $all = $this->service->getAll();

        self::assertCount(count($this->service->getKeys()), $all);

        foreach ($all as $emoji) {
            self::assertInstanceOf(Emoji::class, $emoji);
        }
    }
}
