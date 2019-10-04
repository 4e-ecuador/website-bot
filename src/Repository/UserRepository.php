<?php

namespace App\Repository;

use App\Entity\User;
use App\Helper\Paginator\PaginatorOptions;
use App\Helper\Paginator\PaginatorRepoTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    use PaginatorRepoTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function getPaginatedList(PaginatorOptions $paginatorOptions): Paginator
    {
        $query = $this->createQueryBuilder('a');

        $query->orderBy(
            'a.'.$paginatorOptions->getOrder(),
            $paginatorOptions->getOrderDir()
        );

        if ($paginatorOptions->searchCriteria('username')) {
            $query->andWhere('LOWER(a.username) LIKE LOWER(:username)')
                ->setParameter(
                    'username',
                    '%'.$paginatorOptions->searchCriteria('username').'%'
                );
        }

        if ($paginatorOptions->searchCriteria('email')) {
            $query->andWhere('LOWER(a.email) LIKE LOWER(:email)')
                ->setParameter(
                    'email',
                    '%'.$paginatorOptions->searchCriteria('email').'%'
                );
        }

        $query = $query->getQuery();

        return $this->paginate(
            $query,
            $paginatorOptions->getPage(),
            $paginatorOptions->getLimit()
        );
    }
}
