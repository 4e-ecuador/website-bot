<?php

namespace App\Service;

use Michelf\MarkdownInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class MarkdownHelper
{
    public function __construct(private AdapterInterface $cache, private MarkdownInterface $markdown)
    {
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
