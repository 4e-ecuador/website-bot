<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

class SystemInfoTest extends WebTestCase
{
    public function testSystemInfoExposesVersions(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/system/info/');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertSame(
            ['sf_version' => Kernel::VERSION, 'php_version' => PHP_VERSION],
            json_decode((string) $client->getResponse()->getContent(), true),
        );
    }
}
