<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use DateTime;

class AgentStatResourceBase extends ApiTestCase
{
    protected array $headers
        = [
            'Content-type' => 'application/json',
            'Accept'       => 'application/json',
            'X-AUTH-TOKEN' => 'T3stT0ken',
        ];

    protected function switchCsv(array $replacements = [])
    {
        $csv = "Time Span	Agent Name	Agent Faction	Date (yyyy-mm-dd)	Time (hh:mm:ss)	Level	Lifetime AP	Current AP	Unique Portals Visited	Unique Portals Drone Visited	Furthest Drone Distance	Portals Discovered	Seer Points	XM Collected	OPR Agreements	Distance Walked	Resonators Deployed	Links Created	Control Fields Created	Mind Units Captured	Longest Link Ever Created	Largest Control Field	XM Recharged	Portals Captured	Unique Portals Captured	Mods Deployed	Resonators Destroyed	Portals Neutralized	Enemy Links Destroyed	Enemy Fields Destroyed	Max Time Portal Held	Max Time Link Maintained	Max Link Length x Days	Max Time Field Held	Largest Field MUs x Days	Unique Missions Completed	Hacks	Drone Hacks	Glyph Hack Points	Longest Hacking Streak	Agents Successfully Recruited	Mission Day(s) Attended	NL-1331 Meetup(s) Attended	First Saturday Events	Recursions	  
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
