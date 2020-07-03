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
            '/api/stats',
            ['headers' => ['accept' => 'application/json']]
        );
        self::assertResponseStatusCodeSame(302);
    }

    public function testCollection(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://127.0.0.1']);

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

        $result = json_decode($response->getContent(), false);

        self::assertResponseStatusCodeSame(200);
        self::assertCount(1, $result);
        self::assertEquals('/api/agents/1', $result[0]->agent);
    }

    public function testItemFail(): void
    {
        $client = self::createClient();

        $client->request(
            'GET',
            '/api/stats/1',
            [
                'headers' => [
                    'accept' => 'application/json',
                ],
            ]
        );
        self::assertResponseStatusCodeSame(302);
    }

    public function testItem(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://127.0.0.1']);

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

        $result = json_decode($response->getContent(), false);

        self::assertResponseStatusCodeSame(200);
        self::assertEquals('/api/agents/1', $result->agent);
    }

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
        $client = self::createClient([], ['base_uri' => 'https://127.0.0.1']);

        $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => [
                    'accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
            ]
        );

        self::assertResponseStatusCodeSame(415);
    }

    public function testPostCsvFailMissingData(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://127.0.0.1']);

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => [
                    'Content-type' => 'application/json',
                    'Accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
            ]
        );

        $result = json_decode($response->getContent(false), false);

        self::assertResponseStatusCodeSame(400);
        self::assertEquals('Syntax error', $result->detail);
    }

    public function testPostCsvFailInvalidCsv(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://127.0.0.1']);

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => [
                    'Content-type' => 'application/json',
                    'Accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
                'json'    => [],
            ]
        );

        $result = json_decode($response->getContent(false), false);

        self::assertResponseStatusCodeSame(400);
        self::assertEquals('Invalid CSV', $result->error);
    }

    public function testPostCsv(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://127.0.0.1']);

        $testCsv = "Time Span	Agent Name	Agent Faction	Date (yyyy-mm-dd)	Time (hh:mm:ss)	Level	Lifetime AP	Current AP	Unique Portals Visited	Unique Portals Drone Visited	Furthest Drone Flight Distance	Portals Discovered	Seer Points	XM Collected	OPR Agreements	Distance Walked	Resonators Deployed	Links Created	Control Fields Created	Mind Units Captured	Longest Link Ever Created	Largest Control Field	XM Recharged	Portals Captured	Unique Portals Captured	Mods Deployed	Resonators Destroyed	Portals Neutralized	Enemy Links Destroyed	Enemy Fields Destroyed	Max Time Portal Held	Max Time Link Maintained	Max Link Length x Days	Max Time Field Held	Largest Field MUs x Days	Unique Missions Completed	Hacks	Drone Hacks	Glyph Hack Points	Longest Hacking Streak	Agents Successfully Recruited	Mission Day(s) Attended	NL-1331 Meetup(s) Attended	First Saturday Events	
GESAMT	nikp3h	Enlightened	2020-07-01	21:03:24	16	45806023	45806023	5175	48	1	77	10	127945574	1093	1341	54735	14635	8937	4452558	1201	195376	79618622	7986	3616	7995	24542	4286	4591	2287	430	270	17474	223	3722704	288	31857	84	123516	719	2	5	2	11";

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => [
                    'Content-type' => 'application/json',
                    'Accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
                'json'    => [
                    'csv' => $testCsv,
                ],
            ]
        );

        $result = json_decode($response->getContent(false), false);

        self::assertResponseStatusCodeSame(200);
        self::assertCount(22, (array)$result->result->currents);
    }

    public function testPostCsvDouble(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://127.0.0.1']);

        $testCsv = "Time Span	Agent Name	Agent Faction	Date (yyyy-mm-dd)	Time (hh:mm:ss)	Level	Lifetime AP	Current AP	Unique Portals Visited	Unique Portals Drone Visited	Furthest Drone Flight Distance	Portals Discovered	Seer Points	XM Collected	OPR Agreements	Distance Walked	Resonators Deployed	Links Created	Control Fields Created	Mind Units Captured	Longest Link Ever Created	Largest Control Field	XM Recharged	Portals Captured	Unique Portals Captured	Mods Deployed	Resonators Destroyed	Portals Neutralized	Enemy Links Destroyed	Enemy Fields Destroyed	Max Time Portal Held	Max Time Link Maintained	Max Link Length x Days	Max Time Field Held	Largest Field MUs x Days	Unique Missions Completed	Hacks	Drone Hacks	Glyph Hack Points	Longest Hacking Streak	Agents Successfully Recruited	Mission Day(s) Attended	NL-1331 Meetup(s) Attended	First Saturday Events	
GESAMT	nikp3h	Enlightened	2020-07-01	21:03:24	16	45806023	45806023	5175	48	1	77	10	127945574	1093	1341	54735	14635	8937	4452558	1201	195376	79618622	7986	3616	7995	24542	4286	4591	2287	430	270	17474	223	3722704	288	31857	84	123516	719	2	5	2	11";

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => [
                    'Content-type' => 'application/json',
                    'Accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
                'json'    => [
                    'csv' => $testCsv,
                ],
            ]
        );

        $result = json_decode($response->getContent(false), false);

        self::assertResponseStatusCodeSame(400);
        self::assertEquals(
            'Las estadisticas ya se han subido anteriormente!',
            $result->error
        );
    }
}
