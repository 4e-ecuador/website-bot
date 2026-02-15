<?php

namespace App\Repository;

use App\Entity\Challenge;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Challenge>
 */
class ChallengeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Challenge::class);
    }

    /**
     * @return array<Challenge>
     */
    public function findCurrent(): array
    {
        $now = new DateTime();

        return $this->createQueryBuilder('c')
            ->andWhere('c.date_start <= :now')
            ->andWhere('c.date_end >= :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }
}
