<?php

namespace App\Tests\Command;

use App\Command\UpdateBadgedataCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateBadgedataCommandTest extends TestCase
{
    private UpdateBadgedataCommand $command;

    protected function setUp(): void
    {
        $this->command = new UpdateBadgedataCommand('/tmp');
    }

    public function testCommandName(): void
    {
        self::assertSame('app:update:badgedata', $this->command->getName());
    }

    public function testCommandDescription(): void
    {
        self::assertStringContainsString('medal images', $this->command->getDescription());
    }

    public function testCommandHasForceOption(): void
    {
        $definition = $this->command->getDefinition();
        self::assertTrue($definition->hasOption('force'));
    }

    public function testCutHash(): void
    {
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'cutHash');

        // Standard case: strips last _hash segment
        self::assertSame('badge_name.png', $method->invoke($this->command, 'badge_name_abc123.png'));
        self::assertSame('badge_explorer.png', $method->invoke($this->command, 'badge_explorer_def456.png'));
        self::assertSame('unique_badge_core.png', $method->invoke($this->command, 'unique_badge_core_xyz789.png'));
    }

    public function testIsSkippedBadgeReturnsTrueForKnownSkipped(): void
    {
        $this->setUpCommandIo();
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'isSkippedBadge');

        self::assertTrue($method->invoke($this->command, 'badge_intel_ops_bronze.png'));
        self::assertTrue($method->invoke($this->command, 'unnamed_badge.png'));
        self::assertTrue($method->invoke($this->command, 'placeholder_foo.png'));
        self::assertTrue($method->invoke($this->command, 'bad_cat_bar.png'));
    }

    public function testIsSkippedBadgeReturnsFalseForRegularBadge(): void
    {
        $this->setUpCommandIo();
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'isSkippedBadge');

        self::assertFalse($method->invoke($this->command, 'badge_explorer_bronze.png'));
        self::assertFalse($method->invoke($this->command, 'badge_recon_silver.png'));
        self::assertFalse($method->invoke($this->command, 'badge_pioneer_gold.png'));
    }

    public function testIsSkippedCategoryReturnsTrueForSkippedCategory(): void
    {
        $this->setUpCommandIo();
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'isSkippedCategory');

        self::assertTrue($method->invoke($this->command, 'Characters', 'Any Badge'));
        self::assertTrue($method->invoke($this->command, 'Characters - 2020', 'Some Badge'));
        self::assertTrue($method->invoke($this->command, 'Fan created - Single', 'My Badge'));
        self::assertTrue($method->invoke($this->command, 'NL-1331', 'Event Badge'));
    }

    public function testIsSkippedCategoryReturnsFalseForAllowedCategory(): void
    {
        $this->setUpCommandIo();
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'isSkippedCategory');

        self::assertFalse($method->invoke($this->command, 'Regular Medals', 'Explorer'));
        self::assertFalse($method->invoke($this->command, 'Tiered Medals', 'Recon'));
    }

    public function testIsSkippedCategoryReturnsTrueForUnpickedUniquesMedal(): void
    {
        $this->setUpCommandIo();
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'isSkippedCategory');

        // 'Unique Medals' category uses a pick list — badges not in the list are skipped
        self::assertTrue($method->invoke($this->command, 'Unique Medals', 'Some Unknown Badge'));
    }

    public function testIsSkippedCategoryReturnsFalseForPickedUniqueMedal(): void
    {
        $this->setUpCommandIo();
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'isSkippedCategory');

        // C.O.R.E. is in the pickBadges list for 'Unique Medals'
        self::assertFalse($method->invoke($this->command, 'Unique Medals', 'C.O.R.E.'));
        self::assertFalse($method->invoke($this->command, 'Unique Medals', 'Simulacrum'));
    }

    private function setUpCommandIo(): void
    {
        $input = new ArrayInput([]);
        $output = new NullOutput();
        $io = new SymfonyStyle($input, $output);

        $ref = new \ReflectionClass($this->command);

        $outputProp = $ref->getProperty('output');
        $outputProp->setValue($this->command, $output);

        $ioProp = $ref->getProperty('io');
        $ioProp->setValue($this->command, $io);
    }
}
