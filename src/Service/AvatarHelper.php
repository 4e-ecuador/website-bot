<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AvatarHelper
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function updateAvatar(User $user): self
    {
        $curl = curl_init($user->getAvatar());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $returnValue = curl_exec($curl);

        // TODO: error checking!!!
        curl_close($curl);

        $image = imagecreatefromstring($returnValue);

        $imgResized = imagescale($image, 100, 100);

        ob_start();
        imagejpeg($imgResized);
        $contents = ob_get_clean();

        $imageData = chunk_split(base64_encode($contents));

        $user->setAvatarEncoded('data:image/jpg;base64,'.$imageData);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this;
    }
}
