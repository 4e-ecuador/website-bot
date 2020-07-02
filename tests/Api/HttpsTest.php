<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class HttpsTest extends ApiTestCase
{
    public function testSchemeHttp(): void
    {
        $client = self::createClient([], ['base_uri' => 'http://example.com']);
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

        $result = json_decode($response->getContent(false), false);

        self::assertResponseStatusCodeSame(401);
        self::assertEquals('Please use SSL and not: http', $result->message);
    }

    public function testSchemeHttps(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);
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

        $result = json_decode($response->getContent(false), false);

        self::assertResponseStatusCodeSame(200);
        self::assertCount(6, $result);
    }
}
