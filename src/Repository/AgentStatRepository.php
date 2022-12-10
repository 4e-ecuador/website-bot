<?php

namespace App\Repository;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Helper\Paginator\PaginatorOptions;
use App\Helper\Paginator\PaginatorRepoTrait;
use DateTime;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method AgentStat|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgentStat|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgentStat[]    findAll()
 * @method AgentStat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<AgentStatRepository>
 */
class AgentStatRepository extends ServiceEntityRepository
{
    use PaginatorRepoTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgentStat::class);
    }

    /**
     * @return AgentStat[]
     */
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
     * @return array<AgentStat>
     */
    public function findByDate(
        DateTimeInterface $startDate,
        DateTimeInterface $endDate
    ): iterable {
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
    public function findByDateAndAgent(
        DateTime $startDate,
        DateTime $endDate,
        Agent $agent
    ): iterable {
        return $this->createQueryBuilder('a')
            ->andWhere('a.datetime >= :startDate')
            ->setParameter('startDate', $startDate)
            ->andWhere('a.datetime <= :endDate')
            ->setParameter('endDate', $endDate)
            ->orderBy('a.datetime', 'ASC')
            ->andWhere('a.agent = :agent')
            ->setParameter('agent', $agent)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return AgentStat[]
     */
    public function getAgentStats(
        Agent $agent,
        string $order = 'DESC'
    ): iterable {
        return $this->createQueryBuilder('a')
            ->andWhere('a.agent = :agent')
            ->setParameter('agent', $agent)
            ->orderBy('a.datetime', $order)
            ->getQuery()
            ->getResult();
    }

      /**
     * @return AgentStat[]
     */
    public function getAgentStatsForCsv(
        Agent $agent,
        string $order = 'DESC'
    ): iterable {
        return $this->createQueryBuilder('a')
            ->andWhere('a.agent = :agent')
            ->setParameter('agent', $agent)
            ->orderBy('a.datetime', $order)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return AgentStat
     */
    public function getAgentLatest(
        Agent $agent,
        bool $first = false
    ): ?AgentStat {
        $entries = $this->createQueryBuilder('a')
            ->andWhere('a.agent = :agent')
            ->setParameter('agent', $agent)
            ->orderBy('a.datetime', 'DESC')
            ->getQuery()
            ->getResult();

        if ($entries) {
            return $first ? $entries[count($entries) - 1] : $entries[0];
        }

        return null;
    }

    /**
     * @return Paginator<Query>
     */
    public function getPaginatedList(PaginatorOptions $options): Paginator
    {
        $query = $this->createQueryBuilder('a');

        $query->orderBy('a.'.$options->getOrder(), $options->getOrderDir());

        if ($options->searchCriteria('agent')) {
            $query->andWhere('a.agent = :agent')
                ->setParameter(
                    'agent',
                    $options->searchCriteria('agent')
                );
        }

        $query = $query->getQuery();

        return $this->paginate(
            $query,
            $options->getPage(),
            $options->getLimit()
        );
    }

    /**
     * @return AgentStat[]
     */
    public function findTodays()
    {
        $startDatetime = DateTime::createFromFormat(
            'Y-m-d H:i:s',
            date('Y-m-d 00:00:00')
        );
        $endDatetime = DateTime::createFromFormat(
            'Y-m-d H:i:s',
            date('Y-m-d 23:59:59')
        );

        return $this->createQueryBuilder('a')
            ->andWhere('a.datetime >= :startValue')
            ->setParameter('startValue', $startDatetime)
            ->andWhere('a.datetime <= :endValue')
            ->setParameter('endValue', $endDatetime)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return AgentStat[]
     */
    public function findDayly(DateTime $dateTime)
    {
        $startDatetime = $dateTime->format('Y-m-d').' 00:00:00';
        $endDatetime = $dateTime->format('Y-m-d').' 23:59:59';

        return $this->createQueryBuilder('a')
            ->andWhere('a.datetime >= :startValue')
            ->setParameter('startValue', $startDatetime)
            ->andWhere('a.datetime <= :endValue')
            ->setParameter('endValue', $endDatetime)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return AgentStat[]
     */
    public function findAllDateDesc()
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.datetime', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
