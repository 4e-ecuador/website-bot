<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\CustomMedalsExporter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CustomMedalsExporterTest extends KernelTestCase
{
    private function exporter(): CustomMedalsExporter
    {
        $exporter = static::getContainer()->get(CustomMedalsExporter::class);
        self::assertInstanceOf(CustomMedalsExporter::class, $exporter);

        return $exporter;
    }

    public function testKeepsTheCurrentFormat(): void
    {
        self::assertSame(
            ['anomaly' => ['orion' => 'platinum', 'echo' => ''], 'annual' => ['voyager' => 5], 'event' => []],
            $this->exporter()->normalize(['anomaly' => ['orion' => 'platinum', 'echo' => ''], 'annual' => ['voyager' => 5]])
        );
    }

    public function testConvertsOlderRecords(): void
    {
        self::assertSame(
            [
                'anomaly' => ['nemesis_myriad' => '', '13_magnusreawakens' => ''],
                'annual'  => ['resurgence' => 3],
                'event'   => ['didact_field' => 'gold', 'avenir_shard_challenge' => ''],
            ],
            $this->exporter()->normalize([
                'Anomaly' => ['NemesisMyriad' => '1', '13MagnusReawakens' => '1'],
                'Annual'  => ['Resurgence' => '3'],
                'Event'   => ['DidactField' => 'Gold', 'AvenirShard' => ''],
            ])
        );
    }

    public function testLeavesOutWhatItDoesNotKnow(): void
    {
        self::assertSame(
            ['anomaly' => [], 'annual' => [], 'event' => []],
            $this->exporter()->normalize([
                'anomaly' => ['requiem' => '1', 'orion' => 'diamond'],
                'annual'  => ['voyager' => '6'],
                'secret'  => ['x' => 'y'],
                'event'   => 'not a group',
            ])
        );
    }
}
