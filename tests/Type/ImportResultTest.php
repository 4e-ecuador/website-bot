<?php

namespace App\Tests\Type;

use App\Type\ImportResult;
use PHPUnit\Framework\TestCase;

class ImportResultTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $result = new ImportResult();

        self::assertSame([], $result->currents);
        self::assertSame([], $result->diff);
        self::assertSame([], $result->medalUps);
        self::assertSame([], $result->medalDoubles);
        self::assertSame([], $result->coreSubscribed);
        self::assertSame(0, $result->newLevel);
        self::assertSame(0, $result->recursions);
    }

    public function testPropertiesAreWritable(): void
    {
        $result = new ImportResult();

        $result->currents = ['explorer' => 100];
        $result->diff = ['ap' => 500];
        $result->medalUps = ['recharger' => 3];
        $result->medalDoubles = ['pioneer' => 2];
        $result->coreSubscribed = ['core1'];
        $result->newLevel = 16;
        $result->recursions = 2;

        self::assertSame(['explorer' => 100], $result->currents);
        self::assertSame(['ap' => 500], $result->diff);
        self::assertSame(['recharger' => 3], $result->medalUps);
        self::assertSame(['pioneer' => 2], $result->medalDoubles);
        self::assertSame(['core1'], $result->coreSubscribed);
        self::assertSame(16, $result->newLevel);
        self::assertSame(2, $result->recursions);
    }

    public function testPropertiesAreNullable(): void
    {
        $result = new ImportResult();

        $result->currents = null;
        $result->diff = null;
        $result->newLevel = null;

        self::assertNull($result->currents);
        self::assertNull($result->diff);
        self::assertNull($result->newLevel);
    }
}
