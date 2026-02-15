<?php

namespace App\Tests\Entity;

use App\Entity\IngressEvent;
use PHPUnit\Framework\TestCase;

class IngressEventTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $event = new IngressEvent();

        self::assertNull($event->getId());
        self::assertSame('', $event->getName());
        self::assertSame('', $event->getType());
        self::assertNull($event->getDateStart());
        self::assertNull($event->getDateEnd());
        self::assertSame('', $event->getDescription());
        self::assertSame('', $event->getLink());
    }

    public function testSettersAndGetters(): void
    {
        $event = new IngressEvent();
        $start = new \DateTime('2025-01-01');
        $end = new \DateTime('2025-01-02');

        $event->setName('Anomaly')
            ->setType('anomaly')
            ->setDateStart($start)
            ->setDateEnd($end)
            ->setDescription('A big event')
            ->setLink('https://example.com');

        self::assertSame('Anomaly', $event->getName());
        self::assertSame('anomaly', $event->getType());
        self::assertSame($start, $event->getDateStart());
        self::assertSame($end, $event->getDateEnd());
        self::assertSame('A big event', $event->getDescription());
        self::assertSame('https://example.com', $event->getLink());
    }

    public function testJsonSerialize(): void
    {
        $event = new IngressEvent();
        $event->setName('Test Event')
            ->setLink('https://example.com');

        $json = $event->jsonSerialize();

        self::assertSame(['name' => 'Test Event', 'link' => 'https://example.com'], $json);
    }

    public function testNullableFields(): void
    {
        $event = new IngressEvent();

        $event->setDescription(null);
        self::assertNull($event->getDescription());

        $event->setLink(null);
        self::assertNull($event->getLink());
    }
}
