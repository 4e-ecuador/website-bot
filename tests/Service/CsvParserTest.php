<?php

namespace App\Tests\Service;

use App\Exception\InvalidCsvException;
use App\Exception\StatsNotAllException;
use App\Service\CsvParser;
use App\Service\MedalChecker;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use UnexpectedValueException;

class CsvParserTest extends KernelTestCase
{
    private CsvParser $csvParser;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $medalChecker = new MedalChecker(
            self::getContainer()->get('translator'),
            $kernel->getProjectDir(),
            'test'
        );

        $this->csvParser = new CsvParser($medalChecker);
    }

    /**
     * @throws StatsNotAllException
     * @throws InvalidCsvException
     */
    public function testParseUnknownType(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('unknown CSV type');
        $this->csvParser->parse('', 'INVALID');
    }

    /**
     * @throws InvalidCsvException
     * @throws StatsNotAllException
     */
    public function testParseNotAll(): void
    {
        $this->expectException(StatsNotAllException::class);
        $this->expectExceptionMessage('Prime stats not ALL');
        $this->csvParser->parse($this->switchCsv(['span' => 'TEST']));
    }

    /**
     * @throws InvalidCsvException
     * @throws StatsNotAllException
     */
    public function testParseInvalid(): void
    {
        $this->expectException(InvalidCsvException::class);
        $this->expectExceptionMessage('Invalid CSV');
        $this->csvParser->parse('test');
    }

    /**
     * @throws InvalidCsvException
     * @throws StatsNotAllException
     */
    public function testParse(): void
    {
        $response = $this->csvParser->parse($this->switchCsv());
        self::assertNotEmpty($response);
    }

    public function testParseAllTimeSpanSiempre(): void
    {
        $result = $this->csvParser->parse($this->switchCsv(['span' => 'SIEMPRE']));

        self::assertNotEmpty($result);
    }

    public function testParseAllTimeSpanAllTime(): void
    {
        $result = $this->csvParser->parse($this->switchCsv(['span' => 'ALL TIME']));

        self::assertNotEmpty($result);
    }

    public function testParseAgentStatsCsv(): void
    {
        $csv = "datetime\tap\tlevel\n2026-01-01 10:00:00\t100000\t5";

        $result = $this->csvParser->parse($csv, 'agentstats');

        self::assertNotEmpty($result);
        self::assertArrayHasKey('2026-01-01 10:00:00', $result);
    }

    public function testParseAgentStatsCsvInvalidThrows(): void
    {
        $this->expectException(InvalidCsvException::class);

        $this->csvParser->parse('only-a-header', 'agentstats');
    }

    /**
     * @param array<string, string> $replacements
     */
    protected function switchCsv(array $replacements = []): string
    {
        $csv = "Time Span\tAgent Name\tAgent Faction\tDate (yyyy-mm-dd)\tTime (hh:mm:ss)\tLevel\tLifetime AP\tCurrent AP\tUnique Portals Visited\tUnique Portals Drone Visited\tFurthest Drone Distance\tPortals Discovered\tSeer Points\tXM Collected\tOPR Agreements\tDistance Walked\tResonators Deployed\tLinks Created\tControl Fields Created\tMind Units Captured\tLongest Link Ever Created\tLargest Control Field\tXM Recharged\tPortals Captured\tUnique Portals Captured\tMods Deployed\tResonators Destroyed\tPortals Neutralized\tEnemy Links Destroyed\tEnemy Fields Destroyed\tMax Time Portal Held\tMax Time Link Maintained\tMax Link Length x Days\tMax Time Field Held\tLargest Field MUs x Days\tUnique Missions Completed\tHacks\tDrone Hacks\tGlyph Hack Points\tLongest Hacking Streak\tAgents Successfully Recruited\tMission Day(s) Attended\tNL-1331 Meetup(s) Attended\tFirst Saturday Events\tRecursions\n"
            ."{span}\t{agent}\t{faction}\t{date}\t{time}\t{level}\t{ap}\t1000\t{explorer}\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t{recursions}";

        $dateTime = new DateTime('1999-11-11 11:11:11');
        $vars = [
            'span'       => 'GESAMT',
            'agent'      => 'testAgent',
            'faction'    => 'Enlightened',
            'date'       => $dateTime->format('Y-m-d'),
            'time'       => $dateTime->format('h:i:s'),
            'level'      => 1,
            'ap'         => 1,
            'explorer'   => 1,
            'recursions' => 0,
        ];

        foreach ($vars as $key => $var) {
            if (array_key_exists($key, $replacements)) {
                $csv = str_replace('{'.$key.'}', $replacements[$key], $csv);
            } else {
                $csv = str_replace('{'.$key.'}', (string)$var, $csv);
            }
        }

        return $csv;
    }
}
