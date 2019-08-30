<?php

namespace App\Repository;

use App\Entity\Agent;
use App\Helper\Paginator\PaginatorOptions;
use App\Helper\Paginator\PaginatorRepoTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Agent|null find($id, $lockMode = null, $lockVersion = null)
 * @method Agent|null findOneBy(array $criteria, array $orderBy = null)
 * @method Agent[]    findAll()
 * @method Agent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgentRepository extends ServiceEntityRepository
{
    use PaginatorRepoTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Agent::class);
    }

    /**
     * @param PaginatorOptions $options
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getPaginatedList(PaginatorOptions $options): \Doctrine\ORM\Tools\Pagination\Paginator
    {
        $query = $this->createQueryBuilder('a');

        $query->orderBy('a.'.$options->getOrder(), $options->getOrderDir());

        if ($options->searchCriteria('nickname'))
        {
            $query->andWhere('a.nickname LIKE :nickname')
                ->setParameter('nickname', '%' . $options->searchCriteria('nickname') . '%');
        }

        if ($options->searchCriteria('realName'))
        {
            $query->andWhere('a.realName LIKE :realName')
                ->setParameter('realName', '%' . $options->searchCriteria('realName') . '%');
        }

        $query = $query->getQuery();

        return $this->paginate($query, $options->getPage(), $options->getLimit());
    }

        /**
     * @return Agent[]
     */
    public function searchByAgentName(string $agentName)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('LOWER(a.nickname) LIKE LOWER(:val)')
            ->setParameter('val', '%'.$agentName.'%')
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;

    }

    // /**
    //  * @return Agent[] Returns an array of Agent objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Agent
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
