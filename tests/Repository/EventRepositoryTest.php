<?php

namespace App\Tests\Repository;

use App\Entity\Event;
use App\Repository\EventRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EventRepositoryTest extends KernelTestCase
{
    private EventRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $this->repository = $em->getRepository(Event::class);
    }

    public function testFindBetweenReturnsEventsInRange(): void
    {
        $start = new DateTime('-1 day');
        $end = new DateTime('+1 day');

        $results = $this->repository->findBetween($start, $end);

        self::assertNotEmpty($results);
        self::assertContainsOnlyInstancesOf(Event::class, $results);
    }

    public function testFindBetweenReturnsEmptyOutsideRange(): void
    {
        $start = new DateTime('2000-01-01');
        $end = new DateTime('2000-01-02');

        $results = $this->repository->findBetween($start, $end);

        self::assertEmpty($results);
    }
}
