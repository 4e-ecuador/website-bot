<?php

namespace App\Tests\Service;

use App\Service\FileUploader;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploaderTest extends TestCase
{
    public function testUploadSuccess(): void
    {
        $logger = $this->createStub(LoggerInterface::class);
        $uploader = new FileUploader($logger);

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tmpFile, 'test content');

        $uploadedFile = new UploadedFile($tmpFile, 'test.txt', 'text/plain', null, true);

        $targetDir = sys_get_temp_dir().'/file_uploader_test_'.uniqid();
        mkdir($targetDir);

        try {
            $result = $uploader->upload($targetDir, $uploadedFile, 'uploaded.txt');

            self::assertSame('uploaded.txt', $result->getFilename());
            self::assertFileExists($targetDir.'/uploaded.txt');
        } finally {
            @unlink($targetDir.'/uploaded.txt');
            @rmdir($targetDir);
        }
    }

    public function testUploadFailureLogsAndThrows(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $uploader = new FileUploader($logger);

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tmpFile, 'test content');

        $uploadedFile = new UploadedFile($tmpFile, 'test.txt', 'text/plain', null, true);

        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Failed to upload file');

        $uploader->upload('/nonexistent/directory/path', $uploadedFile, 'test.txt');
    }
}
