<?php

declare(strict_types=1);

namespace App\Tests\Util;

use App\Util\MedalSpriteCss;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

class MedalSpriteCssTest extends TestCase
{
    public function testLaysImagesOutRowByRow(): void
    {
        $files = array_map(
            static fn (string $name): SplFileInfo => new SplFileInfo(__DIR__.'/'.$name.'.png'),
            ['a', 'b', 'c'],
        );

        $sprite = new MedalSpriteCss()->generate($files, 50, 2, 'medals_50.png');

        self::assertStringContainsString('background:url(../images/sprites/medals_50.png) no-repeat', $sprite['css']);
        self::assertStringContainsString('.medal50.medal-a {background-position: 0 0}', $sprite['css']);
        self::assertStringContainsString('.medal50.medal-b {background-position: -50px 0}', $sprite['css']);
        // The third image wraps to the second row
        self::assertStringContainsString('.medal50.medal-c {background-position: 0 -50px}', $sprite['css']);
        self::assertCount(3, $sprite['paths']);
    }
}
