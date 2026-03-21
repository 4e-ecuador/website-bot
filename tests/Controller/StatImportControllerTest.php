<?php

namespace App\Tests\Controller;

use App\Entity\AgentStat;
use App\Entity\User;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class StatImportControllerTest extends WebTestCase
{
    private function getAgentUser(): User
    {
        /** @var User $user */
        $user = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy([]);

        return $user;
    }

    /**
     * @param array<string, string|int> $replacements
     */
    private function buildCsv(array $replacements = []): string
    {
        $csv = "Time Span\tAgent Name\tAgent Faction\tDate (yyyy-mm-dd)\tTime (hh:mm:ss)\tLevel\tLifetime AP\tCurrent AP\tUnique Portals Visited\tUnique Portals Drone Visited\tFurthest Drone Distance\tPortals Discovered\tSeer Points\tXM Collected\tOPR Agreements\tDistance Walked\tResonators Deployed\tLinks Created\tControl Fields Created\tMind Units Captured\tLongest Link Ever Created\tLargest Control Field\tXM Recharged\tPortals Captured\tUnique Portals Captured\tMods Deployed\tResonators Destroyed\tPortals Neutralized\tEnemy Links Destroyed\tEnemy Fields Destroyed\tMax Time Portal Held\tMax Time Link Maintained\tMax Link Length x Days\tMax Time Field Held\tLargest Field MUs x Days\tUnique Missions Completed\tHacks\tDrone Hacks\tGlyph Hack Points\tLongest Hacking Streak\tAgents Successfully Recruited\tMission Day(s) Attended\tNL-1331 Meetup(s) Attended\tFirst Saturday Events\tRecursions\n"
            ."{span}\t{agent}\tEnlightened\t{date}\t{time}\t1\t{ap}\t1000\t1\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0";

        $dateTime = new DateTime((string)($replacements['date'] ?? '1998-01-15 10:00:00'));
        $vars = [
            'span'  => 'GESAMT',
            'agent' => 'testAgent',
            'date'  => $dateTime->format('Y-m-d'),
            'time'  => $dateTime->format('H:i:s'),
            'ap'    => 500,
        ];

        foreach (array_merge($vars, $replacements) as $key => $var) {
            $csv = str_replace('{'.$key.'}', (string)$var, $csv);
        }

        return $csv;
    }

    public function testStatImportRedirectsWhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/stats/stat-import');

        self::assertResponseRedirects();
    }

    public function testStatImportGetRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAgentUser());

        $client->request(Request::METHOD_GET, '/stats/stat-import');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('textarea[name="csv"]');
    }

    public function testStatImportWithValidCsvCreatesStatEntry(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAgentUser());

        $client->request(Request::METHOD_POST, '/stats/stat-import', [
            'csv' => $this->buildCsv(['date' => '1998-01-15']),
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('.alert.alert-danger.alert-dismissible');

        $stat = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(AgentStat::class)
            ->findOneBy(['datetime' => new DateTime('1998-01-15 00:00:00')]);

        self::assertNotNull($stat);
        self::assertSame(500, $stat->getAp());
    }

    public function testStatImportWithDuplicateCsvShowsError(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAgentUser());

        $csv = $this->buildCsv(['date' => '1997-06-20']);

        $client->request(Request::METHOD_POST, '/stats/stat-import', ['csv' => $csv]);
        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('.alert.alert-danger.alert-dismissible');

        $client->request(Request::METHOD_POST, '/stats/stat-import', ['csv' => $csv]);
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.alert.alert-danger.alert-dismissible');
    }

    public function testStatImportWithInvalidCsvShowsError(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAgentUser());

        $client->request(Request::METHOD_POST, '/stats/stat-import', [
            'csv' => 'this is not valid csv data',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.alert.alert-danger.alert-dismissible');
    }

    public function testStatImportWithNotAllTimeCsvShowsError(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAgentUser());

        $client->request(Request::METHOD_POST, '/stats/stat-import', [
            'csv' => $this->buildCsv(['span' => 'DAILY']),
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.alert.alert-danger.alert-dismissible');
    }

    public function testStatImportWithEmptyCsvRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAgentUser());

        $client->request(Request::METHOD_POST, '/stats/stat-import', ['csv' => '']);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('textarea[name="csv"]');
    }
}
