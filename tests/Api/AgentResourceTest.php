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
        $response = $client->request(
            'GET',
            '/api/agents',
            [
                'headers' => [
                    'accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
            ]
        );

        $result = json_decode($response->getContent(), false);

        self::assertResponseStatusCodeSame(200);
        self::assertCount(6, $result);
        self::assertEquals('UserAgent', $result[0]->nickname);
        self::assertEquals('Agent1', $result[1]->nickname);
        self::assertEquals('Agent2', $result[2]->nickname);
        self::assertEquals('Agent3', $result[3]->nickname);
        self::assertEquals('Agent4', $result[4]->nickname);
        self::assertEquals('Agent5', $result[5]->nickname);
    }

    public function testItemFail(): void
    {
        self::createClient()->request('GET', '/api/agents/1');
        self::assertResponseStatusCodeSame(302);
    }

    public function testItem(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://127.0.0.1']);

        $response = $client->request(
            'GET',
            '/api/agents/1',
            [
                'headers' => [
                    'accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
            ]
        );

        $result = json_decode($response->getContent(), false);

        self::assertResponseStatusCodeSame(200);
        self::assertEquals('UserAgent', $result->nickname);
    }
}
