<?php

namespace App\Tests\Service;

use App\Service\CalendarHelper;
use DateTime;
use PHPUnit\Framework\TestCase;

class CalendarHelperTest extends TestCase
{
    public function testGetEventsReturnsEmptyArray(): void
    {
        $helper = new CalendarHelper('America/Guayaquil');

        $events = $helper->getEvents();

        self::assertEmpty($events);
    }

    public function testGetEventsWithDateReturnsEmptyArray(): void
    {
        $helper = new CalendarHelper('America/Guayaquil');

        $events = $helper->getEvents(new DateTime());

        self::assertEmpty($events);
    }
}
