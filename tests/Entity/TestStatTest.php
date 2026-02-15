<?php

namespace App\Tests\Entity;

use App\Entity\TestStat;
use PHPUnit\Framework\TestCase;

class TestStatTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $stat = new TestStat();

        self::assertNull($stat->getId());
        self::assertSame('', $stat->getCsv());
    }

    public function testSetCsv(): void
    {
        $stat = new TestStat();

        $result = $stat->setCsv('col1,col2,col3');

        self::assertSame('col1,col2,col3', $stat->getCsv());
        self::assertSame($stat, $result);
    }
}
