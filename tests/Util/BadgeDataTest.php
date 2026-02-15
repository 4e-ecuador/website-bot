<?php

namespace App\Tests\Util;

use App\Util\BadgeData;
use PHPUnit\Framework\TestCase;

class BadgeDataTest extends TestCase
{
    public function testConstructorPopulatesProperties(): void
    {
        $data = new BadgeData([
            'code' => 'explorer',
            'title' => 'Explorer',
            'description' => 'Visit portals',
        ]);

        self::assertSame('explorer', $data->code);
        self::assertSame('Explorer', $data->title);
        self::assertSame('Visit portals', $data->description);
    }

    public function testEmptyConstructor(): void
    {
        $data = new BadgeData();

        self::assertFalse(isset($data->code));
    }
}
