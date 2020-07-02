<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class AgentStatResourceTest extends ApiTestCase
{
    public function testCollectionFail(): void
    {
        $client = self::createClient();

        $client->request(
            'GET',
            '/api/agent_stats',
            ['headers' => ['accept' => 'application/json']]
        );
        self::assertResponseStatusCodeSame(302);
    }

    public function testCollection(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://127.0.0.1']);

        $response = $client->request(
            'GET',
            '/api/agent_stats',
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
        self::assertEquals('/api/agents/1', $result[0]->agent);
    }

    public function testItemFail(): void
    {
        $client = self::createClient();

        $client->request(
            'GET',
            '/api/agent_stats/1',
            [
                'headers' => [
                    'accept' => 'application/json',
                ],
            ]
        );
        self::assertResponseStatusCodeSame(302);
    }

    public function testItem(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://127.0.0.1']);

        $response = $client->request(
            'GET',
            '/api/agent_stats/1',
            [
                'headers' => [
                    'accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
            ]
        );

        $result = json_decode($response->getContent(), false);

        self::assertResponseStatusCodeSame(200);
        self::assertEquals('/api/agents/1', $result->agent);
    }
}
