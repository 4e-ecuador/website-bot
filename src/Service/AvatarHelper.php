<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AvatarHelper
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function updateAvatar(User $user): self
    {
        $curl = curl_init($user->getAvatar());

        if (!$curl) {
            throw new \UnexpectedValueException('Can not init Curl.');
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $returnValue = curl_exec($curl);

        if (!$returnValue) {
            throw new \RuntimeException('Failed fetching image');
        }

        curl_close($curl);

        $image = imagecreatefromstring((string)$returnValue);

        if (!$image) {
            throw new \RuntimeException('Can not create image from string');
        }

        $imgResized = imagescale($image, 100, 100);

        if (!$imgResized) {
            throw new \UnexpectedValueException('Can not resize image');
        }

        ob_start();
        imagejpeg($imgResized);
        $contents = ob_get_clean();

        if (!$contents) {
            throw new \UnexpectedValueException('Failed obtaining avatar');
        }

        $imageData = chunk_split(base64_encode($contents));

        $user->setAvatarEncoded('data:image/jpg;base64,'.$imageData);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this;
    }
}
