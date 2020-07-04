<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;

class AgentStatResourceTest extends ApiTestCase
{
    use RecreateDatabaseTrait;

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

        $expected = '[{"csv":"","id":1,"datetime":"2112-12-21T00:00:00+00:00","agent":"\/api\/agents\/2","ap":1221,"explorer":null,"recon":null,"seer":null,"trekker":null,"builder":null,"connector":null,"mindController":null,"illuminator":null,"recharger":null,"liberator":null,"pioneer":null,"engineer":null,"purifier":null,"specops":null,"hacker":null,"translator":null,"sojourner":null,"recruiter":null,"missionday":null,"nl1331Meetups":null,"ifs":null,"currentChallenge":null,"level":null,"scout":null,"longestLink":null,"largestField":null,"recursions":null,"faction":"","nickname":"","droneFlightDistance":null,"droneHacks":null,"dronePortalsVisited":null}]';

        self::assertResponseStatusCodeSame(200);
        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent()
        );
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

        $expected = '{"csv":"","id":1,"datetime":"2112-12-21T00:00:00+00:00","agent":"\/api\/agents\/2","ap":1221,"explorer":null,"recon":null,"seer":null,"trekker":null,"builder":null,"connector":null,"mindController":null,"illuminator":null,"recharger":null,"liberator":null,"pioneer":null,"engineer":null,"purifier":null,"specops":null,"hacker":null,"translator":null,"sojourner":null,"recruiter":null,"missionday":null,"nl1331Meetups":null,"ifs":null,"currentChallenge":null,"level":null,"scout":null,"longestLink":null,"largestField":null,"recursions":null,"faction":"","nickname":"","droneFlightDistance":null,"droneHacks":null,"dronePortalsVisited":null}';

        self::assertResponseStatusCodeSame(200);
        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent()
        );
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
        $client = self::createClient([], ['base_uri' => 'https://example.com']);

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
                'headers' => [
                    'Content-type' => 'application/json',
                    'Accept'       => 'application/json',
                    'X-AUTH-TOKEN' => 'T3stT0ken',
                ],
                'json'    => [
                    'csv' => $this->switchCsv(['span' => 'INVALID']),
                ],
            ]
        );

        $expected = '{"error":"Prime stats not ALL"}';

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent(false)
        );
    }

    public function testPostCsvFirstImport(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);

        print_r($this->switchCsv());
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
                    'csv' => $this->switchCsv(),
                ],
            ]
        );

        $expected = '{"result":{"currents":{"explorer":0,"recon":0,"trekker":0,"builder":0,"connector":0,"mind-controller":0,"engineer":0,"illuminator":0,"recharger":0,"liberator":0,"pioneer":0,"purifier":0,"specops":0,"missionday":0,"nl-1331-meetups":0,"hacker":0,"translator":0,"sojourner":0,"ifs":0,"scout":0},"diff":[],"medalUps":[],"newLevel":0,"recursions":0}}';

        self::assertResponseStatusCodeSame(200);
        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent(false)
        );
    }

    public function testPostCsvDouble(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);

        // @TODO not working :(
        // $translator = self::bootKernel()->getContainer()->get('translator');
        // $translator->setLocale('en');

        $content = [
            'headers' => [
                'Content-type' => 'application/json',
                'Accept'       => 'application/json',
                'X-AUTH-TOKEN' => 'T3stT0ken',
            ],
            'json'    => [
                'csv' => $this->switchCsv(),
            ],
        ];

        $client
            ->request('POST', '/api/stats/csv', $content);
        $response = $client
            ->request('POST', '/api/stats/csv', $content);

        // @TODO localizeme....
        // $expected = '{"error":"Las estadisticas ya se han subido anteriormente!"}';
        $expected = '{"error":"Stat entry already added!"}';

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent(false)
        );
    }

    public function testPostCsvFirstStats(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);
        $headers = [
            'Content-type' => 'application/json',
            'Accept'       => 'application/json',
            'X-AUTH-TOKEN' => 'T3stT0ken',
        ];

        $dateTime1 = new \DateTime('1999-11-11 11:11:11');
        $dateTime2 = new \DateTime('1999-11-11 11:11:12');

        $vars1 = [
            'date' => $dateTime1->format('Y-m-d'),
            'time' => $dateTime1->format('h:i:s'),
        ];
        $vars2 = [
            'date' => $dateTime2->format('Y-m-d'),
            'time' => $dateTime2->format('h:i:s'),
            'ap'   => 2,
        ];

        $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => $headers,
                'json'    => ['csv' => $this->switchCsv($vars1)],
            ]
        );

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => $headers,
                'json'    => ['csv' => $this->switchCsv($vars2)],
            ]
        );

        self::assertResponseStatusCodeSame(200);

        $result = json_decode($response->getContent(false), false);

        $expected = '{"result":{"currents":[],"diff":{"ap":1},"medalUps":[],"newLevel":0,"recursions":0}}';
        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent(false)
        );

        self::assertObjectHasAttribute('result', $result);
        self::assertObjectHasAttribute('diff', $result->result);
        self::assertObjectHasAttribute('ap', $result->result->diff);
        self::assertSame(1, $result->result->diff->ap);
    }

    // public function testPostCsvFactionAlert(): void
    // {
    //     $client = self::createClient([], ['base_uri' => 'https://example.com']);
    //     $headers = [
    //         'Content-type' => 'application/json',
    //         'Accept'       => 'application/json',
    //         'X-AUTH-TOKEN' => 'T3stT0ken',
    //     ];
    //     $dateTime = new \DateTime('1999-11-11 11:11:12');
    //     $vars = [
    //         'date' => $dateTime->format('Y-m-d'),
    //         'time' => $dateTime->format('h:i:s'),
    //         'faction'   => 'TEST',
    //     ];
    //
    //     $response = $client->request(
    //         'POST',
    //         '/api/stats/csv',
    //         [
    //             'headers' => $headers,
    //             'json'    => ['csv' => $this->switchCsv($vars)],
    //         ]
    //     );
    //
    //     self::assertResponseStatusCodeSame(501);
    //
    //     $expected = '{"error":"\ud83d\udea8\ud83d\udea8\ud83d\udea8** SMURF ALERT !!! **\ud83d\udea8\ud83d\udea8\ud83d\udea8\n\nWe have detected an agent with the faction: TEST\n\nAgent: testAgent\nID: 1\n\nUser: t0kent3st@example.com\nID: 1\n\nPlease verify!\n\nCC: "}';
    //     self::assertJsonStringEqualsJsonString($expected, $response->getContent(false));
    // }
    //
    // public function testPostCsvNicknameMismatch(): void
    // {
    //     $client = self::createClient([], ['base_uri' => 'https://example.com']);
    //     $headers = [
    //         'Content-type' => 'application/json',
    //         'Accept'       => 'application/json',
    //         'X-AUTH-TOKEN' => 'T3stT0ken',
    //     ];
    //     $dateTime = new \DateTime('1999-11-11 11:11:12');
    //     $vars = [
    //         'date' => $dateTime->format('Y-m-d'),
    //         'time' => $dateTime->format('h:i:s'),
    //         'agent'   => 'TESTfoo',
    //     ];
    //
    //     $response = $client->request(
    //         'POST',
    //         '/api/stats/csv',
    //         [
    //             'headers' => $headers,
    //             'json'    => ['csv' => $this->switchCsv($vars)],
    //         ]
    //     );
    //
    //     self::assertResponseStatusCodeSame(501);
    //
    //     $expected = '{"error":"\ud83d\udea8\ud83d\udea8** Nickname mismatch **\ud83d\udea8\ud83d\udea8\n\nWe have detected a different nickname in uploaded stats!\n\nNick: TESTfoo\n\nAgent: testAgent\nID: 1\n\nUser: t0kent3st@example.com\nID: 1\n\nPlease verify!\n\nCC: "}';
    //     self::assertJsonStringEqualsJsonString($expected, $response->getContent(false));
    // }

    public function testPostCsvNewMedal(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);
        $headers = [
            'Content-type' => 'application/json',
            'Accept'       => 'application/json',
            'X-AUTH-TOKEN' => 'T3stT0ken',
        ];

        $dateTime1 = new \DateTime('1999-11-11 11:11:11');
        $dateTime2 = new \DateTime('1999-11-11 11:11:12');

        $vars1 = [
            'date' => $dateTime1->format('Y-m-d'),
            'time' => $dateTime1->format('h:i:s'),
        ];
        $vars2 = [
            'date'     => $dateTime2->format('Y-m-d'),
            'time'     => $dateTime2->format('h:i:s'),
            'explorer' => 100,
        ];

        $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => $headers,
                'json'    => ['csv' => $this->switchCsv($vars1)],
            ]
        );

        self::assertResponseStatusCodeSame(200);

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => $headers,
                'json'    => ['csv' => $this->switchCsv($vars2)],
            ]
        );

        self::assertResponseStatusCodeSame(200);

        $expected = '{"result":{"currents":[],'
            .'"diff":{"explorer":99},'
            .'"medalUps":{"explorer":1},'
            .'"newLevel":0,'
            .'"recursions":0}}';

        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent(false)
        );
    }

    public function testPostCsvNewLevel(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);
        $headers = [
            'Content-type' => 'application/json',
            'Accept'       => 'application/json',
            'X-AUTH-TOKEN' => 'T3stT0ken',
        ];

        $dateTime1 = new \DateTime('1999-11-11 11:11:11');
        $dateTime2 = new \DateTime('1999-11-11 11:11:12');

        $vars1 = [
            'date' => $dateTime1->format('Y-m-d'),
            'time' => $dateTime1->format('h:i:s'),
        ];
        $vars2 = [
            'date'  => $dateTime2->format('Y-m-d'),
            'time'  => $dateTime2->format('h:i:s'),
            'level' => 2,
        ];

        $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => $headers,
                'json'    => ['csv' => $this->switchCsv($vars1)],
            ]
        );

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => $headers,
                'json'    => ['csv' => $this->switchCsv($vars2)],
            ]
        );

        self::assertResponseStatusCodeSame(200);

        $expected = '{"result":{"currents":[],'
            .'"diff":{"level":1},'
            .'"medalUps":[],'
            .'"newLevel":2,'
            .'"recursions":0}}';
        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent(false)
        );
    }

    public function testPostCsvNewLevelRecursed(): void
    {
        $client = self::createClient([], ['base_uri' => 'https://example.com']);
        $headers = [
            'Content-type' => 'application/json',
            'Accept'       => 'application/json',
            'X-AUTH-TOKEN' => 'T3stT0ken',
        ];

        $dateTime1 = new \DateTime('1999-11-11 11:11:11');
        $dateTime2 = new \DateTime('1999-11-11 11:11:12');

        $vars1 = [
            'date' => $dateTime1->format('Y-m-d'),
            'time' => $dateTime1->format('h:i:s'),
        ];
        $vars2 = [
            'date'  => $dateTime2->format('Y-m-d'),
            'time'  => $dateTime2->format('h:i:s'),
            'level' => 2,
            'recursions' => 1,
        ];

        $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => $headers,
                'json'    => ['csv' => $this->switchCsv($vars1)],
            ]
        );

        $response = $client->request(
            'POST',
            '/api/stats/csv',
            [
                'headers' => $headers,
                'json'    => ['csv' => $this->switchCsv($vars2)],
            ]
        );

        self::assertResponseStatusCodeSame(200);

        $expected = '{"result":{"currents":[],'
            .'"diff":{"level":1,"recursions": 1},'
            .'"medalUps":[],'
            .'"newLevel":2,'
            .'"recursions":1}}';
        self::assertJsonStringEqualsJsonString(
            $expected,
            $response->getContent(false)
        );
    }

    private function switchCsv(array $replacements = [])
    {
        $csv = "Time Span	Agent Name	Agent Faction	Date (yyyy-mm-dd)	Time (hh:mm:ss)	Level	Lifetime AP	Current AP	Unique Portals Visited	Unique Portals Drone Visited	Furthest Drone Flight Distance	Portals Discovered	Seer Points	XM Collected	OPR Agreements	Distance Walked	Resonators Deployed	Links Created	Control Fields Created	Mind Units Captured	Longest Link Ever Created	Largest Control Field	XM Recharged	Portals Captured	Unique Portals Captured	Mods Deployed	Resonators Destroyed	Portals Neutralized	Enemy Links Destroyed	Enemy Fields Destroyed	Max Time Portal Held	Max Time Link Maintained	Max Link Length x Days	Max Time Field Held	Largest Field MUs x Days	Unique Missions Completed	Hacks	Drone Hacks	Glyph Hack Points	Longest Hacking Streak	Agents Successfully Recruited	Mission Day(s) Attended	NL-1331 Meetup(s) Attended	First Saturday Events	Recursions	  
{span}	{agent}	{faction}	{date}	{time}	{level}	{ap}	1000	{explorer}	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	{recursions}";

        $dateTime = new \DateTime('1999-11-11 11:11:11');
        $vars = [
            'span'     => 'GESAMT',
            'agent'    => 'testAgent',
            'faction'  => 'Enlightened',
            'date'     => $dateTime->format('Y-m-d'),
            'time'     => $dateTime->format('h:i:s'),
            'level'    => 1,
            'ap'       => 1,
            'explorer' => 1,
            'recursions' => 0,
        ];

        foreach ($vars as $key => $var) {
            if (array_key_exists($key, $replacements)) {
                $csv = str_replace('{'.$key.'}', $replacements[$key], $csv);
            } else {
                $csv = str_replace('{'.$key.'}', $var, $csv);
            }
        }

        return $csv;
    }
}
