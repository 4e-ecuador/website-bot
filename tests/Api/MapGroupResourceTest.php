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

    public function testCollection(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://127.0.0.1']);
        $response = $client->request(
            'GET',
            '/api/map_groups',
            [
                'headers' => [
                    'accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
            ]
        );

        $result = json_decode($response->getContent(), false);

        self::assertResponseStatusCodeSame(200);
        self::assertCount(1, $result);
        self::assertEquals('TestMapGroup', $result[0]->name);
    }

    public function testItemFail(): void
    {
        self::createClient()->request('GET', '/api/map_groups/1');
        self::assertResponseStatusCodeSame(302);
    }

    public function testItem(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://127.0.0.1']);
        $response = $client->request(
            'GET',
            '/api/map_groups/1',
            [
                'headers' => [
                    'accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
            ]
        );

        $result = json_decode($response->getContent(), false);

        self::assertResponseStatusCodeSame(200);
        self::assertEquals('TestMapGroup', $result->name);
    }
}
