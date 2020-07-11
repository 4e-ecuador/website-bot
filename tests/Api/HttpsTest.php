<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class HttpsTest extends ApiTestCase
{
    use RecreateDatabaseTrait;

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
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

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertJsonStringEqualsJsonString($expected, $response->getContent(false));
    }

    /**
     * @throws TransportExceptionInterface
     */
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

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
