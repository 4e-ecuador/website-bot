<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class AgentStatResourceTest extends ApiTestCase
{
    public function testCollectionFail(): void
    {
        $client = self::createClient();

        $client->request(
            'GET',
            '/api/agent_stats',
            ['headers' => ['accept' => 'application/json']]
        );
        self::assertResponseStatusCodeSame(302);
    }

    public function testItemFail(): void
    {
        $client = self::createClient();

        $client->request(
            'GET',
            '/api/agent_stats/1',
            ['headers' => ['accept' => 'application/json']]
        );
        self::assertResponseStatusCodeSame(302);
    }
}
