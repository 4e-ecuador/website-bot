<?php

namespace App\Repository;

use App\Entity\MapGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MapGroup>
 */
class MapGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MapGroup::class);
    }

    /**
     * @return array<string>
     */
    public function getNames(): array
    {
        return $this->createQueryBuilder('m')
            ->select('m.name')
            ->getQuery()
            ->getArrayResult();
    }
}
