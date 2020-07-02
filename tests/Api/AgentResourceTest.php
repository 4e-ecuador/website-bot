<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class AgentResourceTest extends ApiTestCase
{
    public function testCollectionFail(): void
    {
        $client = self::createClient();

        $client->request(
            'GET',
            '/api/agents',
            ['headers' => ['accept' => 'application/json']]
        );
        self::assertResponseStatusCodeSame(302);
    }

    public function testCollection(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://127.0.0.1']);
        $client->request(
            'GET',
            '/api/agents',
            [
                'headers' => [
                    'accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
            ]
        );

        self::assertResponseStatusCodeSame(200);
    }

    public function testItemFail(): void
    {
        $client = self::createClient();

        $client->request(
            'GET',
            '/api/agents/1',
            ['headers' => ['accept' => 'application/json']]
        );
        self::assertResponseStatusCodeSame(302);
    }
}
