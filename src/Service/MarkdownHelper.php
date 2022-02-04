<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class MarkdownHelper
{
    public function __construct(
        private CacheItemPoolInterface $cache,
        private MarkdownParser $markdown
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
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }
}
