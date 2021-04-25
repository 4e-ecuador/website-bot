<?php

namespace App\Tests\Api;

use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AgentStatResourceGetTest extends AgentStatResourceBase
{
    use RecreateDatabaseTrait;

    /**
     * @throws TransportExceptionInterface
     */
    public function testCollectionFail(): void
    {
        self::createClient()
            ->request('GET', '/api/stats');
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
            '/api/stats',
            [
                'headers' => [
                    'accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
            ]
        );

        $expected = '['
            .'{"csv":"","id":1,"datetime":"2112-12-21T00:00:00+00:00",'
            .'"agent":"\/api\/agents\/2","ap":1221,"explorer":null,"recon":null,"seer":null,"trekker":null,"builder":null,"connector":null,'
            .'"mindController":null,"illuminator":null,"recharger":null,"liberator":null,"pioneer":null,"engineer":null,"epoch": null,"purifier":null,"specops":null,'
            .'"hacker":null,"translator":null,"sojourner":null,"recruiter":null,"missionday":null,"monthsSubscribed": null,"nl1331Meetups":null,"ifs":null,"currentChallenge":null,'
            .'"level":null,"scout":null,"longestLink":null,"largestField":null,"recursions":null,"faction":"","nickname":"","droneFlightDistance":null,'
            .'"droneHacks":null,"dronePortalsVisited":null,"scoutController": null,"droneForcedRecalls": null,"kineticCapsulesCompleted":null}'
            .']';

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent()
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testItemFail(): void
    {
        self::createClient()->request(
            'GET',
            '/api/stats/1',
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
     */
    public function testItem(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);

        $response = $client->request(
            'GET',
            '/api/stats/1',
            [
                'headers' => [
                    'accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
            ]
        );

        $expected = '{"csv":"","id":1,"datetime":"2112-12-21T00:00:00+00:00",'
            .'"agent":"\/api\/agents\/2",'
            .'"ap":1221,"explorer":null,"recon":null,"seer":null,"trekker":null,"builder":null,"connector":null,"mindController":null,"illuminator":null,"recharger":null,'
            .'"liberator":null,"pioneer":null,"engineer":null,"epoch": null,"purifier":null,"specops":null,"hacker":null,"translator":null,"sojourner":null,"recruiter":null,"missionday":null,"monthsSubscribed": null,'
            .'"nl1331Meetups":null,"ifs":null,"currentChallenge":null,"level":null,"scout":null,"longestLink":null,"largestField":null,"recursions":null,"faction":"",'
            .'"nickname":"","droneFlightDistance":null,"droneHacks":null,"dronePortalsVisited":null,"scoutController": null,"droneForcedRecalls": null,"kineticCapsulesCompleted":null}';

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent()
        );
    }
}
