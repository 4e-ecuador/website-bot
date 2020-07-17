<?php

namespace App\Tests\Api;

use DateTime;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AgentStatResourcePostTest extends AgentStatResourceBase
{
    use RecreateDatabaseTrait;

    /**
     * @throws TransportExceptionInterface
     */
    public function testPostCsvFail(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => [
                    'accept' => 'application/json',
                ],
            ]
        );
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws JsonException
     */
    public function testPostCsvFailWrongMediaType(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => [
                    'accept' => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
            ]
        );

        $result = json_decode(
            $response->getContent(false),
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        self::assertEquals('An error occurred', $result->title);
        self::assertStringStartsWith(
            'The content-type "application/x-www-form-urlencoded" is not supported.',
            $result->detail
        );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws JsonException
     */
    public function testPostCsvFailMissingData(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            ['headers' => $this->headers]
        );
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $result = json_decode(
            $response->getContent(false),
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertEquals('Syntax error', $result->detail);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testPostCsvFailInvalidCsv(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => $this->headers,
                'json'    => [],
            ]
        );

        $expected = '{"error":"Invalid CSV"}';

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        self::assertJsonStringEqualsJsonString($expected, $response->getContent(false));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testPostCsvStatsNotAll(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => $this->headers,
                'json'    => [
                    'csv' => $this->switchCsv(['span' => 'INVALID']),
                ],
            ]
        );

        $expected = '{"error":"Prime stats not ALL"}';

        self::assertResponseStatusCodeSame(Response::HTTP_PRECONDITION_REQUIRED);
        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent(false)
        );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testPostCsvFirstImport(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => $this->headers,
                'json'    => [
                    'csv' => $this->switchCsv(),
                ],
            ]
        );

        $expected = '{"result":{'
            .'"currents":{"explorer":0,"recon":0,"trekker":0,"builder":0,"connector":0,"mind-controller":0,"engineer":0,"illuminator":0,"recharger":0,"liberator":0,"pioneer":0,"purifier":0,"specops":0,"missionday":0,"nl-1331-meetups":0,"hacker":0,"translator":0,"sojourner":0,"ifs":0,"scout":0,"scout-controller": 0'
            .'},"diff":[],"medalUps":[],"medalDoubles": [],"newLevel":0,"recursions":0}}';

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent(false)
        );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testPostCsvDouble(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);

        $content = [
            'headers' => $this->headers,
            'json'    => [
                'csv' => $this->switchCsv(),
            ],
        ];

        $client
            ->request('POST', '/api/stats/csv', $content);
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = $client
            ->request('POST', '/api/stats/csv', $content);
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $expected = '{"error":"Stat entry already added!"}';

        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent(false)
        );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testPostCsvFirstStats(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);

        $dateTime = new DateTime('1999-11-11 11:11:12');

        $vars = [
            'date' => $dateTime->format('Y-m-d'),
            'time' => $dateTime->format('h:i:s'),
            'ap'   => 2,
        ];

        $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => $this->headers,
                'json'    => ['csv' => $this->switchCsv()],
            ]
        );
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => $this->headers,
                'json'    => ['csv' => $this->switchCsv($vars)],
            ]
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $expected = '{"result":{"currents":[],'
            .'"diff":{"ap":1},'
            .'"medalUps":[],"medalDoubles": [],"newLevel":0,"recursions":0}}';
        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent(false)
        );
    }
}
