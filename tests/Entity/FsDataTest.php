<?php

namespace App\Tests\Entity;

use App\Entity\FsData;
use PHPUnit\Framework\TestCase;

class FsDataTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $fsData = new FsData();

        self::assertNull($fsData->getId());
        self::assertSame(0, $fsData->getAttendeesCount());
        self::assertNull($fsData->getData());
    }

    public function testCreatedAtIsSetOnConstruction(): void
    {
        $before = new \DateTimeImmutable();
        $fsData = new FsData();
        $after = new \DateTimeImmutable();

        self::assertGreaterThanOrEqual($before, $fsData->getCreatedAt());
        self::assertLessThanOrEqual($after, $fsData->getCreatedAt());
    }

    public function testSettersAndGetters(): void
    {
        $fsData = new FsData();

        $fsData->setAttendeesCount(42);
        self::assertSame(42, $fsData->getAttendeesCount());

        $fsData->setData('{"key":"value"}');
        self::assertSame('{"key":"value"}', $fsData->getData());
    }

    public function testFluentSetters(): void
    {
        $fsData = new FsData();

        $result = $fsData
            ->setAttendeesCount(10)
            ->setData('data')
            ->setCreatedAt(new \DateTimeImmutable('2025-01-01'));

        self::assertSame($fsData, $result);
    }
}
