<?php

namespace App\Service;

use Michelf\MarkdownInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class MarkdownHelper
{
    private AdapterInterface $cache;
    private MarkdownInterface $markdown;

    public function __construct(AdapterInterface $cache, MarkdownInterface $markdown)
    {
        $this->cache = $cache;
        $this->markdown = $markdown;
    }

    public function parse(string $source): string
    {
        // return $this->markdown->transform($source);
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
