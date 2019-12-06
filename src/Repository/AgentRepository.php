<?php

namespace App\Repository;

use App\Entity\Agent;
use App\Entity\MapGroup;
use App\Helper\Paginator\PaginatorOptions;
use App\Helper\Paginator\PaginatorRepoTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Agent|null find($id, $lockMode = null, $lockVersion = null)
 * @method Agent|null findOneBy(array $criteria, array $orderBy = null)
 * @method Agent[]    findAll()
 * @method Agent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgentRepository extends ServiceEntityRepository
{
    use PaginatorRepoTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agent::class);
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

        if ($options->searchCriteria('nickname')) {
            $query->andWhere('LOWER(a.nickname) LIKE LOWER(:nickname)')
                ->setParameter(
                    'nickname',
                    '%'.$options->searchCriteria('nickname').'%'
                );
        }

        if ($options->searchCriteria('realName')) {
            $query->andWhere('LOWER(a.realName) LIKE LOWER(:realName)')
                ->setParameter(
                    'realName',
                    '%'.$options->searchCriteria('realName').'%'
                );
        }

        if ($options->searchCriteria('faction')) {
            $query->andWhere('a.faction = :faction')
                ->setParameter(
                    'faction',
                    $options->searchCriteria('faction')
                );
        }

        $query = $query->getQuery();

        return $this->paginate($query, $options->getPage(), $options->getLimit());
    }

    /**
     * @return Agent[]
     */
    public function searchByAgentName(string $agentName): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('LOWER(a.nickname) LIKE LOWER(:val)')
            ->setParameter('val', '%'.$agentName.'%')
            ->orderBy('a.nickname', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findOneByNickName($value): ?Agent
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nickname = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

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
    public function findMapAgents(MapGroup $group)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.map_group = :val')
            ->setParameter('val', $group)
            ->getQuery()
            ->getResult();
    }

    public function findAllAlphabetical()
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.nickname', 'ASC')
            ->getQuery()
            ->execute();
    }
}
