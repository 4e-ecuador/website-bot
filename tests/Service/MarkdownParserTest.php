<?php

namespace App\Tests\Service;

use App\Entity\Agent;
use App\Service\MarkdownParser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MarkdownParserTest extends KernelTestCase
{
    private MarkdownParser $markdownParser;

    public function setUp(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine.orm.entity_manager');

        $this->markdownParser = new MarkdownParser(
            $em->getRepository(Agent::class),
            self::getContainer()->get('router')->getGenerator()
        );
    }

    public function testTransform(): void
    {
        $result = $this->markdownParser->transform('foo *bar* baz');

        $expected = "<div><p>foo <em>bar</em> baz</p>\n</div>\n";

        self::assertEquals($expected, $result);
    }

    public function testAgentNameMissing(): void
    {
        $result = $this->markdownParser->transform('foo @bar baz');

        $expected = "<div><p>foo <code>@bar</code> baz</p>\n</div>\n";

        self::assertEquals($expected, $result);
    }

    public function testAgentName(): void
    {
        $result = $this->markdownParser->transform('foo @testAgent baz');

        $expected = '<div><p>foo <a href="/agent/1" class="enl">'
            .'<img src="/build/images/logos/enl.svg" style="height: 32px" alt="logo" class="img-fluid"> @testAgent</a>'
            ." baz</p>\n</div>\n";

        self::assertEquals($expected, $result);
    }

    public function testImageResponsive(): void
    {
        $result = $this->markdownParser->transform('foo ![image](https://example.com/image.jpg) baz');

        $expected = '<div><p>foo '
            .'<img src="https://example.com/image.jpg" alt="image" class="img-fluid">'
            ." baz</p>\n</div>\n";

        self::assertEquals($expected, $result);
    }
}
