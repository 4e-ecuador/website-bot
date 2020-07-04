<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;

class HttpsTest extends ApiTestCase
{
    use RecreateDatabaseTrait;

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

        $expected = '{"message":"Please use SSL and not: http"}';

        self::assertResponseStatusCodeSame(401);
        self::assertJsonStringEqualsJsonString($expected, $response->getContent(false));
    }

    public function testSchemeHttps(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);
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
}
