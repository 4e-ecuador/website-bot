<?php

namespace App\Tests\Service;

use App\Entity\Challenge;
use App\Entity\Event;
use App\Service\EventHelper;
use DateTime;
use Exception;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use UnexpectedValueException;

class EventHelperSpanTest extends KernelTestCase
{
    private EventHelper $eventHelper;

    public function setUp(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $tz = new \DateTimeZone('UTC');

        $event = (new Event())
            ->setName('Test')
            ->setDateStart((new DateTime('now', $tz))->modify('-1 day'))
            ->setDateEnd((new DateTime('now', $tz))->modify('-1 day'));
        $em->persist($event);

        $event = (new Event())
            ->setName('Test')
            ->setDateStart(new DateTime('now', $tz))
            ->setDateEnd(new DateTime('now', $tz));
        $em->persist($event);

        $em->flush();

        $this->eventHelper = new EventHelper(
            $em->getRepository(Event::class),
            $em->getRepository(Challenge::class),
            'UTC'
        );
    }

    /**
     * @throws Exception
     */
    public function testGetEventsInSpanInvalid(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'Unknown span (must be: past, present or future)'
        );
        $this->eventHelper->getEventsInSpan('test');
    }

    /**
     * @throws Exception
     */
    public function testGetEventsInSpanPast(): void
    {
        $result = $this->eventHelper->getEventsInSpan('past');

        self::assertIsArray($result);
        self::assertCount(1, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetEventsInSpanPresent(): void
    {
        $result = $this->eventHelper->getEventsInSpan('present');

        self::assertIsArray($result);
        self::assertCount(2, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetEventsInSpanFuture(): void
    {
        $result = $this->eventHelper->getEventsInSpan('future');

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }
}
