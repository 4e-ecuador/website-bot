<?php

namespace App\Tests\Repository;

use App\Entity\IngressEvent;
use App\Helper\Paginator\PaginatorOptions;
use App\Repository\IngressEventRepository;
use DateTime;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IngressEventRepositoryTest extends KernelTestCase
{
    private IngressEventRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $this->repository = $em->getRepository(IngressEvent::class);

        // Add typed future events for filtering tests
        $fs = new IngressEvent();
        $fs->setName('Future FS')
            ->setType('fs')
            ->setDateStart(new DateTime('+1 month'))
            ->setDateEnd(new DateTime('+1 month'));

        $md = new IngressEvent();
        $md->setName('Future MD')
            ->setType('md')
            ->setDateStart(new DateTime('+1 month'))
            ->setDateEnd(new DateTime('+1 month'));

        $em->persist($fs);
        $em->persist($md);
        $em->flush();
    }

    public function testFindFutureEventsReturnsResults(): void
    {
        $results = $this->repository->findFutureEvents();

        self::assertNotEmpty($results);
        self::assertContainsOnlyInstancesOf(IngressEvent::class, $results);
    }

    public function testFindFutureFSReturnsResults(): void
    {
        $results = $this->repository->findFutureFS();

        self::assertNotEmpty($results);
        foreach ($results as $event) {
            self::assertSame('fs', $event->getType());
        }
    }

    public function testFindFutureMDReturnsResults(): void
    {
        $results = $this->repository->findFutureMD();

        self::assertNotEmpty($results);
        foreach ($results as $event) {
            self::assertSame('md', $event->getType());
        }
    }

    public function testFindAllByDateReturnsAll(): void
    {
        $results = $this->repository->findAllByDate();

        self::assertNotEmpty($results);
        self::assertContainsOnlyInstancesOf(IngressEvent::class, $results);
    }

    public function testGetPaginatedListReturnsPaginator(): void
    {
        $options = new PaginatorOptions();
        $options->setOrder('id');
        $options->setOrderDir('ASC');

        $paginator = $this->repository->getPaginatedList($options);

        self::assertInstanceOf(Paginator::class, $paginator);
        self::assertGreaterThan(0, count($paginator));
    }

    public function testFindBetweenReturnsEventsInRange(): void
    {
        $start = new DateTime('-1 day');
        $end = new DateTime('+2 months');

        $results = $this->repository->findBetween($start, $end);

        self::assertNotEmpty($results);
        self::assertContainsOnlyInstancesOf(IngressEvent::class, $results);
    }

    public function testFindBetweenReturnsEmptyOutsideRange(): void
    {
        $start = new DateTime('2000-01-01');
        $end = new DateTime('2000-01-02');

        $results = $this->repository->findBetween($start, $end);

        self::assertEmpty($results);
    }
}
