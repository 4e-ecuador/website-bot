<?php

namespace App\Tests\Service;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Exception\InvalidCsvException;
use App\Exception\StatsAlreadyAddedException;
use App\Exception\StatsNotAllException;
use App\Service\StatsImporter;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StatsImporterTest extends KernelTestCase
{
    private StatsImporter $importer;

    private Agent $agent;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->importer = self::getContainer()->get(StatsImporter::class);

        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        /** @var Agent $agent */
        $agent = $em->getRepository(Agent::class)->findOneBy(['nickname' => 'testAgent']);
        $this->agent = $agent;
    }

    /**
     * @param array<string, string|int> $replacements
     */
    private function buildCsv(array $replacements = []): string
    {
        $csv = "Time Span\tAgent Name\tAgent Faction\tDate (yyyy-mm-dd)\tTime (hh:mm:ss)\tLevel\tLifetime AP\tCurrent AP\tUnique Portals Visited\tUnique Portals Drone Visited\tFurthest Drone Distance\tPortals Discovered\tSeer Points\tXM Collected\tOPR Agreements\tDistance Walked\tResonators Deployed\tLinks Created\tControl Fields Created\tMind Units Captured\tLongest Link Ever Created\tLargest Control Field\tXM Recharged\tPortals Captured\tUnique Portals Captured\tMods Deployed\tResonators Destroyed\tPortals Neutralized\tEnemy Links Destroyed\tEnemy Fields Destroyed\tMax Time Portal Held\tMax Time Link Maintained\tMax Link Length x Days\tMax Time Field Held\tLargest Field MUs x Days\tUnique Missions Completed\tHacks\tDrone Hacks\tGlyph Hack Points\tLongest Hacking Streak\tAgents Successfully Recruited\tMission Day(s) Attended\tNL-1331 Meetup(s) Attended\tFirst Saturday Events\tRecursions\n"
            ."{span}\t{agent}\tEnlightened\t{date}\t{time}\t{level}\t{ap}\t1000\t1\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t{recursions}";

        $dateTime = new DateTime('1996-03-10 08:00:00');
        $vars = [
            'span'       => 'GESAMT',
            'agent'      => 'testAgent',
            'date'       => $dateTime->format('Y-m-d'),
            'time'       => $dateTime->format('H:i:s'),
            'level'      => 5,
            'ap'         => 250000,
            'recursions' => 0,
        ];

        foreach (array_merge($vars, $replacements) as $key => $var) {
            $csv = str_replace('{'.$key.'}', (string)$var, $csv);
        }

        return $csv;
    }

    public function testCreateEntryFromValidCsvReturnsAgentStat(): void
    {
        $stat = $this->importer->createEntryFromCsv(
            $this->agent,
            $this->buildCsv()
        );

        self::assertInstanceOf(AgentStat::class, $stat);
        self::assertSame(250000, $stat->getAp());
        self::assertSame(5, $stat->getLevel());
        self::assertSame($this->agent, $stat->getAgent());
        self::assertSame('1996-03-10', $stat->getDatetime()->format('Y-m-d'));
    }

    public function testCreateEntryThrowsOnInvalidCsv(): void
    {
        $this->expectException(InvalidCsvException::class);

        $this->importer->createEntryFromCsv($this->agent, 'not valid csv');
    }

    public function testCreateEntryThrowsWhenNotAllTime(): void
    {
        $this->expectException(StatsNotAllException::class);

        $this->importer->createEntryFromCsv(
            $this->agent,
            $this->buildCsv(['span' => 'DAILY'])
        );
    }

    public function testCreateEntryThrowsOnDuplicate(): void
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');

        $stat = $this->importer->createEntryFromCsv(
            $this->agent,
            $this->buildCsv(['date' => '1996-04-01'])
        );
        $em->persist($stat);
        $em->flush();

        $this->expectException(StatsAlreadyAddedException::class);

        $this->importer->createEntryFromCsv(
            $this->agent,
            $this->buildCsv(['date' => '1996-04-01'])
        );
    }

    public function testGetImportResultForFirstEntryReturnsCurrents(): void
    {
        $stat = $this->importer->createEntryFromCsv(
            $this->agent,
            $this->buildCsv()
        );

        $result = $this->importer->getImportResult($stat);

        self::assertNotEmpty($result->currents);
        self::assertEmpty($result->diff);
        self::assertEmpty($result->medalUps);
    }

    public function testGetImportResultWithPreviousEntryReturnsDiff(): void
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');

        $first = $this->importer->createEntryFromCsv(
            $this->agent,
            $this->buildCsv(['date' => '1996-05-01', 'ap' => 100000])
        );
        $em->persist($first);
        $em->flush();

        $second = $this->importer->createEntryFromCsv(
            $this->agent,
            $this->buildCsv(['date' => '1996-05-02', 'ap' => 110000])
        );

        $result = $this->importer->getImportResult($second, $first);

        self::assertNotEmpty($result->diff);
        self::assertSame(10000, $result->diff['ap']);
        self::assertEmpty($result->currents);
    }
}
