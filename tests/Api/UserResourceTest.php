<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class UserResourceTest extends ApiTestCase
{
    use RecreateDatabaseTrait;

    /**
     * @throws TransportExceptionInterface
     */
    public function testUsersFail(): void
    {
        self::createClient()->request('GET', '/api/users');
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testUsersFailNoAdmin(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);
        $client->request(
            'GET',
            '/api/users',
            [
                'headers' => [
                    'accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
            ]
        );
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testUsers(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);
        $response = $client->request(
            'GET',
            '/api/users',
            [
                'headers' => [
                    'accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'Adm1nT0ken',
                ],
            ]
        );
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $expected
            = '['
            .'{"id":1,"roles":["ROLE_AGENT","ROLE_USER"],"email":"t0kent3st@example.com","agent":{"id":1,"nickname":"testAgent"},"googleId":"","fireBaseToken":"","avatar":""},'
            .'{"id":2,"roles":["ROLE_ADMIN","ROLE_USER"],"email":"t0kent3stAdmin@example.com","agent":{"id":2,"nickname":"testAgent2"},"googleId":"","fireBaseToken":"","avatar":""}'
            .']';

        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent()
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testMeFail(): void
    {
        self::createClient()->request('GET', '/api/users/me');
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
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

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $result = json_decode(
            $response->getContent(),
            false,
            512,
            JSON_THROW_ON_ERROR
        );
        $expected = '{"id":1,"agent":"\/api/agents\/1"}';

        self::assertEquals(1, $result->id);
        self::assertEquals('/api/agents/1', $result->agent);
        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent()
        );
    }
}
