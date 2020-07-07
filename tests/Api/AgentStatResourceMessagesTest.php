<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use DateTime;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use JsonException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Message;

class AgentStatResourceMessagesTest extends AgentStatResourceBase
{
    use RecreateDatabaseTrait;

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws JsonException
     */
    public function testPostCsvSmurfAlert(): void
    {
        $client = $this->createClientWithMock();

        $vars = [
            'faction' => 'TEST',
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

        $result = json_decode(
            $response->getContent(),
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertCount(1, $result->result->messages);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws JsonException
     */
    public function testPostCsvNicknameMismatch(): void
    {
        $client = $this->createClientWithMock();
        $vars = [
            'agent' => 'TESTfoo',
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

        $result = json_decode(
            $response->getContent(),
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertCount(1, $result->result->messages);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws JsonException
     */
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

        $result = json_decode(
            $response->getContent(),
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertCount(1, $result->result->messages);

        $expectedDiff = '{"explorer":99}';
        $resultDiff = json_encode($result->result->diff, JSON_THROW_ON_ERROR);

        self::assertJsonStringEqualsJsonString($expectedDiff, $resultDiff);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws JsonException
     */
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

        $result = json_decode(
            $response->getContent(),
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertCount(1, $result->result->messages);

        $expectedDiff = '{"level":1}';
        $resultDiff = json_encode($result->result->diff, JSON_THROW_ON_ERROR);

        self::assertJsonStringEqualsJsonString($expectedDiff, $resultDiff);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws JsonException
     */
    public function testPostCsvNewLevelRecursed(): void
    {
        $client = $this->createClientWithMock();
        $dateTime = new DateTime('1999-11-11 11:11:12');
        $vars = [
            'date'       => $dateTime->format('Y-m-d'),
            'time'       => $dateTime->format('h:i:s'),
            'level'      => 2,
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

        $result = json_decode(
            $response->getContent(),
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertCount(1, $result->result->messages);

        $expectedDiff = '{"level":1}';
        $resultDiff = json_encode($result->result->diff, JSON_THROW_ON_ERROR);

        self::assertJsonStringEqualsJsonString($expectedDiff, $resultDiff);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws JsonException
     */
    public function testPostCsvRecursion(): void
    {
        $client = $this->createClientWithMock();
        $dateTime = new DateTime('1999-11-11 11:11:12');
        $vars = [
            'date'       => $dateTime->format('Y-m-d'),
            'time'       => $dateTime->format('h:i:s'),
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

        $result = json_decode(
            $response->getContent(),
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertCount(1, $result->result->messages);

        $expectedJson = '{"recursions":1}';
        $actualJson = json_encode($result->result->diff, JSON_THROW_ON_ERROR);

        self::assertJsonStringEqualsJsonString($expectedJson, $actualJson);
    }

    private function createClientWithMock(): Client
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
