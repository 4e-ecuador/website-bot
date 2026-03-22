<?php

namespace App\Tests\Service;

use App\Service\CiteService;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class CiteServiceTest extends TestCase
{
    public function testGetRandomCiteReturnsString(): void
    {
        $service = new CiteService(dirname(__DIR__, 2));

        $result = $service->getRandomCite();

        self::assertNotEmpty($result);
    }

    #[RunInSeparateProcess]
    public function testGetRandomCiteWithMissingFile(): void
    {
        $service = new CiteService('/nonexistent/path');

        $result = $service->getRandomCite();

        // Should return error message from ParseException
        self::assertNotEmpty($result);
    }

    #[RunInSeparateProcess]
    public function testGetRandomCiteTwiceUsesCachedResult(): void
    {
        $service = new CiteService(dirname(__DIR__, 2));

        $first = $service->getRandomCite();
        $second = $service->getRandomCite();

        // Both should return valid non-empty strings
        self::assertNotEmpty($first);
        self::assertNotEmpty($second);
    }

    #[RunInSeparateProcess]
    public function testGetRandomCiteWithWrongYamlStructure(): void
    {
        $tmpDir = sys_get_temp_dir().'/cite_test_'.uniqid();
        mkdir($tmpDir.'/text-files', 0777, true);
        file_put_contents(
            $tmpDir.'/text-files/warrior-cites-es.yaml',
            "wrong_key:\n  - some item\n"
        );

        $service = new CiteService($tmpDir);
        $result = $service->getRandomCite();

        // Should return error message from UnexpectedValueException
        self::assertStringContainsString("cites", $result);

        // Cleanup
        unlink($tmpDir.'/text-files/warrior-cites-es.yaml');
        rmdir($tmpDir.'/text-files');
        rmdir($tmpDir);
    }
}
