<?php

namespace App\Repository;

use App\Entity\Agent;
use App\Entity\MapGroup;
use App\Helper\Paginator\PaginatorOptions;
use App\Helper\Paginator\PaginatorRepoTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Agent|null find($id, $lockMode = null, $lockVersion = null)
 * @method Agent|null findOneBy(array $criteria, array $orderBy = null)
 * @method Agent[]    findAll()
 * @method Agent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<AgentRepository>
 */
class AgentRepository extends ServiceEntityRepository
{
    use PaginatorRepoTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agent::class);
    }

    /**
     * @return Paginator<Query>
     */
    public function getPaginatedList(PaginatorOptions $options): Paginator
    {
        $query = $this->createQueryBuilder('a');

        $query->orderBy('a.'.$options->getOrder(), $options->getOrderDir());

        if ($options->searchCriteria('nickname') !== '' && $options->searchCriteria('nickname') !== '0') {
            $query->andWhere('LOWER(a.nickname) LIKE LOWER(:nickname)')
                ->setParameter(
                    'nickname',
                    '%'.$options->searchCriteria('nickname').'%'
                );
        }

        if ($options->searchCriteria('realName') !== '' && $options->searchCriteria('realName') !== '0') {
            $query->andWhere('LOWER(a.realName) LIKE LOWER(:realName)')
                ->setParameter(
                    'realName',
                    '%'.$options->searchCriteria('realName').'%'
                );
        }

        if ($options->searchCriteria('faction') !== '' && $options->searchCriteria('faction') !== '0') {
            $query->andWhere('a.faction = :faction')
                ->setParameter(
                    'faction',
                    $options->searchCriteria('faction')
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
     * @param array<int> $excludes
     *
     * @return array<Agent>
     */
    public function searchByAgentName(
        string $agentName,
        array $excludes = []
    ): array {
        $query = $this->createQueryBuilder('a')
            ->andWhere('LOWER(a.nickname) LIKE LOWER(:val)')
            ->setParameter('val', '%'.$agentName.'%')
            ->orderBy('a.nickname', 'ASC')
            ->setMaxResults(10);

        if ($excludes !== []) {
            $query->andWhere('a.id NOT IN (:excludes)')
                ->setParameter('excludes', $excludes);
        }

        return $query->getQuery()
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByNickName(string $value): ?Agent
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nickname = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function has(Agent $agent): ?Agent
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nickname = :val')
            ->setParameter('val', $agent->getNickname())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Agent[]
     */
    public function findMapAgents(MapGroup $group): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.map_group = :val')
            ->setParameter('val', $group)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Agent[]
     */
    public function findAllAlphabetical(): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.nickname', 'ASC')
            ->getQuery()
            ->execute();
    }

    /**
     * @return Agent[]
     */
    public function findNotifyAgents(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.telegram_id IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array<int> $ids
     *
     * @return array<Agent>
     */
    public function searchByIds(array $ids): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.id IN (:val)')
            ->setParameter('val', $ids)
            ->getQuery()->getResult();
    }
}
