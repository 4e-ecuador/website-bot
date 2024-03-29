<?php

namespace App\Repository;

use App\Entity\MapGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MapGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method MapGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method MapGroup[]    findAll()
 * @method MapGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<MapGroupRepository>
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
