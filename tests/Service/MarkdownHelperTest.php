<?php

namespace App\Tests\Service;

use App\Service\MarkdownHelper;
use App\Service\MarkdownParser;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class MarkdownHelperTest extends TestCase
{
    public function testParseCachesMiss(): void
    {
        $item = $this->createStub(CacheItemInterface::class);
        $item->method('isHit')->willReturn(false);
        $item->method('get')->willReturn('<p>Hello</p>');

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('getItem')->willReturn($item);
        $cache->expects(self::once())->method('save')->with($item);

        $parser = $this->createStub(MarkdownParser::class);
        $parser->method('transform')->willReturn('<p>Hello</p>');

        $helper = new MarkdownHelper($cache, $parser);

        self::assertSame('<p>Hello</p>', $helper->parse('Hello'));
    }

    public function testParseCachesHit(): void
    {
        $item = $this->createStub(CacheItemInterface::class);
        $item->method('isHit')->willReturn(true);
        $item->method('get')->willReturn('<p>Cached</p>');

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('getItem')->willReturn($item);
        $cache->expects(self::never())->method('save');

        $parser = $this->createMock(MarkdownParser::class);
        $parser->expects(self::never())->method('transform');

        $helper = new MarkdownHelper($cache, $parser);

        self::assertSame('<p>Cached</p>', $helper->parse('Hello'));
    }
}
