<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use DateTime;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use JsonException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class IngressEventResourceTest extends ApiTestCase
{
    use RecreateDatabaseTrait;

    /**
     * @throws JsonException
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testListing(): void
    {
        $client = self::createClient();

        $response = $client->request(
            'GET',
            '/api/ingress_events',
            ['headers' => ['accept' => 'application/json']]
        );
        self::assertResponseStatusCodeSame(200);

        $result = json_decode(
            $response->getContent(),
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertCount(1, $result);
        self::assertEquals(1, $result[0]->id);
        self::assertEquals('testEvent', $result[0]->name);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws JsonException
     */
    public function testListingWithFutureDate(): void
    {
        $client = self::createClient();

        $date = (new DateTime('2200-12-21'))
            ->format('Y-m-d');

        $response = $client->request(
            'GET',
            '/api/ingress_events?date_start[after]='.$date,
            ['headers' => ['accept' => 'application/json']]
        );
        self::assertResponseStatusCodeSame(200);

        $result = json_decode(
            $response->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        self::assertCount(0, $result);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testListingWithCurrentDate(): void
    {
        $client = self::createClient();

        $date = (new DateTime())
            ->format('Y-m-d');

        $response = $client->request(
            'GET',
            '/api/ingress_events?date_start[after]='.$date,
            ['headers' => ['accept' => 'application/json']]
        );
        self::assertResponseStatusCodeSame(200);

        $result = json_decode(
            $response->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        self::assertCount(1, $result);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testItem(): void
    {
        self::createClient()
            ->request('GET', '/api/ingress_events/1');
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testItemMissing(): void
    {
        self::createClient()
            ->request('GET', '/api/ingress_events/2');
        self::assertResponseStatusCodeSame(404);
    }
}
