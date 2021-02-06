<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CiteResourceTest extends ApiTestCase
{
    use RecreateDatabaseTrait;

    private string $url = '/api/cites/random';

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetRandom(): void
    {
        return;
        $client = self::createClient();

        $response = $client->request(
            'GET',
            $this->url,
            ['headers' => ['accept' => 'application/json']]
        );

        echo "\n\n".$response->getContent()."\n\n";
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
