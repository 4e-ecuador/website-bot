<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AgentResourceTest extends ApiTestCase
{
    use RecreateDatabaseTrait;

    private string $url = '/api/agents';

    /**
     * @throws TransportExceptionInterface
     */
    public function testCollectionFail(): void
    {
        $client = self::createClient();

        $client->request(
            'GET',
            $this->url,
            ['headers' => ['accept' => 'application/json']]
        );
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testCollection(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);
        $response = $client->request(
            'GET',
            $this->url,
            [
                'headers' => [
                    'accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
            ]
        );

        $expected = '['
            .'{"id":1,"nickname":"testAgent","realName":"","lat":0,"lon":0,"faction":{"name":"enl"},"custom_medals":"","telegram_name":""},'
            .'{"id":2,"nickname":"testAgent2","realName":"","lat":0,"lon":0,"faction":{"name":"enl"},"custom_medals":"","telegram_name":""}'
            .']';
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testItemFail(): void
    {
        self::createClient()->request('GET', $this->url.'/1');
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testItem(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);

        $response = $client->request(
            'GET',
            $this->url.'/1',
            [
                'headers' => [
                    'accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
            ]
        );

        $expected = '{"id":1,"nickname":"testAgent","realName":"","lat":0,"lon":0,"faction":{"name":"enl"},"custom_medals":"","telegram_name":""}';
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertJsonStringEqualsJsonString($expected, $response->getContent());
    }
}
