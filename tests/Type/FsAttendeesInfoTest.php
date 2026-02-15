<?php

namespace App\Tests\Type;

use App\Type\FsAttendeesInfo;
use PHPUnit\Framework\TestCase;

class FsAttendeesInfoTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $info = new FsAttendeesInfo();

        self::assertSame([], $info->poc);
        self::assertSame([], $info->attendees);
    }

    public function testConstructorWithValues(): void
    {
        $poc = ['Enlightened' => 'Agent1'];
        $attendees = ['Enlightened' => ['Agent2', 'Agent3']];

        $info = new FsAttendeesInfo($poc, $attendees);

        self::assertSame($poc, $info->poc);
        self::assertSame($attendees, $info->attendees);
    }

    public function testPropertiesAreWritable(): void
    {
        $info = new FsAttendeesInfo();

        $info->poc = ['Resistance' => 'AgentR'];
        $info->attendees = ['Resistance' => ['Agent4']];

        self::assertSame(['Resistance' => 'AgentR'], $info->poc);
        self::assertSame(['Resistance' => ['Agent4']], $info->attendees);
    }
}
