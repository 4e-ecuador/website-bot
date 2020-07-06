<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use DateTime;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;

class IngressEventResourceTest extends ApiTestCase
{
    use RecreateDatabaseTrait;

    public function testListing(): void
    {
        $client = self::createClient();

        $response = $client->request(
            'GET',
            '/api/ingress_events',
            ['headers' => ['accept' => 'application/json']]
        );
        self::assertResponseStatusCodeSame(200);

        $result = json_decode($response->getContent(), false);

        self::assertCount(1, $result);
        self::assertEquals(1, $result[0]->id);
        self::assertEquals('testEvent', $result[0]->name);
    }

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

        $result = json_decode($response->getContent(), true);
        self::assertCount(0, $result);
    }

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

        $result = json_decode($response->getContent(), true);
        self::assertCount(1, $result);
    }

    public function testItem(): void
    {
        self::createClient()
            ->request('GET', '/api/ingress_events/1');
        self::assertResponseStatusCodeSame(200);
    }

    public function testItemMissing(): void
    {
        self::createClient()
            ->request('GET', '/api/ingress_events/2');
        self::assertResponseStatusCodeSame(404);
    }
}
