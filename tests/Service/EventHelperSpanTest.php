<?php

namespace App\Tests\Service;

use App\Entity\Challenge;
use App\Entity\Event;
use App\Service\EventHelper;
use Doctrine\ORM\EntityManager;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use UnexpectedValueException;

class EventHelperSpanTest extends KernelTestCase
{
    use RecreateDatabaseTrait;

    private EventHelper $eventHelper;
    private EntityManager $em;

    public function setUp(): void
    {
        self::bootKernel();
        $this->em = self::$container->get('doctrine.orm.entity_manager');

        $event = (new Event())
            ->setName('Test')
            ->setDateStart((new \DateTime('now'))->modify('-1 day'))
            ->setDateEnd((new \DateTime('now'))->modify('-1 day'));
        $this->em->persist($event);

        $event = (new Event())
            ->setName('Test')
            ->setDateStart(new \DateTime('now'))
            ->setDateEnd(new \DateTime('now'));
        $this->em->persist($event);

        $this->em->flush();

        $this->eventHelper = new EventHelper(
            $this->em->getRepository(Event::class),
            $this->em->getRepository(Challenge::class),
            'UTC'
        );
    }

    public function testGetEventsInSpanInvalid(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'Unknown span (must be: past, present or future)'
        );
        $this->eventHelper->getEventsInSpan('test');
    }

    public function testGetEventsInSpanPast(): void
    {
        $result = $this->eventHelper->getEventsInSpan('past');

        self::assertIsArray($result);
        self::assertCount(1, $result);
    }

    public function testGetEventsInSpanPresent(): void
    {
        $result = $this->eventHelper->getEventsInSpan('present');

        self::assertIsArray($result);
        self::assertCount(1, $result);
    }

    public function testGetEventsInSpanFuture(): void
    {
        $result = $this->eventHelper->getEventsInSpan('future');

        self::assertIsArray($result);
        self::assertCount(1, $result);
    }
}
