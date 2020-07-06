<?php

namespace App\Tests\Api;

use DateTime;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;

class AgentStatResourcePostTest extends AgentStatResourceBase
{
    use RecreateDatabaseTrait;

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
        self::assertResponseStatusCodeSame(302);
    }

    public function testPostCsvFailWrongMediaType(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => [
                    'accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
            ]
        );

        $result = json_decode($response->getContent(false), false);

        self::assertResponseStatusCodeSame(415);
        self::assertEquals('An error occurred', $result->title);
        self::assertStringStartsWith(
            'The content-type "application/x-www-form-urlencoded" is not supported.',
            $result->detail
        );
    }

    public function testPostCsvFailMissingData(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            ['headers' => $this->headers]
        );
        self::assertResponseStatusCodeSame(400);

        $result = json_decode($response->getContent(false), false);

        self::assertEquals('Syntax error', $result->detail);
    }

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

        $result = json_decode($response->getContent(false), false);

        self::assertResponseStatusCodeSame(500);
        self::assertEquals('Invalid CSV', $result->error);
    }

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

        self::assertResponseStatusCodeSame(409);
        self::assertJsonStringEqualsJsonString($expected, $response->getContent(false));
    }

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
            .'"currents":{"explorer":0,"recon":0,"trekker":0,"builder":0,"connector":0,"mind-controller":0,"engineer":0,"illuminator":0,"recharger":0,"liberator":0,"pioneer":0,"purifier":0,"specops":0,"missionday":0,"nl-1331-meetups":0,"hacker":0,"translator":0,"sojourner":0,"ifs":0,"scout":0'
            .'},"diff":[],"medalUps":[],"newLevel":0,"recursions":0,"messages":[]}}';

        self::assertResponseStatusCodeSame(200);
        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent(false)
        );
    }

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
        self::assertResponseStatusCodeSame(200);

        $response = $client
            ->request('POST', '/api/stats/csv', $content);
        self::assertResponseStatusCodeSame(409);

        $expected = '{"error":"Stat entry already added!"}';

        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent(false)
        );
    }

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
        self::assertResponseStatusCodeSame(200);

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => $this->headers,
                'json'    => ['csv' => $this->switchCsv($vars)],
            ]
        );

        self::assertResponseStatusCodeSame(200);

        $expected = '{"result":{"currents":[],'
            .'"diff":{"ap":1},'.'
            "medalUps":[],"newLevel":0,"recursions":0,"messages":[]}}';
        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent(false)
        );
    }
}
