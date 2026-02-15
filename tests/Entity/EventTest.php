<?php

namespace App\Tests\Entity;

use App\Entity\Event;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $event = new Event();

        self::assertNull($event->getId());
        self::assertSame('', $event->getName());
        self::assertNull($event->getDateStart());
        self::assertNull($event->getDateEnd());
        self::assertNull($event->getEventType());
        self::assertNull($event->getRecurring());
    }

    public function testSettersAndGetters(): void
    {
        $event = new Event();
        $start = new \DateTime('2025-06-01');
        $end = new \DateTime('2025-06-02');

        $event->setName('Farm Night')
            ->setDateStart($start)
            ->setDateEnd($end)
            ->setEventType('farming')
            ->setRecurring('weekly');

        self::assertSame('Farm Night', $event->getName());
        self::assertSame($start, $event->getDateStart());
        self::assertSame($end, $event->getDateEnd());
        self::assertSame('farming', $event->getEventType());
        self::assertSame('weekly', $event->getRecurring());
    }

    public function testFluentSetters(): void
    {
        $event = new Event();

        $result = $event
            ->setName('Test')
            ->setDateStart(new \DateTime())
            ->setDateEnd(new \DateTime())
            ->setEventType('type')
            ->setRecurring('monthly');

        self::assertSame($event, $result);
    }
}
