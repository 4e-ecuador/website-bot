<?php

namespace App\Repository;

use App\Entity\LoginAttempt;
use App\Helper\Paginator\PaginatorOptions;
use App\Helper\Paginator\PaginatorRepoTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LoginAttempt>
 */
class LoginAttemptRepository extends ServiceEntityRepository
{
    use PaginatorRepoTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoginAttempt::class);
    }

    /**
     * @return Paginator<LoginAttempt>
     */
    public function getPaginatedList(PaginatorOptions $options): Paginator
    {
        $query = $this->createQueryBuilder('a');

        $query->orderBy('a.'.$options->getOrder(), $options->getOrderDir());

        if ($options->searchCriteria('email') !== '') {
            $query->andWhere('a.email LIKE :email')
                ->setParameter(
                    'email',
                    '%'.$options->searchCriteria('email').'%'
                );
        }

        $query = $query->getQuery();

        return $this->paginate(
            $query,
            $options->getPage(),
            $options->getLimit()
        );
    }
}
