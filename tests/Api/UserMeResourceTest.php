<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class UserMeResourceTest extends ApiTestCase
{
    public function testMeFail(): void
    {
        self::createClient()->request('GET', '/api/users/me');
        self::assertResponseStatusCodeSame(302);
    }

    public function testMe(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://127.0.0.1']);
        $response = $client->request(
            'GET',
            '/api/users/me',
            [
                'headers' => [
                    'accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
            ]
        );

        $result = json_decode($response->getContent(), false);

        self::assertResponseStatusCodeSame(200);
        self::assertEquals(1, $result->id);
        self::assertEquals('/api/agents/1', $result->agent);
    }
}
