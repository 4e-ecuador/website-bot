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

    // /**
    //  * @return TestStat[] Returns an array of TestStat objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TestStat
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
