<?php

namespace App\Tests\Helper\Paginator;

use App\Helper\Paginator\PaginatorOptions;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class PaginatorOptionsTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $options = new PaginatorOptions();

        self::assertSame(0, $options->getPage());
        self::assertSame(0, $options->getMaxPages());
        self::assertSame(10, $options->getLimit());
        self::assertSame('id', $options->getOrder());
        self::assertSame('ASC', $options->getOrderDir());
        self::assertSame([], $options->getCriteria());
    }

    public function testSetPage(): void
    {
        $options = new PaginatorOptions();

        $result = $options->setPage(5);

        self::assertSame(5, $options->getPage());
        self::assertSame($options, $result);
    }

    public function testSetOrder(): void
    {
        $options = new PaginatorOptions();

        $result = $options->setOrder('name');

        self::assertSame('name', $options->getOrder());
        self::assertSame($options, $result);
    }

    public function testSetOrderDirAsc(): void
    {
        $options = new PaginatorOptions();

        $options->setOrderDir('ASC');

        self::assertSame('ASC', $options->getOrderDir());
    }

    public function testSetOrderDirDesc(): void
    {
        $options = new PaginatorOptions();

        $options->setOrderDir('DESC');

        self::assertSame('DESC', $options->getOrderDir());
    }

    public function testSetOrderDirInvalidThrows(): void
    {
        $options = new PaginatorOptions();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Order dir must be ASC, DESC');

        $options->setOrderDir('INVALID');
    }

    public function testSetMaxPages(): void
    {
        $options = new PaginatorOptions();

        $result = $options->setMaxPages(10);

        self::assertSame(10, $options->getMaxPages());
        self::assertSame($options, $result);
    }

    public function testSetLimitZeroDefaultsToTen(): void
    {
        $options = new PaginatorOptions();

        $options->setLimit(0);

        self::assertSame(10, $options->getLimit());
    }

    public function testSetLimitCustomValue(): void
    {
        $options = new PaginatorOptions();

        $options->setLimit(25);

        self::assertSame(25, $options->getLimit());
    }

    public function testSetCriteria(): void
    {
        $options = new PaginatorOptions();
        $criteria = ['name' => 'test', 'status' => 'active'];

        $result = $options->setCriteria($criteria);

        self::assertSame($criteria, $options->getCriteria());
        self::assertSame($options, $result);
    }

    public function testSearchCriteriaFound(): void
    {
        $options = new PaginatorOptions();
        $options->setCriteria(['name' => 'test']);

        self::assertSame('test', $options->searchCriteria('name'));
    }

    public function testSearchCriteriaNotFound(): void
    {
        $options = new PaginatorOptions();

        self::assertSame('', $options->searchCriteria('missing'));
    }

    public function testFluentInterface(): void
    {
        $options = new PaginatorOptions();

        $result = $options
            ->setPage(1)
            ->setOrder('name')
            ->setOrderDir('DESC')
            ->setMaxPages(5)
            ->setLimit(20)
            ->setCriteria(['q' => 'search']);

        self::assertSame($options, $result);
        self::assertSame(1, $options->getPage());
        self::assertSame('name', $options->getOrder());
        self::assertSame('DESC', $options->getOrderDir());
        self::assertSame(5, $options->getMaxPages());
        self::assertSame(20, $options->getLimit());
    }
}
