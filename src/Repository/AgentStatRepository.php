<?php

namespace App\Repository;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Helper\Paginator\PaginatorOptions;
use App\Helper\Paginator\PaginatorRepoTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AgentStat|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgentStat|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgentStat[]    findAll()
 * @method AgentStat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgentStatRepository extends ServiceEntityRepository
{
    use PaginatorRepoTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AgentStat::class);
    }

    public function has(AgentStat $statEntry)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.datetime = :datetime')
            ->setParameter('datetime', $statEntry->getDatetime())
            ->andWhere('a.agent = :agent')
            ->setParameter('agent', $statEntry->getAgent())
            ->getQuery()
            ->getResult();
    }

    public function getPrevious(?AgentStat $statEntry): ?AgentStat
    {
        $entries = $this->createQueryBuilder('a')
            ->andWhere('a.datetime < :datetime')
            ->setParameter('datetime', $statEntry->getDatetime())
            ->andWhere('a.agent = :agent')
            ->setParameter('agent', $statEntry->getAgent())
            ->orderBy('a.datetime', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        return $entries ? $entries[0] : null;
    }

    /**
     * @return AgentStat[]
     */
    public function findByDate($startDate, $endDate): iterable
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.datetime >= :startDate')
            ->setParameter('startDate', $startDate)
            ->andWhere('a.datetime <= :endDate')
            ->setParameter('endDate', $endDate.' 23:59:59')
            ->orderBy('a.datetime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return AgentStat[]
     */
    public function getAgentStats(Agent $agent, string $order = 'DESC'): iterable
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.agent = :agent')
            ->setParameter('agent', $agent)
            ->orderBy('a.datetime', $order)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return AgentStat
     */
    public function getAgentLatest(Agent $agent): ?AgentStat
    {
        $entries = $this->createQueryBuilder('a')
            ->andWhere('a.agent = :agent')
            ->setParameter('agent', $agent)
            ->orderBy('a.datetime', 'DESC')
            ->getQuery()
            ->getResult();

        return $entries ? $entries[0] : null;
    }

    /**
     * @param PaginatorOptions $options
     *
     * @return Paginator
     */
    public function getPaginatedList(PaginatorOptions $options): Paginator
    {
        $query = $this->createQueryBuilder('a');

        $query->orderBy('a.'.$options->getOrder(), $options->getOrderDir());

        // if ($options->searchCriteria('agent')) {
        //     // $query->select()
        //     $query->andWhere('a.agent = :agent')
        //         ->setParameter(
        //             'agent',
        //            $agent
        //            // '%'.$options->searchCriteria('agent').'%'
        //         );
        // }
        //
        // if ($options->searchCriteria('realName')) {
        //     $query->andWhere('LOWER(a.realName) LIKE LOWER(:realName)')
        //         ->setParameter(
        //             'realName',
        //             '%'.$options->searchCriteria('realName').'%'
        //         );
        // }

        $query = $query->getQuery();

        return $this->paginate(
            $query,
            $options->getPage(),
            $options->getLimit()
        );
    }
}
