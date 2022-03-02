<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Helper\Paginator;

use UnexpectedValueException;
use function in_array;

/**
 * Class PaginatorOptions
 */
class PaginatorOptions
{
    private int $page = 0;
    private int $maxPages = 0;
    private int $limit = 10;
    private string $order = 'id';
    private string $orderDir = 'ASC';
    /**
     * @var array<string>
     */
    private array $criteria = [];

    public function setPage(int $page): PaginatorOptions
    {
        $this->page = $page;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setOrder(string $order): PaginatorOptions
    {
        $this->order = $order;

        return $this;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrderDir(string $orderDir): PaginatorOptions
    {
        $dirs = ['ASC', 'DESC'];
        $dir = strtoupper($orderDir);

        if (false === in_array($dir, $dirs, true)) {
            throw new UnexpectedValueException(
                sprintf('Order dir must be %s', implode(', ', $dirs))
            );
        }

        $this->orderDir = $orderDir;

        return $this;
    }

    public function getOrderDir(): string
    {
        return $this->orderDir;
    }

    /**
     * @param array<string> $criteria
     */
    public function setCriteria(array $criteria): PaginatorOptions
    {
        $this->criteria = $criteria;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getCriteria(): array
    {
        return $this->criteria;
    }

    public function setMaxPages(int $maxPages): PaginatorOptions
    {
        $this->maxPages = $maxPages;

        return $this;
    }

    public function getMaxPages(): int
    {
        return $this->maxPages;
    }

    public function setLimit(int $limit): PaginatorOptions
    {
        $this->limit = $limit ?: 10;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit ?: 10;
    }

    /**
     * @return string Criteria value or empty string
     */
    public function searchCriteria(string $name): string
    {
        return array_key_exists($name, $this->criteria) ? $this->criteria[$name]
            : '';
    }
}
