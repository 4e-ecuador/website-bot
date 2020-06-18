<?php

namespace App\Repository;

use App\Entity\User;
use App\Helper\Paginator\PaginatorOptions;
use App\Helper\Paginator\PaginatorRepoTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    use PaginatorRepoTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getPaginatedList(PaginatorOptions $paginatorOptions): Paginator
    {
        $query = $this->createQueryBuilder('a');

        $query->orderBy(
            'a.'.$paginatorOptions->getOrder(),
            $paginatorOptions->getOrderDir()
        );

        if ($paginatorOptions->searchCriteria('email')) {
            $query->andWhere('LOWER(a.email) LIKE LOWER(:email)')
                ->setParameter(
                    'email',
                    '%'.$paginatorOptions->searchCriteria('email').'%'
                );
        }

        if ($paginatorOptions->searchCriteria('roles')) {
            // var_dump($paginatorOptions->searchCriteria('roles'));
            $query->andWhere('a.roles LIKE :roles')
                ->setParameter(
                    'roles',
                    '%'.$paginatorOptions->searchCriteria('roles').'%'
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
