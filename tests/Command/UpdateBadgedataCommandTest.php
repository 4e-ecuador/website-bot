<?php

namespace App\Tests\Command;

use App\Command\UpdateBadgedataCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class UpdateBadgedataCommandTest extends TestCase
{
    private UpdateBadgedataCommand $command;

    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir().'/badge_test_'.uniqid('', true);
        mkdir($this->tmpDir.'/assets/images/badges', 0777, true);
        mkdir($this->tmpDir.'/assets/images/sprites', 0777, true);
        mkdir($this->tmpDir.'/assets/css', 0777, true);
        mkdir($this->tmpDir.'/text-files', 0777, true);

        $this->command = new UpdateBadgedataCommand($this->tmpDir);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpDir);
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach ((array) scandir($dir) as $item) {
            if ($item === '.') {
                continue;
            }

            if ($item === '..') {
                continue;
            }

            $path = $dir.'/'.$item;
            is_dir($path) ? $this->removeDir($path) : unlink($path);
        }

        rmdir($dir);
    }

    private function setUpCommandIo(?OutputInterface $output = null): void
    {
        $input = new ArrayInput([], $this->command->getDefinition());
        $output ??= new NullOutput();
        $io = new SymfonyStyle($input, $output);

        $ref = new \ReflectionClass($this->command);
        $ref->getProperty('input')->setValue($this->command, $input);
        $ref->getProperty('output')->setValue($this->command, $output);
        $ref->getProperty('io')->setValue($this->command, $io);
    }

    private function createMinimalPng(string $path): void
    {
        $img = imagecreatetruecolor(10, 10);
        imagepng($img, $path);
    }

    private function injectMockClient(MockHttpClient $client): void
    {
        new \ReflectionClass($this->command)
            ->getProperty('httpClient')
            ->setValue($this->command, $client);
    }

    private function setScrapeSite(string $url): void
    {
        new \ReflectionClass($this->command)
            ->getProperty('scrapeSite')
            ->setValue($this->command, $url);
    }

    // ── Configuration ────────────────────────────────────────────────────────

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

    // ── cutHash ──────────────────────────────────────────────────────────────

    public function testCutHash(): void
    {
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'cutHash');

        self::assertSame('badge_name.png', $method->invoke($this->command, 'badge_name_abc123.png'));
        self::assertSame('badge_explorer.png', $method->invoke($this->command, 'badge_explorer_def456.png'));
        self::assertSame('unique_badge_core.png', $method->invoke($this->command, 'unique_badge_core_xyz789.png'));
    }

    // ── isSkippedBadge ───────────────────────────────────────────────────────

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

    public function testIsSkippedBadgeLogsWhenVeryVerbose(): void
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_VERY_VERBOSE);
        $this->setUpCommandIo($output);
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'isSkippedBadge');

        self::assertTrue($method->invoke($this->command, 'unnamed_badge.png'));
        self::assertStringContainsString('skipped', $output->fetch());
    }

    // ── isSkippedCategory ────────────────────────────────────────────────────

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

        self::assertTrue($method->invoke($this->command, 'Unique Medals', 'Some Unknown Badge'));
    }

    public function testIsSkippedCategoryReturnsFalseForPickedUniqueMedal(): void
    {
        $this->setUpCommandIo();
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'isSkippedCategory');

        self::assertFalse($method->invoke($this->command, 'Unique Medals', 'C.O.R.E.'));
        self::assertFalse($method->invoke($this->command, 'Unique Medals', 'Simulacrum'));
    }

    public function testIsSkippedCategoryLogsWhenVeryVerbose(): void
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_VERY_VERBOSE);
        $this->setUpCommandIo($output);
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'isSkippedCategory');

        $method->invoke($this->command, 'Characters', 'Any Badge');
        self::assertStringContainsString('skipped', $output->fetch());

        $method->invoke($this->command, 'Unique Medals', 'Unknown Badge');
        self::assertStringContainsString('not been picked', $output->fetch());
    }

    // ── skipItem ─────────────────────────────────────────────────────────────

    public function testSkipItemReturnsTrueWhenNoExpand(): void
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_VERY_VERBOSE);
        $this->setUpCommandIo($output);
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'skipItem');

        $item = new \stdClass();
        $item->image = ['some_badge_abc123.png'];
        $item->title = 'Some Badge';

        self::assertTrue($method->invoke($this->command, $item));
        self::assertStringContainsString('no Category', $output->fetch());
    }

    public function testSkipItemReturnsTrueForSkippedBadge(): void
    {
        $this->setUpCommandIo();
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'skipItem');

        $item = new \stdClass();
        $item->image = ['unnamed_badge_abc123.png'];
        $item->title = 'Some Badge';
        $item->expand = (object) ['category' => (object) ['title' => 'Regular Medals']];

        self::assertTrue($method->invoke($this->command, $item));
    }

    public function testSkipItemReturnsTrueForSkippedCategory(): void
    {
        $this->setUpCommandIo();
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'skipItem');

        $item = new \stdClass();
        $item->image = ['badge_explorer_abc123.png'];
        $item->title = 'Some Badge';
        $item->expand = (object) ['category' => (object) ['title' => 'Characters']];

        self::assertTrue($method->invoke($this->command, $item));
    }

    public function testSkipItemReturnsFalseForNormalItem(): void
    {
        $this->setUpCommandIo();
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'skipItem');

        $item = new \stdClass();
        $item->image = ['badge_explorer_abc123.png'];
        $item->title = 'Explorer';
        $item->expand = (object) ['category' => (object) ['title' => 'Tiered Medals']];

        self::assertFalse($method->invoke($this->command, $item));
    }

    // ── resolveImageName ─────────────────────────────────────────────────────

    public function testResolveImageNameReturnsUnmappedName(): void
    {
        $this->setUpCommandIo();
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'resolveImageName');

        self::assertSame('badge_explorer.png', $method->invoke($this->command, 'badge_explorer.png'));
    }

    public function testResolveImageNameReturnsMappedName(): void
    {
        $this->setUpCommandIo();
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'resolveImageName');

        self::assertSame('event_badge_paragon.png', $method->invoke($this->command, 'unique_badge_paragon.png'));
    }

    public function testResolveImageNameLogsVerbosely(): void
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $this->setUpCommandIo($output);
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'resolveImageName');

        $method->invoke($this->command, 'badge_explorer.png');
        self::assertStringContainsString('badge_explorer.png', $output->fetch());

        $method->invoke($this->command, 'unique_badge_paragon.png');
        $log = $output->fetch();
        self::assertStringContainsString('unique_badge_paragon.png', $log);
        self::assertStringContainsString('event_badge_paragon.png', $log);
    }

    // ── downloadBadgeImage ───────────────────────────────────────────────────

    public function testDownloadBadgeImageReturnsFalseWhenExists(): void
    {
        $this->setUpCommandIo();
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'downloadBadgeImage');

        $existingFile = $this->tmpDir.'/assets/images/badges/badge_existing.png';
        file_put_contents($existingFile, 'fake png data');

        $result = $method->invoke($this->command, $existingFile, 'col/id/badge_existing.png', 'badge_existing.png', 'Test');
        self::assertFalse($result);
    }

    public function testDownloadBadgeImageReturnsFalseWhenExistsVerbose(): void
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $this->setUpCommandIo($output);
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'downloadBadgeImage');

        $existingFile = $this->tmpDir.'/assets/images/badges/badge_existing2.png';
        file_put_contents($existingFile, 'fake png data');

        $result = $method->invoke($this->command, $existingFile, 'col/id/badge_existing2.png', 'badge_existing2.png', 'Test');
        self::assertFalse($result);
        self::assertStringContainsString('exists', $output->fetch());
    }

    public function testDownloadBadgeImageDownloadsNewBadgeNonVerbose(): void
    {
        $sourceDir = $this->tmpDir.'/fake-server/files/col1/id1';
        mkdir($sourceDir, 0777, true);
        file_put_contents($sourceDir.'/badge_new_abc123.png', 'fake png content');

        $this->setScrapeSite('file://'.$this->tmpDir.'/fake-server');
        $this->setUpCommandIo();
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'downloadBadgeImage');

        $destFile = $this->tmpDir.'/assets/images/badges/badge_new.png';
        $result = $method->invoke($this->command, $destFile, 'col1/id1/badge_new_abc123.png', 'badge_new.png', 'Test Category');

        self::assertTrue($result);
        self::assertFileExists($destFile);
    }

    public function testDownloadBadgeImageDownloadsNewBadgeVerbose(): void
    {
        $sourceDir = $this->tmpDir.'/fake-server/files/col1/id1';
        mkdir($sourceDir, 0777, true);
        file_put_contents($sourceDir.'/badge_verbose_abc123.png', 'fake png content');

        $this->setScrapeSite('file://'.$this->tmpDir.'/fake-server');
        $output = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $this->setUpCommandIo($output);
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'downloadBadgeImage');

        $destFile = $this->tmpDir.'/assets/images/badges/badge_verbose.png';
        $result = $method->invoke($this->command, $destFile, 'col1/id1/badge_verbose_abc123.png', 'badge_verbose.png', 'Test Category');

        self::assertTrue($result);
        self::assertStringContainsString('NEW', $output->fetch());
    }

    // ── processItem ──────────────────────────────────────────────────────────

    public function testProcessItemWithExistingBadge(): void
    {
        $this->setUpCommandIo();
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'processItem');

        file_put_contents($this->tmpDir.'/assets/images/badges/badge_explorer.png', 'fake');

        $item = new \stdClass();
        $item->image = ['badge_explorer_abc123.png'];
        $item->title = 'Explorer';
        $item->description = 'An explorer badge';
        $item->collectionId = 'col123';
        $item->id = 'id456';
        $item->expand = (object) ['category' => (object) ['title' => 'Tiered Medals']];

        [$changed, $badges] = $method->invoke($this->command, $item);

        self::assertFalse($changed);
        self::assertCount(1, $badges);
        self::assertSame('badge_explorer', $badges[0]->code);
        self::assertSame('Explorer', $badges[0]->title);
        self::assertSame('An explorer badge', $badges[0]->description);
    }

    public function testProcessItemVerbose(): void
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $this->setUpCommandIo($output);
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'processItem');

        file_put_contents($this->tmpDir.'/assets/images/badges/badge_recon.png', 'fake');

        $item = new \stdClass();
        $item->image = ['badge_recon_xyz789.png'];
        $item->title = 'Recon';
        $item->description = 'Recon badge';
        $item->collectionId = 'colA';
        $item->id = 'idB';
        $item->expand = (object) ['category' => (object) ['title' => 'Tiered Medals']];

        $method->invoke($this->command, $item);
        self::assertStringContainsString('Tiered Medals', $output->fetch());
    }

    // ── execCommand ──────────────────────────────────────────────────────────

    public function testExecCommandReturnsOutputOnSuccess(): void
    {
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'execCommand');

        $result = $method->invoke($this->command, 'true');
        self::assertSame('', $result);
    }

    public function testExecCommandThrowsUnknownErrorOnFailure(): void
    {
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'execCommand');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('An unknown error occurred');
        $method->invoke($this->command, 'false');
    }

    public function testExecCommandThrowsWithMessageOnFailure(): void
    {
        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'execCommand');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('error from cmd');
        $method->invoke($this->command, "sh -c 'echo error from cmd; exit 1'");
    }

    // ── resizeBadgesForSize ──────────────────────────────────────────────────

    public function testResizeBadgesForSizeSkipsExistingFiles(): void
    {
        $this->setUpCommandIo();
        $badgeRoot = $this->tmpDir.'/assets/images/badges';
        $srcPng = $badgeRoot.'/badge_skip_test.png';
        $this->createMinimalPng($srcPng);

        $destDir = $badgeRoot.'/50';
        mkdir($destDir);
        file_put_contents($destDir.'/badge_skip_test.png', 'already resized');

        $progressBar = new ProgressBar(new NullOutput());
        $progressBar->start();

        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'resizeBadgesForSize');
        $method->invoke($this->command, 50, new Filesystem(), $progressBar);

        self::assertSame('already resized', file_get_contents($destDir.'/badge_skip_test.png'));
    }

    public function testResizeBadgesForSizeResizesImages(): void
    {
        $this->setUpCommandIo();
        $badgeRoot = $this->tmpDir.'/assets/images/badges';
        $srcPng = $badgeRoot.'/badge_resize_test.png';
        $this->createMinimalPng($srcPng);

        $progressBar = new ProgressBar(new NullOutput());
        $progressBar->start();

        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'resizeBadgesForSize');
        $method->invoke($this->command, 50, new Filesystem(), $progressBar);

        self::assertFileExists($badgeRoot.'/50/badge_resize_test.png');
    }

    // ── buildSpriteForSize ───────────────────────────────────────────────────

    public function testBuildSpriteForSize(): void
    {
        $this->setUpCommandIo();
        $sizeDir = $this->tmpDir.'/assets/images/badges/50';
        mkdir($sizeDir);
        $this->createMinimalPng($sizeDir.'/badge_sprite_test.png');

        $method = new \ReflectionMethod(UpdateBadgedataCommand::class, 'buildSpriteForSize');
        $method->invoke($this->command, 50, 15, []);

        self::assertFileExists($this->tmpDir.'/assets/images/sprites/medals_50.png');
        self::assertFileExists($this->tmpDir.'/assets/css/medals_50.css');
        $css = (string) file_get_contents($this->tmpDir.'/assets/css/medals_50.css');
        self::assertStringContainsString('.medal50', $css);
        self::assertStringContainsString('badge_sprite_test', $css);
    }

    // ── execute() via CommandTester ──────────────────────────────────────────

    public function testExecuteReturnsFailureOnBadJson(): void
    {
        $this->injectMockClient(new MockHttpClient(new MockResponse('not valid json {{{')));

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
    }

    public function testExecuteReturnsSuccessWhenNothingChanged(): void
    {
        $responseBody = (string) json_encode([
            'totalItems' => 1,
            'perPage' => 500,
            'items' => [
                [
                    'image' => ['badge_explorer_abc123.png'],
                    'title' => 'Explorer',
                    'description' => 'Explorer badge',
                    'collectionId' => 'col1',
                    'id' => 'id1',
                    'expand' => ['category' => ['title' => 'Tiered Medals']],
                ],
            ],
        ]);

        $this->injectMockClient(new MockHttpClient(new MockResponse($responseBody)));
        file_put_contents($this->tmpDir.'/assets/images/badges/badge_explorer.png', 'fake');

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('Nothing has changed', $tester->getDisplay());
    }

    public function testExecuteRunsFullPipeline(): void
    {
        // Pre-create badge so downloadBadgeImage skips the download (no file:// vs http:// conflict)
        $badgePng = $this->tmpDir.'/assets/images/badges/badge_pipeline.png';
        $this->createMinimalPng($badgePng);

        $responseBody = (string) json_encode([
            'totalItems' => 1,
            'perPage' => 500,
            'items' => [
                [
                    'image' => ['badge_pipeline_abc123.png'],
                    'title' => 'Pipeline Badge',
                    'description' => 'Test badge',
                    'collectionId' => 'col1',
                    'id' => 'id1',
                    'expand' => ['category' => ['title' => 'Tiered Medals']],
                ],
            ],
        ]);

        $this->injectMockClient(new MockHttpClient(new MockResponse($responseBody)));

        $tester = new CommandTester($this->command);
        // --force skips NothingHasChangedException (badge already exists → nothingHasChanged=true)
        $tester->execute(['--force' => true]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('Finished', $tester->getDisplay());
        self::assertFileExists($this->tmpDir.'/assets/images/sprites/medals_50.png');
        self::assertFileExists($this->tmpDir.'/assets/css/medals_50.css');
    }
}
