<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return array<Event>
     */
    public function findBetween(
        \DateTimeInterface $start,
        \DateTimeInterface $end
    ): array {
        return $this
            ->createQueryBuilder('event')
            ->where(
                'event.date_start BETWEEN :start and :end OR event.date_end BETWEEN :start and :end'
            )
            ->setParameter('start', $start->format('Y-m-d H:i:s'))
            ->setParameter('end', $end->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();
    }
}
