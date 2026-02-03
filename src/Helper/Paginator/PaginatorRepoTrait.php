<?php
/**
 * Created by PhpStorm.
 * User: test
 * Date: 18.06.18
 * Time: 07:54
 */

namespace App\Helper\Paginator;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

trait PaginatorRepoTrait
{
    /**
     * @template T of object
     * @param Query<int, T> $dql
     * @return Paginator<T>
     */
    public function paginate(
        Query $dql,
        int $page = 1,
        int $limit = 5
    ): Paginator {
        $paginator = new Paginator($dql);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        return $paginator;
    }
}
