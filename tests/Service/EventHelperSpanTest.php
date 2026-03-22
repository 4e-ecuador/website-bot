<?php

namespace App\Tests\Service;

use App\Entity\Challenge;
use App\Entity\Event;
use App\Service\EventHelper;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use UnexpectedValueException;

class EventHelperSpanTest extends KernelTestCase
{
    private EventHelper $eventHelper;

    protected function setUp(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $tz = new \DateTimeZone('UTC');

        // Clear all events so counts are predictable (tearDown restores fixture event)
        $em->createQuery('DELETE FROM App\Entity\Event')->execute();

        $past = new Event()
            ->setName('PastEvent')
            ->setDateStart(new DateTime('now', $tz)->modify('-2 day'))
            ->setDateEnd(new DateTime('now', $tz)->modify('-2 day'));
        $em->persist($past);

        $present = new Event()
            ->setName('PresentEvent')
            ->setDateStart(new DateTime('now', $tz))
            ->setDateEnd(new DateTime('now', $tz));
        $em->persist($present);

        $em->flush();

        $this->eventHelper = new EventHelper(
            $em->getRepository(Event::class),
            $em->getRepository(Challenge::class),
            'UTC'
        );
    }

    protected function tearDown(): void
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        // Remove test events and restore fixture event with ID=1 for other tests
        $em->createQuery("DELETE FROM App\Entity\Event e WHERE e.name IN ('PastEvent', 'PresentEvent')")->execute();
        if (!$em->getRepository(Event::class)->find(1)) {
            $conn = $em->getConnection();
            $now = new DateTime()->format('Y-m-d H:i:s');
            $conn->executeStatement(
                'INSERT INTO event (id, name, date_start, date_end) VALUES (1, :name, :ds, :de)',
                ['name' => 'test', 'ds' => $now, 'de' => $now]
            );
        }

        parent::tearDown();
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
        self::assertCount(1, $result);
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
