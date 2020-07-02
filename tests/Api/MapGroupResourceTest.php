<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class MapGroupResourceTest extends ApiTestCase
{
    public function testCollectionFail(): void
    {
        self::createClient()->request('GET', '/api/map_groups');
        self::assertResponseStatusCodeSame(302);
    }

    public function testItemFail(): void
    {
        self::createClient()->request('GET', '/api/map_groups/1');
        self::assertResponseStatusCodeSame(302);
    }
}
