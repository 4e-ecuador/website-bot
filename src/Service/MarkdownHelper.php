<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class MarkdownHelper
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly MarkdownParser $markdown
    ) {
    }

    public function parse(string $source): string
    {
        try {
            $item = $this->cache->getItem('markdown_'.md5($source));
            if (!$item->isHit()) {
                $item->set($this->markdown->transform($source));
                $this->cache->save($item);
            }

            return $item->get();
        } catch (InvalidArgumentException $invalidArgumentException) {
            return $invalidArgumentException->getMessage();
        }
    }
}
