<?php

namespace App\Repository;

use App\Entity\TestStat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TestStat|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestStat|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestStat[]    findAll()
 * @method TestStat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestStatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestStat::class);
    }
}
