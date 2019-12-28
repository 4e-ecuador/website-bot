<?php

namespace App\Repository;

use App\Entity\IngressEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IngressEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method IngressEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method IngressEvent[]    findAll()
 * @method IngressEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IngressEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IngressEvent::class);
    }

    public function findFutureEvents()
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.date_start >= :val')
            ->setParameter('val', (new \DateTime())->format('Y-m-d)'))
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;

    }

    public function findFutureFS()
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.date_start >= :val')
            ->andWhere('i.type = :type')
            ->setParameter('val', (new \DateTime())->format('Y-m-d)'))
            ->setParameter('type', 'fs')
            ->orderBy('i.name', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findFutureMD()
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.date_start >= :val')
            ->andWhere('i.type = :type')
            ->setParameter('val', (new \DateTime())->format('Y-m-d)'))
            ->setParameter('type', 'md')
            ->orderBy('i.name', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }
}
