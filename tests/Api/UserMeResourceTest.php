<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use JsonException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class UserMeResourceTest extends ApiTestCase
{
    use RecreateDatabaseTrait;

    /**
     * @throws TransportExceptionInterface
     */
    public function testMeFail(): void
    {
        self::createClient()->request('GET', '/api/users/me');
        self::assertResponseStatusCodeSame(302);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws JsonException
     */
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

        $result = json_decode(
            $response->getContent(),
            false,
            512,
            JSON_THROW_ON_ERROR
        );
        $expected = '{"id":1,"agent":"\/api/agents\/1"}';

        self::assertEquals(1, $result->id);
        self::assertEquals('/api/agents/1', $result->agent);
        self::assertJsonStringEqualsJsonString($expected, $response->getContent());
    }
}
