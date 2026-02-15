<?php

namespace App\Tests\Service;

use App\Service\CiteService;
use PHPUnit\Framework\TestCase;

class CiteServiceTest extends TestCase
{
    public function testGetRandomCiteReturnsString(): void
    {
        $service = new CiteService(dirname(__DIR__, 2));

        $result = $service->getRandomCite();

        self::assertNotEmpty($result);
    }

    public function testGetRandomCiteWithMissingFile(): void
    {
        $service = new CiteService('/nonexistent/path');

        $result = $service->getRandomCite();

        // Should return error message from ParseException
        self::assertNotEmpty($result);
    }
}
