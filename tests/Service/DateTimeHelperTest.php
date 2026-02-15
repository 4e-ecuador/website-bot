<?php

namespace App\Tests\Service;

use App\Service\DateTimeHelper;
use PHPUnit\Framework\TestCase;

class DateTimeHelperTest extends TestCase
{
    private DateTimeHelper $helper;

    protected function setUp(): void
    {
        $this->helper = new DateTimeHelper('America/Guayaquil');
    }

    public function testGetNextFsReturnsDateTime(): void
    {
        $result = $this->helper->getNextFS();

        self::assertInstanceOf(\DateTime::class, $result);
    }

    public function testGetNextFsReturnsSaturday(): void
    {
        $result = $this->helper->getNextFS();

        self::assertSame('Saturday', $result->format('l'));
    }

    public function testGetNextFsIsInFuture(): void
    {
        $result = $this->helper->getNextFS();
        $now = new \DateTime('now', new \DateTimeZone('America/Guayaquil'));

        self::assertGreaterThanOrEqual($now->format('Y-m-d'), $result->format('Y-m-d'));
    }

    public function testGetNextFsIsFirstSaturdayOfMonth(): void
    {
        $result = $this->helper->getNextFS();

        // The first Saturday is always within the first 7 days
        self::assertLessThanOrEqual(7, (int) $result->format('j'));
    }
}
