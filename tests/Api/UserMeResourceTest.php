<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;

class UserMeResourceTest extends ApiTestCase
{
    use RecreateDatabaseTrait;

    public function testMeFail(): void
    {
        self::createClient()->request('GET', '/api/users/me');
        self::assertResponseStatusCodeSame(302);
    }

    public function testMe(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);
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

        self::assertResponseStatusCodeSame(200);

        $result = json_decode($response->getContent(), false);
        $expected = '{"id":1,"agent":"\/api/agents\/1"}';

        self::assertEquals(1, $result->id);
        self::assertEquals('/api/agents/1', $result->agent);
        self::assertJsonStringEqualsJsonString($expected, $response->getContent());
    }
}
