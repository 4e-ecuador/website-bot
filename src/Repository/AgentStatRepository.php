<?php

namespace App\Repository;

use App\Entity\Agent;
use App\Entity\AgentStat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AgentStat|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgentStat|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgentStat[]    findAll()
 * @method AgentStat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgentStatRepository extends ServiceEntityRepository
{
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
            ->setParameter('endDate', $endDate)
            ->orderBy('a.datetime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return AgentStat[]
     */
    public function getAgentStats(Agent $agent): iterable
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.agent = :agent')
            ->setParameter('agent', $agent)
            ->orderBy('a.datetime', 'DESC')
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
}
