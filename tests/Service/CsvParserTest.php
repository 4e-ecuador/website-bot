<?php

namespace App\Tests\Service;

use App\Exception\StatsNotAllException;
use App\Service\CsvParser;
use App\Service\MedalChecker;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use UnexpectedValueException;

class CsvParserTest extends KernelTestCase
{
    private CsvParser $csvParser;

    public function setUp(): void
    {
        $kernel = self::bootKernel();
        $medalChecker = new MedalChecker(
            self::$container->get('translator'), $kernel->getProjectDir()
        );

        $this->csvParser = new CsvParser($medalChecker);
    }

    public function testParseUnknownType(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('unknown CSV type');
        $this->csvParser->parse('', 'INVALID');
    }

    public function testParseNotAll(): void
    {
        $this->expectException(StatsNotAllException::class);
        $this->expectExceptionMessage('Prime stats not ALL');
        $this->csvParser->parse($this->switchCsv(['span'=>'TEST']));
    }

    public function testParseInvalid(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid CSV');
        $this->csvParser->parse('test');
    }

    public function testParse(): void
    {
        $response = $this->csvParser->parse($this->switchCsv());
        self::assertIsArray($response);
    }


    protected function switchCsv(array $replacements = [])
    {
        $csv = "Time Span	Agent Name	Agent Faction	Date (yyyy-mm-dd)	Time (hh:mm:ss)	Level	Lifetime AP	Current AP	Unique Portals Visited	Unique Portals Drone Visited	Furthest Drone Flight Distance	Portals Discovered	Seer Points	XM Collected	OPR Agreements	Distance Walked	Resonators Deployed	Links Created	Control Fields Created	Mind Units Captured	Longest Link Ever Created	Largest Control Field	XM Recharged	Portals Captured	Unique Portals Captured	Mods Deployed	Resonators Destroyed	Portals Neutralized	Enemy Links Destroyed	Enemy Fields Destroyed	Max Time Portal Held	Max Time Link Maintained	Max Link Length x Days	Max Time Field Held	Largest Field MUs x Days	Unique Missions Completed	Hacks	Drone Hacks	Glyph Hack Points	Longest Hacking Streak	Agents Successfully Recruited	Mission Day(s) Attended	NL-1331 Meetup(s) Attended	First Saturday Events	Recursions	  
{span}	{agent}	{faction}	{date}	{time}	{level}	{ap}	1000	{explorer}	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	{recursions}";

        $dateTime = new DateTime('1999-11-11 11:11:11');
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
