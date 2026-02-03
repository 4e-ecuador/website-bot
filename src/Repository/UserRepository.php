<?php

namespace App\Repository;

use App\Entity\Agent;
use App\Entity\User;
use App\Helper\Paginator\PaginatorOptions;
use App\Helper\Paginator\PaginatorRepoTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    use PaginatorRepoTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return Paginator<User>
     */
    public function getPaginatedList(PaginatorOptions $paginatorOptions
    ): Paginator {
        $query = $this->createQueryBuilder('a');

        $query->orderBy(
            'a.'.$paginatorOptions->getOrder(),
            $paginatorOptions->getOrderDir()
        );

        if ($paginatorOptions->searchCriteria('email') !== ''
            && $paginatorOptions->searchCriteria('email') !== '0'
        ) {
            $query->andWhere('LOWER(a.email) LIKE LOWER(:email)')
                ->setParameter(
                    'email',
                    '%'.$paginatorOptions->searchCriteria('email').'%'
                );
        }

        if ($paginatorOptions->searchCriteria('roles') !== ''
            && $paginatorOptions->searchCriteria('roles') !== '0'
        ) {
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

    /**
     * @return User[]
     */
    public function getFireBaseUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.fireBaseToken IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findByAgent(Agent $agentId): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.agent = :val')
            ->setParameter('val', $agentId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
