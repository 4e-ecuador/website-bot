<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function upload(string $uploadDir, UploadedFile $file, string $filename): File
    {
        try {
            return $file->move($uploadDir, $filename);
        } catch (FileException $fileException){

            $this->logger->error('failed to upload image: ' . $fileException->getMessage());
            throw new FileException('Failed to upload file');
        }
    }
}
