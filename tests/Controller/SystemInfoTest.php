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
        $info = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertIsArray($info);

        self::assertSame(Kernel::VERSION, $info['sf_version'] ?? null);
        self::assertSame(PHP_VERSION, $info['php_version'] ?? null);
        // The database the tests run on, and the last deploy (none in the tests).
        self::assertSame('postgresql', $info['db_platform'] ?? null);
        self::assertIsString($info['db_version'] ?? null);
        self::assertArrayHasKey('last_deploy', $info);
    }
}
