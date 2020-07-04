<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;

class AgentResourceTest extends ApiTestCase
{
    use RecreateDatabaseTrait;

    private string $url = '/api/agents';

    public function testCollectionFail(): void
    {
        $client = self::createClient();

        $client->request(
            'GET',
            $this->url,
            ['headers' => ['accept' => 'application/json']]
        );
        self::assertResponseStatusCodeSame(302);
    }

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
            .'{"nickname":"testAgent","realName":"","lat":0,"lon":0,"faction":{"name":"enl"},"custom_medals":"","telegram_name":""},'
            .'{"nickname":"testAgent2","realName":"","lat":0,"lon":0,"faction":{"name":"enl"},"custom_medals":"","telegram_name":""}'
            .']';
        self::assertResponseStatusCodeSame(200);
        self::assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testItemFail(): void
    {
        self::createClient()->request('GET', $this->url.'/1');
        self::assertResponseStatusCodeSame(302);
    }

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

        $expected = '{"nickname":"testAgent","realName":"","lat":0,"lon":0,"faction":{"name":"enl"},"custom_medals":"","telegram_name":""}';
        self::assertResponseStatusCodeSame(200);
        self::assertJsonStringEqualsJsonString($expected, $response->getContent());
    }
}
