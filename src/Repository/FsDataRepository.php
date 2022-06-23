<?php

namespace App\Repository;

use App\Entity\FsData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FsData>
 *
 * @method FsData|null find($id, $lockMode = null, $lockVersion = null)
 * @method FsData|null findOneBy(array $criteria, array $orderBy = null)
 * @method FsData[]    findAll()
 * @method FsData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FsDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FsData::class);
    }

    public function add(FsData $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FsData $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return FsData[]
     */
    public function findLatest(): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }
}
