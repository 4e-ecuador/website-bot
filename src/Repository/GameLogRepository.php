<?php

namespace App\Repository;

use App\Entity\GameLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GameLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method GameLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method GameLog[]    findAll()
 * @method GameLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameLog::class);
    }

    // /**
    //  * @return GameLog[] Returns an array of GameLog objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GameLog
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
