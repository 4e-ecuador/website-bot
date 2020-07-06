<?php

namespace App\Tests\Api;

use DateTime;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Message;

class AgentStatResourceMessagesTest extends AgentStatResourceBase
{
    use RecreateDatabaseTrait;

    public function testPostCsvSmurfAlert(): void
    {
        $client = $this->createClientWithMock();

        $vars = [
            'faction'   => 'TEST',
        ];

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => $this->headers,
                'json'    => ['csv' => $this->switchCsv($vars)],
            ]
        );

        self::assertResponseStatusCodeSame(200);

        $result = json_decode($response->getContent());

        self::assertCount(1, $result->result->messages);
    }

    public function testPostCsvNicknameMismatch(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);
        $mockBotApi = $this->createMock(BotApi::class);
        $mockBotApi->expects($this->once())
            ->method('sendMessage')
            ->willReturn(new Message());
        $client->getContainer()->set('app.telegrambot', $mockBotApi);

        $headers = [
            'Content-type' => 'application/json',
            'Accept'       => 'application/json',
            'X-AUTH-TOKEN' => 'T3stT0ken',
        ];
        $vars = [
            'agent'   => 'TESTfoo',
        ];

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => $headers,
                'json'    => ['csv' => $this->switchCsv($vars)],
            ]
        );

        self::assertResponseStatusCodeSame(200);

        $result = json_decode($response->getContent());

        self::assertCount(1, $result->result->messages);
    }

    public function testPostCsvNewMedal(): void
    {
        $client = $this->createClientWithMock();
        $dateTime = new DateTime('1999-11-11 11:11:12');
        $vars = [
            'date'     => $dateTime->format('Y-m-d'),
            'time'     => $dateTime->format('h:i:s'),
            'explorer' => 100,
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

        $result = json_decode($response->getContent());

        self::assertCount(1, $result->result->messages);

        $expectedDiff = '{"explorer":99}';
        $resultDiff = json_encode($result->result->diff);

        self::assertJsonStringEqualsJsonString($expectedDiff, $resultDiff);
    }

    public function testPostCsvNewLevel(): void
    {
        $client = $this->createClientWithMock();
        $dateTime = new DateTime('1999-11-11 11:11:12');
        $vars = [
            'date'  => $dateTime->format('Y-m-d'),
            'time'  => $dateTime->format('h:i:s'),
            'level' => 2,
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

        $result = json_decode($response->getContent());

        self::assertCount(1, $result->result->messages);

        $expectedDiff = '{"level":1}';
        $resultDiff = json_encode($result->result->diff);

        self::assertJsonStringEqualsJsonString($expectedDiff, $resultDiff);
    }

    public function testPostCsvNewLevelRecursed(): void
    {
        $client = $this->createClientWithMock();
        $dateTime = new DateTime('1999-11-11 11:11:12');
        $vars = [
            'date'  => $dateTime->format('Y-m-d'),
            'time'  => $dateTime->format('h:i:s'),
            'level' => 2,
            'recursions' => 1,
        ];

        $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => $this->headers,
                'json'    => ['csv' => $this->switchCsv(['recursions' => 1])],
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


        $result = json_decode($response->getContent());

        self::assertCount(1, $result->result->messages);

        $expectedDiff = '{"level":1}';
        $resultDiff = json_encode($result->result->diff);

        self::assertJsonStringEqualsJsonString($expectedDiff, $resultDiff);
    }

    public function testPostCsvRecursion(): void
    {
        $client = $this->createClientWithMock();
        $dateTime = new DateTime('1999-11-11 11:11:12');
        $vars = [
            'date'  => $dateTime->format('Y-m-d'),
            'time'  => $dateTime->format('h:i:s'),
            'recursions' => 1,
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


        $result = json_decode($response->getContent());

        self::assertCount(1, $result->result->messages);

        $expectedDiff = '{"recursions":1}';
        $resultDiff = json_encode($result->result->diff);

        self::assertJsonStringEqualsJsonString($expectedDiff, $resultDiff);
    }

    private function createClientWithMock()
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);

        $mockBotApi = $this->createMock(BotApi::class);
        $mockBotApi->expects($this->once())
            ->method('sendMessage')
            ->willReturn(new Message());
        $client->getContainer()->set('app.telegrambot', $mockBotApi);

        $client->disableReboot();

        return $client;
    }
}
